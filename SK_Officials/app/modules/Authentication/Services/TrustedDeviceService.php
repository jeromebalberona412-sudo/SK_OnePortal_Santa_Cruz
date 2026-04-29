<?php

namespace App\Modules\Authentication\Services;

use App\Models\User;
use App\Modules\Authentication\Models\TrustedDevice;
use Illuminate\Http\Request;

class TrustedDeviceService
{
    public function __construct(protected DeviceFingerprintService $deviceFingerprintService) {}

    public function isTrusted(User $user, Request $request): bool
    {
        $fingerprint = $this->deviceFingerprintService->fromRequest($request);

        return TrustedDevice::query()
            ->where('user_id', $user->getKey())
            ->where('fingerprint', $fingerprint)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    public function trust(User $user, Request $request): TrustedDevice
    {
        $fingerprint = $this->deviceFingerprintService->fromRequest($request);
        $expirationDays = (int) config('sk_official_auth.trusted_device.expiration_days', 30);

        return TrustedDevice::query()->updateOrCreate(
            [
                'user_id' => $user->getKey(),
                'fingerprint' => $fingerprint,
            ],
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_used_at' => now(),
                'expires_at' => $expirationDays > 0 ? now()->addDays($expirationDays) : null,
            ]
        );
    }

    public function touch(User $user, Request $request): void
    {
        $fingerprint = $this->deviceFingerprintService->fromRequest($request);
        $expirationDays = (int) config('sk_official_auth.trusted_device.expiration_days', 30);

        TrustedDevice::query()
            ->where('user_id', $user->getKey())
            ->where('fingerprint', $fingerprint)
            ->update([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_used_at' => now(),
                'expires_at' => $expirationDays > 0 ? now()->addDays($expirationDays) : null,
                'updated_at' => now(),
            ]);
    }
}
