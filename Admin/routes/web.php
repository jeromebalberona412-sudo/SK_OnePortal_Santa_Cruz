<?php

use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Note: Module routes are loaded by their respective service providers:
// - Authentication module: /login, /logout, /user/two-factor-*
// - Profile module: /profile (primary), /dashboard (backward compatibility)
// - Fortify automatically registers: POST /login, GET & POST /two-factor-challenge
