<?php

use Illuminate\Support\Facades\Route;

// Dashboard module routes
Route::prefix('dashboard')->group(function () {
    Route::get('/', function () {
        return view('Dashboard::dashboard');
    })->name('dashboard.index');
    
    Route::get('/home', function () {
        return view('Dashboard::dashboard');
    })->name('dashboard.home');
});
