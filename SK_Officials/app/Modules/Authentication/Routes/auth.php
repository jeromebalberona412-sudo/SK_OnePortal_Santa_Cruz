<?php

use App\Modules\Authentication\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/email/verify/wait', [AuthController::class, 'showVerificationWait'])->name('sk_official.verification.wait');
    Route::get('/email/verify/wait-status', [AuthController::class, 'checkVerificationStatus'])->name('sk_official.verification.wait.status');
    Route::get('/email/verify/notice', [AuthController::class, 'showVerifyNotice'])->name('sk_official.verification.notice');
    Route::post('/email/verify/resend', [AuthController::class, 'resendVerification'])->name('sk_official.verification.resend');
    Route::get('/email/verify-link/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('sk_official.verification.verify');
    Route::get('/email/verified-success', [AuthController::class, 'showVerificationSuccess'])->name('sk_official.verification.success');

    Route::get('/session/takeover/wait', [AuthController::class, 'showTakeoverWait'])->name('sk_official.takeover.wait');
    Route::post('/session/takeover/send-otp', [AuthController::class, 'sendTakeoverOtp'])->name('sk_official.takeover.send');
    Route::post('/session/takeover/verify-otp', [AuthController::class, 'verifyTakeoverOtp'])->name('sk_official.takeover.verify');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])
        ->middleware(['turnstile', 'throttle:sk-official-password-reset-ip', 'throttle:sk-official-password-reset-email'])
        ->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])
        ->middleware(['throttle:sk-official-password-reset-form'])
        ->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->middleware(['throttle:sk-official-password-reset-ip', 'throttle:sk-official-password-reset-email'])
        ->name('password.update');
    Route::get('/password-reset-success', [AuthController::class, 'showPasswordResetSuccess'])->name('password.reset.success');
});

Route::middleware(['auth', 'verified', 'sk_official.access', 'trusted.device'])->group(function () {
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/change-password', [AuthController::class, 'updatePassword'])->name('password.change.update');
});

Route::middleware(['auth', 'single.session'])->post('/heartbeat', [AuthController::class, 'heartbeat'])->name('sk_official.heartbeat');

Route::middleware('auth')->get('/logout', [AuthController::class, 'logout'])->name('logout.fallback');
