<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\StudentPayment;
use App\Services\PaymentAccessService;
use App\Services\PaymentInitializationService;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentAccessService $paymentAccessService,
        protected PaymentInitializationService $initializationService
    ) {}

    public function index()
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;
        if (! $mahasiswa) {
            abort(403, 'Akses khusus mahasiswa.');
        }

        // Auto-initialize payments if student has none yet
        if ($mahasiswa->payments()->count() === 0) {
            $this->initializationService->initializeStudentPayments($mahasiswa);
        }

        $payments = StudentPayment::with(['paymentType', 'tahunAkademik', 'confirmedBy'])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->join('payment_types', 'student_payments.payment_type_id', '=', 'payment_types.id')
            ->orderBy('payment_types.id', 'asc')
            ->select('student_payments.*')
            ->get();

        $totalKewajiban = $payments->sum('amount');
        $totalDibayar = $payments->where('status', 'paid')->sum('paid_amount');
        $totalTunggakan = $payments->where('status', '!=', 'paid')->sum('amount');

        $activeSemester = $this->paymentAccessService->determineStudentSemester($mahasiswa);
        $krsAccess = $this->paymentAccessService->checkKrsAccess($mahasiswa, $activeSemester);
        $currentSemesterPayment = $this->paymentAccessService->getPaymentStatus($mahasiswa, $activeSemester);

        return view('mahasiswa.payments.index', compact(
            'mahasiswa',
            'payments',
            'totalKewajiban',
            'totalDibayar',
            'totalTunggakan',
            'activeSemester',
            'krsAccess',
            'currentSemesterPayment'
        ));
    }

    public function show(StudentPayment $payment)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (! $mahasiswa || $payment->mahasiswa_id !== $mahasiswa->id) {
            abort(403, 'Unauthorized');
        }

        $payment->load(['paymentType', 'tahunAkademik', 'confirmedBy', 'histories.performer']);

        return view('mahasiswa.payments.show', compact('payment', 'mahasiswa'));
    }

    public function receipt(StudentPayment $payment)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (! $mahasiswa || $payment->mahasiswa_id !== $mahasiswa->id) {
            abort(403, 'Unauthorized');
        }

        if (! $payment->isPaid()) {
            return redirect()->back()->with('error', 'Kwitansi hanya dapat dicetak untuk pembayaran yang telah lunas.');
        }

        $payment->load(['mahasiswa.user', 'mahasiswa.prodi.fakultas', 'paymentType', 'confirmedBy']);

        return view('mahasiswa.payments.receipt', compact('payment', 'mahasiswa'));
    }
}
