<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Services\AkademikCalculationService;
use Illuminate\Support\Facades\Auth;

class TranskripController extends Controller
{
    protected AkademikCalculationService $calculationService;

    public function __construct(AkademikCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            abort(403, 'Unauthorized');
        }

        $transcript = $this->calculationService->getTranscript($mahasiswa);
        $ipsHistory = $this->calculationService->getIPSHistory($mahasiswa);
        $gradeDistribution = $this->calculationService->getGradeDistribution($mahasiswa);
        
        // Calculate max SKS for next semester (use last semester WITH grades)
        $semestersWithGrades = $ipsHistory->filter(fn($s) => $s['ips'] > 0);
        $lastIps = $semestersWithGrades->last()['ips'] ?? 0;
        $maxSks = $this->calculationService->getMaxSKS($lastIps);

        return view('mahasiswa.transkrip.index', compact(
            'transcript', 'ipsHistory', 'gradeDistribution', 'maxSks', 'mahasiswa'
        ));
    }
}
