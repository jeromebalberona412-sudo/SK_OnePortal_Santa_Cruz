<?php

namespace App\Modules\Dashboard\Controllers;

use App\Modules\Dashboard\Services\DashboardService;
use App\Modules\Dashboard\Services\KkProfilingChartService;
use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly KkProfilingChartService $kkProfilingChartService,
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $accountMetrics = $this->dashboardService->getAccountMetrics($user);
        $userDistribution = [
            'federation' => $accountMetrics['federationAccounts'],
            'officials' => $accountMetrics['officialAccounts'],
        ];

        return view('dashboard::dashboard', [
            'user' => $user,
            'accountMetrics' => $accountMetrics,
            'userDistribution' => $userDistribution,
            'barangays' => $this->dashboardService->getBarangays($user),
            'barangayDistribution' => $this->dashboardService->getBarangayDistribution($user),
            'recentAuditActivity' => collect(),
            'termFilters' => $this->dashboardService->getTermFilterOptions($user),
        ]);
    }

    public function dashboardData(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = [
            'year' => $request->input('year', 'all'),
            'term' => $request->input('term', 'all'),
        ];

        return response()->json([
            'metrics' => $this->dashboardService->getAccountMetrics($user, $filters),
            'barangayDistribution' => $this->dashboardService->getBarangayDistribution($user, $filters),
            'recentAuditActivity' => [],
            'termFilters' => $this->dashboardService->getTermFilterOptions($user),
        ]);
    }

    public function kkProfilingData(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => $this->kkProfilingChartService->getChartData($user, $request),
        ]);
    }
}
