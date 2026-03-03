<?php

use Illuminate\Support\Facades\Route;

// Authentication module routes
Route::prefix('auth')->group(function () {
    Route::get('/login', function () {
        return view('Authentication::login');
    })->name('auth.login');
    
    Route::post('/logout', function () {
        session()->flush();
        return redirect()->route('auth.login');
    })->name('auth.logout');
});
