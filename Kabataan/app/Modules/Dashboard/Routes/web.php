<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Dashboard\Controllers\DashboardController;
use App\Modules\Dashboard\Controllers\AnnouncementFeedController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/barangay/{slug}', [DashboardController::class, 'barangay'])->name('barangay');

// Announcement feed API (auth-protected via Auth::check() in controller)
Route::prefix('api/feed')->group(function () {
    Route::get('/',              [AnnouncementFeedController::class, 'feed'])->name('api.feed');
    Route::post('/{id}/react',   [AnnouncementFeedController::class, 'react'])->name('api.feed.react');
    Route::post('/{id}/comment', [AnnouncementFeedController::class, 'comment'])->name('api.feed.comment');
});

