<?php

use App\Modules\KKProfiling\Controllers\KKProfilingController;
use App\Modules\KKProfiling\Controllers\KKProfilingWizardController;
use Illuminate\Support\Facades\Route;

Route::get('/kkprofiling-signup', [KKProfilingController::class, 'showSignup'])->name('kkprofiling.signup');
Route::get('/api/kkprofiling/open-barangays', [KKProfilingController::class, 'openBarangays'])->name('kkprofiling.open-barangays');

// Email verification
Route::get('/kkprofiling/verify/{id}/{hash}', [KKProfilingController::class, 'verifyEmail'])->name('kkprofiling.verify');
Route::get('/kkprofiling/check-email', [KKProfilingController::class, 'showCheckEmail'])->name('kkprofiling.check-email');
Route::post('/api/kkprofiling/check-email-exists', [KKProfilingController::class, 'checkEmailExists'])->name('kkprofiling.check-email-exists');
Route::post('/api/kkprofiling/resend-verification', [KKProfilingController::class, 'resendVerification'])->name('kkprofiling.resend-verification');

// Registration wizard — register BEFORE /kkprofiling/{barangay} to avoid "wizard" slug conflicts
Route::get('/kkprofiling/wizard/set-password/{token}/{hash}', [KKProfilingWizardController::class, 'openSetPasswordFromEmail'])
    ->name('kkprofiling.wizard.set-password');

Route::get('/kkprofiling/wizard/verify/{token}/{hash}', [KKProfilingWizardController::class, 'openSetPasswordFromEmail'])
    ->name('kkprofiling.wizard.verify');

Route::post('/api/kkprofiling/wizard/set-password/{token}/finalize', [KKProfilingWizardController::class, 'finalizeByToken'])
    ->name('kkprofiling.wizard.finalize-token');

Route::prefix('/api/kkprofiling/{barangay}/wizard')->group(function () {
    Route::get('/status', [KKProfilingWizardController::class, 'status'])->name('kkprofiling.wizard.status');
    Route::get('/document/{type}/{side?}', [KKProfilingWizardController::class, 'documentPreview'])
        ->where('type', 'school_id|national_id')
        ->where('side', 'front|back')
        ->name('kkprofiling.wizard.document-preview');
    Route::get('/registration-complete', [KKProfilingWizardController::class, 'checkRegistrationComplete'])->name('kkprofiling.wizard.registration-complete');
    Route::post('/step-1', [KKProfilingWizardController::class, 'saveStep1'])->name('kkprofiling.wizard.step1');
    Route::post('/step-2', [KKProfilingWizardController::class, 'saveStep2'])->name('kkprofiling.wizard.step2');
    Route::post('/set-step', [KKProfilingWizardController::class, 'setStep'])->name('kkprofiling.wizard.set-step');
    Route::post('/send-verification', [KKProfilingWizardController::class, 'sendVerification'])->name('kkprofiling.wizard.send-verification');
    Route::post('/resend-verification', [KKProfilingWizardController::class, 'resendVerification'])->name('kkprofiling.wizard.resend-verification');
    Route::post('/finalize', [KKProfilingWizardController::class, 'finalize'])->name('kkprofiling.wizard.finalize');
    Route::post('/clear-draft', [KKProfilingWizardController::class, 'clearDraft'])->name('kkprofiling.wizard.clear-draft');
});

Route::get('/kkprofiling/{barangay}', [KKProfilingController::class, 'show'])
    ->where('barangay', '^(?!wizard$).*')
    ->name('kkprofiling');
Route::post('/kkprofiling/{barangay}', [KKProfilingController::class, 'submit'])
    ->where('barangay', '^(?!wizard$).*')
    ->name('kkprofiling.submit');

// Set Password (legacy session-based flow after kkprofiling.verify)
Route::get('/kkprofiling/{barangay}/set-password', [KKProfilingController::class, 'showSetPassword'])
    ->where('barangay', '^(?!wizard$).*')
    ->name('kkprofiling.set-password');
Route::post('/kkprofiling/{barangay}/set-password', [KKProfilingController::class, 'storePassword'])
    ->where('barangay', '^(?!wizard$).*')
    ->name('kkprofiling.store-password');

Route::middleware(['auth'])->group(function () {
    Route::put('/kkprofiling/update', [KKProfilingController::class, 'updateForUser'])->name('kkprofiling.update');
});
