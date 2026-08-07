<?php

namespace App\Http\Middleware;

use App\Services\KabataanAuthService;
use App\Services\KabataanEligibilityService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureKabataanUser
{
    public function __construct(
        private readonly KabataanAuthService $kabataanAuthService,
        private readonly KabataanEligibilityService $eligibilityService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $logData = [];

        $user = $request->user();

        if ($user === null) {
            return redirect()->guest(route('login'));
        }

        $authCheckStart = microtime(true);
        if (! $this->kabataanAuthService->canAccessPortal($user)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('login_error', KabataanAuthService::LOGIN_DENIED_MESSAGE);
        }
        $logData['auth_check_ms'] = round((microtime(true) - $authCheckStart) * 1000, 2);

        $eligibilityCheckStart = microtime(true);
        $viewOnly = $this->eligibilityService->isViewOnly($user);
        $logData['eligibility_check_ms'] = round((microtime(true) - $eligibilityCheckStart) * 1000, 2);

        $request->attributes->set('kabataan_view_only', $viewOnly);
        View::share('kabataanViewOnly', $viewOnly);

        $logData['total_middleware_ms'] = round((microtime(true) - $startTime) * 1000, 2);
        Log::info('EnsureKabataanUser middleware profile', $logData);

        return $next($request);
    }
}
