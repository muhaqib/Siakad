<?php

namespace App\Policies;

use App\Models\StudentPayment;
use App\Models\User;

class StudentPaymentPolicy
{
    /**
     * Determine whether the user can view any payments.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('view payments');
    }

    /**
     * Determine whether the user can view the payment.
     */
    public function view(User $user, StudentPayment $payment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->role === 'mahasiswa') {
            return $payment->mahasiswa_id === $user->mahasiswa?->id;
        }

        if ($user->role === 'admin_fakultas') {
            $payment->loadMissing('mahasiswa.prodi');
            $studentFakultasId = $payment->mahasiswa?->prodi?->fakultas_id;

            return $user->can('view payments') && $studentFakultasId === $user->fakultas_id;
        }

        return false;
    }

    /**
     * Determine whether the user can confirm the payment.
     */
    public function confirm(User $user, StudentPayment $payment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->role === 'admin_fakultas') {
            $payment->loadMissing('mahasiswa.prodi');
            $studentFakultasId = $payment->mahasiswa?->prodi?->fakultas_id;

            return $user->can('confirm payments') && $studentFakultasId === $user->fakultas_id;
        }

        return false;
    }

    /**
     * Determine whether the user can cancel the payment.
     */
    public function cancel(User $user, StudentPayment $payment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->role === 'admin_fakultas') {
            $payment->loadMissing('mahasiswa.prodi');
            $studentFakultasId = $payment->mahasiswa?->prodi?->fakultas_id;

            return $user->can('cancel payments') && $studentFakultasId === $user->fakultas_id;
        }

        return false;
    }

    /**
     * Determine whether the user can export payment reports.
     */
    public function export(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('export payment reports');
    }
}
