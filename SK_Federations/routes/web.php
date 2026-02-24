<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function () {

    return redirect('/dashboard');
})->name('login.submit');

Route::get('/password/request', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::post('/password/email', function () {
    return back()->with('status', 'Password reset link sent!');
})->name('password.email');

Route::get('/dashboard', function () {
    return 'Welcome to Dashboard!';
})->name('dashboard');

Route::post('/logout', function () {
    return redirect('/login');
})->name('logout');
