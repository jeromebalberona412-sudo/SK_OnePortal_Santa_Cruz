<?php

namespace App\Http\Middleware;

use App\Services\KabataanAuthService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureKabataanUser
{
    public function __construct(
        private readonly KabataanAuthService $kabataanAuthService,
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

        return $next($request);
    }
}
