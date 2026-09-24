<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\StudentPayment;
use App\Models\TahunAkademik;
use Illuminate\Database\Eloquent\Collection;

class PaymentAccessService
{
    /**
     * Determine the current active semester for a student.
     * Follows the rule:
     * 1. If student already has a KRS in the active academic year, use that semester if available.
     * 2. Otherwise, calculate based on angkatan and active academic year (Ganjil/Genap).
     */
    public function determineStudentSemester(Mahasiswa $mahasiswa, ?TahunAkademik $tahunAktif = null): int
    {
        $tahunAktif = $tahunAktif ?? TahunAkademik::where('is_active', true)->first();

        $angkatan = (int) $mahasiswa->angkatan;
        if ($angkatan <= 0) {
            $angkatan = (int) date('Y');
        }

        if ($tahunAktif && $tahunAktif->tahun) {
            // Format is usually "2024/2025" or "2024"
            $parts = explode('/', $tahunAktif->tahun);
            $activeYear = (int) $parts[0];
            $isGenap = (strtolower($tahunAktif->semester) === 'genap');

            $yearDiff = max(0, $activeYear - $angkatan);
            $semester = ($yearDiff * 2) + ($isGenap ? 2 : 1);

            return max(1, min(14, $semester));
        }

        // Fallback to current calendar date
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');
        $yearsEnrolled = max(0, $currentYear - $angkatan);
        $semester = $yearsEnrolled * 2;
        if ($currentMonth >= 8 || $currentMonth <= 1) {
            $semester += 1;
        }

        return max(1, min(14, $semester));
    }

    /**
     * Check if registration fee is paid.
     */
    public function isRegistrationPaid(Mahasiswa $mahasiswa): bool
    {
        $payment = StudentPayment::where('mahasiswa_id', $mahasiswa->id)
            ->whereHas('paymentType', function ($q) {
                $q->where('category', 'registration')->orWhere('code', 'registration');
            })
            ->first();

        // If no registration payment obligation exists, consider paid to avoid blocking
        if (! $payment) {
            return true;
        }

        return $payment->isPaid();
    }

    /**
     * Check if a specific semester fee is paid.
     */
    public function isSemesterPaid(Mahasiswa $mahasiswa, int $semester): bool
    {
        $payment = StudentPayment::where('mahasiswa_id', $mahasiswa->id)
            ->whereHas('paymentType', function ($q) use ($semester) {
                $q->where('category', 'semester')
                    ->where('semester', $semester);
            })
            ->first();

        if (! $payment) {
            // If semester exceeds standard 8 (e.g. sem 9+), check if semester 8 is paid or allow
            if ($semester > 8) {
                return $this->isSemesterPaid($mahasiswa, 8);
            }

            return false;
        }

        return $payment->isPaid();
    }

    /**
     * Check if the student has paid at least the minimum required amount for KRS access.
     * Uses config('siakad.krs_minimum_payment') to determine the threshold.
     * If config is null, full payment is required (falls back to isSemesterPaid).
     */
    public function hasSufficientPaymentForKrs(Mahasiswa $mahasiswa, int $semester): bool
    {
        $minimumPayment = config('siakad.krs_minimum_payment');

        // If minimum is null, require full payment
        if ($minimumPayment === null) {
            return $this->isSemesterPaid($mahasiswa, $semester);
        }

        // If minimum is 0, always allow KRS access
        if ((float) $minimumPayment <= 0) {
            return true;
        }

        $payment = StudentPayment::where('mahasiswa_id', $mahasiswa->id)
            ->whereHas('paymentType', function ($q) use ($semester) {
                $q->where('category', 'semester')
                    ->where('semester', $semester);
            })
            ->first();

        if (! $payment) {
            if ($semester > 8) {
                return $this->hasSufficientPaymentForKrs($mahasiswa, 8);
            }

            return false;
        }

        // Already fully paid
        if ($payment->isPaid()) {
            return true;
        }

        // Check if paid_amount meets the minimum threshold
        return (float) $payment->paid_amount >= (float) $minimumPayment;
    }

    /**
     * Get payment status record for a given semester.
     */
    public function getPaymentStatus(Mahasiswa $mahasiswa, int $semester): ?StudentPayment
    {
        return StudentPayment::with(['paymentType', 'confirmedBy'])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->whereHas('paymentType', function ($q) use ($semester) {
                $q->where('category', 'semester')
                    ->where('semester', $semester);
            })
            ->first();
    }

    /**
     * Get registration payment status record.
     */
    public function getRegistrationPaymentStatus(Mahasiswa $mahasiswa): ?StudentPayment
    {
        return StudentPayment::with(['paymentType', 'confirmedBy'])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->whereHas('paymentType', function ($q) {
                $q->where('category', 'registration')->orWhere('code', 'registration');
            })
            ->first();
    }

    /**
     * Check whether student can access KRS for the target semester.
     */
    public function canAccessKrs(Mahasiswa $mahasiswa, ?int $semester = null): bool
    {
        $result = $this->checkKrsAccess($mahasiswa, $semester);

        return $result['allowed'];
    }

