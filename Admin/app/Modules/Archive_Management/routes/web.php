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

// NOTE: archived.deleted-sk-federation and archived.deleted-sk-officials are
// registered by their own service providers (DeletedSkFederation / DeletedSkOfficials)
// at /archived/deleted-sk-federation and /archived/deleted-sk-officials respectively.
