<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Services\AkademikCalculationService;
use App\Models\Krs;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected AkademikCalculationService $calculationService;

    public function __construct(AkademikCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    public function index()
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;
        
        if (!$mahasiswa) {
            abort(403, 'Unauthorized');
        }

        $mahasiswa->load(['prodi.fakultas', 'dosenPa.user']);

        // Calculate IPK
        $ipkData = $this->calculationService->calculateIPK($mahasiswa);
        
        // Get IPS History for chart
        $ipsHistory = $this->calculationService->getIPSHistory($mahasiswa);
        
        // Current semester IPS = last semester with actual grades (IPS > 0)
        $activeTA = TahunAkademik::where('is_active', true)->first();
        $semestersWithGrades = $ipsHistory->filter(fn($s) => $s['ips'] > 0);
        $lastSemesterWithGrades = $semestersWithGrades->last();
        $currentIps = $lastSemesterWithGrades ? ['ips' => $lastSemesterWithGrades['ips'], 'total_sks' => $lastSemesterWithGrades['total_sks']] : null;
        
        // Max SKS for next semester based on last IPS
        $lastIps = $lastSemesterWithGrades['ips'] ?? 0;
        $maxSks = $this->calculationService->getMaxSKS($lastIps);

        // SKS per semester for chart
        $sksHistory = $ipsHistory->map(fn($s) => [
            'semester' => $s['tahun_akademik'],
            'sks' => $s['total_sks'],
        ]);

        // Get current KRS status
        $currentKrs = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('tahun_akademik_id', $activeTA?->id)
            ->first();

        // Grade distribution
        $gradeDistribution = $this->calculationService->getGradeDistribution($mahasiswa);

        // Greeting based on time
        $hour = now()->hour;
        if ($hour < 11) {
            $greeting = 'Selamat Pagi';
        } elseif ($hour < 15) {
            $greeting = 'Selamat Siang';
        } elseif ($hour < 18) {
            $greeting = 'Selamat Sore';
        } else {
            $greeting = 'Selamat Malam';
        }

        return view('mahasiswa.dashboard.index', compact(
            'user', 'mahasiswa', 'ipkData', 'ipsHistory', 'currentIps',
            'maxSks', 'sksHistory', 'currentKrs', 'gradeDistribution', 
            'greeting', 'activeTA'
        ));
    }
}
