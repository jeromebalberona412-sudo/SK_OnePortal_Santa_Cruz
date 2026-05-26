<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scholarship Application - SK OnePortal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite([
        'app/Modules/Layout/assets/css/kabataan-header.css',
        'app/Modules/Layout/assets/js/kabataan-header.js',
        'app/Modules/Dashboard/assets/css/chatbot.css',
        'app/Modules/Dashboard/assets/js/chatbot.js',
        'app/Modules/Dashboard/assets/css/notif.css',
        'app/Modules/Dashboard/assets/js/notif.js',
        'app/Modules/Programs/assets/css/scholarship_application.css',
        'app/Modules/Programs/assets/css/scholarship_requirements.css',
        'app/Modules/Programs/assets/js/scholarship_application.js',
        'app/Modules/Programs/assets/js/scholarship_requirements.js',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])
</head>
<body class="sch-app-body kabataan-app-page">
    @include('dashboard::loading')
    @include('layout::kabataan-header', ['showSearch' => false, 'pageBadge' => 'Scholarship Application'])

    <div class="sch-app-shell">
        @include('programs::scholarship.partials.sidebar')

        <main class="sch-app-main">
            <form id="scholarshipApplicationForm" class="sch-app-form" novalidate>
                @include('programs::scholarship.sections.personal_information')
                @include('programs::scholarship.sections.educational_background')
                @include('programs::scholarship.sections.background_information')
                @include('programs::scholarship.sections.additional_information')
                @include('programs::scholarship.sections.requirements')
            </form>
        </main>
    </div>

    @include('programs::scholarship.partials.attachment_modal')
    @include('layout::footer')
</body>
</html>
