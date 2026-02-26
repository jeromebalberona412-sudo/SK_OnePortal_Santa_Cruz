<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Add_Account\Controllers\AddAccountController;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Add SK Federation routes
Route::get('/admin/add-sk-fed', [AddAccountController::class, 'createSkFed'])->name('add.sk.fed');
Route::post('/admin/add-sk-fed', [AddAccountController::class, 'storeSkFed'])->name('add.sk.fed.store');

// Note: Module routes are loaded by their respective service providers:
// - Authentication module: /login, /logout, /user/two-factor-*
// - Dashboard module: /dashboard
// - Fortify automatically registers: POST /login, GET & POST /two-factor-challenge
