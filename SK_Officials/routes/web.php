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
        return view('Authentication::login');
    })->name('login');

    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/forgot-password', function () {
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

