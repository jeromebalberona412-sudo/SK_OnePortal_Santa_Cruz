<?php

namespace App\Modules\Authentication\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TrustedDeviceService
{
    protected function tableName(): string
    {
        return 'sk_official_trusted_devices';
    }

    public function isTrusted(User $user, Request $request): bool
    {
        if (! Schema::hasTable($this->tableName())) {
            return false;
        }

        $fingerprint = app(DeviceFingerprintService::class)->fingerprint($request);
        $expirationDays = (int) config('sk_official_auth.trusted_device.expiration_days', 30);

        return DB::table($this->tableName())
            ->where('user_id', $user->getKey())
            ->where('fingerprint', $fingerprint)
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function trust(User $user, Request $request): void
    {
        if (! Schema::hasTable($this->tableName())) {
            return;
        }

        $fingerprint = app(DeviceFingerprintService::class)->fingerprint($request);
        $expirationDays = (int) config('sk_official_auth.trusted_device.expiration_days', 30);

        DB::table($this->tableName())->updateOrInsert(
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
        if (! Schema::hasTable($this->tableName())) {
            return;
        }

        $fingerprint = app(DeviceFingerprintService::class)->fingerprint($request);

        DB::table($this->tableName())
            ->where('user_id', $user->getKey())
            ->where('fingerprint', $fingerprint)
            ->delete();
    }
}
