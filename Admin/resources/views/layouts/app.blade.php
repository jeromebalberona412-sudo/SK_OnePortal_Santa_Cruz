<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'SK OnePortal Admin'))</title>

    {{-- Apply saved theme BEFORE paint — prevents flash --}}
    <script>
        (function () {
            var t = localStorage.getItem('op_theme');
            var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (t === 'dark' || (!t && d)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <!-- Head Section for additional CSS -->
    @yield('head')
    @stack('styles')

    <!-- Core shared assets via Vite -->
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/theme.js',
        'app/Modules/Layout/assets/css/sidebar.css',
        'app/Modules/Layout/assets/css/header.css',
        'app/Modules/Layout/assets/js/sidebar.js',
        'app/Modules/Layout/assets/js/logout.js'
    ])
</head>
<body class="min-h-screen admin-canvas">
    <div class="min-h-screen flex flex-col">
        <!-- Main Content -->
        <main class="flex-grow">
            @yield('content')
        </main>

        @stack('scripts')
        @yield('scripts')
    </div>
</body>
</html>
