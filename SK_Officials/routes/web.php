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

    // KK Profiling Requests (UI-only, auth-protected via simple session check)
    Route::get('/kk-profiling-requests', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('KKProfilingRequests::kkprofiling-requests');
    })->name('kk-profiling-requests');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

});

/*
|--------------------------------------------------------------------------
| Default Route
|--------------------------------------------------------------------------
*/
