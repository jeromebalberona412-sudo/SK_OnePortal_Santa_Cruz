<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Dashboard\Controllers\DashboardController;

Route::middleware(['auth', 'ensure.password.setup', 'ensure.email.verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'dashboardData'])->name('dashboard.data');
    Route::get('/dashboard/kk-profiling-data', [DashboardController::class, 'kkProfilingData'])->name('dashboard.kk-profiling-data');
});
