<?php

use App\Modules\Authentication\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/modules/authentication/{type}/{file}', function ($type, $file) {
    $path = __DIR__."/../assets/{$type}/{$file}";

    if (! file_exists($path)) {
        abort(404);
    }

    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'webp' => 'image/webp',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'svg' => 'image/svg+xml',
    ];

    $extension = pathinfo($file, PATHINFO_EXTENSION);
    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

    return response()->file($path, ['Content-Type' => $mimeType]);
})->where('type', 'css|js|images')->where('file', '.*');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/email/verify/wait', [AuthController::class, 'showVerificationWait'])->name('skfed.verification.wait');
    Route::get('/email/verify/wait-status', [AuthController::class, 'checkVerificationStatus'])->name('skfed.verification.wait.status');
    Route::get('/email/verify/notice', [AuthController::class, 'showVerifyNotice'])->name('skfed.verification.notice');
    Route::post('/email/verify/resend', [AuthController::class, 'resendVerification'])->name('skfed.verification.resend');
    Route::get('/email/verify-link/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('skfed.verification.verify');
    Route::get('/email/verified-success', [AuthController::class, 'showVerificationSuccess'])->name('skfed.verification.success');

    Route::get('/session/takeover/wait', [AuthController::class, 'showTakeoverWait'])->name('skfed.takeover.wait');
    Route::post('/session/takeover/send-otp', [AuthController::class, 'sendTakeoverOtp'])->name('skfed.takeover.send');
    Route::post('/session/takeover/verify-otp', [AuthController::class, 'verifyTakeoverOtp'])->name('skfed.takeover.verify');
});

Route::middleware(['auth', 'single.session'])->group(function () {
    Route::post('/heartbeat', [AuthController::class, 'heartbeat'])->name('skfed.heartbeat');
});
