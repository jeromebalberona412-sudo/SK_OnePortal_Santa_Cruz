<?php

namespace App\Modules\Authentication\Middleware;

use App\Modules\Authentication\Services\TenantContextService;
use App\Modules\Shared\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSkFedAccess
{
    public function __construct(protected TenantContextService $tenantContextService) {}

    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        $user->loadMissing('officialProfile');

        $tenantId = $this->tenantContextService->tenantId();
        $tenantMismatch = $tenantId !== null
            && (int) ($user->tenant_id ?? 0) !== $tenantId
            && ! $user->hasFederationLeadershipAccess();

        if ($tenantId === null || ! $user->canAccessFederationPortal() || $tenantMismatch) {
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
