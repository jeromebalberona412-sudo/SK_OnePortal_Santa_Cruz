<?php

use App\Modules\Authentication\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/modules/authentication/{type}/{file}', function ($type, $file) {
	$path = __DIR__ . "/../assets/{$type}/{$file}";

	if (!file_exists($path)) {
		abort(404);
	}

	$mimeTypes = [
		'css' => 'text/css',
		'js' => 'application/javascript',
		'webp' => 'image/webp',
		'png' => 'image/png',
		'jpg' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'svg' => 'image/svg+xml',
	];

	$extension = pathinfo($file, PATHINFO_EXTENSION);
	$mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

	return response()->file($path, ['Content-Type' => $mimeType]);
})->where('type', 'css|js|images')->where('file', '.*');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/password/request', [AuthController::class, 'showPasswordRequest'])->name('password.request');

Route::post('/password/email', [AuthController::class, 'sendPasswordEmail'])->name('password.email');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');