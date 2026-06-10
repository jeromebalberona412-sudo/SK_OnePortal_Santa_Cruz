<?php

namespace App\Modules\Reports_Management\Controllers;

use App\Http\Controllers\Controller;

class ReportsManagementController extends Controller
{
    public function index()
    {
        return view('Reports_Management::reports-management');
    }
}
