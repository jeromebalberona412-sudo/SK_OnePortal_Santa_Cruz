<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Dashboard\Controllers\DashboardController;
use App\Modules\Dashboard\Controllers\AnnouncementFeedController;

// Dashboard routes with authentication
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/barangay/{slug}', [DashboardController::class, 'barangay'])->name('barangay');
});
