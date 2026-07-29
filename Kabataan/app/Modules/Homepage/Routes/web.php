<?php

use App\Http\Controllers\Admin\AbyipUploadController;
use App\Modules\Homepage\Controllers\BarangayAbyipController;
use App\Modules\Homepage\Controllers\HomepageController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/homepage');

Route::get('/homepage', [HomepageController::class, 'index'])->name('homepage');

Route::get('/homepage/{section}', [HomepageController::class, 'index'])
    ->whereIn('section', ['about', 'faqs', 'contact'])
    ->name('homepage.section');

Route::get('/barangays', [BarangayAbyipController::class, 'index'])
    ->name('homepage.barangays');

Route::get('/barangays/{barangay:slug}', [BarangayAbyipController::class, 'show'])
    ->name('homepage.barangays.show');

Route::middleware(['auth', 'staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/abyip/upload', [AbyipUploadController::class, 'create'])->name('abyip.upload.create');
    Route::post('/abyip/upload', [AbyipUploadController::class, 'store'])->name('abyip.upload.store');
});
