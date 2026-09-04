<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\User;
use App\Models\Prodi;
use App\Models\Kelas;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DosenController extends Controller
{
    public function index(Request $request)
    {
        $query = Dosen::with(['user', 'prodi.fakultas'])
            ->withCount('kelas');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nidn', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by prodi
        if ($prodiId = $request->get('prodi_id')) {
            $query->where('prodi_id', $prodiId);
        }

        // Faculty scoping
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $query->whereHas('prodi', fn($q) => $q->where('fakultas_id', $request->get('fakultas_scope')));
        }

        // Sorting
        $sortColumn = $request->get('sort', 'nidn');
        $sortDirection = $request->get('order', 'asc');

        if ($sortColumn === 'name') {
            $query->join('users', 'dosen.user_id', '=', 'users.id')
                  ->select('dosen.*')
                  ->orderBy('users.name', $sortDirection);
        } elseif ($sortColumn === 'prodi') {
             $query->join('prodi', 'dosen.prodi_id', '=', 'prodi.id')
                   ->select('dosen.*')
                   ->orderBy('prodi.nama', $sortDirection);
        } else {
             $query->orderBy('nidn', $sortDirection);
        }

        $dosen = $query->paginate(config('siakad.pagination', 15))->withQueryString();
        
        // Prodi list scoped by faculty
        $prodiQuery = Prodi::with('fakultas');
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $prodiQuery->where('fakultas_id', $request->get('fakultas_scope'));
        }
        $prodiList = $prodiQuery->get();

        return view('admin.dosen.index', compact('dosen', 'prodiList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'nidn' => 'required|string|unique:dosen,nidn',
            'prodi_id' => 'required|exists:prodi,id',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'dosen',
            ]);

            Dosen::create([
                'user_id' => $user->id,
                'nidn' => $validated['nidn'],
                'prodi_id' => $validated['prodi_id'],
            ]);
        });

        return back()->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function update(Request $request, Dosen $dosen)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $dosen->user_id,
            'nidn' => 'required|string|unique:dosen,nidn,' . $dosen->id,
            'prodi_id' => 'required|exists:prodi,id',
            'password' => 'nullable|string|min:8',
        ]);

        DB::transaction(function () use ($validated, $dosen) {
            $dosen->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if (!empty($validated['password'])) {
                $dosen->user->update(['password' => Hash::make($validated['password'])]);
            }

            $dosen->update([
                'nidn' => $validated['nidn'],
                'prodi_id' => $validated['prodi_id'],
            ]);
        });

        return back()->with('success', 'Dosen berhasil diupdate.');
    }

    public function destroy(Dosen $dosen)
    {
        // Check if dosen has kelas
        if ($dosen->kelas()->exists()) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus dosen yang memiliki kelas.']);
        }

        // Check if dosen is PA for any mahasiswa
        if ($dosen->mahasiswaBimbingan()->exists()) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus dosen yang menjadi dosen PA.']);
        }

        DB::transaction(function () use ($dosen) {
            $userId = $dosen->user_id;
            $dosen->delete();
            User::destroy($userId);
        });

        return back()->with('success', 'Dosen berhasil dihapus.');
    }

    public function show(Dosen $dosen)
    {
        $dosen->load(['user', 'prodi.fakultas', 'kelas.mataKuliah', 'kelas.krsDetail']);
        
        // Paginate kelas (4 per page)
        $kelasIds = $dosen->kelas()->pluck('id');
        $teachingLoad = $dosen->kelas()->with(['mataKuliah', 'krsDetail'])->paginate(4);

        // Calculate totals for stats (based on all classes, not just paginated ones)
        $totalSks = $dosen->kelas->sum(fn($k) => $k->mataKuliah->sks);
        $totalStudents = \App\Models\KrsDetail::whereIn('kelas_id', $kelasIds)->count();

        return view('admin.dosen.show', compact('dosen', 'teachingLoad', 'totalSks', 'totalStudents'));
    }

    public function export(Request $request)
    {
        $query = Dosen::with(['user', 'prodi.fakultas'])->withCount('kelas');

        // Faculty scoping
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $query->whereHas('prodi', fn($q) => $q->where('fakultas_id', $request->get('fakultas_scope')));
        }

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nidn', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by prodi
        if ($prodiId = $request->get('prodi_id')) {
            $query->where('prodi_id', $prodiId);
        }

        $dosenList = $query->orderBy('nidn')->get();

        // Generate HTML table for export
        $html = '<table border="1" cellpadding="5" cellspacing="0">';
        $html .= '<thead><tr><th>No</th><th>NIDN</th><th>Nama</th><th>Email</th><th>Prodi</th><th>Fakultas</th><th>Jumlah Kelas</th></tr></thead>';
        $html .= '<tbody>';
        
        foreach ($dosenList as $idx => $dosen) {
            $html .= '<tr>';
            $html .= '<td>' . ($idx + 1) . '</td>';
            $html .= '<td>' . $dosen->nidn . '</td>';
            $html .= '<td>' . $dosen->user->name . '</td>';
            $html .= '<td>' . $dosen->user->email . '</td>';
            $html .= '<td>' . ($dosen->prodi->nama ?? '-') . '</td>';
            $html .= '<td>' . ($dosen->prodi->fakultas->nama ?? '-') . '</td>';
            $html .= '<td>' . $dosen->kelas_count . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="dosen_export_' . date('Y-m-d') . '.xls"');
    }
}

