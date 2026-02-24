<?php

namespace App\Modules\Dashboard\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('dashboard::dashboard', ['user' => $request->user()]);
    }
}
