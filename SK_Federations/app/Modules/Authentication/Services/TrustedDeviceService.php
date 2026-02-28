<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Authentication\Models\TrustedDevice;
use App\Modules\Shared\Models\User;
use Illuminate\Http\Request;

class TrustedDeviceService
{
    public function __construct(protected DeviceFingerprintService $fingerprintService) {}

    public function isTrusted(User $user, Request $request): bool
    {
        $fingerprint = $this->fingerprintService->fromRequest($request);

        return TrustedDevice::query()
            ->where('user_id', $user->getKey())
            ->where('fingerprint', $fingerprint)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    public function trust(User $user, Request $request): TrustedDevice
    {
        $fingerprint = $this->fingerprintService->fromRequest($request);
        $expirationDays = (int) config('sk_fed_auth.trusted_device.expiration_days', 30);

        return TrustedDevice::query()->updateOrCreate(
            [
                'user_id' => $user->getKey(),
                'fingerprint' => $fingerprint,
            ],
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_used_at' => now(),
                'expires_at' => now()->addDays($expirationDays),
                'metadata' => [
                    'session_cookie' => config('session.cookie'),
                ],
            ]
        );
    }

    public function touch(User $user, Request $request): void
    {
        $fingerprint = $this->fingerprintService->fromRequest($request);

        TrustedDevice::query()
            ->where('user_id', $user->getKey())
            ->where('fingerprint', $fingerprint)
            ->update([
                'last_used_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function trustFingerprint(
        User $user,
        string $fingerprint,
        ?string $ipAddress,
        ?string $userAgent,
        ?array $metadata = null,
    ): TrustedDevice {
        $expirationDays = (int) config('sk_fed_auth.trusted_device.expiration_days', 30);

        return TrustedDevice::query()->updateOrCreate(
            [
                'user_id' => $user->getKey(),
                'fingerprint' => $fingerprint,
            ],
            [
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'last_used_at' => now(),
                'expires_at' => now()->addDays($expirationDays),
                'metadata' => $metadata ?? [
                    'session_cookie' => config('session.cookie'),
                ],
            ]
        );
    }
}
