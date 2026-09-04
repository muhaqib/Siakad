<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BimbinganController extends Controller
{
    /**
     * Daftar mahasiswa bimbingan
     */
    public function index(Request $request)
    {
        $dosen = Auth::user()->dosen;
        
        if (!$dosen) {
            abort(403, 'Unauthorized');
        }

        $query = $dosen->mahasiswaBimbingan()
            ->with(['user', 'prodi', 'krs' => fn($q) => $q->latest()]);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter angkatan
        if ($request->filled('angkatan')) {
            $query->where('angkatan', $request->angkatan);
        }

        // Sorting
        $sortBy = $request->get('sort', 'angkatan');
        $sortDir = $request->get('dir', 'desc');
        
        // Validate sort direction
        $sortDir = in_array($sortDir, ['asc', 'desc']) ? $sortDir : 'desc';
        
        // Apply sorting
        if ($sortBy === 'nama') {
            // Sort by user name using join
            $query->join('users', 'mahasiswa.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortDir)
                  ->select('mahasiswa.*');
        } elseif (in_array($sortBy, ['nim', 'angkatan', 'status'])) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('angkatan', 'desc');
        }

        $mahasiswaBimbingan = $query->paginate(10)->withQueryString();

        // Get available angkatan for filter
        $angkatanList = $dosen->mahasiswaBimbingan()->distinct()->pluck('angkatan')->sort()->reverse();

        if ($request->ajax()) {
            return view('dosen.bimbingan._table', compact('mahasiswaBimbingan'))->render();
        }

        return view('dosen.bimbingan.index', compact('mahasiswaBimbingan', 'angkatanList'));
    }

    /**
     * Daftar KRS yang perlu diapprove
     */
    public function krsApproval(Request $request)
    {
        $dosen = Auth::user()->dosen;
        
        if (!$dosen) {
            abort(403, 'Unauthorized');
        }

        $status = $request->get('status', 'pending');

        // Get KRS from mahasiswa bimbingan
        $mahasiswaIds = $dosen->mahasiswaBimbingan()->pluck('id');

        $query = Krs::with(['mahasiswa.user', 'mahasiswa.prodi', 'tahunAkademik', 'krsDetail.kelas.mataKuliah'])
            ->whereIn('krs.mahasiswa_id', $mahasiswaIds)
            ->when($status !== 'all', fn($q) => $q->where('krs.status', $status));

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('mahasiswa', function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        // Sorting
        $sortBy = $request->get('sort', 'updated_at');
        $sortDir = $request->get('dir', 'desc');
        $sortDir = in_array($sortDir, ['asc', 'desc']) ? $sortDir : 'desc';

        if ($sortBy === 'nama') {
            $query->join('mahasiswa', 'krs.mahasiswa_id', '=', 'mahasiswa.id')
                  ->join('users', 'mahasiswa.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortDir)
                  ->select('krs.*');
        } elseif ($sortBy === 'nim') {
            $query->join('mahasiswa', 'krs.mahasiswa_id', '=', 'mahasiswa.id')
                  ->orderBy('mahasiswa.nim', $sortDir)
                  ->select('krs.*');
        } elseif ($sortBy === 'status') {
            $query->orderBy('krs.status', $sortDir);
        } else {
            $query->orderBy('krs.updated_at', 'desc');
        }

        $krsList = $query->paginate(config('siakad.pagination', 15))->withQueryString();

        // Optimized: Single query for status counts using groupBy
        $statusCountsRaw = Krs::whereIn('mahasiswa_id', $mahasiswaIds)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $statusCounts = [
            'pending' => $statusCountsRaw->get('pending', 0),
            'approved' => $statusCountsRaw->get('approved', 0),
            'rejected' => $statusCountsRaw->get('rejected', 0),
        ];

        if ($request->ajax()) {
            return view('dosen.bimbingan._krs-table', compact('krsList'))->render();
        }

        return view('dosen.bimbingan.krs-approval', compact('krsList', 'status', 'statusCounts'));
    }

    /**
     * Detail KRS
     */
    public function showKrs(Krs $krs)
    {
        $dosen = Auth::user()->dosen;
        
        // Verify this mahasiswa is under this dosen's supervision
        if ($krs->mahasiswa->dosen_pa_id !== $dosen->id) {
            abort(403, 'Anda tidak berhak mengakses KRS ini');
        }

        $krs->load(['mahasiswa.user', 'mahasiswa.prodi', 'tahunAkademik', 'krsDetail.kelas.mataKuliah', 'krsDetail.kelas.dosen.user']);
        
        $totalSks = $krs->krsDetail->sum(fn($d) => $d->kelas->mataKuliah->sks);

        return view('dosen.bimbingan.krs-show', compact('krs', 'totalSks'));
    }

    /**
     * Approve KRS
     */
    public function approveKrs(Krs $krs)
    {
        $dosen = Auth::user()->dosen;
        
        if ($krs->mahasiswa->dosen_pa_id !== $dosen->id) {
            abort(403, 'Anda tidak berhak mengakses KRS ini');
        }

        if ($krs->status !== 'pending') {
            return redirect()->back()->with('error', 'KRS tidak dalam status pending');
        }

        $krs->update(['status' => 'approved']);

        return redirect()->route('dosen.bimbingan.krs-approval')
            ->with('success', 'KRS mahasiswa ' . $krs->mahasiswa->user->name . ' berhasil disetujui');
    }

    /**
     * Reject KRS
     */
    public function rejectKrs(Request $request, Krs $krs)
    {
        $dosen = Auth::user()->dosen;
        
        if ($krs->mahasiswa->dosen_pa_id !== $dosen->id) {
            abort(403, 'Anda tidak berhak mengakses KRS ini');
        }

        if ($krs->status !== 'pending') {
            return redirect()->back()->with('error', 'KRS tidak dalam status pending');
        }

        $catatan = $request->input('catatan', 'KRS ditolak oleh Dosen PA. Silakan revisi dan ajukan kembali.');
        $krs->update(['status' => 'rejected', 'catatan' => $catatan]);

        return redirect()->route('dosen.bimbingan.krs-approval')
            ->with('success', 'KRS mahasiswa ' . $krs->mahasiswa->user->name . ' ditolak');
    }
}
