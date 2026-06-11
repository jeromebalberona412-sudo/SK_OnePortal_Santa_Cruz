<?php

use Illuminate\Support\Facades\Route;
use App\Modules\AuditLog\Controllers\AuditLogController;

Route::middleware(['auth', 'ensure.password.setup', 'ensure.email.verified'])->group(function () {
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('auditlogs.index');
    Route::get('/audit-logs/data', [AuditLogController::class, 'data'])->name('auditlogs.data');
    Route::get('/audit-logs/stats', [AuditLogController::class, 'stats'])->name('auditlogs.stats');
    Route::get('/audit-logs/recent', [AuditLogController::class, 'recent'])->name('auditlogs.recent');
    Route::get('/audit-logs/export/csv', [AuditLogController::class, 'exportCsv'])->name('auditlogs.export.csv');
    Route::get('/audit-logs/export/excel', [AuditLogController::class, 'exportExcel'])->name('auditlogs.export.excel');
});
