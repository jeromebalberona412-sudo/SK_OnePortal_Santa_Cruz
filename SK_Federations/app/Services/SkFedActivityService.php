<?php

namespace App\Services;

use App\Models\SkFedActivity;
use App\Models\User;

class SkFedActivityService
{
    /**
     * Log an activity for the SK Federation user.
     */
    public function log(User $user, string $actionType, string $description, array $metadata = []): void
    {
        SkFedActivity::create([
            'user_id' => $user->id,
            'barangay_id' => $user->barangay_id,
            'action_type' => $actionType,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
