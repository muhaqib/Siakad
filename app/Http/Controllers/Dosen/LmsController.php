<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\Auth;

class LmsController extends Controller
{
    /**
     * Show LMS dashboard with all kelas for dosen
     */
    public function index()
    {
        $dosen = Auth::user()->dosen;
        if (!$dosen) {
            abort(403, 'Anda tidak memiliki akses sebagai dosen.');
        }

        $activeTA = TahunAkademik::active();

        $kelasQuery = $dosen->kelas()
            ->with(['mataKuliah', 'tugas', 'jadwal.pertemuan.materiList']);
        
        if ($activeTA) {
            $kelasQuery->where(function($q) use ($activeTA) {
                $q->where('tahun_akademik_id', $activeTA->id)
                  ->orWhereNull('tahun_akademik_id');
            });
        }

        $kelasList = $kelasQuery->get()
            ->map(function($kelas) {
                $kelas->materi_count = $kelas->jadwal?->flatMap(fn($j) => $j->pertemuan)->flatMap(fn($p) => $p->materiList)->count() ?? 0;
                $kelas->tugas_count = $kelas->tugas->count();
                return $kelas;
            });

        return view('dosen.lms.index', compact('kelasList', 'activeTA'));
    }
}
