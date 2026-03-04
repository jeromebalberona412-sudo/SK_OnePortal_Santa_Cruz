<?php

use Illuminate\Support\Facades\Route;

// Calendar module routes (UI-only, auth-protected via session flag)
Route::middleware(['web'])->group(function () {
    Route::get('/calendar', function () {
        if (!session('authenticated')) {
            return redirect()->route('login');
        }
        return view('Calendar::calendar');
    })->name('calendar');
});

