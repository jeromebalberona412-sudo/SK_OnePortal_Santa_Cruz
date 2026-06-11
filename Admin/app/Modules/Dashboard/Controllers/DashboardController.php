<?php

namespace App\Modules\Dashboard\Controllers;

use App\Modules\Archive_Management\Services\ExpiredTermProcessorService;
use App\Modules\AuditLog\Services\AuditLogQueryService;
use App\Modules\Dashboard\Services\DashboardService;
use App\Modules\Dashboard\Services\KkProfilingChartService;
use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly AuditLogQueryService $auditLogQueryService,
        private readonly ExpiredTermProcessorService $expiredTermProcessor,
        private readonly KkProfilingChartService $kkProfilingChartService,
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $tenantId = $user?->tenant_id;
        $this->expiredTermProcessor->processForTenant($tenantId, $user);
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
            'recentAuditActivity' => $this->auditLogQueryService->dashboardRecentActivity($tenantId, 10),
            'termFilters' => $this->dashboardService->getTermFilterOptions($user),
        ]);
    }

    public function dashboardData(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenantId = $user?->tenant_id;
        $this->expiredTermProcessor->processForTenant($tenantId, $user);
        $filters = [
            'year' => $request->input('year', 'all'),
            'term' => $request->input('term', 'all'),
        ];

        return response()->json([
            'metrics' => $this->dashboardService->getAccountMetrics($user, $filters),
            'barangayDistribution' => $this->dashboardService->getBarangayDistribution($user, $filters),
            'recentAuditActivity' => $this->auditLogQueryService->dashboardRecentActivity($tenantId, 10)->values(),
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
