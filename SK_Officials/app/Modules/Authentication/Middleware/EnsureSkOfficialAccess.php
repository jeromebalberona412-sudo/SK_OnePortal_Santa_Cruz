<?php

namespace App\Modules\Authentication\Middleware;

use App\Models\User;
use App\Modules\Authentication\Services\TenantContextService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSkOfficialAccess
{
    public function __construct(protected TenantContextService $tenantContextService) {}

    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        $tenantId = $this->tenantContextService->tenantId();
        $requiredRole = (string) config('sk_official_auth.required_role', User::ROLE_SK_OFFICIAL);

        if ($tenantId === null || ! $user->hasRole($requiredRole) || (int) ($user->tenant_id ?? 0) !== $tenantId || ! $user->isActiveOfficial()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'auth' => 'Authentication could not be completed.',
            ]);
        }

        return $next($request);
    }
}