    /**
     * Detailed check for KRS access with context and unpaid invoice info.
     *
     * @return array{allowed: bool, semester: int, unpaid_payment: ?StudentPayment, reason: ?string}
     */
    public function checkKrsAccess(Mahasiswa $mahasiswa, ?int $semester = null): array
    {
        $targetSemester = $semester ?? $this->determineStudentSemester($mahasiswa);

        // Check if Admin has manually unlocked KRS (Dispensasi Pembayaran)
        if ($mahasiswa->is_krs_unlocked) {
            return [
                'allowed' => true,
                'semester' => $targetSemester,
                'unpaid_payment' => null,
                'reason' => 'Akses KRS dibuka secara khusus oleh Admin (Dispensasi Pembayaran).',
            ];
        }

        // 1. Check registration fee
        if (! $this->isRegistrationPaid($mahasiswa)) {
            $regPayment = $this->getRegistrationPaymentStatus($mahasiswa);

            return [
                'allowed' => false,
                'semester' => $targetSemester,
                'unpaid_payment' => $regPayment,
                'reason' => 'Biaya pendaftaran mahasiswa baru belum dikonfirmasi lunas.',
            ];
        }

        // 2. Check target semester fee with minimum payment threshold
        if (! $this->hasSufficientPaymentForKrs($mahasiswa, $targetSemester)) {
            $semesterPayment = $this->getPaymentStatus($mahasiswa, $targetSemester);
            $minimumPayment = config('siakad.krs_minimum_payment');
            $formattedMinimum = number_format((float) $minimumPayment, 0, ',', '.');

            return [
                'allowed' => false,
                'semester' => $targetSemester,
                'unpaid_payment' => $semesterPayment,
                'reason' => "Pembayaran perkuliahan Semester {$targetSemester} belum mencapai minimum Rp {$formattedMinimum} untuk akses KRS.",
            ];
        }

        return [
            'allowed' => true,
            'semester' => $targetSemester,
            'unpaid_payment' => null,
            'reason' => null,
        ];
    }

    /**
     * Get all unpaid payments for a student.
     */
    public function getUnpaidPayments(Mahasiswa $mahasiswa): Collection
    {
        return StudentPayment::with('paymentType')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->unpaid()
            ->get();
    }

    /**
     * Get all payments for a student sorted by sequence order.
     * Order: Registration / Heregistrasi (0), Semester 1 (1), ..., Semester 8 (8).
     */
    public function getOrderedPayments(Mahasiswa $mahasiswa): \Illuminate\Support\Collection
    {
        return StudentPayment::with(['paymentType', 'tahunAkademik', 'confirmedBy'])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->get()
            ->sortBy(fn (StudentPayment $p) => $p->getSequenceOrder())
            ->values();
    }

    /**
     * Find the first unpaid prerequisite payment that must be settled before this payment.
     * Returns null if this payment is first in order or all prior payments are paid.
     */
    public function getUnpaidPrerequisite(StudentPayment $payment): ?StudentPayment
    {
        $currentOrder = $payment->getSequenceOrder();
        $orderedPayments = $this->getOrderedPayments($payment->mahasiswa);

        foreach ($orderedPayments as $p) {
            if ($p->id === $payment->id) {
                break;
            }

            if ($p->getSequenceOrder() < $currentOrder && ! $p->isPaid()) {
                return $p;
            }
        }

        return null;
    }

    /**
     * Check if a payment can be executed / paid.
     * Enforces strict sequential payment order unless a written note/keterangan is provided.
     *
     * @return array{allowed: bool, is_locked: bool, reason: ?string, prerequisite: ?StudentPayment, is_skipped_with_notes: bool}
     */
    public function canExecutePayment(StudentPayment $payment, ?string $notes = null): array
    {
        if ($payment->isPaid()) {
            return [
                'allowed' => false,
                'is_locked' => false,
                'reason' => 'Pembayaran ini sudah berstatus lunas.',
                'prerequisite' => null,
                'is_skipped_with_notes' => false,
            ];
        }

        $unpaidPrereq = $this->getUnpaidPrerequisite($payment);

        if (! $unpaidPrereq) {
            return [
                'allowed' => true,
                'is_locked' => false,
                'reason' => null,
                'prerequisite' => null,
                'is_skipped_with_notes' => false,
            ];
        }

        // Has unpaid prerequisite. Check if custom note / keterangan is provided
        $effectiveNotes = trim((string) ($notes ?? $payment->notes ?? ''));
        $hasCustomKeterangan = ! empty($effectiveNotes) && ! str_starts_with($effectiveNotes, 'Kewajiban ');

        if ($hasCustomKeterangan) {
            return [
                'allowed' => true,
                'is_locked' => false,
                'reason' => "Diizinkan loncat pembayaran dengan keterangan: {$effectiveNotes}",
                'prerequisite' => $unpaidPrereq,
                'is_skipped_with_notes' => true,
            ];
        }

        $prereqName = $unpaidPrereq->paymentType->name ?? 'Heregistrasi/Pembayaran sebelumnya';

        return [
            'allowed' => false,
            'is_locked' => true,
            'reason' => "Pembayaran mahasiswa harus berurutan. Anda belum menyelesaikan {$prereqName}. Pembayaran ini tidak dapat dieksekusi secara loncat kecuali terdapat catatan/keterangan dispensasi tertulis.",
            'prerequisite' => $unpaidPrereq,
            'is_skipped_with_notes' => false,
        ];
    }

    /**
     * Get the next payment obligation that the student must pay right now.
     */
    public function getNextPaymentToPay(Mahasiswa $mahasiswa): ?StudentPayment
    {
        $ordered = $this->getOrderedPayments($mahasiswa);

        foreach ($ordered as $payment) {
            if (! $payment->isPaid()) {
                return $payment;
            }
        }

        return null;
    }
}
