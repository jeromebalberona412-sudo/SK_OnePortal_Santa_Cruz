<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Profile\Controllers\ProfileController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
    Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('change-password');
    Route::post('/upload-profile-picture', [ProfileController::class, 'uploadProfilePicture'])->name('profile.upload-picture');
});
