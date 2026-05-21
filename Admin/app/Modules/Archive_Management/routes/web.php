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

// ── SK Officials Archive — Deleted / Rejected ─────────────────────────────────
Route::get('/manage-archive/deleted-kabataan', function () {
    return view('archive-management::deleted-kabataan');
})->name('archive.deleted-kabataan');

Route::get('/manage-archive/rejected-kk-profiling', function () {
    return view('archive-management::rejected-kk-profiling');
})->name('archive.rejected-kk-profiling');

Route::get('/manage-archive/rejected-scholarships', function () {
    return view('archive-management::rejected-scholarships');
})->name('archive.rejected-scholarships');

// ── Deleted Barangay (Archive) ────────────────────────────────────────────────
Route::middleware(['web', 'auth', 'ensure2fa', 'role:admin'])->group(function () {
    Route::get('/archived/deleted-barangay', function () {
        return view('archive-management::deleted_barangay');
    })->name('archived.deleted-barangay');
});

// ── Deleted SK Federation & Officials (Consolidated) ──────────────────────────
Route::get('/archived/deleted-sk-federation', function () {
    return view('archive-management::deleted-sk-federation');
})->name('archived.deleted-sk-federation');

Route::get('/archived/deleted-sk-officials', function () {
    return view('archive-management::deleted-sk-officials');
})->name('archived.deleted-sk-officials');

