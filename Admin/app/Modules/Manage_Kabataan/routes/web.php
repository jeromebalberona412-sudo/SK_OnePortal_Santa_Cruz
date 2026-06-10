<?php

use App\Modules\Manage_Kabataan\Controllers\ManageKabataanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'ensure.password.setup', 'role:admin'])->group(function () {
    Route::prefix('manage-kabataan')->name('manage-kabataan.')->group(function () {
        Route::get('/',      [ManageKabataanController::class, 'index'])->name('index');
        Route::get('/{id}',  [ManageKabataanController::class, 'show'])->name('show');
        Route::put('/{id}',  [ManageKabataanController::class, 'update'])->name('update');
        Route::delete('/{id}', [ManageKabataanController::class, 'destroy'])->name('destroy');
    });
});
