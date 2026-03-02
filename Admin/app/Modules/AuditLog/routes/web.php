<?php

use Illuminate\Support\Facades\Route;
use App\Modules\AuditLog\Controllers\AuditLogController;

Route::get('/audit-logs', [AuditLogController::class, 'index'])
    ->middleware(['auth', 'ensure2fa'])
    ->name('auditlogs.index');
