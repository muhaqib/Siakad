<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PaymentHistory;
use App\Models\StudentPayment;
use App\Services\PaymentAccessService;
use App\Services\PaymentInitializationService;
use App\Services\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentAccessService $paymentAccessService,
        protected PaymentInitializationService $initializationService,
        protected PaymentService $paymentService
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

        $recentTransactions = $this->paymentService->getTransactionHistory($mahasiswa);

        return view('mahasiswa.payments.index', compact(
            'mahasiswa',
            'payments',
            'recentTransactions',
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

        if ((float) $payment->paid_amount <= 0 && $payment->status === 'unpaid') {
            return redirect()->back()->with('error', 'Kwitansi hanya dapat dicetak untuk pembayaran yang telah memiliki transaksi setoran.');
        }

        $payment->load(['mahasiswa.user', 'mahasiswa.prodi.fakultas', 'paymentType', 'tahunAkademik', 'confirmedBy']);

        $receiptData = $this->paymentService->getReceiptData($mahasiswa, $payment, request('ref'));

        $pdf = Pdf::loadView('mahasiswa.payments.receipt', $receiptData)->setPaper('a5', 'landscape');

        $filename = 'Kwitansi_'.str_replace('/', '_', $receiptData['nomorBukti'] ?? $payment->invoice_number).'.pdf';

        if (request()->has('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    public function transactionReceipt(string $reference)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (! $mahasiswa) {
            abort(403, 'Unauthorized');
        }

        $history = PaymentHistory::where('reference_number', $reference)
            ->whereHas('payment', fn ($q) => $q->where('mahasiswa_id', $mahasiswa->id))
            ->with(['payment.mahasiswa.user', 'payment.mahasiswa.prodi.fakultas'])
            ->first();

        if (! $history) {
            $payment = StudentPayment::where('mahasiswa_id', $mahasiswa->id)
                ->where(fn ($q) => $q->where('invoice_number', $reference)->orWhere('midtrans_order_id', $reference))
                ->with(['mahasiswa.user', 'mahasiswa.prodi.fakultas'])
                ->first();

            if (! $payment) {
                abort(404, 'Transaksi pembayaran tidak ditemukan.');
            }

            return $this->receipt($payment);
        }

        $receiptData = $this->paymentService->getReceiptData($mahasiswa, null, $reference);

        $pdf = Pdf::loadView('mahasiswa.payments.receipt', $receiptData)->setPaper('a5', 'landscape');
        $filename = 'Kwitansi_'.str_replace('/', '_', $reference).'.pdf';

        if (request()->has('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * Konversi angka nominal ke terbilang bahasa Indonesia.
     */
    private function terbilang(int $angka): string
    {
        $angka = abs($angka);
        $baca = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $hasil = '';

        if ($angka < 12) {
            $hasil = ' '.$baca[$angka];
        } elseif ($angka < 20) {
            $hasil = $this->terbilang($angka - 10).' Belas';
        } elseif ($angka < 100) {
            $hasil = $this->terbilang((int) ($angka / 10)).' Puluh '.$this->terbilang($angka % 10);
        } elseif ($angka < 200) {
            $hasil = ' Seratus '.$this->terbilang($angka - 100);
        } elseif ($angka < 1000) {
            $hasil = $this->terbilang((int) ($angka / 100)).' Ratus '.$this->terbilang($angka % 100);
        } elseif ($angka < 2000) {
            $hasil = ' Seribu '.$this->terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            $hasil = $this->terbilang((int) ($angka / 1000)).' Ribu '.$this->terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            $hasil = $this->terbilang((int) ($angka / 1000000)).' Juta '.$this->terbilang($angka % 1000000);
        } elseif ($angka < 1000000000000) {
            $hasil = $this->terbilang((int) ($angka / 1000000000)).' Miliar '.$this->terbilang($angka % 1000000000);
        }

        return trim(preg_replace('/\s+/', ' ', $hasil));
    }
}
