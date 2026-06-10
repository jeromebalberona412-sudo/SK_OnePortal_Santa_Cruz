<?php

use App\Modules\Authentication\Controllers\AuthController;
use App\Modules\Authentication\Controllers\EmailVerificationController;
use App\Modules\Authentication\Controllers\PasswordSetupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])
        ->name('password.request');

    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])
        ->name('password.reset');

    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->name('password.update');

});

Route::get('/email/verify/{id}/{token}', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify');

/*
|--------------------------------------------------------------------------
| Password Setup (guest with token or authenticated first-login)
|--------------------------------------------------------------------------
*/

Route::get('/setup-password', [PasswordSetupController::class, 'show'])
    ->name('setup-password');

Route::post('/setup-password', [PasswordSetupController::class, 'store'])
    ->name('setup-password.store');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::post('/setup-password/resend', [PasswordSetupController::class, 'resend'])
        ->name('setup-password.resend');

    Route::get('/verify-email', [EmailVerificationController::class, 'show'])
        ->name('verification.notice');

    Route::get('/verify-email/status', [EmailVerificationController::class, 'status'])
        ->name('verification.status');

    Route::post('/verify-email/resend', [EmailVerificationController::class, 'resend'])
        ->name('verification.send');

});
