<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KehadiranDosen;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KehadiranDosenController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $dosenId = $request->get('dosen_id');

        $query = KehadiranDosen::with(['dosen.user', 'dosen.prodi', 'jadwalKuliah.kelas.mataKuliah'])
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month);

        if ($dosenId) {
            $query->where('dosen_id', $dosenId);
        }

        // Faculty scoping for admin_fakultas
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasId = $request->get('fakultas_scope');
            $query->whereHas('dosen.prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
        }

        $kehadiranList = $query->orderBy('tanggal', 'desc')->paginate(50);

        // Stats - also scoped
        $statsQuery = KehadiranDosen::whereYear('tanggal', $year)->whereMonth('tanggal', $month);
        if ($dosenId) $statsQuery->where('dosen_id', $dosenId);
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $statsQuery->whereHas('dosen.prodi', fn($q) => $q->where('fakultas_id', $request->get('fakultas_scope')));
        }
        
        $stats = $statsQuery->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Scope dosen list for dropdown
        $dosenQuery = Dosen::with('user');
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $dosenQuery->whereHas('prodi', fn($q) => $q->where('fakultas_id', $request->get('fakultas_scope')));
        }
        $dosenList = $dosenQuery->get();

        // Rekap per dosen - also scoped
        $rekapQuery = KehadiranDosen::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month);
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $rekapQuery->whereHas('dosen.prodi', fn($q) => $q->where('fakultas_id', $request->get('fakultas_scope')));
        }
        $rekapDosen = $rekapQuery->selectRaw('dosen_id, status, COUNT(*) as count')
            ->groupBy('dosen_id', 'status')
            ->get()
            ->groupBy('dosen_id');

        return view('admin.kehadiran-dosen.index', compact('kehadiranList', 'stats', 'dosenList', 'rekapDosen', 'month', 'year', 'dosenId'));
    }


    public function show(Dosen $dosen, Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $kehadiranList = KehadiranDosen::where('dosen_id', $dosen->id)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->with('jadwalKuliah.kelas.mataKuliah')
            ->orderBy('tanggal', 'desc')
            ->get();

        $stats = $kehadiranList->groupBy('status')->map->count();

        return view('admin.kehadiran-dosen.show', compact('dosen', 'kehadiranList', 'stats', 'month', 'year'));
    }
}
