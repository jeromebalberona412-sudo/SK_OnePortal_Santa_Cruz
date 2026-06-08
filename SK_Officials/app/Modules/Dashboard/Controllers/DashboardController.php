<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $firstName = 'SK Official';

        if ($user !== null) {
            $name = trim((string) $user->name);
            if ($name !== '') {
                $firstName = explode(' ', $name)[0];
            }
        }

        return view('Dashboard::dashboard', [
            'userFirstName' => $firstName,
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'granularity' => ['nullable', 'in:monthly,weekly'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'summary' => ['nullable', 'boolean'],
            'charts' => ['nullable', 'boolean'],
        ]);

        $year = (int) ($validated['year'] ?? now()->year);
        $granularity = $validated['granularity'] ?? 'monthly';
        $month = isset($validated['month']) ? (int) $validated['month'] : null;

        if ($request->boolean('summary')) {
            return response()->json([
                'data' => $this->dashboardService->getSummary($user, $year),
            ]);
        }

        if ($request->boolean('charts')) {
            return response()->json([
                'data' => $this->dashboardService->getChartData($user, $year, $granularity, $month),
            ]);
        }

        return response()->json([
            'data' => $this->dashboardService->getStats($user, $year, $granularity, $month),
        ]);
    }
}
