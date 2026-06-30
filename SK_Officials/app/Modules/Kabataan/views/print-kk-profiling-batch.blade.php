<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KK Profiling Batch Print — {{ $profilingYear }}</title>
    @vite([
        'app/Modules/KKProfilingRequests/assets/css/kk-questionnaire-view.css',
        'app/Modules/Kabataan/assets/css/kabataan.css',
        'app/Modules/Kabataan/assets/css/kabataan-print-questionnaire.css',
    ])
</head>
<body class="kk-print-body" onload="window.print()">
    <div class="kk-print-root">
        @foreach ($records as $item)
            @include('Kabataan::partials.print-kk-profiling-sheet', [
                'registration' => $item['registration'],
                'formData' => $item['formData'],
                'submittedAt' => $item['submittedAt'],
                'barangayLogoUrl' => $barangayLogoUrl ?? null,
            ])
        @endforeach
    </div>
</body>
</html>
