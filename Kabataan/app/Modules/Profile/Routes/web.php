<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Profile\Controllers\ProfileController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/change-password', [ProfileController::class, 'showChangePassword'])->name('change-password');
    Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('change-password.post');
    Route::post('/upload-profile-picture', [ProfileController::class, 'uploadProfilePicture'])->name('profile.upload-picture');
    Route::get('/change-email', [ProfileController::class, 'changeEmail'])->name('change-email');
    Route::post('/change-email', [ProfileController::class, 'postChangeEmail'])->name('change-email.post');
    Route::get('/change-email/verify', [ProfileController::class, 'changeEmailVerify'])->name('change-email.verify');
});
