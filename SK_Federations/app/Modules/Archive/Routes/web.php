<?php

use App\Modules\Archive\Controllers\ArchiveController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/archive', [ArchiveController::class, 'index'])->name('archive');
});
