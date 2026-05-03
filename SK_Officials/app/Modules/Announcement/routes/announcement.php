<?php

use App\Modules\Announcement\Controllers\AnnouncementController;
use Illuminate\Support\Facades\Route;

// Announcement module routes (UI-only, auth-protected via session flag)
Route::middleware(['web'])->group(function () {
    Route::get('/announcements', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Announcement::announcement');
    })->name('announcements');

    Route::post('/announcements/upload-image', [AnnouncementController::class, 'uploadImage'])
        ->name('announcements.upload-image');
});

