<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Authentication\Models\DeviceVerificationToken;
use App\Modules\Authentication\Notifications\DeviceVerificationNotification;
use App\Modules\Shared\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class DeviceVerificationService
{
    public function __construct(
        protected DeviceFingerprintService $fingerprintService,
        protected TrustedDeviceService $trustedDeviceService,
    ) {}

    public function issueToken(User $user, Request $request): void
    {
        $plainToken = Str::random(64);
        $expiresMinutes = (int) config('sk_fed_auth.trusted_device.token_expiration_minutes', 20);

        DeviceVerificationToken::query()
            ->where('user_id', $user->getKey())
            ->whereNull('used_at')
            ->delete();

        DeviceVerificationToken::query()->create([
            'user_id' => $user->getKey(),
            'token_hash' => hash('sha256', $plainToken),
            'fingerprint' => $this->fingerprintService->fromRequest($request),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expires_at' => now()->addMinutes($expiresMinutes),
            'metadata' => [
                'issued_at' => now()->toIso8601String(),
            ],
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'device.verify',
            now()->addMinutes($expiresMinutes),
            ['user' => $user->getKey(), 'token' => $plainToken]
        );

        $user->notify(new DeviceVerificationNotification($signedUrl, $request->ip(), $request->userAgent()));
    }

    public function verifyToken(int $userId, string $plainTextToken, Request $request): DeviceVerificationResult
    {
        $token = DeviceVerificationToken::query()
            ->where('user_id', $userId)
            ->where('token_hash', hash('sha256', $plainTextToken))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($token === null) {
            return new DeviceVerificationResult(false, null);
        }

        $user = User::query()->find($userId);

        if ($user === null) {
            return new DeviceVerificationResult(false, null);
        }

        $token->forceFill(['used_at' => now()])->save();
        $this->trustedDeviceService->trust($user, $request);

        return new DeviceVerificationResult(true, $user);
    }
}
