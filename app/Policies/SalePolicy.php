<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Sale;

class SalePolicy
{
    /**
     * Determine whether the user can create sales.
     * Only cashiers and admins may create sales.
     */
    public function create(?User $user)
    {
        if (! $user) return false;
        return in_array($user->role, ['cashier', 'admin']);
    }
}
