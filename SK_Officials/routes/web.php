<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/change-email/confirm/{id}/{token}', [\App\Modules\Profile\Controllers\ProfileController::class, 'confirmChangeEmail'])
    ->middleware('throttle:6,1')
    ->name('change-email.confirm');

Route::get('/change-email/set-password/{id}/{token}', [\App\Modules\Profile\Controllers\ProfileController::class, 'showSetPasswordAfterEmailChange'])
    ->middleware('throttle:6,1')
    ->name('change-email.set-password');

Route::post('/change-email/set-password/{id}/{token}', [\App\Modules\Profile\Controllers\ProfileController::class, 'updateSetPasswordAfterEmailChange'])
    ->middleware('throttle:6,1')
    ->name('change-email.set-password.update');

Route::get('/change-password/confirm/{id}/{token}', [\App\Modules\Profile\Controllers\ProfileController::class, 'confirmChangePassword'])
    ->middleware('throttle:6,1')
    ->name('change-password.confirm');

Route::middleware([
    'auth',
    'single.session',
    'sk_official.access',
    'must.change.password',
])->group(function () {
    Route::get('/dashboard', [\App\Modules\Dashboard\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/stats', [\App\Modules\Dashboard\Controllers\DashboardController::class, 'stats'])->name('api.dashboard.stats');

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

    Route::get('/profile', [\App\Modules\Profile\Controllers\ProfileController::class, 'index'])
        ->name('profile');

    Route::get('/change-email', [\App\Modules\Profile\Controllers\ProfileController::class, 'showChangeEmail'])->name('change-email');
    Route::post('/change-email', [\App\Modules\Profile\Controllers\ProfileController::class, 'requestChangeEmail'])->name('change-email.request');
    Route::get('/change-email/verify', [\App\Modules\Profile\Controllers\ProfileController::class, 'showChangeEmailVerify'])->name('change-email.verify');
    Route::post('/change-email/resend', [\App\Modules\Profile\Controllers\ProfileController::class, 'resendChangeEmail'])->name('change-email.resend');
    Route::post('/change-email/cancel', [\App\Modules\Profile\Controllers\ProfileController::class, 'cancelChangeEmail'])->name('change-email.cancel');

    Route::get('/notifications', function () {
        return view('Profile::notification');
    })->name('notifications');

    Route::get('/calendar', function () {
        return view('Calendar::calendar');
    })->name('calendar');

    Route::prefix('api/calendar')->group(function () {
        Route::get('/notes', [\App\Modules\Calendar\Controllers\CalendarController::class, 'index'])->name('api.calendar.notes.index');
        Route::post('/notes', [\App\Modules\Calendar\Controllers\CalendarController::class, 'store'])->name('api.calendar.notes.store');
        Route::put('/notes/{id}', [\App\Modules\Calendar\Controllers\CalendarController::class, 'update'])->name('api.calendar.notes.update');
        Route::delete('/notes/{id}', [\App\Modules\Calendar\Controllers\CalendarController::class, 'destroy'])->name('api.calendar.notes.destroy');
    });

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

    Route::prefix('api/committees')->group(function () {
        Route::get('/', [\App\Modules\Committees\Controllers\CommitteeController::class, 'index'])->name('api.committees.index');
        Route::get('/abyip-programs', [\App\Modules\Committees\Controllers\CommitteeController::class, 'abyipPrograms'])->name('api.committees.abyip-programs');
        Route::get('/sk-officials', [\App\Modules\Committees\Controllers\CommitteeController::class, 'officials'])->name('api.committees.officials');
        Route::post('/', [\App\Modules\Committees\Controllers\CommitteeController::class, 'store'])->name('api.committees.store');
        Route::put('/{id}', [\App\Modules\Committees\Controllers\CommitteeController::class, 'update'])->name('api.committees.update');
    });

    Route::get('/programs', function () {
        return view('Programs::programs');
    })->name('programs');

    Route::prefix('api/programs')->group(function () {
        Route::get('/', [\App\Modules\Programs\Controllers\ProgramController::class, 'index'])->name('api.programs.index');
        Route::get('/management', [\App\Modules\Programs\Controllers\ProgramController::class, 'management'])->name('api.programs.management');
        Route::put('/{programId}/duration', [\App\Modules\Programs\Controllers\ProgramController::class, 'updateDuration'])->name('api.programs.update-duration');
    });

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

    Route::prefix('api/abyip')->group(function () {
        Route::get('/', [\App\Modules\ABYIP\Controllers\AbyipController::class, 'index'])->name('api.abyip.index');
        Route::get('/{id}', [\App\Modules\ABYIP\Controllers\AbyipController::class, 'show'])->name('api.abyip.show');
        Route::post('/', [\App\Modules\ABYIP\Controllers\AbyipController::class, 'store'])->name('api.abyip.store');
        Route::put('/{id}', [\App\Modules\ABYIP\Controllers\AbyipController::class, 'update'])->name('api.abyip.update');
        Route::delete('/{id}', [\App\Modules\ABYIP\Controllers\AbyipController::class, 'destroy'])->name('api.abyip.destroy');
    });

    Route::get('/kabataan', [\App\Modules\Kabataan\Controllers\KabataanController::class, 'index'])->name('kabataan');
    Route::get('/kabataan/data', [\App\Modules\Kabataan\Controllers\KabataanController::class, 'data'])->name('kabataan.data');
    Route::post('/kabataan', [\App\Modules\Kabataan\Controllers\KabataanController::class, 'store'])->name('kabataan.store');
    Route::put('/kabataan/{id}', [\App\Modules\Kabataan\Controllers\KabataanController::class, 'update'])->name('kabataan.update');
    Route::delete('/kabataan/{id}', [\App\Modules\Kabataan\Controllers\KabataanController::class, 'destroy'])->name('kabataan.destroy');

    Route::get('/previous-kabataan', [\App\Modules\PreviousKabataan\Controllers\PreviousKabataanController::class, 'index'])->name('previous-kabataan');
    Route::get('/previous-kabataan/data', [\App\Modules\PreviousKabataan\Controllers\PreviousKabataanController::class, 'data'])->name('previous-kabataan.data');
    Route::post('/previous-kabataan/upload', [\App\Modules\PreviousKabataan\Controllers\PreviousKabataanController::class, 'upload'])->name('previous-kabataan.upload');
    Route::post('/previous-kabataan/move/{id}', [\App\Modules\PreviousKabataan\Controllers\PreviousKabataanController::class, 'moveFromActive'])->name('previous-kabataan.move');

    Route::get('/deleted-kabataan', [\App\Modules\Deleted_Kabataan\Controllers\DeletedKabataanController::class, 'index'])->name('deleted-kabataan');
    Route::get('/deleted-kabataan/data', [\App\Modules\Deleted_Kabataan\Controllers\DeletedKabataanController::class, 'data'])->name('deleted-kabataan.data');
    Route::post('/deleted-kabataan/{id}/restore', [\App\Modules\Deleted_Kabataan\Controllers\DeletedKabataanController::class, 'restore'])->name('deleted-kabataan.restore');

    Route::get('/deleted-abyip', function () {
        return view('Deleted_Abyip::deleted-abyip');
    })->name('deleted-abyip');

    Route::get('/rejected-kkprofiling', [\App\Modules\Rejected_KKProfiling\Controllers\RejectedKKProfilingController::class, 'index'])->name('rejected-kkprofiling');
    Route::get('/rejected-kkprofiling/data', [\App\Modules\Rejected_KKProfiling\Controllers\RejectedKKProfilingController::class, 'data'])->name('rejected-kkprofiling.data');
    Route::post('/rejected-kkprofiling/{id}/restore', [\App\Modules\Rejected_KKProfiling\Controllers\RejectedKKProfilingController::class, 'restore'])->name('rejected-kkprofiling.restore');

    Route::get('/schedule-kk-profiling', [\App\Modules\ScheduleKKProfiling\Controllers\ScheduleKKProfilingController::class, 'index'])->name('schedule-kk-profiling');
    Route::get('/api/schedule-kk-profiling/data', [\App\Modules\ScheduleKKProfiling\Controllers\ScheduleKKProfilingController::class, 'data'])->name('schedule-kk-profiling.data');
    Route::post('/api/schedule-kk-profiling', [\App\Modules\ScheduleKKProfiling\Controllers\ScheduleKKProfilingController::class, 'store'])->name('schedule-kk-profiling.store');
    Route::put('/api/schedule-kk-profiling/{id}', [\App\Modules\ScheduleKKProfiling\Controllers\ScheduleKKProfilingController::class, 'update'])->name('schedule-kk-profiling.update');
    Route::delete('/api/schedule-kk-profiling/{id}', [\App\Modules\ScheduleKKProfiling\Controllers\ScheduleKKProfilingController::class, 'destroy'])->name('schedule-kk-profiling.destroy');

    Route::get('/schedule-programs', function () {
        $catalog = app(\App\Modules\Programs\Services\AbyipProgramCatalogService::class);
        $management = $catalog->listForManagement(auth()->user());

        return view('Program_Management::program-management', [
            'managementPrograms' => $management['programs'],
            'calendarYear' => $management['calendar_year'],
        ]);
    })->name('schedule-programs'); // legacy route name kept for sidebar

    Route::get('/program-management', function () {
        return redirect()->route('schedule-programs');
    });

    Route::get('/schedule-programs/sports-application-form', function () {
        return view('Program_Management::sports.schedule');
    })->name('schedule-programs.sports-application-form');

    Route::get('/sports-application-form', function () {
        return view('Program_Management::sports.schedule');
    })->name('sports-application-form');

    Route::prefix('api/schedule-programs')->group(function () {
        Route::get('/meta', [\App\Modules\Program_Management\Controllers\ScheduleProgramController::class, 'meta'])->name('api.schedule-programs.meta');
        Route::get('/', [\App\Modules\Program_Management\Controllers\ScheduleProgramController::class, 'index'])->name('api.schedule-programs.index');
        Route::get('/{id}', [\App\Modules\Program_Management\Controllers\ScheduleProgramController::class, 'show'])->name('api.schedule-programs.show');
        Route::post('/', [\App\Modules\Program_Management\Controllers\ScheduleProgramController::class, 'store'])->name('api.schedule-programs.store');
        Route::put('/{id}', [\App\Modules\Program_Management\Controllers\ScheduleProgramController::class, 'update'])->name('api.schedule-programs.update');
        Route::delete('/{id}', [\App\Modules\Program_Management\Controllers\ScheduleProgramController::class, 'destroy'])->name('api.schedule-programs.destroy');
    });

    Route::prefix('api/program-surveys/{committee}')->group(function () {
        Route::get('/meta', [\App\Modules\Program_Management\Controllers\ProgramSurveyController::class, 'meta'])->name('api.program-surveys.meta');
        Route::get('/responses', [\App\Modules\Program_Management\Controllers\ProgramSurveyController::class, 'responses'])->name('api.program-surveys.responses');
        Route::get('/', [\App\Modules\Program_Management\Controllers\ProgramSurveyController::class, 'index'])->name('api.program-surveys.index');
        Route::get('/{id}', [\App\Modules\Program_Management\Controllers\ProgramSurveyController::class, 'show'])->name('api.program-surveys.show');
        Route::post('/', [\App\Modules\Program_Management\Controllers\ProgramSurveyController::class, 'store'])->name('api.program-surveys.store');
        Route::put('/{id}', [\App\Modules\Program_Management\Controllers\ProgramSurveyController::class, 'update'])->name('api.program-surveys.update');
        Route::delete('/{id}', [\App\Modules\Program_Management\Controllers\ProgramSurveyController::class, 'destroy'])->name('api.program-surveys.destroy');
    });

    Route::get('/scholarship-schedule', function () {
        return view('Program_Management::scholarship.scholarship-schedule');
    })->name('scholarship.schedule');

    Route::get('/scholarship-schedule/{id}', function ($id) {
        return view('Program_Management::scholarship.scholarship-schedule', ['formId' => $id]);
    })->name('scholarship.schedule.edit');

    Route::redirect('/scholar-application-form', '/scholarship-schedule');
    Route::redirect('/scholar-application-form/{id}', '/scholarship-schedule/{id}');

    Route::get('/scholarship-applications', function () {
        return view('Program_Management::scholarship.scholarship-applications');
    })->name('scholarship.applications');

    Route::redirect('/scholarship-application-request', '/scholarship-applications');
    Route::redirect('/scholarship', '/scholarship-applications');

    Route::get('/rejected-scholars', function () {
        return view('Rejected_Scholarship::rejected-scholarship');
    })->name('rejected-scholars');

    Route::redirect('/rejected-scholarship', '/rejected-scholars');

    Route::get('/rejected-sports', function () {
        return view('Rejected_Sports::rejected-sports');
    })->name('rejected-sports');

    // ── Approved Scholars (pure front-end, no DB) ──
    Route::get('/approved-scholars', function () {
        return view('Program_Management::scholarship.approved-scholars');
    })->name('approved-scholars');

    Route::redirect('/scholar-list', '/approved-scholars');

    // ── Scholar Evaluation route (pure front-end, no DB) ──
    Route::get('/scholar-evaluation', function () {
        return view('Program_Management::scholarship.evaluation');
    })->name('scholar.evaluation');

    Route::redirect('/sports', '/sport_list');

    // ── Sports List route (pure front-end, no DB) ──
    Route::get('/sport_list', function () {
        return view('Program_Management::sports.list');
    })->name('sport.list');

    // ── Sports requests route (pure front-end, no DB) ──
    Route::get('/sports-requests', function () {
        return view('Program_Management::sports.requests');
    })->name('sports-requests');

    // ── Sports evaluation route (pure front-end, no DB) ──
    Route::get('/sports-evaluation', function () {
        return view('Program_Management::sports.evaluation');
    })->name('sports.evaluation');

    // ── Committee survey modules (config-driven: one blade + JS per tab) ──
    $surveyPrograms = require app_path('Modules/Program_Management/config/survey-programs.php');

    $surveyTabViews = [
        'forms' => ['view' => 'survey.survey-forms', 'title' => 'Survey Forms', 'subtitle' => 'Create and manage survey questions for Kabataan members.'],
        'results' => ['view' => 'survey.survey-results', 'title' => 'Survey Results', 'subtitle' => 'View Kabataan who answered your surveys and read each response.'],
        'analytics' => ['view' => 'survey.survey-analytics', 'title' => 'Survey Analytics', 'subtitle' => 'Charts and counts per question — see how many chose each answer.'],
    ];

    foreach ($surveyPrograms as $key => $meta) {
        foreach ($surveyTabViews as $tab => $tabMeta) {
            Route::get("/{$key}-survey-{$tab}", function () use ($key, $meta, $tab, $tabMeta) {
                return view('Program_Management::'.$tabMeta['view'], [
                    'committee' => $key,
                    'activeTab' => $tab,
                    'committeeTitle' => $meta['title'],
                    'committeeDescription' => $meta['description'],
                    'skHead' => $meta['sk_head'],
                    'activities' => $meta['activities'],
                    'pageTitle' => $tabMeta['title'],
                    'pageSubtitle' => $tabMeta['subtitle'],
                ]);
            })->name("{$key}.survey.{$tab}");
        }

        Route::redirect("/{$key}-schedule", "/{$key}-survey-forms");
        Route::redirect("/{$key}-requests", "/{$key}-survey-results");
        Route::redirect("/{$key}-list", "/{$key}-survey-results");
        Route::redirect("/{$key}-evaluation", "/{$key}-survey-analytics");
    }
});