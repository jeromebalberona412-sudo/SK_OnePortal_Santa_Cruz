<?php

use App\Modules\KKProfiling\Controllers\KKProfilingController;
use Illuminate\Support\Facades\Route;

Route::get('/kkprofiling-signup', [KKProfilingController::class, 'showSignup'])->name('kkprofiling.signup');
Route::get('/api/kkprofiling/open-barangays', [KKProfilingController::class, 'openBarangays'])->name('kkprofiling.open-barangays');

// Email verification
Route::get('/kkprofiling/verify/{id}/{hash}', [KKProfilingController::class, 'verifyEmail'])->name('kkprofiling.verify');
Route::get('/kkprofiling/check-email', [KKProfilingController::class, 'showCheckEmail'])->name('kkprofiling.check-email');
Route::post('/api/kkprofiling/check-email-exists', [KKProfilingController::class, 'checkEmailExists'])->name('kkprofiling.check-email-exists');
Route::post('/api/kkprofiling/resend-verification', [KKProfilingController::class, 'resendVerification'])->name('kkprofiling.resend-verification');

Route::get('/kkprofiling/{barangay}', [KKProfilingController::class, 'show'])->name('kkprofiling');
Route::post('/kkprofiling/{barangay}', [KKProfilingController::class, 'submit'])->name('kkprofiling.submit');

// Set Password
Route::get('/kkprofiling/{barangay}/set-password', [KKProfilingController::class, 'showSetPassword'])->name('kkprofiling.set-password');
Route::post('/kkprofiling/{barangay}/set-password', [KKProfilingController::class, 'storePassword'])->name('kkprofiling.store-password');

Route::middleware(['auth'])->group(function () {
    Route::put('/kkprofiling/update', [KKProfilingController::class, 'updateForUser'])->name('kkprofiling.update');
});
