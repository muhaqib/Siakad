<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\StudentPayment;
use App\Services\PaymentAccessService;
use App\Services\PaymentInitializationService;
use Barryvdh\DomPDF\Facade\Pdf;
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

        $recentTransactions = StudentPayment::with(['paymentType', 'confirmedBy'])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where(function ($q) {
                $q->where('status', 'paid')
                    ->orWhere('status', 'pending')
                    ->orWhere('paid_amount', '>', 0);
            })
            ->orderBy('updated_at', 'desc')
            ->get();

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

        if (! $payment->isPaid()) {
            return redirect()->back()->with('error', 'Kwitansi hanya dapat dicetak untuk pembayaran yang telah lunas.');
        }

        $payment->load(['mahasiswa.user', 'mahasiswa.prodi.fakultas', 'paymentType', 'tahunAkademik', 'confirmedBy']);

        $amountPaid = (int) ($payment->paid_amount > 0 ? $payment->paid_amount : $payment->amount);
        $terbilang = $this->terbilang($amountPaid);

        // Logo Base64 (mengutamakan kwitansi_logo.png yang diekstrak dari kwitansi.pdf, fallback logo.PNG)
        $logoPath = public_path('kwitansi_logo.png');
        if (! file_exists($logoPath)) {
            $logoPath = public_path('logo.PNG');
        }
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : null;

        // QR Code Base64
        $qrPath = public_path('default_qr.png');
        $qrBase64 = file_exists($qrPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($qrPath))
            : null;

        $tahunAkademik = $payment->tahunAkademik
            ? $payment->tahunAkademik->tahun.' '.$payment->tahunAkademik->semester
            : '2026 / 2027 Akhir';

        $tanggalPembayaran = $payment->payment_date
            ? $payment->payment_date->translatedFormat('d F Y')
            : ($payment->confirmed_at ? $payment->confirmed_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y'));

        $tanggalCetak = now()->translatedFormat('d F Y');

        $pdf = Pdf::loadView('mahasiswa.payments.receipt', compact(
            'payment',
            'mahasiswa',
            'terbilang',
            'logoBase64',
            'qrBase64',
            'tahunAkademik',
            'tanggalPembayaran',
            'tanggalCetak'
        ))->setPaper('a5', 'landscape');

        $filename = 'Kwitansi_'.str_replace('/', '_', $payment->invoice_number).'.pdf';

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
