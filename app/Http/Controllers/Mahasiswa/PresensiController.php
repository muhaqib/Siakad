<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Presensi;
use App\Services\PresensiService;
use Illuminate\Support\Facades\Auth;

class PresensiController extends Controller
{
    protected PresensiService $presensiService;

    public function __construct(PresensiService $presensiService)
    {
        $this->presensiService = $presensiService;
    }

    /**
     * Rekap presensi semua mata kuliah
     */
    public function index()
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;
        
        if (!$mahasiswa) {
            abort(403, 'Unauthorized');
        }

        // Get all kelas from approved KRS with tahun akademik info
        $kelasList = Kelas::whereHas('krsDetail', function ($q) use ($mahasiswa) {
            $q->whereHas('krs', fn($q2) => $q2
                ->where('mahasiswa_id', $mahasiswa->id)
                ->where('status', 'approved')
            );
        })->with(['mataKuliah', 'dosen.user', 'jadwal', 'krsDetail.krs.tahunAkademik'])->get();

        // Get rekap for each kelas and group by semester
        $rekapList = $kelasList->map(function ($kelas) use ($mahasiswa) {
            $krs = $kelas->krsDetail->first()?->krs;
            $ta = $krs?->tahunAkademik;
            return [
                'kelas' => $kelas,
                'rekap' => $this->presensiService->getRekapPresensi($mahasiswa->id, $kelas->id),
                'semester' => $ta ? $ta->tahun . ' ' . ucfirst($ta->semester) : 'Unknown',
                'semester_order' => $ta ? $ta->id : 0,
            ];
        });

        // Group by semester - sort by semester_order desc first so latest semester is first
        $rekapBySemester = $rekapList->sortByDesc('semester_order')->groupBy('semester');

        return view('mahasiswa.presensi.index', compact('mahasiswa', 'rekapList', 'rekapBySemester'));
    }

    /**
     * Detail presensi per kelas
     */
    public function show(Kelas $kelas)
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;
        
        if (!$mahasiswa) {
            abort(403, 'Unauthorized');
        }

        // Verify mahasiswa is enrolled in this kelas
        $isEnrolled = $kelas->krsDetail()
            ->whereHas('krs', fn($q) => $q
                ->where('mahasiswa_id', $mahasiswa->id)
                ->where('status', 'approved')
            )->exists();

        if (!$isEnrolled) {
            abort(403, 'Anda tidak terdaftar di kelas ini');
        }

        $kelas->load(['mataKuliah', 'dosen.user']);
        
        // Get pertemuan list with presensi
        $pertemuanList = $this->presensiService->getPertemuanByKelas($kelas->id);
        
        // Get presensi for this mahasiswa
        $presensiData = Presensi::where('mahasiswa_id', $mahasiswa->id)
            ->whereIn('pertemuan_id', $pertemuanList->pluck('id'))
            ->get()
            ->keyBy('pertemuan_id');

        $rekap = $this->presensiService->getRekapPresensi($mahasiswa->id, $kelas->id);

        return view('mahasiswa.presensi.show', compact('kelas', 'mahasiswa', 'pertemuanList', 'presensiData', 'rekap'));
    }
}
