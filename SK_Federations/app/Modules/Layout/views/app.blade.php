<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @hasSection('meta-cache')
        @yield('meta-cache')
    @else
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
    @endif
    <title>@yield('title', 'SK OnePortal')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/modules/dashboard/css/dashboard.css') }}">
    @php
        $layoutCssVersion = @filemtime(app_path('Modules/Layout/assets/css/layout.css')) ?: time();
    @endphp
    <link rel="stylesheet" href="{{ url('/modules/layout/css/layout.css') }}?v={{ $layoutCssVersion }}">
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    @stack('styles')
</head>
<body @stack('body-attributes')>
    @include('layout::anti-back')
    @include('layout::header')
    @include('layout::notif-popover')
    @include('layout::sidebar-overlay')
    @include('layout::sidebar')

    <main class="main-content @stack('main-class')" @stack('main-attributes')>
        @yield('content')
    </main>

    @include('layout::logout-modal')

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script src="{{ url('/modules/layout/js/layout.js') }}"></script>
    <script>
        window.logoutRoute = "{{ route('logout') }}";
        window.loginRoute  = "{{ route('login') }}";
    </script>
    @stack('scripts')
</body>
</html>
