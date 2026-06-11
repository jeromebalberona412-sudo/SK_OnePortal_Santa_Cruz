<?php

use App\Modules\Archive_Management\Controllers\DeletedArchiveController;
use App\Modules\Archive_Management\Controllers\TermRecordsArchiveController;
use Illuminate\Support\Facades\Route;

// ── Archive Management Dashboard ──────────────────────────────────────────────
Route::get('/manage-archive', function () {
    return view('archive-management::dashboard');
})->name('archive.dashboard');

// ── Archived Data (completed term records) ────────────────────────────────────
Route::get('/manage-archive/sk-federation-records', function () {
    return view('archive-management::SK_Federation_Records');
})->name('archived.sk-federation-records');

Route::get('/manage-archive/sk-federation-records/data', [TermRecordsArchiveController::class, 'federationData'])
    ->name('archived.sk-federation-records.data');

Route::post('/manage-archive/sk-federation-records/{record}/restore', [TermRecordsArchiveController::class, 'restoreFederationRecord'])
    ->whereNumber('record')
    ->name('archived.sk-federation-records.restore');

Route::get('/manage-archive/sk-officials-records', function () {
    return view('archive-management::SK_Officials_Records');
})->name('archived.sk-officials-records');

Route::get('/manage-archive/sk-officials-records/data', [TermRecordsArchiveController::class, 'officialsData'])
    ->name('archived.sk-officials-records.data');

Route::post('/manage-archive/sk-officials-records/{record}/restore', [TermRecordsArchiveController::class, 'restoreOfficialRecord'])
    ->whereNumber('record')
    ->name('archived.sk-officials-records.restore');

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
