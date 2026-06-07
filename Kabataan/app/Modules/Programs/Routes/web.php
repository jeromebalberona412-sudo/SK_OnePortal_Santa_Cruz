<?php

use Illuminate\Support\Facades\Route;

/*
| Frontend-only program application pages (no controller / no database).
*/
Route::middleware(['web', 'auth'])->group(function () {
    Route::view('/scholarship/apply', 'programs::scholarship_landing')->name('scholarship.apply');
    Route::view('/scholarship/apply/form', 'programs::scholarship_application')->name('scholarship.apply.form');
    Route::view('/sports/apply', 'programs::sports-registration')->name('sports.apply');

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
