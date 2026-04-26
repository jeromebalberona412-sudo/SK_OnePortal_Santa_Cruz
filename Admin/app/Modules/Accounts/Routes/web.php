<?php

use App\Modules\Accounts\Controllers\AdminAccountController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'ensure2fa', 'role:admin'])->group(function () {
    Route::get('/manage-account', [AdminAccountController::class, 'indexFederation'])->name('accounts.manage');

    Route::get('/accounts/federation', [AdminAccountController::class, 'indexFederation'])->name('accounts.federation.index');
    Route::get('/accounts/officials', [AdminAccountController::class, 'indexOfficials'])->name('accounts.officials.index');

    Route::get('/accounts/create', [AdminAccountController::class, 'create'])->name('accounts.create');
    Route::post('/accounts', [AdminAccountController::class, 'store'])->name('accounts.store');

    Route::put('/accounts/{user}', [AdminAccountController::class, 'update'])->name('accounts.update');

    Route::post('/accounts/{user}/deactivate', [AdminAccountController::class, 'deactivate'])->name('accounts.deactivate');
    Route::post('/accounts/{user}/reset-password', [AdminAccountController::class, 'resetPassword'])->name('accounts.reset-password');
    Route::post('/accounts/{officialProfile}/extend-term', [AdminAccountController::class, 'extendTerm'])->name('accounts.extend-term');
});
