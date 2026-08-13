<?php

use App\Modules\Community_feed\Controllers\CommunityFeedController;
use App\Modules\Community_feed\Controllers\CommunityFeedPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/community-feed', [CommunityFeedPageController::class, 'index'])
        ->name('community-feed.index');

    Route::post('/community-feed/upload-image', [CommunityFeedController::class, 'uploadImage'])
        ->name('community-feed.upload-image');
});
