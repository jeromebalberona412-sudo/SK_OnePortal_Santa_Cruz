<?php

namespace App\Http\Middleware;

use App\Services\KabataanAuthService;
use App\Services\KabataanEligibilityService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $user = $request->user();

        if ($user === null) {
            return redirect()->guest(route('login'));
        }

        if (! $this->kabataanAuthService->canAccessPortal($user)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('login_error', KabataanAuthService::LOGIN_DENIED_MESSAGE);
        }

        $viewOnly = $this->eligibilityService->isViewOnly($user);
        $request->attributes->set('kabataan_view_only', $viewOnly);
        View::share('kabataanViewOnly', $viewOnly);

        return $next($request);
    }
}
