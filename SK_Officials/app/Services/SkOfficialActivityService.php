<?php

namespace App\Services;

use App\Models\SkOfficialActivity;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class SkOfficialActivityService
{
    public function log(User $user, string $action, string $description, ?array $metadata = null): void
    {
        if (! Schema::hasTable('sk_official_activities') || $user->barangay_id === null) {
            return;
        }

        SkOfficialActivity::create([
            'tenant_id' => $user->tenant_id,
            'barangay_id' => $user->barangay_id,
            'user_id' => $user->id,
            'action' => $action,
            'description' => mb_substr(trim($description), 0, 500),
            'metadata' => $metadata,
        ]);
    }
}
