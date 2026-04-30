<?php

namespace App\Modules\Authentication\Services;

use App\Models\User;
use App\Modules\Authentication\Models\EmailVerifiedDevice;
use Illuminate\Http\Request;

class EmailVerificationDeviceService
{
    public function __construct(protected DeviceFingerprintService $deviceFingerprintService) {}

    public function fingerprintFromRequest(Request $request): string
    {
        return $this->deviceFingerprintService->fromRequest($request);
    }

    public function requiresReverification(User $user, string $fingerprint): bool
    {
        $verifiedDevice = EmailVerifiedDevice::query()
            ->where('user_id', $user->getKey())
            ->first();

        if ($verifiedDevice === null) {
            return false;
        }

        return ! hash_equals((string) $verifiedDevice->fingerprint, $fingerprint);
    }

    public function upsertCurrentDevice(User $user, Request $request): void
    {
        EmailVerifiedDevice::query()->updateOrCreate(
            ['user_id' => $user->getKey()],
            [
                'fingerprint' => $this->fingerprintFromRequest($request),
                'verified_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $pending
     */
    public function markVerifiedDeviceFromPending(User $user, array $pending): void
    {
        $fingerprint = (string) ($pending['fingerprint'] ?? '');

        if ($fingerprint === '') {
            return;
        }

        EmailVerifiedDevice::query()->updateOrCreate(
            ['user_id' => $user->getKey()],
            [
                'fingerprint' => $fingerprint,
                'verified_at' => now(),
                'ip_address' => $pending['ip'] ?? null,
                'user_agent' => $pending['user_agent'] ?? null,
            ]
        );
    }
}
