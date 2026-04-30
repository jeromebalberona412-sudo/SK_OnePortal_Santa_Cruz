<?php

use Illuminate\Support\Facades\Route;

Route::get('/archived/sk-federation-records', function () {
    return view('archived-records::SK_Federation_Records');
})->name('archived.sk-federation-records');

Route::get('/archived/sk-officials-records', function () {
    return view('archived-records::SK_Officials_Records');
})->name('archived.sk-officials-records');
