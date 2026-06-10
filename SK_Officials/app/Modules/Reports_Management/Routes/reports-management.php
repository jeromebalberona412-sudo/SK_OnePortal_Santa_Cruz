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
});
