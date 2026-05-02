<?php

use App\Modules\KKProfilingRequests\Controllers\KKProfilingRequestsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/kkprofiling-requests', [KKProfilingRequestsController::class, 'index'])->name('kkprofiling-requests');
    Route::get('/kkprofiling-requests/data', [KKProfilingRequestsController::class, 'data'])->name('kkprofiling-requests.data');
    Route::post('/kkprofiling-requests/{id}/approve', [KKProfilingRequestsController::class, 'approve'])->name('kkprofiling-requests.approve');
    Route::post('/kkprofiling-requests/{id}/reject', [KKProfilingRequestsController::class, 'reject'])->name('kkprofiling-requests.reject');
});
