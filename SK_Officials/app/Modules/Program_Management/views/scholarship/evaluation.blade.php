<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scholarship Evaluation - SK Officials Portal</title>
    @include('layout::favicon')
    @vite([
        'app/Modules/Layout/css/header.css',
        'app/Modules/Layout/css/sidebar.css',
        'app/Modules/GForm_Builder/assets/css/gform-builder.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholarship_application_form.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholar_list.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholar_evaluation.css'
    ])
</head>
<body data-program-key="scholarship" data-program-letter="A">

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="sl-page-container schol-page-container">

        @include('Program_Management::scholarship.partials.page-top', [
            'activeTab' => 'evaluation',
            'pageTitle' => 'Evaluation',
            'pageSubtitle' => 'Create and manage scholarship evaluation forms.',
        ])

        @include('Program_Management::partials.program-evaluation-content')

    </div>
</main>

@vite([
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
    'app/Modules/GForm_Builder/assets/js/gform-builder.js',
    'app/Modules/Program_Management/assets/js/scholarship/scholar_evaluation.js'
])
</body>
</html>
