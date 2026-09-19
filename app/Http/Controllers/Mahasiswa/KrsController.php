<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Services\KrsService;
use App\Services\PaymentAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KrsController extends Controller
{
    protected $krsService;

    protected PaymentAccessService $paymentAccessService;

    public function __construct(
        KrsService $krsService,
        PaymentAccessService $paymentAccessService
    ) {
        $this->krsService = $krsService;
        $this->paymentAccessService = $paymentAccessService;
    }

    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (! $mahasiswa) {
            abort(403, 'Unauthorized');
        }

        // Check if student's payment is completed
        $paymentAccess = $this->paymentAccessService->checkKrsAccess($mahasiswa);
        if (! $paymentAccess['allowed']) {
            return view('mahasiswa.krs.index', [
                'isLocked' => true,
                'paymentAccess' => $paymentAccess,
                'unpaidPayment' => $paymentAccess['unpaid_payment'],
                'targetSemester' => $paymentAccess['semester'],
                'krs' => null,
                'availableKelas' => collect(),
            ]);
        }

        $krs = $this->krsService->getActiveKrsOrNew($mahasiswa);

        // Load available classes (that are not yet taken), grouped by semester
        // IMPORTANT: Only show kelas from mata kuliah of mahasiswa's prodi
        $availableKelas = Kelas::with(['mataKuliah', 'dosen.user', 'krsDetail'])
            ->whereHas('mataKuliah', fn ($q) => $q->where('prodi_id', $mahasiswa->prodi_id))
            ->whereDoesntHave('krsDetail', function ($q) use ($krs) {
                $q->where('krs_id', $krs->id);
            })
            ->get()
            ->groupBy(fn ($k) => 'Semester '.$k->mataKuliah->semester);

        // Sort by semester number
        $availableKelas = $availableKelas->sortKeys();

        return view('mahasiswa.krs.index', [
            'isLocked' => false,
            'krs' => $krs,
            'availableKelas' => $availableKelas,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['kelas_id' => 'required|exists:kelas,id']);

        $mahasiswa = Auth::user()->mahasiswa;
        $krs = $this->krsService->getActiveKrsOrNew($mahasiswa);

        try {
            $this->krsService->addKelas($krs, $request->kelas_id);

            return redirect()->back()->with('success', 'Kelas berhasil diambil');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy($detailId)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $krs = $this->krsService->getActiveKrsOrNew($mahasiswa);

        try {
            $this->krsService->removeKelas($krs, $detailId);

            return redirect()->back()->with('success', 'Kelas dibatalkan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function submit()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $krs = $this->krsService->getActiveKrsOrNew($mahasiswa);

        try {
            $this->krsService->submitKrs($krs);

            return redirect()->back()->with('success', 'KRS berhasil diajukan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function revise()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $krs = $this->krsService->getActiveKrsOrNew($mahasiswa);

        if ($krs->status !== 'rejected') {
            return redirect()->back()->with('error', 'Hanya KRS yang ditolak yang dapat direvisi');
        }

        $krs->update(['status' => 'draft', 'catatan' => null]);

        return redirect()->back()->with('success', 'KRS berhasil direset ke draft. Silakan edit dan ajukan kembali.');
    }
}
