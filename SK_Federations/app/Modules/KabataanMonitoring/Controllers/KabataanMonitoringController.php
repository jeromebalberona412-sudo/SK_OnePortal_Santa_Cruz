<?php

namespace App\Modules\KabataanMonitoring\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KabataanMonitoringController extends Controller
{
    public function index(Request $request): View
    {
        return view('kabataan_monitoring::index', [
            'user' => $request->user(),
        ]);
    }

    public function show(Request $request, string $kabataan): View
    {
        return view('kabataan_monitoring::show', [
            'user' => $request->user(),
            'kabataan' => $kabataan,
        ]);
    }

    public function barangayDetail(Request $request, string $barangay): View
    {
        return view('kabataan_monitoring::barangay-detail', [
            'user' => $request->user(),
            'barangay' => urldecode($barangay),
        ]);
    }
}
