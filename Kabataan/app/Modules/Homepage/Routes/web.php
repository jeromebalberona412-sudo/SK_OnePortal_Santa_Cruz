<?php

use App\Modules\Homepage\Controllers\HomepageController;
use Illuminate\Support\Facades\Route;

Route::get('/homepage', [HomepageController::class, 'index'])->name('homepage');
Route::get('/about', [HomepageController::class, 'about'])->name('about');
Route::get('/kkprofiling/{barangay}', [HomepageController::class, 'kkProfiling'])->name('kkprofiling');
Route::post('/kkprofiling/{barangay}', [HomepageController::class, 'kkProfilingSubmit'])->name('kkprofiling.submit');
