<?php

namespace App\Modules\Authentication\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AuthAuditLogService
{
    public const OUTCOME_SUCCESS = 'success';
    public const OUTCOME_FAILED = 'failed';
    public const OUTCOME_BLOCKED = 'blocked';

    /**
     * @param  mixed  $resourceId
     * @param  array<string, mixed>  $metadata
     */
    public function log(
        string $event,
        ?User $user,
        Request $request,
        array $metadata = [],
        string $outcome = self::OUTCOME_SUCCESS,
        string $resourceType = 'auth',
        mixed $resourceId = null,
    ): void {
        $entry = [
            'event'         => $event,
            'outcome'       => $outcome,
            'user_id'       => $user?->getKey(),
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->userAgent(),
            'resource_type' => $resourceType,
            'resource_id'   => $resourceId,
            'metadata'      => $metadata,
            'occurred_at'   => now()->toIso8601String(),
        ];

        try {
            if (Schema::hasTable('auth_audit_logs')) {
                DB::table('auth_audit_logs')->insert([
                    'event'         => $entry['event'],
                    'outcome'       => $entry['outcome'],
                    'user_id'       => $entry['user_id'],
                    'ip_address'    => $entry['ip_address'],
                    'user_agent'    => $entry['user_agent'],
                    'resource_type' => $entry['resource_type'],
                    'resource_id'   => is_scalar($entry['resource_id']) ? (string) $entry['resource_id'] : null,
                    'metadata'      => json_encode($entry['metadata']),
                    'occurred_at'   => now(),
                ]);

                return;
            }
        } catch (\Throwable) {
            // Fall through to log channel
        }

        Log::channel('daily')->info('[auth_audit]', $entry);
    }
}
