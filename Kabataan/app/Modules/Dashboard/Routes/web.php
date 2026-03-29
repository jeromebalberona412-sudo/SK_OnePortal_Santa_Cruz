<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Dashboard\Controllers\DashboardController;

// PROTOTYPE MODE: Removed 'auth' middleware, using session-based check instead
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/barangay/{slug}', [DashboardController::class, 'barangay'])->name('barangay');

