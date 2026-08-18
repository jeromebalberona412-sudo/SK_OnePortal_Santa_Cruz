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
    <title>{{ $title }} - SK Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/css/forgot-password.css',
    ])
</head>
<body class="sk-login-page">

    <main class="sk-login-container">
        <div class="sk-branding-section">
            <div class="branding-content">
                <div class="collab-logo-wrapper">
                    <div class="logo-glow-wrapper logo-left">
                        <img src="{{ asset('images/skoneportal_logo.webp') }}"
                             alt="SK OnePortal Logo"
                             class="collab-logo">
                    </div>
                    <div class="logo-glow-wrapper logo-right">
                        <img src="{{ asset('images/logo.png') }}"
                             alt="SK Officials Logo"
                             class="collab-logo">
                    </div>
                </div>
                <h1 class="sk-main-title">SK OnePortal</h1>
                <p class="sk-tagline">SK Officials Portal - Santa Cruz, Laguna</p>
            </div>
        </div>

        <div class="sk-login-section">
            <div class="sk-login-card">
                <div class="card-header">
                    <h2 class="card-title">{{ $title }}</h2>
                    <p class="card-subtitle">{{ $message }}</p>
                </div>

                @if (! empty($showVerifyButton))
                    <a href="{{ route('account.activation.request') }}" class="sk-submit-btn" data-no-loading>Activate Account</a>
                    <div class="youth-register-section">
                        <p class="register-text">
                            <a href="{{ route('login') }}" class="register-link" data-no-loading>Back to Login</a>
                        </p>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="sk-submit-btn" data-no-loading>Go to Login</a>
                @endif
            </div>
        </div>
    </main>

</body>
</html>
