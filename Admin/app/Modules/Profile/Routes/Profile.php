<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Profile\Controllers\ProfileController;

Route::get('/profile', [ProfileController::class, 'index'])
    ->middleware(['auth', 'ensure2fa'])
    ->name('profile');
