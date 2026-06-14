<?php

namespace App\Modules\Dashboard\Controllers;

use App\Modules\Dashboard\Services\DashboardStatsService;
use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardStatsService $dashboardStatsService,
    ) {
    }

    public function index(Request $request): View
    {
        return view('dashboard::dashboard', [
            'user' => $request->user(),
            'totalKabataanRegistered' => $this->dashboardStatsService->totalKabataanRegistered(),
        ]);
    }
}