<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title></title>
    <style>
        @page { margin: 0; }
        html, body { margin: 0 !important; padding: 0 !important; }
    </style>
    @vite([
        'app/Modules/KKProfilingRequests/assets/css/kkp-paper-form.css',
        'app/Modules/KKProfilingRequests/assets/css/kk-questionnaire-view.css',
        'app/Modules/Kabataan/assets/css/kabataan-print-questionnaire.css',
    ])
</head>
<body class="kk-print-body" onload="window.print()">
    <div class="kk-print-root">
        @include('Kabataan::partials.print-kk-profiling-sheet', [
            'registration' => $registration,
            'formData' => $formData,
            'submittedAt' => $submittedAt,
            'barangayLogoUrl' => $barangayLogoUrl,
        ])
    </div>
</body>
</html>
