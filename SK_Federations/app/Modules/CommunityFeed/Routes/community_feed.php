<?php

use App\Modules\CommunityFeed\Controllers\CommunityFeedController;
use Illuminate\Support\Facades\Route;

Route::get('/modules/community-feed/{type}/{file}', function ($type, $file) {
    $path = __DIR__ . "/../assets/{$type}/{$file}";

    if (! file_exists($path)) {
        abort(404);
    }

    $mimeTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'webp' => 'image/webp',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'svg'  => 'image/svg+xml',
    ];

    $extension = pathinfo($file, PATHINFO_EXTENSION);
    $mimeType  = $mimeTypes[$extension] ?? 'application/octet-stream';

    return response()->file($path, ['Content-Type' => $mimeType]);
})->where('type', 'css|js|images')->where('file', '.*');

Route::middleware(['auth', 'verified', 'single.session', 'sk_fed.access', 'trusted.device', 'prevent.back'])
    ->group(function () {
        Route::get('/community-feed', [CommunityFeedController::class, 'index'])->name('community-feed');
        Route::get('/sk-federation-profile', [CommunityFeedController::class, 'skFedProfile'])->name('sk-fed-profile');
        Route::post('/sk-federation-profile/post', [CommunityFeedController::class, 'createPost'])->name('sk-fed-profile.post');
        Route::get('/barangay-profile/{slug}', [CommunityFeedController::class, 'barangayProfile'])->name('skfed.barangay-profile');
    });
