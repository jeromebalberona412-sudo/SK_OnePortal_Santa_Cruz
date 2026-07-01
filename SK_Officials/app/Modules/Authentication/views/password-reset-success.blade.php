<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Password Reset Successful - SK Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/css/password-reset-success.css',
        'app/Modules/Authentication/assets/js/password-reset-success.js',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body class="sk-login-page">
    @include('loading')
    
    <!-- Animated Background -->
    <div class="sk-bg-wrapper">
        <div class="sk-bg-image"></div>
        <div class="sk-gradient-overlay"></div>
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>

    <main class="sk-login-container">
        <!-- Left Side - Logo & Branding -->
        <div class="sk-branding-section">
            <div class="branding-content">
                <div class="logo-wrapper">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="SK Officials Logo"
                        class="sk-logo"
                    >
                </div>
                <h1 class="sk-main-title">SK OnePortal</h1>
                <p class="sk-tagline">SK Officials Portal – Santa Cruz, Laguna</p>
            </div>
        </div>

        <!-- Right Side - Success Card -->
        <div class="sk-login-section">
            <div class="sk-login-card">
                <div class="success-content">
                    <div class="check-wrap">
                        <span class="check-icon">?</span>
                    </div>
                    <h1 class="success-title">Password Reset Successfully!</h1>
                    <p class="success-message">Your password has been updated successfully.</p>
                    <p class="success-message">You can now log in with your new password.</p>
                    <p class="next-step">Redirecting to login page...</p>
                    <a href="{{ route('login') }}" class="success-btn">Go to Login</a>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
