<?php

namespace App\Modules\Authentication\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ((bool) ($user->must_change_password ?? false) && ! $request->routeIs('password.change', 'password.change.update', 'logout', 'sk_official.heartbeat')) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
