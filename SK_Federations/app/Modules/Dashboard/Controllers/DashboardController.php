<?php

namespace App\Modules\Dashboard\Controllers;

use App\Modules\Dashboard\Services\DashboardStatsService;
use App\Modules\Dashboard\Services\KkProfilingChartService;
use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardStatsService $dashboardStatsService,
        private readonly KkProfilingChartService $kkProfilingChartService,
    ) {
    }

    public function index(Request $request): View
    {
        $tenantId = $request->user()?->tenant_id;

        return view('dashboard::dashboard', [
            'user' => $request->user(),
            'totalKabataanRegistered' => $this->dashboardStatsService->totalKabataanRegistered($tenantId),
            'totalSkOfficials' => $this->dashboardStatsService->totalSkOfficials($tenantId),
            'totalSkChairpersons' => $this->dashboardStatsService->totalSkChairpersons($tenantId),
            'sexDistribution' => $this->dashboardStatsService->sexDistribution($tenantId),
            'federationOfficers' => $this->dashboardStatsService->federationOfficers($tenantId),
            'topBarangays' => $this->dashboardStatsService->topBarangaysByYouth($tenantId),
            'barangays' => $this->dashboardStatsService->getBarangays($tenantId),
            'kkBarangayOptions' => $this->dashboardStatsService->getBarangays($tenantId)
                ->map(fn ($barangay) => ['id' => $barangay->id, 'name' => $barangay->name])
                ->values()
                ->all(),
        ]);
    }

    public function kkProfilingData(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->kkProfilingChartService->getChartData($request->user(), $request),
        ]);
    }
}
