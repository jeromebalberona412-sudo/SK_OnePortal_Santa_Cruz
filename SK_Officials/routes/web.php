<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware([
    'auth',
    'verified',
    'single.session',
    'sk_official.access',
    'trusted.device',
    'must.change.password',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('Dashboard::dashboard');
    })->name('dashboard');

    Route::get('/ai-assistant', function () {
        $user = auth()->user();
        $userName = $user->name ?? 'SK Official';
        $userFirstName = explode(' ', trim($userName))[0] ?: 'there';
        $parts = preg_split('/\s+/', trim($userName));
        $userInitials = count($parts) >= 2
            ? strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1))
            : strtoupper(substr($userName, 0, 2));

        return view('AI_Assistant::ai-assistant', compact('userName', 'userFirstName', 'userInitials'));
    })->name('ai-assistant');

    Route::get('/profile', function () {
        return view('Profile::profile');
    })->name('profile');

    Route::get('/change-email', function () {
        return view('Profile::change-email');
    })->name('change-email');

    Route::get('/notifications', function () {
        return view('Profile::notification');
    })->name('notifications');

    Route::get('/calendar', function () {
        return view('Calendar::calendar');
    })->name('calendar');

    Route::get('/announcements', function () {
        $user  = auth()->user();
        $brgy  = \App\Models\Barangay::find($user->barangay_id);
        $slug  = $brgy ? \Illuminate\Support\Str::slug($brgy->name) : 'san-jose';
        $name  = $brgy?->name ?? 'Your Barangay';
        $color = '#f5c518';
        return view('Announcement::announcement', compact('slug', 'name', 'color', 'user'));
    })->name('announcements');

    // Announcement API
    Route::prefix('api/announcements')->group(function () {
        Route::get('/',              [\App\Modules\Announcement\Controllers\AnnouncementController::class, 'feed'])->name('api.announcements.feed');
        Route::post('/',             [\App\Modules\Announcement\Controllers\AnnouncementController::class, 'store'])->name('api.announcements.store');
        Route::post('/upload-image', [\App\Modules\Announcement\Controllers\AnnouncementController::class, 'uploadImage'])->name('api.announcements.upload-image');
        Route::put('/{id}',          [\App\Modules\Announcement\Controllers\AnnouncementController::class, 'update'])->name('api.announcements.update');
        Route::delete('/{id}',       [\App\Modules\Announcement\Controllers\AnnouncementController::class, 'destroy'])->name('api.announcements.destroy');
        Route::post('/{id}/react',   [\App\Modules\Announcement\Controllers\AnnouncementController::class, 'react'])->name('api.announcements.react');
        Route::post('/{id}/comment', [\App\Modules\Announcement\Controllers\AnnouncementController::class, 'comment'])->name('api.announcements.comment');
    });

    Route::get('/announcements/barangay/{slug}', function ($slug) {
        $brgyList = [
            'alipit' => ['name' => 'Alipit', 'color' => '#4CAF50'],
            'bagumbayan' => ['name' => 'Bagumbayan', 'color' => '#2196F3'],
            'bubukal' => ['name' => 'Bubukal', 'color' => '#9C27B0'],
            'duhat' => ['name' => 'Duhat', 'color' => '#FF9800'],
            'gatid' => ['name' => 'Gatid', 'color' => '#009688'],
            'labuin' => ['name' => 'Labuin', 'color' => '#f44336'],
            'pagsawitan' => ['name' => 'Pagsawitan', 'color' => '#673AB7'],
            'san-jose' => ['name' => 'San Jose', 'color' => '#0450a8'],
            'santisima-cruz' => ['name' => 'Santisima Cruz', 'color' => '#FF5722'],
        ];

        $brgy = $brgyList[$slug] ?? ['name' => ucfirst($slug), 'color' => '#f5c518'];

        return view('Announcement::barangay-profile', [
            'slug' => $slug,
            'name' => $brgy['name'],
            'color' => $brgy['color'],
        ]);
    })->name('sk-officials.barangay-profile');

    Route::get('/committees', function () {
        return view('Committees::committees');
    })->name('committees');

    Route::get('/programs', function () {
        return view('Programs::programs');
    })->name('programs');

    Route::get('/budget-finance', function () {
        return view('BudgetFinance::budget-finance');
    })->name('budget-finance');

    Route::get('/kk-profiling-requests', [\App\Modules\KKProfilingRequests\Controllers\KKProfilingRequestsController::class, 'index'])->name('kk-profiling-requests');
    Route::get('/kk-profiling-requests/data', [\App\Modules\KKProfilingRequests\Controllers\KKProfilingRequestsController::class, 'data'])->name('kk-profiling-requests.data');
    Route::post('/kk-profiling-requests/{id}/approve', [\App\Modules\KKProfilingRequests\Controllers\KKProfilingRequestsController::class, 'approve'])->name('kk-profiling-requests.approve');
    Route::post('/kk-profiling-requests/{id}/reject', [\App\Modules\KKProfilingRequests\Controllers\KKProfilingRequestsController::class, 'reject'])->name('kk-profiling-requests.reject');

    Route::get('/abyip', function () {
        return view('ABYIP::abyip');
    })->name('abyip.index');

    Route::get('/kabataan', [\App\Modules\Kabataan\Controllers\KabataanController::class, 'index'])->name('kabataan');
    Route::get('/kabataan/data', [\App\Modules\Kabataan\Controllers\KabataanController::class, 'data'])->name('kabataan.data');

    Route::get('/previous-kabataan', [\App\Modules\PreviousKabataan\Controllers\PreviousKabataanController::class, 'index'])->name('previous-kabataan');
    Route::get('/previous-kabataan/data', [\App\Modules\PreviousKabataan\Controllers\PreviousKabataanController::class, 'data'])->name('previous-kabataan.data');
    Route::post('/previous-kabataan/upload', [\App\Modules\PreviousKabataan\Controllers\PreviousKabataanController::class, 'upload'])->name('previous-kabataan.upload');
    Route::post('/previous-kabataan/move/{id}', [\App\Modules\PreviousKabataan\Controllers\PreviousKabataanController::class, 'moveFromActive'])->name('previous-kabataan.move');

    Route::get('/deleted-kabataan', function () {
        return view('Deleted_Kabataan::deleted-kabataan');
    })->name('deleted-kabataan');

    Route::get('/deleted-abyip', function () {
        return view('Deleted_Abyip::deleted-abyip');
    })->name('deleted-abyip');

    Route::get('/rejected-kkprofiling', function () {
        return view('Rejected_KKProfiling::rejected-kkprofiling');
    })->name('rejected-kkprofiling');

    Route::get('/schedule-kk-profiling', [\App\Modules\ScheduleKKProfiling\Controllers\ScheduleKKProfilingController::class, 'index'])->name('schedule-kk-profiling');
    Route::get('/api/schedule-kk-profiling/data', [\App\Modules\ScheduleKKProfiling\Controllers\ScheduleKKProfilingController::class, 'data'])->name('schedule-kk-profiling.data');
    Route::post('/api/schedule-kk-profiling', [\App\Modules\ScheduleKKProfiling\Controllers\ScheduleKKProfilingController::class, 'store'])->name('schedule-kk-profiling.store');
    Route::put('/api/schedule-kk-profiling/{id}', [\App\Modules\ScheduleKKProfiling\Controllers\ScheduleKKProfilingController::class, 'update'])->name('schedule-kk-profiling.update');
    Route::delete('/api/schedule-kk-profiling/{id}', [\App\Modules\ScheduleKKProfiling\Controllers\ScheduleKKProfilingController::class, 'destroy'])->name('schedule-kk-profiling.destroy');

    Route::get('/schedule-programs', function () {
        return view('schedule_programs::schedule-programs');
    })->name('schedule-programs');

    Route::get('/schedule-programs/sports-application-form', function () {
        return view('schedule_programs::sports.schedule');
    })->name('schedule-programs.sports-application-form');

    Route::get('/sports-application-form', function () {
        return view('schedule_programs::sports.schedule');
    })->name('sports-application-form');

    // ── Scholarship routes (pure front-end, no DB) ──
    Route::get('/scholarship-schedule', function () {
        return view('schedule_programs::scholarship.scholarship-schedule');
    })->name('scholarship.schedule');

    Route::get('/scholarship-schedule/{id}', function ($id) {
        return view('schedule_programs::scholarship.scholarship-schedule', ['formId' => $id]);
    })->name('scholarship.schedule.edit');

    Route::redirect('/scholar-application-form', '/scholarship-schedule');
    Route::redirect('/scholar-application-form/{id}', '/scholarship-schedule/{id}');

    Route::get('/scholarship-applications', function () {
        return view('schedule_programs::scholarship.scholarship-applications');
    })->name('scholarship.applications');

    Route::redirect('/scholarship-application-request', '/scholarship-applications');
    Route::redirect('/scholarship', '/scholarship-applications');

    Route::get('/rejected-scholars', function () {
        return view('Rejected_Scholarship::rejected-scholarship');
    })->name('rejected-scholars');

    Route::redirect('/rejected-scholarship', '/rejected-scholars');

    // ── Approved Scholars (pure front-end, no DB) ──
    Route::get('/approved-scholars', function () {
        return view('schedule_programs::scholarship.approved-scholars');
    })->name('approved-scholars');

    Route::redirect('/scholar-list', '/approved-scholars');

    // ── Scholar Evaluation route (pure front-end, no DB) ──
    Route::get('/scholar-evaluation', function () {
        return view('schedule_programs::scholarship.evaluation');
    })->name('scholar.evaluation');

    Route::redirect('/sports', '/sport_list');

    // ── Sports List route (pure front-end, no DB) ──
    Route::get('/sport_list', function () {
        return view('schedule_programs::sports.list');
    })->name('sport.list');

    // ── Sports requests route (pure front-end, no DB) ──
    Route::get('/sports-requests', function () {
        return view('schedule_programs::sports.requests');
    })->name('sports-requests');

    // ── Sports evaluation route (pure front-end, no DB) ──
    Route::get('/sports-evaluation', function () {
        return view('schedule_programs::sports.evaluation');
    })->name('sports.evaluation');

    // ── Environmental routes (pure front-end, no DB) ──
    Route::get('/environmental-schedule', function () {
        return view('schedule_programs::environmental.schedule');
    })->name('environmental.schedule');
    Route::get('/environmental-requests', function () {
        return view('schedule_programs::environmental.requests');
    })->name('environmental.requests');
    Route::get('/environmental-list', function () {
        return view('schedule_programs::environmental.list');
    })->name('environmental.list');
    Route::get('/environmental-evaluation', function () {
        return view('schedule_programs::environmental.evaluation');
    })->name('environmental.evaluation');

    // ── Disaster routes (pure front-end, no DB) ──
    Route::get('/disaster-schedule', function () {
        return view('schedule_programs::disaster.schedule');
    })->name('disaster.schedule');
    Route::get('/disaster-requests', function () {
        return view('schedule_programs::disaster.requests');
    })->name('disaster.requests');
    Route::get('/disaster-list', function () {
        return view('schedule_programs::disaster.list');
    })->name('disaster.list');
    Route::get('/disaster-evaluation', function () {
        return view('schedule_programs::disaster.evaluation');
    })->name('disaster.evaluation');

    // ── Livelihood routes (pure front-end, no DB) ──
    Route::get('/livelihood-schedule', function () {
        return view('schedule_programs::livelihood.schedule');
    })->name('livelihood.schedule');
    Route::get('/livelihood-requests', function () {
        return view('schedule_programs::livelihood.requests');
    })->name('livelihood.requests');
    Route::get('/livelihood-list', function () {
        return view('schedule_programs::livelihood.list');
    })->name('livelihood.list');
    Route::get('/livelihood-evaluation', function () {
        return view('schedule_programs::livelihood.evaluation');
    })->name('livelihood.evaluation');

    // ── Medicines routes (pure front-end, no DB) ──
    Route::get('/medicines-schedule', function () {
        return view('schedule_programs::medicines.schedule');
    })->name('medicines.schedule');
    Route::get('/medicines-requests', function () {
        return view('schedule_programs::medicines.requests');
    })->name('medicines.requests');
    Route::get('/medicines-list', function () {
        return view('schedule_programs::medicines.list');
    })->name('medicines.list');
    Route::get('/medicines-evaluation', function () {
        return view('schedule_programs::medicines.evaluation');
    })->name('medicines.evaluation');

    // ── Anti-drug routes (pure front-end, no DB) ──
    Route::get('/antidrug-schedule', function () {
        return view('schedule_programs::antidrug.schedule');
    })->name('antidrug.schedule');
    Route::get('/antidrug-requests', function () {
        return view('schedule_programs::antidrug.requests');
    })->name('antidrug.requests');
    Route::get('/antidrug-list', function () {
        return view('schedule_programs::antidrug.list');
    })->name('antidrug.list');
    Route::get('/antidrug-evaluation', function () {
        return view('schedule_programs::antidrug.evaluation');
    })->name('antidrug.evaluation');

    // ── Gender routes (pure front-end, no DB) ──
    Route::get('/gender-schedule', function () {
        return view('schedule_programs::gender.schedule');
    })->name('gender.schedule');
    Route::get('/gender-requests', function () {
        return view('schedule_programs::gender.requests');
    })->name('gender.requests');
    Route::get('/gender-list', function () {
        return view('schedule_programs::gender.list');
    })->name('gender.list');
    Route::get('/gender-evaluation', function () {
        return view('schedule_programs::gender.evaluation');
    })->name('gender.evaluation');

    // ── Feeding routes (pure front-end, no DB) ──
    Route::get('/feeding-schedule', function () {
        return view('schedule_programs::feeding.schedule');
    })->name('feeding.schedule');
    Route::get('/feeding-requests', function () {
        return view('schedule_programs::feeding.requests');
    })->name('feeding.requests');
    Route::get('/feeding-list', function () {
        return view('schedule_programs::feeding.list');
    })->name('feeding.list');
    Route::get('/feeding-evaluation', function () {
        return view('schedule_programs::feeding.evaluation');
    })->name('feeding.evaluation');

    // ── Others routes (pure front-end, no DB) ──
    Route::get('/others-schedule', function () {
        return view('schedule_programs::others.schedule');
    })->name('others.schedule');
    Route::get('/others-requests', function () {
        return view('schedule_programs::others.requests');
    })->name('others.requests');
    Route::get('/others-list', function () {
        return view('schedule_programs::others.list');
    })->name('others.list');
    Route::get('/others-evaluation', function () {
        return view('schedule_programs::others.evaluation');
    })->name('others.evaluation');
});