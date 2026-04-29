<?php

use App\Modules\BarangayLogos\Controllers\BarangayLogoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'ensure2fa', 'role:admin'])->group(function () {
    Route::get('/barangay-logos', [BarangayLogoController::class, 'index'])->name('barangay-logos.index');
});
