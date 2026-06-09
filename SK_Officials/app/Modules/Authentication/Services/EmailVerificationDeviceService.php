<?php

namespace App\Modules\Authentication\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EmailVerificationDeviceService
{
    public const RESEND_COOLDOWN_SECONDS = 60;

    /**
     * Store a pending email verification session in the request session.
     *
     * @param  array<string, mixed>  $extra
     */
    public function storePendingVerification(User $user, Request $request, array $extra = []): void
    {
        $waitMinutes = (int) config('sk_official_auth.verification.wait_minutes', 60);
        $sentAt = now();

        $request->session()->put('sk_official_email_verification_pending', array_merge([
            'user_id' => $user->getKey(),
            'email' => $user->email,
            'started_at' => $sentAt->toIso8601String(),
            'expires_at' => $sentAt->copy()->addMinutes($waitMinutes)->toIso8601String(),
            'verified_at_snapshot' => $user->email_verified_at?->toIso8601String() ?? '',
            'requires_fresh_verification' => true,
        ], $extra));

        $user->sendEmailVerificationNotification();
    }

    public function resendCooldownRemaining(array $pending): int
    {
        $lastSent = (string) ($pending['resend_last_sent_at'] ?? '');

        if ($lastSent === '') {
            return 0;
        }

        $elapsed = (int) Carbon::parse($lastSent)->diffInSeconds(now());

        return max(0, self::RESEND_COOLDOWN_SECONDS - $elapsed);
    }

    /**
     * Clear any pending verification session.
     */
    public function clearPendingVerification(Request $request): void
    {
        $request->session()->forget('sk_official_email_verification_pending');
    }
}
