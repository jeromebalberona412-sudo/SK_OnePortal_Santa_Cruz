<?php

namespace App\Modules\Authentication\Middleware;

use App\Modules\Authentication\Services\AuthenticationService;
use App\Modules\Shared\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleSession
{
    public function __construct(protected AuthenticationService $authenticationService) {}

    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        $currentSessionId = $request->session()->getId();
        $activeSessionId = (string) ($user->active_session_id ?? '');

        if ($activeSessionId === '' || $activeSessionId === $currentSessionId) {
            $this->authenticationService->claimCurrentSession($user, $request);

            return $next($request);
        }

        if (! $this->authenticationService->isSessionActive($user)) {
            $this->authenticationService->claimCurrentSession($user, $request);

            return $next($request);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'auth' => 'Your session ended because your account was accessed from another device.',
        ]);
    }
}
