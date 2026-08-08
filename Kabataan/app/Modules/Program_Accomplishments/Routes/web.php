<?php

use App\Modules\Program_Accomplishments\Controllers\ProgramAccomplishmentsController;
use Illuminate\Support\Facades\Route;

Route::get('/accomplishments', [ProgramAccomplishmentsController::class, 'index'])
    ->name('program_accomplishments.barangays');

Route::get('/accomplishments/{barangay:slug}', [ProgramAccomplishmentsController::class, 'show'])
    ->name('program_accomplishments.barangays.show');
