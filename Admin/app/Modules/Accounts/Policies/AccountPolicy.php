<?php

namespace App\Modules\Accounts\Policies;

use App\Modules\Accounts\Models\OfficialProfile;
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

    public function update(User $user, User $target): bool
    {
        return $user->isAdmin()
            && $user->tenant_id !== null
            && $target->tenant_id !== null
            && $user->tenant_id === $target->tenant_id;
    }

    public function deactivate(User $user, User $target): bool
    {
        return $user->isAdmin()
            && $user->tenant_id !== null
            && $target->tenant_id !== null
            && $user->tenant_id === $target->tenant_id;
    }

    public function resetPassword(User $user, User $target): bool
    {
        return $this->update($user, $target);
    }

    public function extendTerm(User $user, OfficialProfile $profile): bool
    {
        return $user->isAdmin()
            && $user->tenant_id !== null
            && $profile->tenant_id !== null
            && $user->tenant_id === $profile->tenant_id;
    }
}
