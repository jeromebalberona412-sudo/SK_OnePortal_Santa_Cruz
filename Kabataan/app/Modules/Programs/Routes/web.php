<?php

use Illuminate\Support\Facades\Route;

/*
| Frontend-only program application pages (no controller / no database).
*/
Route::middleware(['web', 'auth'])->group(function () {
    Route::view('/scholarship/apply', 'programs::scholarship_application')->name('scholarship.apply');
    Route::view('/sports/apply', 'programs::sports-registration')->name('sports.apply');
});
