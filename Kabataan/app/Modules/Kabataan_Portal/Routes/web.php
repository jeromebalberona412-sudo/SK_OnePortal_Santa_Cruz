<?php

use App\Modules\Kabataan_Portal\Controllers\KabataanPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/portal', [KabataanPortalController::class, 'index'])->name('portal');
