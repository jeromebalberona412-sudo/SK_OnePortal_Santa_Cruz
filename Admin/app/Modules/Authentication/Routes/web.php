<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Authentication\Controllers\AuthController;
use App\Modules\Authentication\Controllers\TwoFactorChallengeController;

// Login routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Two-Factor Challenge (after password verification)
    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'show'])
        ->name('two-factor.login');
    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'verify']);
});

// Logout route
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// Email Verification routes (authenticated, unverified)
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [AuthController::class, 'showVerifyEmail'])
        ->name('verification.notice');
    Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail'])
        ->name('verification.send')
        ->middleware('throttle:6,1');
});
