<?php

namespace App\Modules\Accounts\Services;

use App\Modules\Shared\Models\User;
use Illuminate\Support\Str;

class ChairpersonFederationSyncService
{
    private const CHAIR_POSITIONS = ['Chairperson', 'Chairman'];

    public function syncForUser(User $user, string $position): void
    {
        if (! $this->isChairpersonPosition($position)) {
            if ($user->role === User::ROLE_SK_OFFICIAL && $user->has_federation_access) {
                $user->forceFill(['has_federation_access' => false])->save();
            }

            return;
        }

        if ($user->role !== User::ROLE_SK_OFFICIAL) {
            return;
        }

        if (! $user->has_federation_access) {
            $user->forceFill(['has_federation_access' => true])->save();
        }
    }

    public function isChairpersonPosition(string $position): bool
    {
        $normalized = Str::lower(trim($position));

        return in_array($normalized, ['chairperson', 'chairman'], true)
            || in_array($position, self::CHAIR_POSITIONS, true);
    }
}
