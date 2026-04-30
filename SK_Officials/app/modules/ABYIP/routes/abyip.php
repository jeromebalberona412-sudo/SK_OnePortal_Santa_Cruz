<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ABYIPController;

// Guest middleware can be removed if authentication is not needed
Route::middleware(['auth'])->group(function () {
    Route::get('/abyip', [ABYIPController::class, 'index'])->name('abyip.index');
});
