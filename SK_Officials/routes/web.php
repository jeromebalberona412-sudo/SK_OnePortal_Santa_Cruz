<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Health check endpoint for Render
Route::get('/health', function () {
    $health = [
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'services' => [
            'database' => 'connected',
            'cache' => 'connected',
        ],
    ];

    try {
        DB::connection()->getPdo();
        $health['services']['database'] = 'connected';
    } catch (Exception $e) {
        $health['services']['database'] = 'disconnected';
        $health['status'] = 'degraded';
    }

    try {
        Cache::store()->get('health_check', 'ok');
        $health['services']['cache'] = 'connected';
    } catch (Exception $e) {
        $health['services']['cache'] = 'disconnected';
        $health['status'] = 'degraded';
    }

    $statusCode = $health['status'] === 'ok' ? 200 : 503;

    return response()->json($health, $statusCode);
})->name('health');

use App\Http\Controllers\ArchiveTermController;
use App\Modules\Archived_Youth_Records\Controllers\ArchivedYouthRecordsController;
use App\Modules\Authentication\Controllers\AuthController;
use App\Modules\Barangay_ABYIP\Controllers\AbyipController;
use App\Modules\Calendar\Controllers\CalendarController;
use App\Modules\Committees\Controllers\CommitteeController;
use App\Modules\Community_feed\Controllers\ArchiveCommunityFeedController;
use App\Modules\Community_feed\Controllers\BarangayProfileController;
use App\Modules\Community_feed\Controllers\CommunityFeedCommentController;
use App\Modules\Community_feed\Controllers\CommunityFeedController;
use App\Modules\Community_feed\Controllers\CommunityFeedImageController;
use App\Modules\Community_feed\Controllers\CommunityFeedPageController;
use App\Modules\Community_feed\Controllers\CommunityFeedReactionController;
use App\Modules\Dashboard\Controllers\DashboardController;
use App\Modules\Deleted_Kabataan\Controllers\DeletedKabataanController;
use App\Modules\Kabataan\Controllers\KabataanController;
use App\Modules\KKProfilingRequests\Controllers\KKProfilingRequestsController;
// use App\Modules\PreviousKabataan\Controllers\PreviousKabataanController;
use App\Modules\Profile\Controllers\NotificationController;
use App\Modules\Profile\Controllers\ProfileController;
use App\Modules\Program_Accomplishment\Controllers\ProgramAccomplishmentController;
use App\Modules\Program_Accomplishment\Services\ProgramAccomplishmentService;
use App\Modules\Program_Management\Controllers\ProgramApplicationController;
use App\Modules\Program_Management\Controllers\ProgramEvaluationController;
use App\Modules\Program_Management\Controllers\ProgramSurveyController;
use App\Modules\Program_Management\Controllers\ScheduleProgramController;
use App\Modules\Program_Management\Controllers\ScholarshipSchoolYearController;
use App\Modules\Programs\Controllers\ProgramController;
use App\Modules\Programs\Services\AbyipProgramCatalogService;
use App\Modules\Rejected_KKProfiling\Controllers\RejectedKKProfilingController;
use App\Modules\Rejected_Scholarship\Controllers\RejectedScholarshipController;
use App\Modules\Rejected_Sports\Controllers\RejectedSportsController;
use App\Modules\ScheduleKKProfiling\Controllers\ScheduleKKProfilingController;
use App\Modules\Sports_Programs\Controllers\ArchivedSportsProgramController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/forgot-password/check-email', [AuthController::class, 'showForgotPasswordCheckEmail'])
    ->middleware('guest')
    ->name('password.check-email');

Route::get('/change-email/confirm/{id}/{token}', [ProfileController::class, 'confirmChangeEmail'])
    ->middleware('throttle:6,1')
    ->name('change-email.confirm');

Route::get('/change-email/set-password/{id}/{token}', [ProfileController::class, 'showSetPasswordAfterEmailChange'])
    ->middleware('throttle:6,1')
    ->name('change-email.set-password');

Route::post('/change-email/set-password/{id}/{token}', [ProfileController::class, 'updateSetPasswordAfterEmailChange'])
    ->middleware('throttle:6,1')
    ->name('change-email.set-password.update');

Route::get('/change-password/confirm/{id}/{token}', [ProfileController::class, 'confirmChangePassword'])
    ->middleware('throttle:6,1')
    ->name('change-password.confirm');

