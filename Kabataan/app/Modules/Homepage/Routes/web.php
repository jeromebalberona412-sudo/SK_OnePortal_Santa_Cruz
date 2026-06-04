<?php

use App\Modules\Homepage\Controllers\HomepageController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/homepage');

Route::get('/homepage', [HomepageController::class, 'index'])->name('homepage');

Route::get('/homepage/{section}', [HomepageController::class, 'index'])
    ->whereIn('section', ['about', 'faqs', 'contact'])
    ->name('homepage.section');
