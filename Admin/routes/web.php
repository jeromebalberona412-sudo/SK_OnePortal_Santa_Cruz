<?php

use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Include admin authentication routes
require __DIR__.'/../app/Modules/Authentication/Routes/auth.php';

// Note: Module routes are loaded by their respective service providers:
// - Authentication module: /login, /logout, /user/two-factor-*
// - Dashboard module: /dashboard
// - Fortify automatically registers: POST /login, GET & POST /two-factor-challenge
