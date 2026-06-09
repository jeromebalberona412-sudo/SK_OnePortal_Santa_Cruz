<?php

use App\Modules\Programs\Controllers\ProgramController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/api/kabataan/programs', [ProgramController::class, 'index'])->name('kabataan.programs.index');
    Route::get('/api/kabataan/programs/schedule/{id}', [ProgramController::class, 'showSchedule'])->name('kabataan.programs.schedule.show');
    Route::get('/api/kabataan/programs/applications', [ProgramController::class, 'listApplications'])->name('kabataan.programs.applications.index');
    Route::get('/api/kabataan/programs/applications/{id}', [ProgramController::class, 'showApplication'])->name('kabataan.programs.applications.show');
    Route::post('/api/kabataan/programs/applications', [ProgramController::class, 'submitApplication'])->name('kabataan.programs.applications.store');
    Route::post('/api/kabataan/programs/applications/{id}/cancel', [ProgramController::class, 'cancelApplication'])->name('kabataan.programs.applications.cancel');
    Route::post('/api/kabataan/programs/documents/upload', [ProgramController::class, 'uploadDocument'])->name('kabataan.programs.documents.upload');
    Route::get('/api/kabataan/programs/documents/{scheduleProgramId}/{questionId}', [ProgramController::class, 'showDocument'])->name('kabataan.programs.documents.show');

    Route::get('/scholarship/apply', [ProgramController::class, 'scholarshipLanding'])->name('scholarship.apply');
    Route::get('/scholarship/apply/form', [ProgramController::class, 'scholarshipForm'])->name('scholarship.apply.form');

    Route::view('/sports/apply', 'programs::sports_landing')->name('sports.apply');
    Route::view('/sports/apply/form', 'programs::sports-registration')->name('sports.apply.form');

    $presurveyPrograms = [
        'anti-drugs' => [
            'title' => 'Anti-Drugs',
            'subtitle' => 'Drug Awareness & Prevention Seminar',
            'icon' => '🚫',
            'accent' => '#dc2626',
        ],
        'agriculture' => [
            'title' => 'Agriculture',
            'subtitle' => 'Urban Farming & Livelihood Program',
            'icon' => '🌱',
            'accent' => '#16a34a',
        ],
        'disaster-preparedness' => [
            'title' => 'Disaster Preparedness',
            'subtitle' => 'Emergency Response & Safety Training',
            'icon' => '🛡️',
            'accent' => '#ea580c',
        ],
        'gad' => [
            'title' => 'Gender and Development',
            'subtitle' => 'GAD Seminar & Youth Empowerment',
            'icon' => '💜',
            'accent' => '#9333ea',
        ],
        'health' => [
            'title' => 'Health',
            'subtitle' => 'Community Health & Wellness Program',
            'icon' => '❤️',
            'accent' => '#0891b2',
        ],
        'others' => [
            'title' => 'Others',
            'subtitle' => 'General Youth Program / Seminar',
            'icon' => '📋',
            'accent' => '#475569',
        ],
    ];

    Route::get('/presurvey/{slug}', function (string $slug) use ($presurveyPrograms) {
        if (! isset($presurveyPrograms[$slug])) {
            abort(404);
        }

        return view('programs::programs-pre-survey', [
            'slug' => $slug,
            'program' => $presurveyPrograms[$slug],
        ]);
    })->where('slug', implode('|', array_keys($presurveyPrograms)))->name('programs.presurvey');
});
