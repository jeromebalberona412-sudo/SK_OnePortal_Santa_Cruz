<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KabataanInAppNotificationService
{
    public function notifyKkProfilingRevoked(int $userId, string $reason): void
    {
        if (! Schema::hasTable('kabataan_notifications')) {
            return;
        }

        $body = 'Your approved KK Profiling record was revoked by SK Officials and moved back to pending review.';
        if ($reason !== '') {
            $body .= ' Reason: '.$reason;
        }

        DB::table('kabataan_notifications')->insert([
            'user_id' => $userId,
            'category' => 'kk_profiling',
            'title' => 'KK Profiling Revoked',
            'body' => $body,
            'action_url' => '/kk-profiling',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
