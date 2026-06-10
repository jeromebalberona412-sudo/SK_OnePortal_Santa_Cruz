<?php

use App\Modules\Profile\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'ensure.password.setup'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])
        ->name('profile');

    Route::get('/profile/change-password', [ProfileController::class, 'showChangePassword'])
        ->name('profile.change-password');

    Route::post('/profile/change-password', [ProfileController::class, 'sendChangePasswordLink'])
        ->name('profile.change-password.send');

    Route::get('/profile/change-password/verify', [ProfileController::class, 'showChangePasswordVerify'])
        ->name('profile.change-password.verify');

    Route::get('/profile/change-password/verify-status', [ProfileController::class, 'checkChangePasswordVerifyStatus'])
        ->name('profile.change-password.verify.status');

    Route::post('/profile/change-password/resend', [ProfileController::class, 'resendChangePassword'])
        ->name('profile.change-password.resend');

    Route::post('/profile/change-password/cancel', [ProfileController::class, 'cancelChangePassword'])
        ->name('profile.change-password.cancel');

    Route::get('/profile/change-email', [ProfileController::class, 'showChangeEmail'])
        ->name('profile.change-email');

    Route::post('/profile/change-email', [ProfileController::class, 'requestChangeEmail'])
        ->name('profile.change-email.request');

    Route::get('/profile/change-email/verify', [ProfileController::class, 'showChangeEmailVerify'])
        ->name('profile.change-email.verify');

    Route::post('/profile/change-email/resend', [ProfileController::class, 'resendChangeEmail'])
        ->name('profile.change-email.resend');

    Route::post('/profile/change-email/cancel', [ProfileController::class, 'cancelChangeEmail'])
        ->name('profile.change-email.cancel');
});

Route::get('/profile/change-email/confirm/{id}/{token}', [ProfileController::class, 'confirmChangeEmail'])
    ->name('profile.change-email.confirm');

Route::get('/profile/change-email/set-password/{id}/{token}', [ProfileController::class, 'showSetPasswordAfterEmailChange'])
    ->name('profile.change-email.set-password');

Route::post('/profile/change-email/set-password/{id}/{token}', [ProfileController::class, 'updateSetPasswordAfterEmailChange'])
    ->name('profile.change-email.set-password.update');
