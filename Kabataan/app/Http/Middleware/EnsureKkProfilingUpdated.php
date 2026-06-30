<?php

namespace App\Http\Middleware;

use App\Models\KabataanRegistration;
use App\Services\KkProfilingScheduleService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureKkProfilingUpdated
{
    public function __construct(private readonly KkProfilingScheduleService $scheduleService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if ($user === null) {
            return $next($request);
        }

        $registration = KabataanRegistration::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if (! $this->scheduleService->requiresProfilingUpdate($registration)) {
            return $next($request);
        }

        if ($request->routeIs(
            'dashboard',
            'kkprofiling.update',
            'logout',
        )) {
            return $next($request);
        }

        return redirect()
            ->route('dashboard')
            ->with('show_kk_profiling_update', true)
            ->with('kk_profiling_update_required', true);
    }
}
