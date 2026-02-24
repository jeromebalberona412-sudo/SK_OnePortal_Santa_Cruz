<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

// Note: Module routes are loaded by their respective service providers:
// - Authentication module: /login, /logout, /user/two-factor-*
// - Dashboard module: /dashboard
// - Fortify automatically registers: POST /login, GET & POST /two-factor-challenge
