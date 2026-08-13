<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Authentication\Controllers\AuthController;

// Sign In routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/sign-in', [AuthController::class, 'showSignin'])->name('sign-in');
    Route::post('/sign-in', [AuthController::class, 'signin']);

    // Backward-compat alias: any route('signin') reference redirects to /sign-in
    Route::get('/login', fn () => redirect()->route('sign-in'))->name('login');

// Registration routes — direct users to KK Profiling signup
     Route::get('/register', function () {
        return redirect()->route('kkprofiling.signup');
     })->name('register');
     Route::post('/register', function () {
        return redirect()->route('kkprofiling.signup');
    });

    // Password Reset Routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])
        ->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
        ->name('password.email');
    Route::get('/forgot-password/verify-email', [AuthController::class, 'showForgotPasswordVerifyEmail'])
        ->name('password.verify-email');
    Route::post('/forgot-password/verify-email/resend', [AuthController::class, 'resendForgotPasswordEmail'])
        ->name('password.verify-email.resend');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])
        ->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->name('password.update');

    // Email Verification Routes (Prototype)
    Route::get('/email/verify', [AuthController::class, 'showEmailVerification'])
        ->name('verification.notice');
    Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail'])
        ->name('verification.send');
    Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])
        ->name('verification.resend');
    Route::get('/email/verify/{token}', [AuthController::class, 'verifyEmail'])
        ->name('verification.verify');
});

// Email verification status check (can be accessed by guest or auth)
Route::get('/email/check-status', [AuthController::class, 'checkVerificationStatus'])
    ->name('verification.check');

// Test page for email verification (Development only)
Route::get('/test-email-verification', [AuthController::class, 'showTestEmailVerification'])
    ->name('test.verification');

// Logout route
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('web');
