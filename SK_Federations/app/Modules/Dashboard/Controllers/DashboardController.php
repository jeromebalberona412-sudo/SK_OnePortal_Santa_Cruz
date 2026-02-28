<?php

namespace App\Modules\Dashboard\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard::dashboard', ['user' => $request->user()]);
    }
}