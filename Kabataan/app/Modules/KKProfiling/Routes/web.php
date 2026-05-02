<?php

use App\Modules\KKProfiling\Controllers\KKProfilingController;
use Illuminate\Support\Facades\Route;

Route::get('/kkprofiling-signup', [KKProfilingController::class, 'showSignup'])->name('kkprofiling.signup');
Route::get('/kkprofiling/{barangay}', [KKProfilingController::class, 'show'])->name('kkprofiling');
Route::post('/kkprofiling/{barangay}', [KKProfilingController::class, 'submit'])->name('kkprofiling.submit');

// Set Password — separate route for post-email-verification password setup
Route::get('/kkprofiling/{barangay}/set-password', [KKProfilingController::class, 'showSetPassword'])->name('kkprofiling.set-password');
Route::post('/kkprofiling/{barangay}/set-password', [KKProfilingController::class, 'storePassword'])->name('kkprofiling.store-password');
