<?php

use App\Modules\KKProfilingRequests\Controllers\KKProfilingRequestsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'single.session', 'sk_official.access', 'must.change.password'])->group(function () {
    Route::get('/kkprofiling-requests', [KKProfilingRequestsController::class, 'index'])->name('kkprofiling-requests');
    Route::get('/kkprofiling-requests/data', [KKProfilingRequestsController::class, 'data'])->name('kkprofiling-requests.data');
    Route::get('/kkprofiling-requests/{id}', [KKProfilingRequestsController::class, 'show'])->name('kkprofiling-requests.show');
    Route::post('/kkprofiling-requests/bulk-approve', [KKProfilingRequestsController::class, 'bulkApprove'])->name('kkprofiling-requests.bulk-approve');
    Route::post('/kkprofiling-requests/{id}/approve', [KKProfilingRequestsController::class, 'approve'])->name('kkprofiling-requests.approve');
    Route::post('/kkprofiling-requests/{id}/reject', [KKProfilingRequestsController::class, 'reject'])->name('kkprofiling-requests.reject');
    Route::put('/kkprofiling-requests/{id}', [KKProfilingRequestsController::class, 'update'])->name('kkprofiling-requests.update');
    Route::get('/kkprofiling-requests/{id}/documents/{documentIndex}/{side}', [KKProfilingRequestsController::class, 'document'])
        ->whereIn('side', ['front', 'back'])
        ->name('kkprofiling-requests.document');
});
