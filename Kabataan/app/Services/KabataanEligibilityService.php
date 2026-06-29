<?php

namespace App\Services;

use App\Models\KabataanRegistration;
use App\Models\User;

class KabataanEligibilityService
{
    public const VIEW_ONLY_MESSAGE = 'Your KK Profiling term has ended. You can view your account only — program applications and updates are no longer available.';

    public function isViewOnly(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        $registration = $this->latestRegistrationForUser($user);

        return $registration !== null && $registration->status === 'archived';
    }

    public function latestRegistrationForUser(User $user): ?KabataanRegistration
    {
        return KabataanRegistration::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();
    }
}
