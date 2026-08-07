<?php

namespace App\Http\Middleware;

use App\Models\KabataanRegistration;
use App\Services\KkProfilingScheduleService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureKkProfilingUpdated
{
    public function __construct(private readonly KkProfilingScheduleService $scheduleService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $logData = [];

        $user = Auth::user();
        if ($user === null) {
            return $next($request);
        }

        // Check if profiling update was already determined during login
        $sessionCheckStart = microtime(true);
        if ($request->session()->has('kk_profiling_update_required')) {
            $requiresUpdate = $request->session()->get('kk_profiling_update_required');
            $logData['session_check_ms'] = round((microtime(true) - $sessionCheckStart) * 1000, 2);
            $logData['used_session_cache'] = true;

            if (! $requiresUpdate) {
                $logData['total_middleware_ms'] = round((microtime(true) - $startTime) * 1000, 2);
                \Log::info('EnsureKkProfilingUpdated middleware profile', $logData);
                return $next($request);
            }

            if ($request->routeIs(
                'dashboard',
                'kkprofiling.update',
                'kkprofiling.resend-update-verification',
                'notifications',
                'api.kabataan.notifications',
                'api.kabataan.notifications.read',
                'api.kabataan.notifications.read-all',
                'logout',
            )) {
                $logData['total_middleware_ms'] = round((microtime(true) - $startTime) * 1000, 2);
                \Log::info('EnsureKkProfilingUpdated middleware profile', $logData);
                return $next($request);
            }

            $logData['total_middleware_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            \Log::info('EnsureKkProfilingUpdated middleware profile', $logData);
            return redirect()
                ->route('dashboard')
                ->with('show_kk_profiling_update', true)
                ->with('kk_profiling_update_required', true);
        }

        $logData['session_check_ms'] = round((microtime(true) - $sessionCheckStart) * 1000, 2);
        $logData['used_session_cache'] = false;

        // Fallback to database query if not already determined
        $dbQueryStart = microtime(true);
        $registration = KabataanRegistration::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();
        $logData['db_query_ms'] = round((microtime(true) - $dbQueryStart) * 1000, 2);

        $profilingCheckStart = microtime(true);
        if (! $this->scheduleService->requiresProfilingUpdate($registration)) {
            $logData['profiling_check_ms'] = round((microtime(true) - $profilingCheckStart) * 1000, 2);
            $logData['total_middleware_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            \Log::info('EnsureKkProfilingUpdated middleware profile', $logData);
            return $next($request);
        }
        $logData['profiling_check_ms'] = round((microtime(true) - $profilingCheckStart) * 1000, 2);

        if ($request->routeIs(
            'dashboard',
            'kkprofiling.update',
            'kkprofiling.resend-update-verification',
            'notifications',
            'api.kabataan.notifications',
            'api.kabataan.notifications.read',
            'api.kabataan.notifications.read-all',
            'logout',
        )) {
            $logData['total_middleware_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            \Log::info('EnsureKkProfilingUpdated middleware profile', $logData);
            return $next($request);
        }

        $logData['total_middleware_ms'] = round((microtime(true) - $startTime) * 1000, 2);
        \Log::info('EnsureKkProfilingUpdated middleware profile', $logData);
        return redirect()
            ->route('dashboard')
            ->with('show_kk_profiling_update', true)
            ->with('kk_profiling_update_required', true);
    }
}
