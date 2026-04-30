<?php

namespace App\Modules\Authentication\Middleware;

use App\Models\User;
use App\Modules\Authentication\Services\EmailVerificationDeviceService;
use App\Modules\Authentication\Services\FeatureFlagService;
use App\Modules\Authentication\Services\TrustedDeviceService;
use Closure;
use Illuminate\Http\Request;
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
        // If device verification feature is disabled, skip
        if (! $this->featureFlagService->deviceVerificationEnabled()) {
            return $next($request);
        }

        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        // If device is already trusted, proceed
        if ($this->trustedDeviceService->isTrusted($user, $request)) {
            return $next($request);
        }

        // Device not trusted — require email verification
        if (! $user->hasVerifiedEmail()) {
            $this->emailVerificationDeviceService->storePendingVerification($user, $request);

            return redirect()->route('sk_official.verification.wait');
        }

        // Email is verified — trust this device going forward
        $this->trustedDeviceService->trust($user, $request);

        return $next($request);
    }
}
