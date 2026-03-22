<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'SK OnePortal Admin'))</title>

    <!-- Head Section for additional CSS -->
    @yield('head')
    @stack('styles')

    <!-- Core shared assets via Vite -->
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js',
        'app/Modules/Layout/assets/css/layout/sidebar.css',
        'app/Modules/Layout/assets/css/layout/header.css',
        'app/Modules/Layout/assets/js/layout/sidebar.js',
        'app/Modules/Layout/assets/js/layout/logout.js'
    ])
</head>
<body class="min-h-screen admin-dark-canvas">
    <div class="min-h-screen flex flex-col">
        <!-- Flash Messages -->
        @if (session('message'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="flash-success-modern px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline pr-8">{{ session('message') }}</span>
            </div>
        </div>
        @endif

        @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="flash-error-modern px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline pr-8">{{ session('error') }}</span>
            </div>
        </div>
        @endif

        <!-- Main Content -->
        <main class="flex-grow">
            @yield('content')
        </main>

        @stack('scripts')
        @yield('scripts')
    </div>
</body>
</html>
