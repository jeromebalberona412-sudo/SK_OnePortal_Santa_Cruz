<?php

namespace App\Modules\Authentication\Middleware;

use App\Models\User;
use App\Modules\Authentication\Services\FeatureFlagService;
use App\Modules\Authentication\Services\TrustedDeviceService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTrustedDevice
{
    public function __construct(
        protected FeatureFlagService $featureFlagService,
        protected TrustedDeviceService $trustedDeviceService,
    ) {}

    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if (! $this->featureFlagService->enabled('features.device_verification')) {
            return $next($request);
        }

        if (! (bool) config('sk_official_auth.trusted_device.enforce_every_request', true)) {
            return $next($request);
        }

        if ($this->trustedDeviceService->isTrusted($user, $request)) {
            $this->trustedDeviceService->touch($user, $request);

            return $next($request);
        }

        $this->trustedDeviceService->trust($user, $request);

        return $next($request);
    }
}
