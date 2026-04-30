<?php

namespace App\Modules\Authentication\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TrustedDeviceService
{
    public function isTrusted(User $user, Request $request): bool
    {
        if (! Schema::hasTable('trusted_devices')) {
            return false;
        }

        $fingerprint = app(DeviceFingerprintService::class)->fingerprint($request);
        $expirationDays = (int) config('sk_official_auth.trusted_device.expiration_days', 30);

        return DB::table('trusted_devices')
            ->where('user_id', $user->getKey())
            ->where('fingerprint', $fingerprint)
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function trust(User $user, Request $request): void
    {
        if (! Schema::hasTable('trusted_devices')) {
            return;
        }

        $fingerprint = app(DeviceFingerprintService::class)->fingerprint($request);
        $expirationDays = (int) config('sk_official_auth.trusted_device.expiration_days', 30);

        DB::table('trusted_devices')->updateOrInsert(
            [
                'user_id'     => $user->getKey(),
                'fingerprint' => $fingerprint,
            ],
            [
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'expires_at' => now()->addDays($expirationDays),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function revoke(User $user, Request $request): void
    {
        if (! Schema::hasTable('trusted_devices')) {
            return;
        }

        $fingerprint = app(DeviceFingerprintService::class)->fingerprint($request);

        DB::table('trusted_devices')
            ->where('user_id', $user->getKey())
            ->where('fingerprint', $fingerprint)
            ->delete();
    }
}
