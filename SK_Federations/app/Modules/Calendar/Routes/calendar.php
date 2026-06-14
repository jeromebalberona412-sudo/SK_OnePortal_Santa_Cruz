<?php

use App\Modules\Calendar\Controllers\CalendarController;
use Illuminate\Support\Facades\Route;

Route::get('/modules/calendar/{type}/{file}', function (string $type, string $file) {
    $path = __DIR__."/../assets/{$type}/{$file}";

    if (! file_exists($path)) {
        abort(404);
    }

    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
    ];

    $extension = pathinfo($file, PATHINFO_EXTENSION);
    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

    return response()->file($path, ['Content-Type' => $mimeType]);
})->where('type', 'css|js')->where('file', '.*');

Route::middleware(['auth', 'verified', 'single.session', 'sk_fed.access', 'trusted.device', 'prevent.back'])
    ->group(function () {
        Route::get('/calendar', [CalendarController::class, 'page'])->name('calendar');
        Route::get('/api/calendar/events', [CalendarController::class, 'index'])->name('api.calendar.events.index');
        Route::post('/api/calendar/events', [CalendarController::class, 'store'])->name('api.calendar.events.store');
        Route::put('/api/calendar/events/{id}', [CalendarController::class, 'update'])->name('api.calendar.events.update');
        Route::delete('/api/calendar/events/{id}', [CalendarController::class, 'destroy'])->name('api.calendar.events.destroy');
    });
