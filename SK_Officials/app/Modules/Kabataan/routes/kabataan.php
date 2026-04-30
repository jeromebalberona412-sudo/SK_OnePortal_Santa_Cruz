<?php

use Illuminate\Support\Facades\Route;

// Kabataan module routes (UI-only, auth-protected via session flag)
Route::middleware(['web'])->group(function () {
    Route::get('/kabataan', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Kabataan::kabataan');
    })->name('kabataan');
});

