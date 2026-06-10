<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OnePortal Admin — Change Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/css/forgot-password.css',
        'app/Modules/Authentication/assets/js/login.js',
        'resources/js/theme.js',
    ])
    <script>
        (function () {
            var t = localStorage.getItem('op_theme');
            var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (t === 'dark' || (!t && d)) document.documentElement.classList.add('dark');
        })();
    </script>
</head>
<body class="login-page">

    <div id="signin-overlay" class="signin-overlay" aria-hidden="true" hidden>
        <div class="signin-overlay-inner">
            <div class="signin-spinner">
                <div class="signin-spinner-ring"></div>
                <div class="signin-spinner-ring signin-spinner-ring--2"></div>
                <div class="signin-spinner-dot"></div>
            </div>
            <p class="signin-overlay-title">Sending Reset Link</p>
            <p class="signin-overlay-sub">Please wait...</p>
        </div>
    </div>

    <button data-theme-toggle class="theme-toggle-btn" aria-label="Switch to dark mode" title="Switch to dark mode">
        <span class="theme-icon-dark" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" viewBox="0 0 16 16">
                <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/>
            </svg>
        </span>
        <span class="theme-icon-light" aria-hidden="true" style="display:none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6m0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
            </svg>
        </span>
        <span class="theme-label">Dark Mode</span>
    </button>

    <div class="login-page">
        <div class="bg-wrapper">
            <div class="bg-image"></div>
            <div class="gradient-overlay"></div>
            <div class="floating-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
            </div>
        </div>

        <div class="login-container">
            <div class="logo-container">
                <div class="logo-glow-wrapper">
                    <img src="{{ asset('Images/image.png') }}" alt="OnePortal Logo" class="large-logo">
                </div>
                <h1 class="brand-title">OnePortal Admin</h1>
                <p class="brand-subtitle">Municipality of Santa Cruz, Laguna</p>
            </div>

            <div class="login-form-container">
                <div class="login-card-inner">
                    <div class="form-header">
                        <h2>Change Password</h2>
                        <p>Enter your registered email and we'll send you a reset link.</p>
                    </div>

                    @if (session('status'))
                        <div class="login-alert login-alert--success" role="alert">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="login-alert login-alert--danger" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('profile.change-password.send') }}" novalidate id="fpForm">
                        @csrf
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email ?? '') }}"
                                placeholder="Enter your registered email"
                                required autofocus autocomplete="email">
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="login-btn" id="fpSubmitBtn">
                            Send Reset Link
                        </button>
                    </form>

                    <div class="form-footer">
                        <p><a href="{{ route('profile') }}">← Back to Profile</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        var btn     = document.getElementById('fpSubmitBtn');
        var input   = document.getElementById('email');
        var form    = document.getElementById('fpForm');
        var overlay = document.getElementById('signin-overlay');

        if (btn && input) {
            function toggle() { btn.disabled = !input.value.trim(); }
            toggle();
            input.addEventListener('input', toggle);
        }

        if (form && overlay) {
            form.addEventListener('submit', function () {
                overlay.removeAttribute('hidden');
                overlay.classList.add('is-visible');
            });
        }
    })();
    </script>

</body>
</html>
