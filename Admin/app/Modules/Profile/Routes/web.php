<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Profile\Controllers\ProfileController;

Route::middleware(['auth', 'ensure2fa'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])
        ->name('profile');

    Route::get('/profile/change-password', [ProfileController::class, 'showChangePassword'])
        ->name('profile.change-password');

    Route::post('/profile/change-password', [ProfileController::class, 'sendChangePasswordLink'])
        ->name('profile.change-password.send');

    Route::get('/profile/change-email', [ProfileController::class, 'showChangeEmail'])
        ->name('profile.change-email');
});
