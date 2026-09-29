<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\Notification;
use App\Models\PaymentHistory;
use App\Models\StudentPayment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PaymentService
{
    public function __construct(
        protected ActivityLogService $activityLogService,
        protected NotificationService $notificationService,
        protected PaymentInitializationService $initializationService,
        protected PaymentAccessService $paymentAccessService
    ) {}

    /**
     * Confirm a student payment administratively (supports partial/installments and full payments).
     */
    public function confirmPayment(StudentPayment $payment, array $data, User $actor): StudentPayment
    {
        $this->authorizePaymentAccess($payment, $actor);

        if ($payment->isPaid()) {
            throw new InvalidArgumentException('Pembayaran ini sudah berstatus lunas.');
        }

        $notes = $data['notes'] ?? $payment->notes ?? null;
        $executionCheck = $this->paymentAccessService->canExecutePayment($payment, $notes);
        if (! $executionCheck['allowed']) {
            throw new InvalidArgumentException($executionCheck['reason']);
        }

        return DB::transaction(function () use ($payment, $data, $actor) {
            $payment->loadMissing(['mahasiswa.user', 'mahasiswa.prodi', 'paymentType']);

            $oldStatus = $payment->status;
            $currentPaid = (float) $payment->paid_amount;
            $remaining = (float) $payment->remaining_amount;

            // Determine the installment amount for this transaction
            if (isset($data['pay_amount']) && is_numeric($data['pay_amount'])) {
                $thisPaymentAmount = (float) $data['pay_amount'];
            } elseif (isset($data['paid_amount']) && is_numeric($data['paid_amount'])) {
                $inputAmount = (float) $data['paid_amount'];
                // If input equals total amount and currentPaid is 0, it's paying full bill
                if ($inputAmount >= (float) $payment->amount && $currentPaid === 0.0) {
                    $thisPaymentAmount = (float) $payment->amount;
                } elseif ($inputAmount <= $remaining) {
                    $thisPaymentAmount = $inputAmount;
                } else {
                    $thisPaymentAmount = $remaining;
                }
            } else {
                $thisPaymentAmount = $remaining;
            }

            if ($thisPaymentAmount <= 0) {
                throw new InvalidArgumentException('Nominal pembayaran harus lebih besar dari 0.');
            }

            if ($thisPaymentAmount > $remaining) {
                throw new InvalidArgumentException('Nominal pembayaran (Rp '.number_format($thisPaymentAmount, 0, ',', '.').') melebihi sisa tagihan (Rp '.number_format($remaining, 0, ',', '.').').');
            }

            $newTotalPaid = $currentPaid + $thisPaymentAmount;
            $isFullPayment = ($newTotalPaid >= (float) $payment->amount);
            $newStatus = $isFullPayment ? 'paid' : 'partial';
            $remainingAfter = max(0, (float) $payment->amount - $newTotalPaid);

            $paymentMethod = $data['payment_method'] ?? 'Tunai';
            $paymentDate = $data['payment_date'] ?? now()->toDateString();
            $referenceNumber = $data['reference_number'] ?? null;
            if (! $referenceNumber) {
                $prefix = $paymentMethod === 'Transfer' ? 'TRF-' : 'CASH-';
                $referenceNumber = $prefix.now()->format('Ymd').'-'.strtoupper(Str::random(6));
            }
            $notes = $data['notes'] ?? $payment->notes;

            if ($referenceNumber) {
                $notes = $notes ? "{$notes} | Ref: {$referenceNumber}" : "Ref: {$referenceNumber}";
            }

            $payment->update([
                'status' => $newStatus,
                'paid_amount' => $newTotalPaid,
                'payment_method' => $paymentMethod,
                'payment_date' => $paymentDate,
                'confirmed_at' => now(),
                'confirmed_by' => $actor->id,
                'notes' => $notes,
            ]);

            $action = $isFullPayment ? 'confirmed' : 'partial_payment';
            $historyNote = $notes ?: ($isFullPayment
                ? 'Pelunasan pembayaran tagihan oleh administrasi.'
                : 'Pembayaran cicilan/sebagian diterima. Sisa tagihan: Rp '.number_format($remainingAfter, 0, ',', '.'));

            // Audit history
            PaymentHistory::create([
                'student_payment_id' => $payment->id,
                'action' => $action,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'amount' => $thisPaymentAmount,
                'reference_number' => $referenceNumber,
                'notes' => $historyNote,
                'performed_by' => $actor->id,
            ]);

            // Activity Log
            $mhsName = $payment->mahasiswa->user->name ?? $payment->mahasiswa->nim;
            $typeName = $payment->paymentType->name ?? 'Pembayaran';
            $amountFormatted = number_format($thisPaymentAmount, 0, ',', '.');
            $remainingFormatted = number_format($remainingAfter, 0, ',', '.');
            $actionLabel = $isFullPayment ? 'mengonfirmasi pelunasan' : 'mencatat pembayaran cicilan';

            $this->activityLogService->log(
                action: "Admin {$actionLabel} {$typeName} mahasiswa {$mhsName} ({$payment->mahasiswa->nim}) sebesar Rp {$amountFormatted}. (Sisa: Rp {$remainingFormatted})",
                model: $payment,
                changes: [
                    'status' => $newStatus,
                    'paid_amount' => $newTotalPaid,
                    'payment_method' => $paymentMethod,
                    'installment_amount' => $thisPaymentAmount,
                    'remaining_amount' => $remainingAfter,
                ]
            );

            // Note: KRS access is now determined dynamically by PaymentAccessService
            // based on the minimum payment threshold (config: siakad.krs_minimum_payment).
            // The is_krs_unlocked flag is reserved for manual admin dispensation only.

            // Notify Mahasiswa
            if ($payment->mahasiswa->user) {
                $semesterInfo = $payment->paymentType->semester ? " Semester {$payment->paymentType->semester}" : '';
                $notifTitle = $isFullPayment ? 'Pembayaran Berhasil Dikonfirmasi' : 'Setoran Pembayaran Cicilan Diterima';
                $notifMessage = $isFullPayment
                    ? "Pembayaran {$typeName} Anda telah lunas diverifikasi oleh Administrasi Fakultas. Fitur KRS{$semesterInfo} sekarang dapat diakses."
                    : "Setoran pembayaran {$typeName} Anda sebesar Rp {$amountFormatted} telah diterima. Sisa tagihan Anda saat ini Rp {$remainingFormatted}.";

                $this->notificationService->send(
                    user: $payment->mahasiswa->user,
                    type: Notification::TYPE_PAYMENT_CONFIRMED,
                    title: $notifTitle,
                    message: $notifMessage,
                    data: [
                        'payment_id' => $payment->id,
                        'invoice_number' => $payment->invoice_number,
                        'paid_now' => $thisPaymentAmount,
                        'total_paid' => $newTotalPaid,
                        'remaining' => $remainingAfter,
                        'status' => $newStatus,
                    ]
                );
            }

            return $payment->fresh(['mahasiswa.user', 'paymentType', 'confirmedBy', 'histories']);
        });
    }

    /**
     * Cancel a confirmed or partial student payment.
     */
    public function cancelPayment(StudentPayment $payment, ?string $reason, User $actor, bool $isDelete = false): StudentPayment
    {
        $this->authorizePaymentAccess($payment, $actor);

        if (! in_array($payment->status, ['paid', 'partial'])) {
            throw new InvalidArgumentException('Hanya pembayaran berstatus lunas atau cicilan yang dapat dibatalkan.');
        }

        return DB::transaction(function () use ($payment, $reason, $actor, $isDelete) {
            $payment->loadMissing(['mahasiswa.user', 'paymentType']);

            $oldStatus = $payment->status;
            $oldPaidAmount = $payment->paid_amount;

            $actionNote = $reason
                ? ($isDelete ? "Transaksi dihapus: {$reason}" : "Dibatalkan: {$reason}")
                : ($isDelete ? 'Transaksi dihapus oleh admin' : 'Dibatalkan oleh admin');

            $payment->update([
                'status' => 'unpaid',
                'paid_amount' => 0,
                'confirmed_at' => null,
                'confirmed_by' => null,
                'payment_method' => null,
                'payment_date' => null,
                'notes' => $actionNote,
            ]);

            if ($isDelete) {
                $payment->histories()->delete();
            } else {
                PaymentHistory::create([
                    'student_payment_id' => $payment->id,
                    'action' => 'cancelled',
                    'old_status' => $oldStatus,
                    'new_status' => 'unpaid',
                    'amount' => 0,
                    'notes' => $reason ?: 'Konfirmasi pembayaran dibatalkan.',
                    'performed_by' => $actor->id,
                ]);
            }

            // Note: is_krs_unlocked is reserved for manual admin dispensation only.
            // KRS access is determined dynamically by PaymentAccessService.

            $mhsName = $payment->mahasiswa->user->name ?? $payment->mahasiswa->nim;
            $typeName = $payment->paymentType->name ?? 'Pembayaran';
            $verb = $isDelete ? 'menghapus transaksi' : 'membatalkan konfirmasi';

            $this->activityLogService->log(
                action: "Admin {$verb} pembayaran {$typeName} mahasiswa {$mhsName} ({$payment->mahasiswa->nim}).",
                model: $payment,
                changes: [
                    'status' => 'unpaid',
                    'is_delete' => $isDelete,
                    'reason' => $reason,
                ]
            );

            if ($payment->mahasiswa->user) {
                $notifTitle = $isDelete ? 'Transaksi Pembayaran Dihapus' : 'Konfirmasi Pembayaran Dibatalkan';
                $notifBody = $isDelete
                    ? "Transaksi pembayaran {$typeName} Anda telah dihapus oleh pihak administrasi."
                    : "Konfirmasi pembayaran {$typeName} Anda telah dibatalkan oleh pihak administrasi.";

                $this->notificationService->send(
                    user: $payment->mahasiswa->user,
                    type: Notification::TYPE_PAYMENT_CANCELLED,
                    title: $notifTitle,
                    message: $notifBody.($reason ? " Catatan: {$reason}" : ''),
                    data: [
                        'payment_id' => $payment->id,
                        'invoice_number' => $payment->invoice_number,
                    ]
                );
            }

            return $payment->fresh(['mahasiswa.user', 'paymentType', 'confirmedBy', 'histories']);
        });
    }

    /**
     * Authorize user access to a specific payment record based on fakultas scope.
     */
    public function authorizePaymentAccess(StudentPayment $payment, User $actor): void
    {
        if ($actor->isSuperAdmin()) {
            return;
        }

        if ($actor->role === 'admin_fakultas') {
            $payment->loadMissing('mahasiswa.prodi');
            $studentFakultasId = $payment->mahasiswa?->prodi?->fakultas_id;

            if ($studentFakultasId && $studentFakultasId !== $actor->fakultas_id) {
                throw new AuthorizationException('Anda tidak berwenang mengelola pembayaran mahasiswa di luar fakultas Anda.');
            }

            return;
        }

        throw new AuthorizationException('Peran Anda tidak memiliki izin untuk mengelola pembayaran.');
    }

    /**
     * Authorize user access to a specific student based on fakultas scope.
     */
    public function authorizeStudentAccess(Mahasiswa $mahasiswa, User $actor): void
    {
        if ($actor->isSuperAdmin()) {
            return;
        }

        if ($actor->role === 'admin_fakultas') {
            $mahasiswa->loadMissing('prodi');
            $studentFakultasId = $mahasiswa->prodi?->fakultas_id;

            if ($studentFakultasId && $studentFakultasId !== $actor->fakultas_id) {
                throw new AuthorizationException('Anda tidak berwenang mengelola pembayaran mahasiswa di luar fakultas Anda.');
            }

            return;
        }

        throw new AuthorizationException('Peran Anda tidak memiliki izin untuk mengelola pembayaran.');
    }

    /**
     * Get financial statistics for payment dashboard.
     */
    public function getStatistics(?int $fakultasId = null, array $filters = []): array
    {
        $query = StudentPayment::query();

        if ($fakultasId) {
            $query->forFakultas($fakultasId);
        }

        // Apply filters
        if (! empty($filters['prodi_id'])) {
            $query->whereHas('mahasiswa', fn ($q) => $q->where('prodi_id', $filters['prodi_id']));
        }
        if (! empty($filters['angkatan'])) {
            $query->whereHas('mahasiswa', fn ($q) => $q->where('angkatan', $filters['angkatan']));
        }
        if (! empty($filters['semester'])) {
            $query->whereHas('paymentType', fn ($q) => $q->where('semester', $filters['semester']));
        }
        if (! empty($filters['payment_type_id'])) {
            $query->where('payment_type_id', $filters['payment_type_id']);
        }

        $totalTagihan = (clone $query)->count();
        $totalLunas = (clone $query)->where('status', 'paid')->count();
        $totalBelumLunas = (clone $query)->where('status', 'unpaid')->count();
        $totalNominalLunas = (clone $query)->where('status', 'paid')->sum('paid_amount');
        $totalTunggakan = (clone $query)->where('status', 'unpaid')->sum('amount');

        // Total distinct students
        $totalMahasiswaQuery = Mahasiswa::query();
        if ($fakultasId) {
            $totalMahasiswaQuery->whereHas('prodi', fn ($q) => $q->where('fakultas_id', $fakultasId));
        }
        if (! empty($filters['prodi_id'])) {
            $totalMahasiswaQuery->where('prodi_id', $filters['prodi_id']);
        }
        if (! empty($filters['angkatan'])) {
            $totalMahasiswaQuery->where('angkatan', $filters['angkatan']);
        }
        $totalMahasiswa = $totalMahasiswaQuery->count();

        // Today & this month payments
        $today = now()->toDateString();
        $thisMonth = now()->format('Y-m');

        $pembayaranHariIni = (clone $query)
            ->where('status', 'paid')
            ->whereDate('payment_date', $today)
            ->sum('paid_amount');

        $pembayaranBulanIni = (clone $query)
            ->where('status', 'paid')
            ->where('payment_date', 'like', "{$thisMonth}%")
            ->sum('paid_amount');

        return [
            'total_mahasiswa' => $totalMahasiswa,
            'total_tagihan' => $totalTagihan,
            'total_lunas' => $totalLunas,
            'total_belum_lunas' => $totalBelumLunas,
            'total_nominal_lunas' => (float) $totalNominalLunas,
            'total_tunggakan' => (float) $totalTunggakan,
            'pembayaran_hari_ini' => (float) $pembayaranHariIni,
            'pembayaran_bulan_ini' => (float) $pembayaranBulanIni,
        ];
    }

    /**
     * Bulk generate payments for students matching filters.
     */
    public function bulkGeneratePayments(array $filters = []): int
    {
        $query = Mahasiswa::query();

        if (! empty($filters['fakultas_id'])) {
            $query->whereHas('prodi', fn ($q) => $q->where('fakultas_id', $filters['fakultas_id']));
        }
        if (! empty($filters['prodi_id'])) {
            $query->where('prodi_id', $filters['prodi_id']);
        }
        if (! empty($filters['angkatan'])) {
            $query->where('angkatan', $filters['angkatan']);
        }

        $students = $query->get();
        $totalCreated = 0;

        foreach ($students as $student) {
            $totalCreated += $this->initializationService->initializeStudentPayments($student);
        }

        return $totalCreated;
    }

    /**
     * Ambil riwayat transaksi pembayaran mahasiswa yang dikelompokkan per transaksi.
     */
    public function getTransactionHistory(Mahasiswa $mahasiswa): Collection
    {
        $histories = PaymentHistory::whereHas('payment', fn ($q) => $q->where('mahasiswa_id', $mahasiswa->id))
            ->whereIn('action', ['confirmed', 'partial_payment', 'midtrans_settlement', 'midtrans_partial_settlement'])
            ->with([
                'payment.paymentType',
                'payment.tahunAkademik',
                'performer',
            ])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $grouped = $histories->groupBy(function ($h) {
            return ! empty($h->reference_number)
                ? $h->reference_number
                : ('HIST-'.$h->id);
        });

        $transactions = $grouped->map(function ($items, $ref) {
            $first = $items->first();
            $payment = $first->payment;
            $totalAmount = (float) $items->sum('amount');
            $date = $payment?->payment_date ?? $first->created_at;

            $itemsDetail = $items->map(function ($h) {
                return (object) [
                    'history_id' => $h->id,
                    'payment_id' => $h->student_payment_id,
                    'name' => $h->payment?->paymentType?->name ?? 'Pembayaran',
                    'semester' => $h->payment?->paymentType?->semester,
                    'invoice_number' => $h->payment?->invoice_number,
                    'amount_paid' => (float) $h->amount,
                    'total_obligation' => (float) ($h->payment?->amount ?? 0),
                    'remaining_amount' => (float) ($h->payment?->remaining_amount ?? 0),
                    'is_paid' => $h->payment?->isPaid() ?? false,
                    'status' => $h->payment?->status ?? 'unpaid',
                    'notes' => $h->notes,
                ];
            });

            $allPaid = $items->every(fn ($h) => $h->payment && $h->payment->isPaid());
            $hasMultiple = $items->count() > 1;

            if ($hasMultiple) {
                $names = $items->map(fn ($h) => $h->payment?->paymentType?->name)->filter()->unique()->values();
                $description = $items->count().' Tagihan ('.$names->implode(', ').')';
            } else {
                $description = $items->first()?->payment?->paymentType?->name ?? 'Pembayaran';
            }

            return (object) [
                'id' => $first->student_payment_id,
                'reference_number' => $ref,
                'invoice_number' => $items->count() === 1 ? ($payment?->invoice_number ?? $ref) : $ref,
                'description' => $description,
                'total_amount' => $totalAmount,
                'paid_amount' => $totalAmount,
                'amount' => $totalAmount,
                'payment_method' => $payment?->payment_method ?? 'Tunai',
                'payment_date' => $date,
                'created_at' => $first->created_at,
                'confirmed_at' => $first->created_at,
                'confirmed_by' => $first->performer?->name ?? 'Kasir / Admin',
                'is_paid' => $allPaid,
                'status' => $allPaid ? 'paid' : 'partial',
                'items_count' => $items->count(),
                'items' => $itemsDetail,
                'first_payment_id' => $payment?->id,
            ];
        })->values();

        // Fallback untuk payment yang sudah terbayar tapi belum tercatat di PaymentHistory (misal seeder lama)
        $existingPaymentIds = $histories->pluck('student_payment_id')->unique()->all();
        $fallbackPayments = StudentPayment::where('mahasiswa_id', $mahasiswa->id)
            ->where(fn ($q) => $q->where('status', 'paid')->orWhere('paid_amount', '>', 0))
            ->whereNotIn('id', $existingPaymentIds)
            ->with(['paymentType', 'confirmedBy'])
            ->get();

        foreach ($fallbackPayments as $fp) {
            $paidAmt = (float) ($fp->paid_amount > 0 ? $fp->paid_amount : $fp->amount);
            $transactions->push((object) [
                'id' => $fp->id,
                'reference_number' => $fp->invoice_number,
                'invoice_number' => $fp->invoice_number,
                'description' => $fp->paymentType?->name ?? 'Pembayaran',
                'total_amount' => $paidAmt,
                'paid_amount' => $paidAmt,
                'amount' => $paidAmt,
                'payment_method' => $fp->payment_method ?? 'Tunai',
                'payment_date' => $fp->payment_date ?? $fp->confirmed_at ?? $fp->updated_at,
                'created_at' => $fp->confirmed_at ?? $fp->updated_at,
                'confirmed_at' => $fp->confirmed_at ?? $fp->updated_at,
                'confirmed_by' => $fp->confirmedBy?->name ?? 'Kasir / Admin',
                'is_paid' => $fp->isPaid(),
                'status' => $fp->status,
                'items_count' => 1,
                'items' => collect([(object) [
                    'history_id' => null,
                    'payment_id' => $fp->id,
                    'name' => $fp->paymentType?->name ?? 'Pembayaran',
                    'semester' => $fp->paymentType?->semester,
                    'invoice_number' => $fp->invoice_number,
                    'amount_paid' => $paidAmt,
                    'total_obligation' => (float) $fp->amount,
                    'remaining_amount' => (float) $fp->remaining_amount,
                    'is_paid' => $fp->isPaid(),
                    'status' => $fp->status,
                    'notes' => $fp->notes,
                ]]),
                'first_payment_id' => $fp->id,
            ]);
        }

        return $transactions->sortByDesc('created_at')->values();
    }

    /**
     * Susun data kwitansi berdasarkan referensi transaksi atau spesifik StudentPayment.
     */
    public function getReceiptData(Mahasiswa $mahasiswa, ?StudentPayment $payment = null, ?string $referenceNumber = null): array
    {
        $histories = collect();

        if ($referenceNumber) {
            $histories = PaymentHistory::where('reference_number', $referenceNumber)
                ->whereHas('payment', fn ($q) => $q->where('mahasiswa_id', $mahasiswa->id))
                ->whereIn('action', ['confirmed', 'partial_payment', 'midtrans_settlement', 'midtrans_partial_settlement'])
                ->with(['payment.paymentType', 'payment.tahunAkademik', 'performer'])
                ->get();
        }

        if ($histories->isEmpty() && $payment) {
            $latestHist = $payment->histories()
                ->whereIn('action', ['confirmed', 'partial_payment', 'midtrans_settlement', 'midtrans_partial_settlement'])
                ->latest()
                ->first();

            if ($latestHist && ! empty($latestHist->reference_number)) {
                $histories = PaymentHistory::where('reference_number', $latestHist->reference_number)
                    ->whereHas('payment', fn ($q) => $q->where('mahasiswa_id', $mahasiswa->id))
                    ->whereIn('action', ['confirmed', 'partial_payment', 'midtrans_settlement', 'midtrans_partial_settlement'])
                    ->with(['payment.paymentType', 'payment.tahunAkademik', 'performer'])
                    ->get();
            }
        }

        $items = [];
        $totalPaidNow = 0;
        $totalRemaining = 0;
        $nomorBukti = $referenceNumber;
        $confirmedBy = null;
        $tanggalPembayaran = null;
        $tahunAkademik = null;

        if ($histories->isNotEmpty()) {
            $first = $histories->first();
            $nomorBukti = $first->reference_number ?: ($first->payment?->invoice_number ?? 'KWT-'.now()->format('YmdHis'));
            $confirmedBy = $first->performer ?? $first->payment?->confirmedBy;
            $tanggalPembayaran = $first->payment?->payment_date
                ? $first->payment->payment_date->translatedFormat('d F Y')
                : ($first->created_at ? $first->created_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y'));

            $tahunAkademikModel = $first->payment?->tahunAkademik;
            $tahunAkademik = $tahunAkademikModel
                ? $tahunAkademikModel->tahun.' '.$tahunAkademikModel->semester
                : '2026 / 2027 Akhir';

            foreach ($histories as $h) {
                $p = $h->payment;
                $paidNow = (float) $h->amount;
                $rem = max(0, (float) ($p?->amount ?? 0) - (float) ($p?->paid_amount ?? 0));
                $items[] = [
                    'name' => $p?->paymentType?->name ?? 'Pembayaran',
                    'amount' => (float) ($p?->amount ?? 0),
                    'paid_amount' => $paidNow,
                    'notes' => $h->notes && ! str_starts_with($h->notes, 'Kewajiban') ? $h->notes : '-',
                    'remaining_amount' => $rem,
                    'status' => $p?->isPaid() ? 'LUNAS' : ($p?->status === 'partial' ? 'CICILAN' : strtoupper($p?->status ?? 'UNPAID')),
                ];
                $totalPaidNow += $paidNow;
                $totalRemaining += $rem;
            }
        } elseif ($payment) {
            $nomorBukti = $payment->invoice_number;
            $confirmedBy = $payment->confirmedBy;
            $tanggalPembayaran = $payment->payment_date
                ? $payment->payment_date->translatedFormat('d F Y')
                : ($payment->confirmed_at ? $payment->confirmed_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y'));

            $tahunAkademik = $payment->tahunAkademik
                ? $payment->tahunAkademik->tahun.' '.$payment->tahunAkademik->semester
                : '2026 / 2027 Akhir';

            $paidNow = (float) ($payment->paid_amount > 0 ? $payment->paid_amount : $payment->amount);
            $items[] = [
                'name' => $payment->paymentType?->name ?? 'Pembayaran',
                'amount' => (float) $payment->amount,
                'paid_amount' => $paidNow,
                'notes' => $payment->notes && ! str_starts_with($payment->notes, 'Kewajiban') ? $payment->notes : '-',
                'remaining_amount' => (float) $payment->remaining_amount,
                'status' => $payment->isPaid() ? 'LUNAS' : strtoupper($payment->status),
            ];
            $totalPaidNow = $paidNow;
            $totalRemaining = (float) $payment->remaining_amount;
        }

        $terbilang = $this->terbilang((int) $totalPaidNow);

        // Logo Base64
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

        return [
            'mahasiswa' => $mahasiswa,
            'payment' => $payment,
            'nomorBukti' => $nomorBukti,
            'items' => $items,
            'totalPaid' => $totalPaidNow,
            'totalRemaining' => $totalRemaining,
            'terbilang' => $terbilang,
            'logoBase64' => $logoBase64,
            'qrBase64' => $qrBase64,
            'tahunAkademik' => $tahunAkademik,
            'tanggalPembayaran' => $tanggalPembayaran,
            'tanggalCetak' => now()->translatedFormat('d F Y'),
            'confirmedByName' => $confirmedBy?->name ?? 'Muhammad Ziidan Amani',
        ];
    }

    /**
     * Konversi angka nominal rupiah menjadi teks terbilang bahasa Indonesia.
     */
    public function terbilang(int $angka): string
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
            $hasil = $this->terbilang((int) ($angka / 1000000000)).' Miliar '.$this->terbilang((int) fmod($angka, 1000000000));
        } else {
            $hasil = 'Angka terlalu besar';
        }

        return trim($hasil);
    }
}
