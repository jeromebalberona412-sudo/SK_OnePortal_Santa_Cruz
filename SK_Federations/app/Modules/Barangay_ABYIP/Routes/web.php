<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Barangay_ABYIP\Controllers\BarangayAbyipController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/barangay-abyip', [BarangayAbyipController::class, 'index'])->name('barangay.abyip');
});
