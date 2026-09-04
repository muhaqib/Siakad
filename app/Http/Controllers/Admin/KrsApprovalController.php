<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use Illuminate\Http\Request;

class KrsApprovalController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        
        $krsList = Krs::with(['mahasiswa.user', 'mahasiswa.prodi.fakultas', 'tahunAkademik', 'krsDetail.kelas.mataKuliah'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status));

        // Faculty scoping for admin_fakultas
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasId = $request->get('fakultas_scope');
            $krsList->whereHas('mahasiswa.prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
        }

        // Sorting
        $sortColumn = $request->get('sort', 'updated_at');
        $sortDirection = $request->get('order', 'desc');

        if ($sortColumn === 'name') {
            $krsList = $krsList->join('mahasiswa', 'krs.mahasiswa_id', '=', 'mahasiswa.id')
                             ->join('users', 'mahasiswa.user_id', '=', 'users.id')
                             ->select('krs.*')
                             ->orderBy('users.name', $sortDirection);
        } elseif ($sortColumn === 'nim') {
            $krsList = $krsList->join('mahasiswa', 'krs.mahasiswa_id', '=', 'mahasiswa.id')
                             ->select('krs.*')
                             ->orderBy('mahasiswa.nim', $sortDirection);
        } elseif ($sortColumn === 'prodi') {
             $krsList = $krsList->join('mahasiswa', 'krs.mahasiswa_id', '=', 'mahasiswa.id')
                              ->join('prodi', 'mahasiswa.prodi_id', '=', 'prodi.id')
                              ->select('krs.*')
                              ->orderBy('prodi.nama', $sortDirection);
        } elseif ($sortColumn === 'status') {
             $krsList = $krsList->orderBy('status', $sortDirection);
        } else {
             $krsList = $krsList->orderBy('updated_at', 'desc');
        }

        $krsList = $krsList->paginate(config('siakad.pagination', 15))->withQueryString();

        // Status counts - also scoped for admin_fakultas
        $statusCountsQuery = Krs::query();
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasId = $request->get('fakultas_scope');
            $statusCountsQuery->whereHas('mahasiswa.prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
        }
        
        // Optimized: Single query for status counts using groupBy
        $statusCountsRaw = (clone $statusCountsQuery)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
        
        $statusCounts = [
            'pending' => $statusCountsRaw->get('pending', 0),
            'approved' => $statusCountsRaw->get('approved', 0),
            'rejected' => $statusCountsRaw->get('rejected', 0),
            'draft' => $statusCountsRaw->get('draft', 0),
        ];

        return view('admin.krs-approval.index', compact('krsList', 'status', 'statusCounts'));
    }


    public function show(Krs $krs)
    {
        $krs->load(['mahasiswa.user', 'mahasiswa.prodi', 'tahunAkademik', 'krsDetail.kelas.mataKuliah', 'krsDetail.kelas.dosen.user']);
        
        $totalSks = $krs->krsDetail->sum(fn($d) => $d->kelas->mataKuliah->sks);

        return view('admin.krs-approval.show', compact('krs', 'totalSks'));
    }

    public function approve(Request $request, Krs $krs)
    {
        if ($krs->status !== 'pending') {
            return redirect()->back()->with('error', 'KRS tidak dalam status pending');
        }

        $krs->update(['status' => 'approved']);

        return redirect()->route('admin.krs-approval.index')
            ->with('success', 'KRS mahasiswa ' . $krs->mahasiswa->user->name . ' berhasil disetujui');
    }

    public function reject(Request $request, Krs $krs)
    {
        if ($krs->status !== 'pending') {
            return redirect()->back()->with('error', 'KRS tidak dalam status pending');
        }

        $krs->update(['status' => 'rejected']);

        return redirect()->route('admin.krs-approval.index')
            ->with('success', 'KRS mahasiswa ' . $krs->mahasiswa->user->name . ' ditolak');
    }

    public function bulkApprove(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'krs_ids' => 'required|array|min:1',
            'krs_ids.*' => 'required|integer|exists:krs,id',
        ], [
            'krs_ids.required' => 'Pilih minimal satu KRS',
            'krs_ids.array' => 'Format data tidak valid',
            'krs_ids.min' => 'Pilih minimal satu KRS',
            'krs_ids.*.integer' => 'ID KRS tidak valid',
            'krs_ids.*.exists' => 'KRS tidak ditemukan',
        ]);

        $ids = $validated['krs_ids'];

        // Build query with fakultas scope for admin_fakultas
        $query = Krs::whereIn('id', $ids)->where('status', 'pending');
        
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasId = $request->get('fakultas_scope');
            $query->whereHas('mahasiswa.prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
        }

        $updatedCount = $query->update(['status' => 'approved']);

        if ($updatedCount === 0) {
            return redirect()->back()->with('warning', 'Tidak ada KRS yang dapat disetujui (mungkin sudah diproses atau di luar wewenang Anda)');
        }

        return redirect()->back()->with('success', $updatedCount . ' KRS berhasil disetujui');
    }
}
