<?php

use App\Modules\KKProfiling\Controllers\KKProfilingController;
use Illuminate\Support\Facades\Route;

Route::get('/kkprofiling/{barangay}', [KKProfilingController::class, 'show'])->name('kkprofiling');
Route::post('/kkprofiling/{barangay}', [KKProfilingController::class, 'submit'])->name('kkprofiling.submit');
