<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Programs\Controllers\ProgramController;

Route::middleware(['web', 'auth'])->group(function () {
    // Scholarship application form view
    Route::get('/scholarship/apply', [ProgramController::class, 'showScholarshipApplication'])->name('scholarship.apply');
    
    // Get all program categories
    Route::get('/api/programs/categories', [ProgramController::class, 'getCategories'])->name('programs.categories');
    
    // Get programs by category
    Route::get('/api/programs/category/{categoryId}', [ProgramController::class, 'getByCategory'])->name('programs.byCategory');
    
    // Get single program
    Route::get('/api/programs/{programId}', [ProgramController::class, 'getProgram'])->name('programs.show');
    
    // Submit program application
    Route::post('/api/programs/apply', [ProgramController::class, 'submitApplication'])->name('programs.apply');
});
