<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Authentication\Controllers\AuthController;
use App\Modules\Authentication\Controllers\TwoFactorAuthController;

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/user/two-factor-authentication', [TwoFactorAuthController::class, 'show'])
        ->name('two-factor.setup');
    Route::post('/user/confirmed-two-factor-authentication', [TwoFactorAuthController::class, 'confirm'])
        ->name('two-factor.confirm');
    Route::get('/user/two-factor-recovery-codes', [TwoFactorAuthController::class, 'showRecoveryCodes'])
        ->name('two-factor.recovery-codes');
    Route::post('/user/two-factor-recovery-codes', [TwoFactorAuthController::class, 'regenerateRecoveryCodes'])
        ->name('two-factor.recovery-codes.regenerate');
});
