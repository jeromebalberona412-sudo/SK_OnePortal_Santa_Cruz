<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KK Profiling — {{ $registration->full_name }} ({{ $profilingYear }})</title>
    @vite([
        'app/Modules/KKProfilingRequests/assets/css/kk-questionnaire-view.css',
        'app/Modules/Kabataan/assets/css/kabataan.css',
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
