@php
    $activeTab = $activeTab ?? 'forms';
    $committee = $committee ?? 'environmental';
    $pageTitle = $pageTitle ?? 'Survey';
    $pageSubtitle = $pageSubtitle ?? '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} — {{ $committeeTitle }} | SK Officials</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholarship_application_form.css',
        'app/Modules/Program_Management/assets/css/survey/survey.css',
        'app/Modules/GForm_Builder/assets/css/gform-builder.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <script type="application/json" id="surveyProgramConfig">{!! json_encode([
        'committee' => $committee,
        'activeTab' => $activeTab,
        'title' => $committeeTitle,
        'description' => $committeeDescription,
        'skHead' => $skHead,
        'activities' => $activities,
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
</head>
<body class="survey-app" data-committee="{{ $committee }}" data-survey-tab="{{ $activeTab }}">
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="schol-page-container survey-page-wrap">

    <a href="{{ route('schedule-programs') }}" class="schol-back-top">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Back to Program Management
    </a>

    <section class="survey-hero-card">
        <div class="survey-hero-main">
            <span class="survey-hero-badge">SK Program Survey</span>
            <h1 class="survey-hero-title">{{ $committeeTitle }}</h1>
            <p class="survey-hero-desc">{{ $committeeDescription }}</p>
            <p class="survey-sk-head">SK Head: <strong>{{ $skHead }}</strong></p>
        </div>
        <div class="survey-hero-activities">
            <h2 class="survey-activities-label">Program activities</h2>
            <ul class="survey-activity-chips">
                @foreach($activities as $activity)
                    <li>{{ $activity }}</li>
                @endforeach
            </ul>
        </div>
    </section>

    @include('Program_Management::partials.survey-tabs', ['activeTab' => $activeTab, 'committee' => $committee])

    <section class="survey-page-head">
        <div>
            <h2 class="schol-page-title">{{ $pageTitle }}</h2>
            <p class="schol-page-subtitle">{{ $pageSubtitle }}</p>
        </div>
        <div class="survey-page-actions">
            @yield('survey_actions')
        </div>
    </section>

    @yield('survey_content')

</div>
</main>

@include('Program_Management::survey._modals')

<div class="sports-toast" id="surveyToast" style="display:none;" role="status" aria-live="polite"></div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/GForm_Builder/assets/js/gform-builder.js',
    'app/Modules/Program_Management/assets/js/survey/survey.js',
])
<script>
document.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('surveyToast');
    const showToast = (msg, type) => {
        if (!toast || !msg) return;
        toast.textContent = msg;
        toast.style.display = 'flex';
        toast.style.background = type === 'error' ? '#ef4444' : '#22c55e';
        setTimeout(() => { toast.style.display = 'none'; }, 2800);
    };
    if (window.GFormBuilder) {
        window.GFormBuilder.init({ showToast });
    }
});
</script>
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
