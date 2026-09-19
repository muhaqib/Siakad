<?php

namespace App\Policies;

use App\Models\User;

class PaymentTypePolicy
{
    /**
     * Determine whether the user can manage payment types.
     */
    public function manage(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('manage payment types');
    }
}
