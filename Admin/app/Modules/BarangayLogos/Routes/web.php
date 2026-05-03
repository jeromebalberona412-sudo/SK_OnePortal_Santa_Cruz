<?php

use App\Modules\BarangayLogos\Controllers\BarangayLogoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'ensure2fa', 'role:admin'])->group(function () {
    Route::get('/barangay-logos', [BarangayLogoController::class, 'index'])->name('barangay-logos.index');
    Route::post('/barangay-logos/upload', [BarangayLogoController::class, 'upload'])->name('barangay-logos.upload');
    Route::delete('/barangay-logos/{id}', [BarangayLogoController::class, 'delete'])->name('barangay-logos.delete');
});
