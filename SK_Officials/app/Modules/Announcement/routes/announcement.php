<?php

use Illuminate\Support\Facades\Route;

// Announcement module routes (UI-only, auth-protected via session flag)
Route::middleware(['web'])->group(function () {
    Route::get('/announcements', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Announcement::announcement');
    })->name('announcements');
});

