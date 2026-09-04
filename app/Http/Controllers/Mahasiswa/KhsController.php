<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use App\Models\Nilai;
use App\Models\TahunAkademik;
use App\Services\AkademikCalculationService;
use Illuminate\Support\Facades\Auth;

class KhsController extends Controller
{
    protected $calculationService;

    public function __construct(AkademikCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * Display list of semesters with KHS
     */
    public function index()
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;
        
        if (!$mahasiswa) {
            abort(403, 'Unauthorized');
        }

        // Get all semesters where student has KRS (oldest first for historical view)
        $semesterList = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'approved')
            ->with('tahunAkademik')
            ->orderBy('tahun_akademik_id', 'asc')
            ->get()
            ->map(function ($krs) use ($mahasiswa) {
                $ipsData = $this->calculationService->calculateIPS($mahasiswa, $krs->tahun_akademik_id);
                return [
                    'krs' => $krs,
                    'tahun_akademik' => $krs->tahunAkademik,
                    'ips' => $ipsData['ips'],
                    'total_sks' => $ipsData['total_sks'],
                    'jumlah_mk' => Nilai::where('mahasiswa_id', $mahasiswa->id)
                        ->whereHas('kelas', fn($q) => $q->whereHas('krsDetail.krs', fn($q2) => $q2->where('tahun_akademik_id', $krs->tahun_akademik_id)))
                        ->count(),
                ];
            });

        // Get current IPK
        $ipkData = $this->calculationService->calculateIPK($mahasiswa);

        return view('mahasiswa.khs.index', compact('mahasiswa', 'semesterList', 'ipkData'));
    }

    /**
     * Display KHS detail for a specific semester
     */
    public function show(TahunAkademik $tahunAkademik)
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;
        
        if (!$mahasiswa) {
            abort(403, 'Unauthorized');
        }

        // Check if student has approved KRS for this semester
        $krs = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('tahun_akademik_id', $tahunAkademik->id)
            ->where('status', 'approved')
            ->first();

        if (!$krs) {
            return redirect()->route('mahasiswa.khs.index')
                ->with('error', 'KRS untuk semester ini belum diapprove');
        }

        // Get all grades for this semester
        $nilaiList = Nilai::where('mahasiswa_id', $mahasiswa->id)
            ->whereHas('kelas', function ($q) use ($tahunAkademik) {
                $q->whereHas('krsDetail.krs', fn($q2) => $q2->where('tahun_akademik_id', $tahunAkademik->id));
            })
            ->with(['kelas.mataKuliah', 'kelas.dosen.user'])
            ->get()
            ->sortBy('kelas.mataKuliah.kode_mk');

        // Calculate IPS for this semester
        $ipsData = $this->calculationService->calculateIPS($mahasiswa, $tahunAkademik->id);

        // Get IPK cumulative
        $ipkData = $this->calculationService->calculateIPK($mahasiswa);

        // Grade distribution for this semester
        $gradeDistribution = $nilaiList->groupBy('nilai_huruf')
            ->map(fn($group) => $group->count())
            ->sortKeys();

        return view('mahasiswa.khs.show', compact(
            'mahasiswa', 'tahunAkademik', 'nilaiList', 'ipsData', 'ipkData', 'gradeDistribution'
        ));
    }
}
