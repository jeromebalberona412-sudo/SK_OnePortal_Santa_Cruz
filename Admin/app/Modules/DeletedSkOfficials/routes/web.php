<?php

use Illuminate\Support\Facades\Route;

Route::get('/archived/deleted-sk-officials', function () {
    return view('deleted-sk-officials::index');
})->name('archived.deleted-sk-officials');
