<?php

use App\Modules\Archive_Management\Controllers\DeletedArchiveController;
use Illuminate\Support\Facades\Route;

// ── Archive Management Dashboard ──────────────────────────────────────────────
Route::get('/manage-archive', function () {
    return view('archive-management::dashboard');
})->name('archive.dashboard');

// ── Archived Data (completed term records) ────────────────────────────────────
Route::get('/manage-archive/sk-federation-records', function () {
    return view('archive-management::SK_Federation_Records');
})->name('archived.sk-federation-records');

Route::get('/manage-archive/sk-officials-records', function () {
    return view('archive-management::SK_Officials_Records');
})->name('archived.sk-officials-records');

// ── Deleted SK Federation & Officials ─────────────────────────────────────────
Route::get('/archived/deleted-sk-federation', [DeletedArchiveController::class, 'federationIndex'])
    ->name('archived.deleted-sk-federation');

Route::get('/archived/deleted-sk-federation/data', [DeletedArchiveController::class, 'federationData'])
    ->name('archived.deleted-sk-federation.data');

Route::post('/archived/deleted-sk-federation/{account}/restore', [DeletedArchiveController::class, 'restoreFederation'])
    ->whereNumber('account')
    ->name('archived.deleted-sk-federation.restore');

Route::get('/archived/deleted-sk-officials', [DeletedArchiveController::class, 'officialsIndex'])
    ->name('archived.deleted-sk-officials');

Route::get('/archived/deleted-sk-officials/data', [DeletedArchiveController::class, 'officialsData'])
    ->name('archived.deleted-sk-officials.data');

Route::post('/archived/deleted-sk-officials/{account}/restore', [DeletedArchiveController::class, 'restoreOfficial'])
    ->whereNumber('account')
    ->name('archived.deleted-sk-officials.restore');
