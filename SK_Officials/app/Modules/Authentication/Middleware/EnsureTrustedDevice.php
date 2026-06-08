<?php

namespace App\Modules\Authentication\Middleware;

use App\Models\User;
use App\Modules\Authentication\Services\EmailVerificationDeviceService;
use App\Modules\Authentication\Services\FeatureFlagService;
use App\Modules\Authentication\Services\TrustedDeviceService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureTrustedDevice
{
    public function __construct(
        protected FeatureFlagService $featureFlagService,
        protected TrustedDeviceService $trustedDeviceService,
        protected EmailVerificationDeviceService $emailVerificationDeviceService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->deviceVerificationEnabled()) {
            return $next($request);
        }

        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($request->routeIs(
            'sk_official.verification.wait',
            'sk_official.verification.wait.status',
            'sk_official.verification.resend',
            'sk_official.verification.verify',
            'sk_official.verification.success',
        )) {
            return $next($request);
        }

        if ($this->trustedDeviceService->isTrusted($user, $request)) {
            return $next($request);
        }

        $this->emailVerificationDeviceService->storePendingVerification($user, $request, [
            'reason' => 'session_device_verification',
        ]);

        Auth::logout();

        return redirect()
            ->route('sk_official.verification.wait')
            ->with('status', 'We sent a verification link to your email. Please verify to continue on this device.');
    }

    protected function deviceVerificationEnabled(): bool
    {
        return $this->featureFlagService->deviceVerificationEnabled()
            || Schema::hasTable('sk_official_trusted_devices');
    }
}
