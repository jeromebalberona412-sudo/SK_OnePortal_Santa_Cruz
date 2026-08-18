<?php

use App\Modules\Authentication\Controllers\AccountActivationController;
use App\Modules\Authentication\Controllers\AuthController;
use App\Modules\Profile\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/email/verify/wait', [AuthController::class, 'showVerificationWait'])
    ->middleware('guest')
    ->name('sk_official.verification.wait');
Route::get('/email/verify/wait-status', [AuthController::class, 'checkVerificationStatus'])
    ->middleware('guest')
    ->name('sk_official.verification.wait.status');
Route::post('/email/verify/resend', [AuthController::class, 'resendVerification'])
    ->middleware('guest')
    ->name('sk_official.verification.resend');
Route::get('/email/verify-link/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('sk_official.verification.verify');
Route::get('/email/verified-success', [AuthController::class, 'showVerificationSuccess'])->name('sk_official.verification.success');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/email/verify/notice', [AuthController::class, 'showVerifyNotice'])->name('sk_official.verification.notice');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])
        ->middleware(['throttle:sk-official-password-reset-ip', 'throttle:sk-official-password-reset-email'])
        ->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])
        ->middleware(['throttle:sk-official-password-reset-form'])
        ->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->middleware(['throttle:sk-official-password-reset-ip', 'throttle:sk-official-password-reset-email'])
        ->name('password.update');
    Route::get('/password-reset-success', [AuthController::class, 'showPasswordResetSuccess'])->name('password.reset.success');

    Route::get('/verify-account', [AccountActivationController::class, 'showRequestForm'])
        ->name('account.activation.request');
    Route::post('/verify-account', [AccountActivationController::class, 'sendLink'])
        ->middleware(['throttle:sk-official-account-activation-ip', 'throttle:sk-official-account-activation-email'])
        ->name('account.activation.send');
    Route::get('/verify-account/check-email', [AccountActivationController::class, 'showSent'])
        ->name('account.activation.sent');
    Route::get('/verify-account/already-active', [AccountActivationController::class, 'showAlreadyActive'])
        ->name('account.activation.already-active');
    Route::get('/activate-account/{token}', [AccountActivationController::class, 'showActivateForm'])
        ->middleware(['throttle:sk-official-account-activation-form'])
        ->name('account.activation.show');
    Route::post('/activate-account', [AccountActivationController::class, 'activate'])
        ->middleware(['throttle:sk-official-account-activation-ip', 'throttle:sk-official-account-activation-email'])
        ->name('account.activation.activate');
});

Route::middleware(['auth', 'sk_official.access'])->group(function () {
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('change-password');
    Route::post('/change-password', [AuthController::class, 'updatePassword'])->name('password.change.update');
    Route::get('/change-password/verify', [ProfileController::class, 'showChangePasswordVerify'])->name('change-password.verify');
    Route::get('/change-password/verify-status', [ProfileController::class, 'checkChangePasswordVerifyStatus'])->name('change-password.verify.status');
    Route::post('/change-password/resend', [ProfileController::class, 'resendChangePassword'])->name('change-password.resend');
    Route::post('/change-password/cancel', [ProfileController::class, 'cancelChangePassword'])->name('change-password.cancel');
});

Route::middleware(['auth', 'single.session'])->post('/heartbeat', [AuthController::class, 'heartbeat'])->name('sk_official.heartbeat');

Route::middleware('auth')->get('/logout', [AuthController::class, 'logout'])->name('logout.fallback');
