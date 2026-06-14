<?php

use App\Modules\Calendar\Controllers\CalendarController;
use Illuminate\Support\Facades\Route;

// Calendar UI route
Route::middleware(['web', 'auth'])
    ->group(function () {
        Route::get('/calendar', [CalendarController::class, 'page'])->name('calendar');
    });

// Calendar API routes (protected via Sanctum)
Route::middleware(['auth:sanctum'])->prefix('api')->group(function () {
    Route::get('/calendar/events', [CalendarController::class, 'index']);
    Route::post('/calendar/events', [CalendarController::class, 'store']);
    Route::put('/calendar/events/{id}', [CalendarController::class, 'update']);
    Route::delete('/calendar/events/{id}', [CalendarController::class, 'destroy']);
});
