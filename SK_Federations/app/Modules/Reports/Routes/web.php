<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Reports\Controllers\ReportsController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
});
