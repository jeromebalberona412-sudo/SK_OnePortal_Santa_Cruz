<?php

use App\Modules\Homepage\Controllers\HomepageController;
use Illuminate\Support\Facades\Route;

Route::get('/homepage', [HomepageController::class, 'index'])->name('homepage');
Route::get('/about', [HomepageController::class, 'about'])->name('about');
