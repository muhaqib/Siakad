<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\Nilai;
use App\Models\TahunAkademik;
use App\Services\AkademikCalculationService;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    protected $calculationService;

    public function __construct(AkademikCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * Export Transkrip as printable HTML (PDF-ready)
     */
    public function transkrip()
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (! $mahasiswa) {
            abort(403, 'Unauthorized');
        }

        $mahasiswa->load(['prodi.fakultas', 'dosenPa.user']);

        // Get all nilai
        $nilaiList = Nilai::where('mahasiswa_id', $mahasiswa->id)
            ->with('kelas.mataKuliah')
            ->get()
            ->sortBy('kelas.mataKuliah.kode_mk');

        // Calculate IPK
        $ipkData = $this->calculationService->calculateIPK($mahasiswa);

        // Get grade distribution
        $gradeDistribution = $this->calculationService->getGradeDistribution($mahasiswa);

        return view('mahasiswa.export.transkrip', compact('mahasiswa', 'nilaiList', 'ipkData', 'gradeDistribution'));
    }

    /**
     * Export KHS as printable HTML (PDF-ready)
     */
    public function khs(TahunAkademik $tahunAkademik)
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (! $mahasiswa) {
            abort(403, 'Unauthorized');
        }

        $mahasiswa->load(['prodi.fakultas', 'dosenPa.user']);

        // Check if student has approved KRS for this semester
        $krs = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('tahun_akademik_id', $tahunAkademik->id)
            ->where('status', 'approved')
            ->first();

        if (! $krs) {
            return redirect()->back()->with('error', 'KRS untuk semester ini belum diapprove');
        }

        // Get all grades for this semester
        $nilaiList = Nilai::where('mahasiswa_id', $mahasiswa->id)
            ->whereHas('kelas', function ($q) use ($tahunAkademik) {
                $q->whereHas('krsDetail.krs', fn ($q2) => $q2->where('tahun_akademik_id', $tahunAkademik->id));
            })
            ->with(['kelas.mataKuliah', 'kelas.dosen.user'])
            ->get()
            ->sortBy('kelas.mataKuliah.kode_mk');

        // Calculate IPS for this semester
        $ipsData = $this->calculationService->calculateIPS($mahasiswa, $tahunAkademik->id);

        // Get IPK cumulative
        $ipkData = $this->calculationService->calculateIPK($mahasiswa);

        return view('mahasiswa.export.khs', compact('mahasiswa', 'tahunAkademik', 'nilaiList', 'ipsData', 'ipkData'));
    }

    /**
     * Export Jadwal Kuliah as printable HTML (PDF-ready)
     */
    public function jadwal()
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (! $mahasiswa) {
            abort(403, 'Unauthorized');
        }

        $mahasiswa->load(['prodi.fakultas', 'dosenPa.user']);
        $activeTA = TahunAkademik::where('is_active', true)->first();

        if (! $activeTA) {
            return redirect()->back()->with('error', 'Tidak ada tahun akademik aktif');
        }

        // Get approved classes
        $kelasList = Kelas::whereHas('krsDetail', function ($q) use ($mahasiswa, $activeTA) {
            $q->whereHas('krs', fn ($q2) => $q2
                ->where('mahasiswa_id', $mahasiswa->id)
                ->where('tahun_akademik_id', $activeTA->id)
                ->where('status', 'approved')
            );
        })->with(['mataKuliah', 'dosen.user', 'jadwal'])->get();

        $hariOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Ahad'];
        $jadwalPerHari = collect();
        $totalSks = $kelasList->sum(fn ($k) => $k->mataKuliah?->sks ?? 0);

        foreach ($hariOrder as $hari) {
            $jadwalHariIni = collect();
            $aliasHari = $hari === 'Ahad' ? ['Ahad', 'Minggu'] : [$hari];

            foreach ($kelasList as $kelas) {
                foreach ($kelas->jadwal as $jadwal) {
                    if (in_array($jadwal->hari, $aliasHari)) {
                        $jadwalHariIni->push([
                            'kelas' => $kelas,
                            'jadwal' => $jadwal,
                        ]);
                    }
                }
            }

            if ($jadwalHariIni->isNotEmpty()) {
                $jadwalPerHari[$hari] = $jadwalHariIni->sortBy(fn ($j) => $j['jadwal']->jam_mulai)->values();
            }
        }

        return view('mahasiswa.export.jadwal', compact('mahasiswa', 'activeTA', 'jadwalPerHari', 'kelasList', 'totalSks'));
    }
}
