<?php

namespace App\Modules\Accounts\Policies;

use App\Modules\Shared\Models\User;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user): bool
    {
        return $user->isAdmin();
    }

    public function deactivate(User $user): bool
    {
        return $user->isAdmin();
    }

    public function extendTerm(User $user): bool
    {
        return $user->isAdmin();
    }
}
