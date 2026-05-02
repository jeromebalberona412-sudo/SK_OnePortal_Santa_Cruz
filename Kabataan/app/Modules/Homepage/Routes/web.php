<?php

use App\Modules\Homepage\Controllers\HomepageController;
use Illuminate\Support\Facades\Route;

Route::get('/homepage', [HomepageController::class, 'index'])->name('homepage');
Route::get('/about', [HomepageController::class, 'about'])->name('about');
Route::get('/programs', [HomepageController::class, 'programs'])->name('programs');
Route::get('/faqs', [HomepageController::class, 'faqs'])->name('faqs');
Route::get('/contact', [HomepageController::class, 'contact'])->name('contact');
