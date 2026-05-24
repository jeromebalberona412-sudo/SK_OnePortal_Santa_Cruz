<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Authentication\Controllers\AuthController;
use App\Modules\Authentication\Controllers\TwoFactorAuthController;
use App\Modules\Authentication\Controllers\TwoFactorChallengeController;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    // Login
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login']);

    // Forgot Password — Step 1: email form
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])
        ->name('password.request');

    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
        ->name('password.email');

    // Forgot Password — Step 2: OTP page
    Route::get('/forgot-password/otp', [AuthController::class, 'showOtp'])
        ->name('password.otp');

    Route::post('/forgot-password/otp', [AuthController::class, 'verifyOtp'])
        ->name('password.verify-otp');

    // Forgot Password — Step 3: Set new password page
    Route::get('/forgot-password/set-new-password', [AuthController::class, 'showSetNewPassword'])
        ->name('password.set-new-password');

    Route::post('/forgot-password/set-new-password', [AuthController::class, 'setNewPassword'])
        ->name('password.set-new');

    // Reset Password
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])
        ->name('password.reset');

    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->name('password.update');

    // Two-Factor Challenge
    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'show'])
        ->name('two-factor.login');

    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'verify']);

});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    // Email Verification
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function () {
        // Verification logic here
    })->middleware(['signed', 'throttle:6,1'])
      ->name('verification.verify');

    Route::post('/email/verification-notification', function () {
        // Resend verification notification
    })->middleware('throttle:6,1')
      ->name('verification.send');

    // Two-Factor Authentication
    Route::get('/user/two-factor-authentication', [TwoFactorAuthController::class, 'show'])
        ->name('two-factor.setup');

    Route::post('/user/confirmed-two-factor-authentication', [TwoFactorAuthController::class, 'confirm'])
        ->name('two-factor.confirm');

    Route::get('/user/two-factor-recovery-codes', [TwoFactorAuthController::class, 'showRecoveryCodes'])
        ->name('two-factor.recovery-codes');

    Route::post('/user/two-factor-recovery-codes', [TwoFactorAuthController::class, 'regenerateRecoveryCodes'])
        ->name('two-factor.recovery-codes.regenerate');

});
