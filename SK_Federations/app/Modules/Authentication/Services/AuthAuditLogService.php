<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Authentication\Models\AuthAuditLog;
use App\Modules\Shared\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthAuditLogService
{
    public function log(string $event, ?User $user, Request $request, array $metadata = []): void
    {
        AuthAuditLog::query()->create([
            'user_id' => $user?->getKey(),
            'event' => $event,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => $metadata,
            'created_at' => now(),
        ]);

        Log::channel('sk_fed_auth_json')->info('sk_fed_auth_event', [
            'event' => $event,
            'user_id' => $user?->getKey(),
            'ip_address' => $request->ip(),
            'metadata' => $metadata,
        ]);
    }
}
