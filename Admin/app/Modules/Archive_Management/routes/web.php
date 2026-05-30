<?php

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



// ── Deleted SK Federation & Officials (Consolidated) ──────────────────────────
Route::get('/archived/deleted-sk-federation', function () {
    return view('archive-management::deleted-sk-federation');
})->name('archived.deleted-sk-federation');

Route::get('/archived/deleted-sk-officials', function () {
    return view('archive-management::deleted-sk-officials');
})->name('archived.deleted-sk-officials');

