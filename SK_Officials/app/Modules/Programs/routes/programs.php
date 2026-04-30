<?php

use Illuminate\Support\Facades\Route;

// Programs module routes (UI-only, auth-protected via session flag)
Route::middleware(['web'])->group(function () {
    Route::get('/programs', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Programs::programs');
    })->name('programs');
});

