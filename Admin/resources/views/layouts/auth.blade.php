<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'OnePortal Admin')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/js/login.js',
        'resources/js/theme.js',
    ])
    {{-- ── Apply saved theme BEFORE paint — prevents flash ── --}}
    <script>
        (function () {
            var t = localStorage.getItem('op_theme');
            var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (t === 'dark' || (!t && d)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="login-page">

    {{-- ── Sign-in loading overlay (hidden by default) ───── --}}
    <div id="signin-overlay" class="signin-overlay" aria-hidden="true" hidden>
        <div class="signin-overlay-inner">
            <div class="signin-spinner">
                <div class="signin-spinner-ring"></div>
                <div class="signin-spinner-ring signin-spinner-ring--2"></div>
                <div class="signin-spinner-dot"></div>
            </div>
            <p class="signin-overlay-title">Signing In</p>
            <p class="signin-overlay-sub" id="signin-overlay-sub">Verifying credentials...</p>
        </div>
    </div>

    {{-- ── ONE theme toggle button for ALL auth pages ──────── --}}
    <button
        data-theme-toggle
        class="theme-toggle-btn"
        aria-label="Switch to dark mode"
        title="Switch to dark mode"
    >
        {{-- Moon — visible in light mode --}}
        <span class="theme-icon-dark" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" viewBox="0 0 16 16">
                <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/>
            </svg>
        </span>
        {{-- Sun — visible in dark mode --}}
        <span class="theme-icon-light" aria-hidden="true" style="display:none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6m0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
            </svg>
        </span>
        <span class="theme-label">Dark Mode</span>
    </button>

    {{-- ── Shared left panel ────────────────────────────────── --}}
    <div class="login-wrapper">

        <div class="login-left d-none d-lg-flex">
            <div class="login-left-inner">
                <div class="login-brand-logo-wrap">
                    <img src="{{ asset('Images/image.png') }}" alt="OnePortal Logo" class="login-brand-logo">
                </div>
                <h1 class="login-brand-title">OnePortal</h1>
                <p class="login-brand-sub">Admin Portal — Santa Cruz, Laguna</p>
                <p class="login-left-disclaimer">
                    You are accessing an official information system of the Municipality of Santa Cruz, Laguna.
                    Unauthorized access is prohibited. All activities are monitored and logged.
                    Use of this system constitutes consent to monitoring and auditing.
                </p>
                <blockquote class="login-left-quote">
                    <p>"Thou shalt not steal."</p>
                    <cite>— Exodus 20:15</cite>
                </blockquote>
            </div>
            <div class="login-left-overlay" aria-hidden="true"></div>
        </div>

        {{-- ── Right panel — page content goes here ─────────── --}}
        <div class="login-right">
            <div class="login-card-wrap @yield('card-wrap-class')">

                {{-- Mobile logo --}}
                <div class="d-flex d-lg-none justify-content-center mb-4">
                    <div class="login-mobile-logo-wrap">
                        <img src="{{ asset('Images/image.png') }}" alt="OnePortal Logo" class="login-mobile-logo">
                    </div>
                </div>

                <div class="login-card">
                    @yield('content')
                </div>

                {{-- Mobile disclaimer --}}
                <p class="login-disclaimer d-lg-none">
                    You are accessing an official information system of the Municipality of Santa Cruz, Laguna.
                    Unauthorized access is prohibited. All activities are monitored and logged.
                    Use of this system constitutes consent to monitoring and auditing.
                </p>

            </div>
        </div>

    </div>

    @stack('scripts')

</body>
</html>