Route::get('/accomplishment/{id}', function ($id) {
    try {
        $report = app(ProgramAccomplishmentService::class)->getPublishedById($id);

        return view('Program_Accomplishment::public.show', compact('report'));
    } catch (Exception $e) {
        abort(404, 'Accomplishment report not found.');
    }
})->name('public.accomplishment.show');

Route::middleware([
    'auth',
    'single.session',
    'sk_official.access',
    'must.change.password',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/stats', [DashboardController::class, 'stats'])->name('api.dashboard.stats');
    Route::get('/api/archive/terms', [ArchiveTermController::class, 'index'])->name('api.archive.terms');

    Route::get('/ai-assistant', function () {
        $user = auth()->user();
        $userName = $user->name ?? 'SK Official';
        $userFirstName = explode(' ', trim($userName))[0] ?: 'there';
        $parts = preg_split('/\s+/', trim($userName));
        $userInitials = count($parts) >= 2
            ? strtoupper(substr($parts[0], 0, 1).substr($parts[count($parts) - 1], 0, 1))
            : strtoupper(substr($userName, 0, 2));

        return view('AI_Assistant::ai-assistant', compact('userName', 'userFirstName', 'userInitials'));
    })->name('ai-assistant');

    Route::get('/profile', [ProfileController::class, 'index'])
        ->name('profile');

    Route::get('/change-email', [ProfileController::class, 'showChangeEmail'])->name('change-email');
    Route::post('/change-email', [ProfileController::class, 'requestChangeEmail'])->name('change-email.request');
    Route::get('/change-email/verify', [ProfileController::class, 'showChangeEmailVerify'])->name('change-email.verify');
    Route::get('/change-email/verify-status', [ProfileController::class, 'checkChangeEmailVerifyStatus'])->name('change-email.verify.status');
    Route::post('/change-email/resend', [ProfileController::class, 'resendChangeEmail'])->name('change-email.resend');
    Route::post('/change-email/cancel', [ProfileController::class, 'cancelChangeEmail'])->name('change-email.cancel');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::get('/api/sk-officials/notifications', [NotificationController::class, 'list'])->name('api.sk-officials.notifications');
    Route::post('/api/sk-officials/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('api.sk-officials.notifications.read');
    Route::post('/api/sk-officials/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('api.sk-officials.notifications.read-all');

    Route::get('/calendar', function () {
        return view('Calendar::calendar');
    })->name('calendar');

    Route::prefix('api/calendar')->group(function () {
        Route::get('/notes', [CalendarController::class, 'index'])->name('api.calendar.notes.index');
        Route::post('/notes', [CalendarController::class, 'store'])->name('api.calendar.notes.store');
        Route::put('/notes/{id}', [CalendarController::class, 'update'])->name('api.calendar.notes.update');
        Route::delete('/notes/{id}', [CalendarController::class, 'destroy'])->name('api.calendar.notes.destroy');
    });

    Route::get('/community-feed', [CommunityFeedPageController::class, 'index'])
        ->name('community-feed.index');

    Route::get('/community-feed/archive', [ArchiveCommunityFeedController::class, 'index'])
        ->name('community-feed.archive');

    Route::get('/community-feed/archive/data', [ArchiveCommunityFeedController::class, 'data'])
        ->name('community-feed.archive.data');

    Route::get('/community-feed/archive/{id}', [ArchiveCommunityFeedController::class, 'show'])
        ->name('community-feed.archive.show');

    Route::post('/community-feed/archive/{id}/restore', [ArchiveCommunityFeedController::class, 'restore'])
        ->name('community-feed.archive.restore');

    Route::delete('/community-feed/archive/{id}', [ArchiveCommunityFeedController::class, 'destroy'])
        ->name('community-feed.archive.destroy');

    Route::prefix('api/community-feed')->group(function () {
        Route::get('/', [CommunityFeedController::class, 'feed'])->name('community-feed.feed');
        Route::get('/{id}', [CommunityFeedController::class, 'show'])->name('community-feed.show');
        Route::post('/', [CommunityFeedController::class, 'store'])->name('community-feed.store');
        Route::post('/upload-image', [CommunityFeedController::class, 'uploadImage'])->name('community-feed.images.upload');
        Route::put('/{id}', [CommunityFeedController::class, 'update'])->name('community-feed.update');
        Route::delete('/{id}', [CommunityFeedController::class, 'destroy'])->name('community-feed.destroy');
        Route::post('/{id}/comments', [CommunityFeedCommentController::class, 'store'])->name('community-feed.comments.store');
        Route::put('/{id}/comments/{comment}', [CommunityFeedCommentController::class, 'update'])->name('community-feed.comments.update');
        Route::delete('/{id}/comments/{comment}', [CommunityFeedCommentController::class, 'destroy'])->name('community-feed.comments.destroy');
        Route::get('/{id}/reactions', [CommunityFeedReactionController::class, 'index'])->name('community-feed.reactions.index');
        Route::post('/{id}/reactions', [CommunityFeedReactionController::class, 'store'])->name('community-feed.reactions.store');
        Route::delete('/{id}/reactions', [CommunityFeedReactionController::class, 'destroy'])->name('community-feed.reactions.destroy');
        Route::get('/{id}/comments/{comment}/reactions', [CommunityFeedReactionController::class, 'commentIndex'])->name('community-feed.comment-reactions.index');
        Route::post('/{id}/comments/{comment}/reactions', [CommunityFeedReactionController::class, 'commentStore'])->name('community-feed.comment-reactions.store');
        Route::post('/{id}/images', [CommunityFeedImageController::class, 'store'])->name('community-feed.images.store');
        Route::delete('/{id}/images/{image}', [CommunityFeedImageController::class, 'destroy'])->name('community-feed.images.destroy');
    });

    Route::get('/community-feed/comments/{id}', [CommunityFeedPageController::class, 'comments'])
        ->whereNumber('id')
        ->name('community-feed.comments');
    Route::get('/community-feed/{id}/comments', function (int $id) {
        return redirect('/community-feed/comments/'.$id);
    })->whereNumber('id');

    Route::get('/community-feed/barangay/{slug}', [BarangayProfileController::class, 'show'])
        ->name('community-feed.barangay');

    Route::redirect('/announcements', '/community-feed');
    Route::redirect('/announcements/archive', '/community-feed/archive');

    Route::get('/committees', function () {
        $catalog = app(AbyipProgramCatalogService::class);

        return view('Committees::committees', [
            'abyipGate' => $catalog->resolveAccessGate(auth()->user()?->barangay_id),
        ]);
    })->name('committees');

    Route::prefix('api/committees')->group(function () {
        Route::get('/', [CommitteeController::class, 'index'])->name('api.committees.index');
        Route::get('/abyip-programs', [CommitteeController::class, 'abyipPrograms'])->name('api.committees.abyip-programs');
        Route::get('/sk-officials', [CommitteeController::class, 'officials'])->name('api.committees.officials');
        Route::post('/', [CommitteeController::class, 'store'])->name('api.committees.store');
        Route::put('/{id}', [CommitteeController::class, 'update'])->name('api.committees.update');
    });

    Route::get('/program-accomplishment', [ProgramAccomplishmentController::class, 'index'])->name('program-accomplishment.index');

    Route::get('/programs', function () {
        $catalog = app(AbyipProgramCatalogService::class);

        return view('Programs::programs', [
            'abyipGate' => $catalog->resolveAccessGate(auth()->user()?->barangay_id),
        ]);
    })->name('programs');

    Route::prefix('api/programs')->group(function () {
        Route::get('/', [ProgramController::class, 'index'])->name('api.programs.index');
        Route::get('/management', [ProgramController::class, 'management'])->name('api.programs.management');
        Route::put('/{programId}/duration', [ProgramController::class, 'updateDuration'])->name('api.programs.update-duration');
    });

    Route::get('/kk-profiling-requests', [KKProfilingRequestsController::class, 'index'])->name('kk-profiling-requests');
    Route::get('/kk-profiling-requests/data', [KKProfilingRequestsController::class, 'data'])->name('kk-profiling-requests.data');
    Route::get('/kk-profiling-requests/{id}', [KKProfilingRequestsController::class, 'show'])->name('kk-profiling-requests.show');
    Route::put('/kk-profiling-requests/{id}', [KKProfilingRequestsController::class, 'update'])->name('kk-profiling-requests.update');
    Route::post('/kk-profiling-requests/bulk-approve', [KKProfilingRequestsController::class, 'bulkApprove'])->name('kk-profiling-requests.bulk-approve');
    Route::post('/kk-profiling-requests/{id}/approve', [KKProfilingRequestsController::class, 'approve'])->name('kk-profiling-requests.approve');
    Route::post('/kk-profiling-requests/{id}/reject', [KKProfilingRequestsController::class, 'reject'])->name('kk-profiling-requests.reject');

    Route::get('/abyip', function () {
        return view('Barangay_ABYIP::abyip');
    })->name('abyip.index');

    Route::prefix('api/abyip')->group(function () {
        Route::get('/', [AbyipController::class, 'index'])->name('api.abyip.index');
        Route::get('/{id}', [AbyipController::class, 'show'])->name('api.abyip.show');
        Route::post('/', [AbyipController::class, 'store'])->name('api.abyip.store');
        Route::post('/{id}/resubmit', [AbyipController::class, 'resubmit'])->name('api.abyip.resubmit');
        Route::delete('/{id}', [AbyipController::class, 'destroy'])->name('api.abyip.destroy');
    });

    // ── Program Accomplishment Reports ──
    Route::get('/program-accomplishment', [ProgramAccomplishmentController::class, 'index'])
        ->name('program-accomplishment.index');

    Route::get('/program-accomplishment/{id}/edit', [ProgramAccomplishmentController::class, 'edit'])
        ->name('program-accomplishment.edit');

    Route::get('/program-accomplishment/{id}', [ProgramAccomplishmentController::class, 'show'])
        ->name('program-accomplishment.show');

    Route::prefix('api/program-accomplishment')->group(function () {
        Route::get('/data', [ProgramAccomplishmentController::class, 'data'])
            ->name('api.program-accomplishment.data');
        Route::post('/prepare-from-catalog', [ProgramAccomplishmentController::class, 'prepareFromCatalog'])
            ->name('api.program-accomplishment.prepare');
        Route::get('/{id}', [ProgramAccomplishmentController::class, 'show'])
            ->name('api.program-accomplishment.show');
        Route::post('/', [ProgramAccomplishmentController::class, 'store'])
            ->name('api.program-accomplishment.store');
        Route::put('/{id}', [ProgramAccomplishmentController::class, 'update'])
            ->name('api.program-accomplishment.update');
        Route::post('/{id}/publish', [ProgramAccomplishmentController::class, 'publish'])
            ->name('api.program-accomplishment.publish');
        Route::delete('/{id}', [ProgramAccomplishmentController::class, 'destroy'])
            ->name('api.program-accomplishment.destroy');
    });

    Route::get('/kabataan', [KabataanController::class, 'index'])->name('kabataan');
    Route::get('/kabataan/data', [KabataanController::class, 'data'])->name('kabataan.data');
    Route::get('/kabataan/{id}/print', [KabataanController::class, 'print'])->name('kabataan.print');
    Route::post('/kabataan/batch-print', [KabataanController::class, 'batchPrint'])->name('kabataan.batch-print');
    Route::get('/kabataan/{id}/documents/{documentIndex}/{side}', [KabataanController::class, 'document'])
        ->where('side', 'front|back')
        ->name('kabataan.document');
    Route::post('/kabataan', [KabataanController::class, 'store'])->name('kabataan.store');
    Route::put('/kabataan/{id}', [KabataanController::class, 'update'])->name('kabataan.update');
    Route::delete('/kabataan/{id}', [KabataanController::class, 'destroy'])->name('kabataan.destroy');
    Route::post('/kabataan/bulk-delete', [KabataanController::class, 'bulkDestroy'])->name('kabataan.bulk-destroy');

    // Previous Kabataan routes (hidden)
    // Route::get('/previous-kabataan', [PreviousKabataanController::class, 'index'])->name('previous-kabataan');
    // Route::get('/previous-kabataan/data', [PreviousKabataanController::class, 'data'])->name('previous-kabataan.data');
    // Route::post('/previous-kabataan/upload', [PreviousKabataanController::class, 'upload'])->name('previous-kabataan.upload');
    // Route::post('/previous-kabataan/bulk-delete', [PreviousKabataanController::class, 'bulkDestroy'])->name('previous-kabataan.bulk-destroy');
    // Route::post('/previous-kabataan/move/{id}', [PreviousKabataanController::class, 'moveFromActive'])->name('previous-kabataan.move');
    // Route::delete('/previous-kabataan/{id}', [PreviousKabataanController::class, 'destroy'])->name('previous-kabataan.destroy');

    Route::get('/deleted-kabataan', [DeletedKabataanController::class, 'index'])->name('deleted-kabataan');
    Route::get('/deleted-kabataan/data', [DeletedKabataanController::class, 'data'])->name('deleted-kabataan.data');
    Route::post('/deleted-kabataan/{id}/restore', [DeletedKabataanController::class, 'restore'])->name('deleted-kabataan.restore');

    Route::get('/archived-youth-records', [ArchivedYouthRecordsController::class, 'index'])->name('archived-youth-records');
    Route::get('/archived-youth-records/data', [ArchivedYouthRecordsController::class, 'data'])->name('archived-youth-records.data');

    Route::get('/deleted-abyip', function () {
        return view('Deleted_Abyip::deleted-abyip');
    })->name('deleted-abyip');

    Route::get('/rejected-kkprofiling', [RejectedKKProfilingController::class, 'index'])->name('rejected-kkprofiling');
    Route::get('/rejected-kkprofiling/data', [RejectedKKProfilingController::class, 'data'])->name('rejected-kkprofiling.data');
    Route::post('/rejected-kkprofiling/{id}/restore', [RejectedKKProfilingController::class, 'restore'])->name('rejected-kkprofiling.restore');

    Route::get('/schedule-kk-profiling', [ScheduleKKProfilingController::class, 'index'])->name('schedule-kk-profiling');
    Route::get('/api/schedule-kk-profiling/data', [ScheduleKKProfilingController::class, 'data'])->name('schedule-kk-profiling.data');
    Route::post('/api/schedule-kk-profiling', [ScheduleKKProfilingController::class, 'store'])->name('schedule-kk-profiling.store');
    Route::put('/api/schedule-kk-profiling/{id}', [ScheduleKKProfilingController::class, 'update'])->name('schedule-kk-profiling.update');
    Route::delete('/api/schedule-kk-profiling/{id}', [ScheduleKKProfilingController::class, 'destroy'])->name('schedule-kk-profiling.destroy');

    Route::get('/schedule-programs', function () {
        $catalog = app(AbyipProgramCatalogService::class);
        $management = $catalog->listForManagement(auth()->user());

        return view('Program_Management::program-management', [
            'managementPrograms' => $management['programs'],
            'calendarYear' => $management['calendar_year'],
            'abyipGate' => $catalog->resolveAccessGate(auth()->user()?->barangay_id),
        ]);
    })->name('schedule-programs'); // legacy route name kept for sidebar

    Route::get('/program-management', function () {
        return redirect()->route('schedule-programs');
    });

    Route::get('/schedule-programs/sports-application-form', function () {
        $catalog = app(AbyipProgramCatalogService::class);

        return view('Program_Management::sports.schedule', [
            'abyipGate' => $catalog->resolveAccessGate(auth()->user()?->barangay_id),
        ]);
    })->name('schedule-programs.sports-application-form');

    Route::get('/sports-application-form', function () {
        $catalog = app(AbyipProgramCatalogService::class);

        return view('Program_Management::sports.schedule', [
            'abyipGate' => $catalog->resolveAccessGate(auth()->user()?->barangay_id),
        ]);
    })->name('sports-application-form');

    Route::get('/sports-programs/archived', [ArchivedSportsProgramController::class, 'index'])
        ->name('sports-programs.archived');
    Route::get('/sports-programs/archived/data', [ArchivedSportsProgramController::class, 'data'])
        ->name('sports-programs.archived.data');
    Route::post('/sports-programs/archive/{id}', [ArchivedSportsProgramController::class, 'archive'])
        ->whereNumber('id')
        ->name('sports-programs.archive');
    Route::post('/sports-programs/restore/{id}', [ArchivedSportsProgramController::class, 'restore'])
        ->whereNumber('id')
        ->name('sports-programs.restore');
    Route::delete('/sports-programs/delete/{id}', [ArchivedSportsProgramController::class, 'destroy'])
        ->whereNumber('id')
        ->name('sports-programs.delete');

    Route::prefix('api/schedule-programs')->group(function () {
        Route::get('/meta', [ScheduleProgramController::class, 'meta'])->name('api.schedule-programs.meta');
        Route::get('/', [ScheduleProgramController::class, 'index'])->name('api.schedule-programs.index');
        Route::get('/{id}', [ScheduleProgramController::class, 'show'])->name('api.schedule-programs.show');
        Route::post('/', [ScheduleProgramController::class, 'store'])->name('api.schedule-programs.store');
        Route::put('/{id}', [ScheduleProgramController::class, 'update'])->name('api.schedule-programs.update');
        Route::delete('/{id}', [ScheduleProgramController::class, 'destroy'])->name('api.schedule-programs.destroy');
    });

    Route::prefix('api/scholarship-school-years')->group(function () {
        Route::get('/', [ScholarshipSchoolYearController::class, 'index'])->name('api.scholarship-school-years.index');
        Route::post('/', [ScholarshipSchoolYearController::class, 'store'])->name('api.scholarship-school-years.store');
    });

    Route::prefix('api/program-applications')->group(function () {
        Route::get('/', [ProgramApplicationController::class, 'index'])->name('api.program-applications.index');
        Route::get('/{id}/documents/{questionId}', [ProgramApplicationController::class, 'showDocument'])->whereNumber('id')->name('api.program-applications.document');
        Route::get('/{id}', [ProgramApplicationController::class, 'show'])->whereNumber('id')->name('api.program-applications.show');
        Route::put('/{id}/status', [ProgramApplicationController::class, 'updateStatus'])->whereNumber('id')->name('api.program-applications.update-status');
        Route::put('/{id}/payment', [ProgramApplicationController::class, 'updatePaymentStatus'])->whereNumber('id')->name('api.program-applications.update-payment');
    });

    Route::prefix('api/program-evaluations')->group(function () {
        Route::get('/meta', [ProgramEvaluationController::class, 'meta'])->name('api.program-evaluations.meta');
        Route::get('/', [ProgramEvaluationController::class, 'index'])->name('api.program-evaluations.index');
        Route::get('/{id}', [ProgramEvaluationController::class, 'show'])->whereNumber('id')->name('api.program-evaluations.show');
        Route::post('/', [ProgramEvaluationController::class, 'store'])->name('api.program-evaluations.store');
        Route::put('/{id}', [ProgramEvaluationController::class, 'update'])->whereNumber('id')->name('api.program-evaluations.update');
        Route::delete('/{id}', [ProgramEvaluationController::class, 'destroy'])->whereNumber('id')->name('api.program-evaluations.destroy');
    });

    Route::prefix('api/program-surveys/{committee}')->group(function () {
        Route::get('/meta', [ProgramSurveyController::class, 'meta'])->name('api.program-surveys.meta');
        Route::get('/responses', [ProgramSurveyController::class, 'responses'])->name('api.program-surveys.responses');
        Route::get('/', [ProgramSurveyController::class, 'index'])->name('api.program-surveys.index');
        Route::get('/{id}', [ProgramSurveyController::class, 'show'])->name('api.program-surveys.show');
        Route::post('/', [ProgramSurveyController::class, 'store'])->name('api.program-surveys.store');
        Route::put('/{id}', [ProgramSurveyController::class, 'update'])->name('api.program-surveys.update');
        Route::delete('/{id}', [ProgramSurveyController::class, 'destroy'])->name('api.program-surveys.destroy');
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

    Route::get('/rejected-scholars', [RejectedScholarshipController::class, 'index'])->name('rejected-scholars');
    Route::get('/rejected-scholars/data', [RejectedScholarshipController::class, 'data'])->name('rejected-scholars.data');
    Route::post('/rejected-scholars/{id}/restore', [RejectedScholarshipController::class, 'restore'])->whereNumber('id')->name('rejected-scholars.restore');

    Route::redirect('/rejected-scholarship', '/rejected-scholars');

    Route::get('/rejected-sports', [RejectedSportsController::class, 'index'])->name('rejected-sports');
    Route::get('/rejected-sports/data', [RejectedSportsController::class, 'data'])->name('rejected-sports.data');
    Route::post('/rejected-sports/{id}/restore', [RejectedSportsController::class, 'restore'])->whereNumber('id')->name('rejected-sports.restore');

    // ── Approved Scholars (pure front-end, no DB) ──
    Route::get('/approved-scholars', function () {
        return view('Program_Management::scholarship.approved-scholars');
    })->name('approved-scholars');

    Route::redirect('/scholar-list', '/approved-scholars');

    // ── Scholar Evaluation route ──
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

    Route::get('/sports-evaluation', function () {
        return view('Program_Management::sports.evaluation');
    })->name('sports.evaluation');

    // ── Committee survey modules (config-driven: one blade + JS per tab) ──
    $surveyPrograms = require app_path('Modules/Program_Management/config/survey-programs.php');

    $surveyTabViews = [
        'forms' => ['view' => 'survey.survey-forms', 'title' => 'Survey Forms', 'subtitle' => 'Create and manage survey questions for Kabataan members.'],
        'results' => ['view' => 'survey.survey-results', 'title' => 'Survey Response', 'subtitle' => 'View Kabataan who answered your surveys and read each response.'],
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
