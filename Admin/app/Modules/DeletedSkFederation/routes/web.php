<?php

use Illuminate\Support\Facades\Route;

Route::get('/archived/deleted-sk-federation', function () {
    return view('deleted-sk-federation::index');
})->name('archived.deleted-sk-federation');
