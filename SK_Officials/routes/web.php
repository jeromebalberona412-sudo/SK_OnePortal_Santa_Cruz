<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Guest Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', function () {
        if (session('authenticated')) {
            return redirect()->route('dashboard');
        }

        return view('Authentication::login');
    })->name('login');

    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/forgot-password', function () {
        if (session('authenticated')) {
            return redirect()->route('dashboard');
        }

        return view('Authentication::forgot-password');
    })->name('forgot-password');

    Route::post('/forgot-password', function (\Illuminate\Http\Request $request) {
        $request->validate(['email' => 'required|email']);
        // Password reset logic goes here when backend is ready.
        return back()->with('status', 'If that email exists, a reset link has been sent.');
    })->name('password.email');

});

/*
|--------------------------------------------------------------------------
| Simple Authentication Middleware
|--------------------------------------------------------------------------
*/

Route::middleware(['web'])->group(function () {

    Route::get('/dashboard', function () {
        // Simple session check for demo
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Dashboard::dashboard');
    })->name('dashboard');

    Route::get('/profile', function () {
        // Simple session check for demo
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Profile::profile');
    })->name('profile');

    Route::get('/calendar', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Calendar::calendar');
    })->name('calendar');

    Route::get('/announcements', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Announcement::announcement');
    })->name('announcements');

    Route::get('/announcements/barangay/{slug}', function ($slug) {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        $brgyList = [
            'alipit'         => ['name' => 'Alipit',         'color' => '#4CAF50'],
            'bagumbayan'     => ['name' => 'Bagumbayan',     'color' => '#2196F3'],
            'bubukal'        => ['name' => 'Bubukal',        'color' => '#9C27B0'],
            'duhat'          => ['name' => 'Duhat',          'color' => '#FF9800'],
            'gatid'          => ['name' => 'Gatid',          'color' => '#009688'],
            'labuin'         => ['name' => 'Labuin',         'color' => '#f44336'],
            'pagsawitan'     => ['name' => 'Pagsawitan',     'color' => '#673AB7'],
            'san-jose'       => ['name' => 'San Jose',       'color' => '#0450a8'],
            'santisima-cruz' => ['name' => 'Santisima Cruz', 'color' => '#FF5722'],
        ];
        $brgy = $brgyList[$slug] ?? ['name' => ucfirst($slug), 'color' => '#f5c518'];
        return view('Announcement::barangay-profile', [
            'slug'  => $slug,
            'name'  => $brgy['name'],
            'color' => $brgy['color'],
        ]);
    })->name('sk-officials.barangay-profile');

    /*
    |--------------------------------------------------------------------------
    | SK Officials Modules: Committees, Programs, Events (UI Only)
    |--------------------------------------------------------------------------
    */

    Route::get('/committees', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Committees::committees');
    })->name('committees');

    Route::get('/programs', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Programs::programs');
    })->name('programs');

    Route::get('/events', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Events::events');
    })->name('events');

    Route::get('/budget-finance', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('BudgetFinance::budget-finance');
    })->name('budget-finance');

    // KK Profiling Requests (UI-only, auth-protected via simple session check)
    Route::get('/kk-profiling-requests', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('KKProfilingRequests::kkprofiling-requests');
    })->name('kk-profiling-requests');

    // ABYIP (Annual Barangay Youth Investment Program)
    Route::get('/abyip', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('ABYIP::abyip');
    })->name('abyip.index');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

});

/*
|--------------------------------------------------------------------------
| Default Route
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});
