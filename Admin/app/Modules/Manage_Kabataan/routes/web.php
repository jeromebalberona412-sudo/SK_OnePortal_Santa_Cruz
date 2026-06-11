<?php

use App\Modules\Manage_Kabataan\Controllers\ManageKabataanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'ensure.password.setup', 'ensure.email.verified', 'role:admin'])
    ->prefix('manage-kabataan')
    ->name('manage-kabataan.')
    ->group(function () {
        Route::get('/', [ManageKabataanController::class, 'index'])->name('index');
        Route::get('/data', [ManageKabataanController::class, 'data'])->name('data');
        Route::get('/{id}', [ManageKabataanController::class, 'show'])->name('show');
    });
