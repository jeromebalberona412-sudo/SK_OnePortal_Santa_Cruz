<?php

use App\Modules\KabataanMonitoring\Controllers\KabataanMonitoringController;
use Illuminate\Support\Facades\Route;

Route::get('/modules/kabataan-monitoring/{type}/{file}', function ($type, $file) {
    $path = __DIR__ . "/../assets/{$type}/{$file}";

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

Route::middleware(['auth', 'verified', 'single.session', 'sk_fed.access', 'trusted.device', 'prevent.back'])
    ->group(function () {
        Route::get('/kabataan-monitoring', [KabataanMonitoringController::class, 'index'])->name('kabataan-monitoring');
        Route::get('/kabataan-monitoring/{kabataan}', [KabataanMonitoringController::class, 'show'])->name('kabataan-monitoring.show');
    });
