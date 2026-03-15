<?php

use Illuminate\Support\Facades\Route;

// Events module routes (UI-only, auth-protected via session flag)
Route::middleware(['web'])->group(function () {
    Route::get('/events', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Events::events');
    })->name('events');
});

