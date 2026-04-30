<?php

use Illuminate\Support\Facades\Route;

// Committees module routes (UI-only, auth-protected via session flag)
Route::middleware(['web'])->group(function () {
    Route::get('/committees', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Committees::committees');
    })->name('committees');
});

