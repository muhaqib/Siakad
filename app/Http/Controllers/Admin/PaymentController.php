<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\StudentPayment;
use App\Services\PaymentAccessService;
use App\Services\PaymentInitializationService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected PaymentAccessService $paymentAccessService,
        protected PaymentInitializationService $initializationService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $fakultasId = $user->isSuperAdmin() ? $request->get('fakultas_id') : $user->fakultas_id;

        $query = Mahasiswa::with([
            'user',
            'prodi.fakultas',
            'payments.paymentType',
        ]);

        if ($fakultasId) {
            $query->whereHas('prodi', fn ($q) => $q->where('fakultas_id', $fakultasId));
        }

        // Search: NIM, Nama Mahasiswa, Email
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Status Filter
        $status = $request->get('status');
        if ($status === 'unpaid' || $status === 'debt') {
            $query->whereHas('payments', fn ($q) => $q->whereIn('status', ['unpaid', 'partial']));
        } elseif ($status === 'partial') {
            $query->whereHas('payments', fn ($q) => $q->where('status', 'partial'));
        } elseif ($status === 'paid') {
            $query->whereDoesntHave('payments', fn ($q) => $q->whereIn('status', ['unpaid', 'partial']))
                ->whereHas('payments');
        }

        if ($prodiId = $request->get('prodi_id')) {
            $query->where('prodi_id', $prodiId);
        }

        if ($angkatan = $request->get('angkatan')) {
            $query->where('angkatan', $angkatan);
        }

        $mahasiswaList = $query->orderBy('nim', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Calculate summary and next payment for each student
        $mahasiswaList->getCollection()->transform(function ($m) {
            if ($m->payments->isEmpty()) {
                $this->initializationService->initializeStudentPayments($m);
                $m->load('payments.paymentType');
            }

            $totalKewajiban = (float) $m->payments->sum('amount');
            $totalDibayar = (float) $m->payments->sum('paid_amount');
            $sisaTunggakan = max(0, $totalKewajiban - $totalDibayar);
            $persenLunas = $totalKewajiban > 0 ? min(100, (int) round(($totalDibayar / $totalKewajiban) * 100)) : 0;
            $unpaidCount = $m->payments->whereIn('status', ['unpaid', 'partial'])->count();
            $partialCount = $m->payments->where('status', 'partial')->count();
            $paidCount = $m->payments->where('status', 'paid')->count();
            $nextPayment = $this->paymentAccessService->getNextPaymentToPay($m);

            $m->total_kewajiban = $totalKewajiban;
            $m->total_dibayar = $totalDibayar;
            $m->sisa_tunggakan = $sisaTunggakan;
            $m->persen_lunas = $persenLunas;
            $m->unpaid_count = $unpaidCount;
            $m->partial_count = $partialCount;
            $m->paid_count = $paidCount;
            $m->next_payment = $nextPayment;

            return $m;
        });

        // Option lists for filter
        $fakultasList = $user->isSuperAdmin() ? Fakultas::orderBy('nama')->get() : collect();
        $prodiQuery = Prodi::query();
        if ($fakultasId) {
            $prodiQuery->where('fakultas_id', $fakultasId);
        }
        $prodiList = $prodiQuery->orderBy('nama')->get();
        $angkatanList = Mahasiswa::distinct()->whereNotNull('angkatan')->pluck('angkatan')->sort()->reverse();

        return view('admin.payments.index', compact(
            'mahasiswaList',
            'fakultasList',
            'prodiList',
            'angkatanList',
            'user'
        ));
    }

    public function studentPayments(Mahasiswa $mahasiswa)
    {
        $user = Auth::user();
        $this->paymentService->authorizeStudentAccess($mahasiswa, $user);

        // Auto-initialize if empty
        if ($mahasiswa->payments()->count() === 0) {
            $this->initializationService->initializeStudentPayments($mahasiswa);
        }

        $mahasiswa->load([
            'user',
            'prodi.fakultas',
            'dosenPa.user',
        ]);

        $payments = $this->paymentAccessService->getOrderedPayments($mahasiswa);
        $payments->loadMissing([
            'paymentType',
            'tahunAkademik',
            'confirmedBy',
            'histories.performer',
        ]);

        // Attach sequence prerequisite and check info
        $payments->each(function ($p) {
            $unpaidPrereq = $this->paymentAccessService->getUnpaidPrerequisite($p);
            $check = $this->paymentAccessService->canExecutePayment($p);
            $p->unpaid_prereq = $unpaidPrereq;
            $p->is_ready = ($unpaidPrereq === null && ! $p->isPaid());
            $p->execution_check = $check;
        });

        $activeSemester = $this->paymentAccessService->determineStudentSemester($mahasiswa);
        $krsAccess = $this->paymentAccessService->checkKrsAccess($mahasiswa, $activeSemester);

        $totalKewajiban = (float) $payments->sum('amount');
        $totalDibayar = (float) $payments->sum('paid_amount');
        $sisaTunggakan = max(0, $totalKewajiban - $totalDibayar);
        $persenLunas = $totalKewajiban > 0 ? min(100, (int) round(($totalDibayar / $totalKewajiban) * 100)) : 0;

        return view('admin.payments.student', compact(
            'mahasiswa',
            'payments',
            'activeSemester',
            'krsAccess',
            'totalKewajiban',
            'totalDibayar',
            'sisaTunggakan',
            'persenLunas',
            'user'
        ));
    }

    public function show(StudentPayment $payment)
    {
        $user = Auth::user();
        $this->paymentService->authorizePaymentAccess($payment, $user);

        $payment->load([
            'mahasiswa.user',
            'mahasiswa.prodi.fakultas',
            'paymentType',
            'tahunAkademik',
            'confirmedBy',
            'histories.performer',
        ]);

        $unpaidPrereq = $this->paymentAccessService->getUnpaidPrerequisite($payment);
        $check = $this->paymentAccessService->canExecutePayment($payment);

        return view('admin.payments.show', compact('payment', 'user', 'unpaidPrereq', 'check'));
    }

    public function confirm(Request $request, StudentPayment $payment)
    {
        $user = Auth::user();
        $this->paymentService->authorizePaymentAccess($payment, $user);

        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:Tunai,Transfer,Lainnya,QRIS,Virtual Account',
            'paid_amount' => 'nullable|numeric|min:0',
            'pay_amount' => 'nullable|numeric|min:1',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $updated = $this->paymentService->confirmPayment($payment, $validated, $user);

            if ($updated->isPaid()) {
                $msg = "Pembayaran {$payment->paymentType->name} mahasiswa {$payment->mahasiswa->nim} berhasil dikonfirmasi lunas.";
            } else {
                $sisaFormatted = number_format($updated->remaining_amount, 0, ',', '.');
                $msg = "Setoran cicilan {$payment->paymentType->name} mahasiswa {$payment->mahasiswa->nim} berhasil diproses. Sisa tagihan: Rp {$sisaFormatted}.";
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function receipt(StudentPayment $payment)
    {
        $user = Auth::user();
        $this->paymentService->authorizePaymentAccess($payment, $user);

        if ((float) $payment->paid_amount <= 0 && $payment->status === 'unpaid') {
            return redirect()->back()->with('error', 'Kwitansi hanya dapat dicetak untuk pembayaran yang telah memiliki transaksi setoran.');
        }

        $payment->load([
            'mahasiswa.user',
            'mahasiswa.prodi.fakultas',
            'paymentType',
            'confirmedBy',
            'histories.performer',
        ]);

        return view('admin.payments.receipt', compact('payment', 'user'));
    }

    public function cancel(Request $request, StudentPayment $payment)
    {
        $user = Auth::user();
        $this->paymentService->authorizePaymentAccess($payment, $user);

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->paymentService->cancelPayment($payment, $validated['reason'], $user);

            return redirect()->back()->with('success', 'Konfirmasi pembayaran berhasil dibatalkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function history(StudentPayment $payment)
    {
        $user = Auth::user();
        $this->paymentService->authorizePaymentAccess($payment, $user);

        $payment->load([
            'mahasiswa.user',
            'mahasiswa.prodi.fakultas',
            'paymentType',
            'histories.performer',
        ]);

        return view('admin.payments.history', compact('payment', 'user'));
    }

    public function bulkGenerate(Request $request)
    {
        $user = Auth::user();
        $fakultasId = $user->isSuperAdmin() ? $request->get('fakultas_id') : $user->fakultas_id;

        $filters = [
            'fakultas_id' => $fakultasId,
            'prodi_id' => $request->get('prodi_id'),
            'angkatan' => $request->get('angkatan'),
        ];

        try {
            $totalCreated = $this->paymentService->bulkGeneratePayments($filters);

            return redirect()->back()->with('success', "Berhasil membuat {$totalCreated} tagihan pembayaran baru.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat tagihan: '.$e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $fakultasId = $user->isSuperAdmin() ? $request->get('fakultas_id') : $user->fakultas_id;

        $query = StudentPayment::with([
            'mahasiswa.user',
            'mahasiswa.prodi.fakultas',
            'paymentType',
            'confirmedBy',
        ]);

        if ($fakultasId) {
            $query->forFakultas($fakultasId);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($prodiId = $request->get('prodi_id')) {
            $query->whereHas('mahasiswa', fn ($q) => $q->where('prodi_id', $prodiId));
        }

        if ($angkatan = $request->get('angkatan')) {
            $query->whereHas('mahasiswa', fn ($q) => $q->where('angkatan', $angkatan));
        }

        if ($paymentTypeId = $request->get('payment_type_id')) {
            $query->where('payment_type_id', $paymentTypeId);
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        // Check if format is CSV
        if ($request->get('format') === 'csv') {
            $filename = 'laporan-pembayaran-'.date('YmdHis').'.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($payments) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['No', 'Invoice', 'NIM', 'Nama Mahasiswa', 'Prodi', 'Fakultas', 'Jenis Pembayaran', 'Semester', 'Tagihan', 'Dibayar', 'Status', 'Tgl Bayar', 'Metode', 'Konfirmasi Oleh']);

                foreach ($payments as $index => $p) {
                    fputcsv($file, [
                        $index + 1,
                        $p->invoice_number,
                        $p->mahasiswa->nim ?? '-',
                        $p->mahasiswa->user->name ?? '-',
                        $p->mahasiswa->prodi->nama ?? '-',
                        $p->mahasiswa->prodi->fakultas->nama ?? '-',
                        $p->paymentType->name ?? '-',
                        $p->paymentType->semester ?? '-',
                        $p->amount,
                        $p->paid_amount,
                        strtoupper($p->status),
                        $p->payment_date ? $p->payment_date->format('d/m/Y') : '-',
                        $p->payment_method ?? '-',
                        $p->confirmedBy->name ?? '-',
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Printable HTML view
        return view('admin.payments.export', compact('payments', 'user'));
    }
}
