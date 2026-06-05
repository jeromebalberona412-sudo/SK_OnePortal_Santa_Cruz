<!DOCTYPE html>
<html lang="en">
<head>
    @include('favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0450a8">
    <meta name="description" content="SK OnePortal Kabataan - Youth Community Platform for Santa Cruz, Laguna">
    <title>@yield('title', 'SK OnePortal Kabataan')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

    @vite([
        'app/Modules/Layout/assets/css/kabataan-header.css',
        'app/Modules/Layout/assets/js/kabataan-header.js',
        'app/Modules/Layout/assets/css/kabataan-logout.css',
        'app/Modules/Layout/assets/js/kabataan-logout.js',
        'app/Modules/Homepage/assets/css/homepage.css',
        'app/Modules/Homepage/assets/css/about.css',
        'app/Modules/Homepage/assets/css/pages.css',
        'app/Modules/Homepage/assets/css/faqs.css',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])

    @stack('styles')
</head>
<body class="homepage-body @if(Route::currentRouteName() === 'about') about-body @endif @if(Route::currentRouteName() === 'faqs') faqs-body @endif">
    {{-- LOADING OVERLAY --}}
    @include('dashboard::loading')

    {{-- KABATAAN HEADER (consistent across all pages) --}}
    @include('layout::kabataan-header', ['showSearch' => false])

    <main class="kabataan-main">
        @yield('content')
    </main>

    {{-- SCRIPTS --}}
    @vite('app/Modules/Homepage/assets/js/homepage.js')
    @stack('scripts')
</body>
</html>
