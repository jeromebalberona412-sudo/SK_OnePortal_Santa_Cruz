<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejected KK Profiling - SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/Rejected_KKProfiling/assets/css/rejected-kkprofiling.css'
    ])
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container rejected-kk-page">
        <h1 class="page-title">Rejected KK Profiling</h1>
    </div>
</main>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js'
])

</body>
</html>
