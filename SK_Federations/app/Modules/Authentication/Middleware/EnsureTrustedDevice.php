<?php

namespace App\Modules\Authentication\Middleware;

use App\Modules\Authentication\Services\DeviceVerificationService;
use App\Modules\Authentication\Services\FeatureFlagService;
use App\Modules\Authentication\Services\TrustedDeviceService;
use App\Modules\Shared\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTrustedDevice
{
    public function __construct(
        protected FeatureFlagService $featureFlagService,
        protected TrustedDeviceService $trustedDeviceService,
        protected DeviceVerificationService $deviceVerificationService,
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

        if (! (bool) config('sk_fed_auth.trusted_device.enforce_every_request', true)) {
            return $next($request);
        }

        if ($this->trustedDeviceService->isTrusted($user, $request)) {
            $this->trustedDeviceService->touch($user, $request);

            return $next($request);
        }

        $this->deviceVerificationService->issueToken($user, $request);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('device.verify.notice', ['email' => $user->email])
            ->with('status', 'Device verification is required before access is granted.');
    }
}
