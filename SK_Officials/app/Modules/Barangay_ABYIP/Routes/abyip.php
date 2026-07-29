<?php

use App\Modules\Barangay_ABYIP\Controllers\AbyipController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/abyip', fn () => view('Barangay_ABYIP::abyip'))->name('abyip.module.index');
});
