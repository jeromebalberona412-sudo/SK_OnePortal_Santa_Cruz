<?php

use App\Modules\Profile\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'single.session', 'sk_fed.access', 'trusted.device'])
    ->group(function () {
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    });
