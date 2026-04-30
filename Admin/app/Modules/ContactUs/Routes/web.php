<?php

use App\Modules\ContactUs\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'ensure2fa', 'role:admin'])->group(function () {
    Route::get('/manage-contact', [ContactController::class, 'index'])->name('contact.manage');
});
