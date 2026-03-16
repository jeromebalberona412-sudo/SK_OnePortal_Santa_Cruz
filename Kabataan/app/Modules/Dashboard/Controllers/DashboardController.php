<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // PROTOTYPE MODE: Check for prototype authentication
        if (!$request->session()->has('prototype_authenticated')) {
            // If not authenticated in prototype mode, redirect to login
            return redirect()->route('login')->with('error', 'Please login first.');
        }
        
        // Get prototype user data
        $prototypeUser = $request->session()->get('prototype_user', [
            'id' => 1,
            'name' => 'Youth User',
            'email' => 'youth@skportal.com',
            'barangay' => 'Barangay 1',
        ]);
        
        // Pass user data to view
        return view('dashboard::index', [
            'user' => (object) $prototypeUser
        ]);
    }
}

