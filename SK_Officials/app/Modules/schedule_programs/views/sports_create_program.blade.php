<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Sports Program - SK Officials Portal</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/schedule_programs/assets/css/scholarship_application_form.css',
        'app/Modules/schedule_programs/assets/css/sports_requests.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body data-sports-page="create-program">
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="sports-page-container schol-page-container">

    @include('schedule_programs::partials.sports-page-top', [
        'activeTab' => 'create',
        'pageTitle' => 'Create Sports Program',
        'pageSubtitle' => 'Set program details, committee head, and build the Kabataan application form.',
    ])

    @include('schedule_programs::partials.sports-create-program-form')

</div>
</main>

@include('schedule_programs::partials.sports-program-success-modal')

<div class="sports-toast" id="sportsToast" style="display:none;">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="sportsToastMsg"></span>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/schedule_programs/assets/js/sports_requests.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
