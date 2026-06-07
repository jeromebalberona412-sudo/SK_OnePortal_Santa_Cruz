<?php

namespace App\Modules\Dashboard\Controllers;

use App\Modules\Dashboard\Services\DashboardService;
use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function index(Request $request)
    {
        $accountMetrics = $this->dashboardService->getAccountMetrics($request->user());
        $userDistribution = [
            'federation' => $accountMetrics['federationAccounts'],
            'officials' => $accountMetrics['officialAccounts'],
        ];

        return view('dashboard::dashboard', [
            'user' => $request->user(),
            'accountMetrics' => $accountMetrics,
            'userDistribution' => $userDistribution,
            'barangays' => $this->dashboardService->getBarangays($request->user()),
        ]);
    }
}
