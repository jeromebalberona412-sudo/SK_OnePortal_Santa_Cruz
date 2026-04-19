<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deleted ABYIP - SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/Deleted_Abyip/assets/css/deleted-abyip.css'
    ])
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container deleted-abyip-page">
        <h1 class="page-title">Deleted ABYIP</h1>
    </div>
</main>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js'
])

</body>
</html>
