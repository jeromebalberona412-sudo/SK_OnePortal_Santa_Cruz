<?php

namespace App\Modules\Authentication\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EmailVerificationDeviceService
{
    /**
     * Store a pending email verification session in the request session.
     *
     * @param  array<string, mixed>  $extra
     */
    public function storePendingVerification(User $user, Request $request, array $extra = []): void
    {
        $waitMinutes = (int) config('sk_official_auth.verification.wait_minutes', 10);

        $request->session()->put('sk_official_email_verification_pending', array_merge([
            'user_id'    => $user->getKey(),
            'email'      => $user->email,
            'started_at' => now()->toIso8601String(),
            'expires_at' => now()->addMinutes($waitMinutes)->toIso8601String(),
            'verified_at_snapshot' => $user->email_verified_at?->toIso8601String() ?? '',
            'requires_fresh_verification' => true,
        ], $extra));

        $user->sendEmailVerificationNotification();
    }

    /**
     * Clear any pending verification session.
     */
    public function clearPendingVerification(Request $request): void
    {
        $request->session()->forget('sk_official_email_verification_pending');
    }
}
