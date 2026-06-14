<?php

use App\Modules\Reports_Management\Controllers\ReportsManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'single.session',
    'sk_official.access',
    'must.change.password',
])->group(function () {
    Route::get('/reports-management', [ReportsManagementController::class, 'index'])
        ->name('reports-management');

    Route::get('/api/reports-management', [ReportsManagementController::class, 'list'])
        ->name('api.reports-management.index');

    Route::post('/api/reports-management', [ReportsManagementController::class, 'store'])
        ->name('api.reports-management.store');

    Route::delete('/api/reports-management/{id}', [ReportsManagementController::class, 'destroy'])
        ->name('api.reports-management.destroy');

    Route::get('/api/reports-management/{id}/download', [ReportsManagementController::class, 'download'])
        ->name('api.reports-management.download');
});
