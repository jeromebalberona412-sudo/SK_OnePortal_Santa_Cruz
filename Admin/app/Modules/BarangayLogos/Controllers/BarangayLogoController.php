<?php

namespace App\Modules\BarangayLogos\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BarangayLogoController extends Controller
{
    /**
     * Display the barangay logos management page
     */
    public function index(Request $request): View
    {
        return view('barangay_logos::barangay-logos', [
            'user' => $request->user(),
        ]);
    }
}
