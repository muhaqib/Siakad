<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\Notification;
use App\Models\PaymentHistory;
use App\Models\StudentPayment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
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

            // Otomatis buka akses KRS jika pembayaran semester telah lunas
            if ($isFullPayment && ($payment->paymentType?->category === 'semester' || $payment->paymentType?->category === 'registration')) {
                $payment->mahasiswa->update(['is_krs_unlocked' => true]);
            }

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

            // Jika pembayaran semester dibatalkan/dihapus, kunci kembali jika tidak ada semester lain yang lunas
            if ($payment->paymentType?->category === 'semester') {
                $hasOtherPaidSemester = $payment->mahasiswa->payments()
                    ->where('id', '!=', $payment->id)
                    ->whereHas('paymentType', fn ($q) => $q->where('category', 'semester'))
                    ->where('status', 'paid')
                    ->exists();

                if (! $hasOtherPaidSemester) {
                    $payment->mahasiswa->update(['is_krs_unlocked' => false]);
                }
            }

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
}
