<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Manage_Account\Controllers\AddAccountController;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Manage Account routes
Route::get('/manage-account', [AddAccountController::class, 'createSkFed'])->name('manage.account');
Route::post('/manage-account', [AddAccountController::class, 'storeSkFed'])->name('manage.account.store');

// Note: Module routes are loaded by their respective service providers:
// - Authentication module: /login, /logout, /user/two-factor-*
// - Profile module: /profile (primary), /dashboard (backward compatibility)
// - Fortify automatically registers: POST /login, GET & POST /two-factor-challenge
