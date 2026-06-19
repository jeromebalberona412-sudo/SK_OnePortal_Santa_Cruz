<?php

namespace App\Modules\Notifications\Controllers;

use App\Modules\Notifications\Services\SampleNotificationService;
use Illuminate\Contracts\View\View;

class NotificationPageController
{
    public function __construct(private readonly SampleNotificationService $sampleNotificationService) {}

    public function index(): View
    {
        return view('notifications::index', [
            'notifications' => $this->sampleNotificationService->allSamples(),
        ]);
    }
}
