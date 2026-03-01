<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Authentication\Controllers\AuthController;
use App\Modules\Authentication\Controllers\TwoFactorAuthController;
use App\Modules\Authentication\Controllers\TwoFactorChallengeController;

// Serve module assets directly (for development)
Route::get('/modules/authentication/{type}/{file}', function ($type, $file) {
    $path = __DIR__ . "/../assets/{$type}/{$file}";
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'webp' => 'image/webp',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
    ];
    
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
    
    return response()->file($path, ['Content-Type' => $mimeType]);
})->where('type', 'css|js|images')->where('file', '.*');

// Login routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    // Password Reset Routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])
        ->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
        ->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])
        ->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->name('password.update');
    
    // Two-Factor Challenge (after password verification)
    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'show'])
        ->name('two-factor.login');
    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'verify']);
});

// Logout route
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// Two-Factor Authentication routes (authenticated users)
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

