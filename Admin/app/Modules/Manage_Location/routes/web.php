<?php

use App\Modules\Manage_Location\Controllers\ManageLocationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'ensure2fa', 'role:admin'])->group(function () {
    Route::prefix('manage-location')->name('manage-location.')->group(function () {
        Route::get('/',        [ManageLocationController::class, 'index'])->name('index');
        Route::post('/',       [ManageLocationController::class, 'store'])->name('store');
        Route::get('/{id}',    [ManageLocationController::class, 'show'])->name('show');
        Route::put('/{id}',    [ManageLocationController::class, 'update'])->name('update');
        Route::delete('/{id}', [ManageLocationController::class, 'destroy'])->name('destroy');
    });
});
