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
        return view('Announcement::announcement');
    })->name('announcements');

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

    Route::get('/kk-profiling-requests', function () {
        return view('KKProfilingRequests::kkprofiling-requests');
    })->name('kk-profiling-requests');

    Route::get('/abyip', function () {
        return view('ABYIP::abyip');
    })->name('abyip.index');

    Route::get('/kabataan', function () {
        return view('Kabataan::kabataan');
    })->name('kabataan');

    Route::get('/previous-kabataan', function () {
        return view('PreviousKabataan::previous-kabataan');
    })->name('previous-kabataan');

    Route::get('/deleted-kabataan', function () {
        return view('Deleted_Kabataan::deleted-kabataan');
    })->name('deleted-kabataan');

    Route::get('/deleted-abyip', function () {
        return view('Deleted_Abyip::deleted-abyip');
    })->name('deleted-abyip');

    Route::get('/rejected-kkprofiling', function () {
        return view('Rejected_KKProfiling::rejected-kkprofiling');
    })->name('rejected-kkprofiling');

    Route::get('/schedule-kk-profiling', function () {
        return view('ScheduleKKProfiling::schedule-kkprofiling');
    })->name('schedule-kk-profiling');

    Route::get('/schedule-programs', function () {
        return view('schedule_programs::schedule-programs');
    })->name('schedule-programs');

    Route::get('/schedule-programs/sports-application-form', function () {
        return view('schedule_programs::sports_application_form');
    })->name('schedule-programs.sports-application-form');

    Route::get('/sports-application-form', function () {
        return view('schedule_programs::sports_application_form');
    })->name('sports-application-form');

    // ── Scholarship routes (pure front-end, no DB) ──
    Route::get('/scholarship', function () {
        return view('schedule_programs::scholarship_requests');
    })->name('scholarship.index');

    Route::get('/rejected-scholarship', function () {
        return view('Rejected_Scholarship::rejected-scholarship');
    })->name('rejected-scholarship');

    // ── Sports requests route (pure front-end, no DB) ──
    Route::get('/sports-requests', function () {
        return view('schedule_programs::sports_requests');
    })->name('sports-requests');
});