<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Email - SK Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/css/verify-wait.css',
        'app/Modules/Authentication/assets/js/verify-wait.js',
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

        <!-- Right Side - Verification Card -->
        <div class="sk-login-section">
            <div class="sk-login-card">
                <div class="card-header">
                    <h2 class="card-title">Verify Your Email</h2>
                    <p class="card-subtitle">Complete verification to access your account</p>
                </div>

                <div class="verify-content" data-status-url="{{ route('sk_official.verification.wait.status', [], false) }}" data-expires-at="{{ $expiresAtIso }}" data-email="{{ $email }}">

                    <div class="verification-state waiting" id="verification-state">
                        Waiting for email verification...
                    </div>

                    <p class="countdown-text">
                        We sent a verification link to <span class="email-highlight">{{ $email }}</span>
                    </p>

                    <p class="countdown-text" id="countdown">
                        Expires in: <span id="countdown-timer">{{ sprintf('%02d:%02d', $waitMinutes, 0) }}</span>
                    </p>

                    <div class="resend-section">
                        <form method="POST" action="{{ route('sk_official.verification.resend', [], false) }}" id="resend-form">
                            @csrf
                            <input type="hidden" name="email" value="{{ $email }}">
                            <button type="submit" class="sk-submit-btn btn-resend" id="resend-btn">
                                Resend Verification Email
                            </button>
                        </form>
                        <div class="resend-cooldown" id="resend-cooldown" style="display: none;"></div>
                    </div>

                    <div class="form-footer">
                        <a href="{{ route('login', [], false) }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 12H5M12 19l-7-7 7-7"/>
                            </svg>
                            Back to login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Success Modal -->
    <div class="success-modal-overlay" id="success-modal">
        <div class="success-modal">
            <div class="check-wrap" aria-hidden="true">
                <span class="checkmark"></span>
            </div>
            <h2>Verified Successfully!</h2>
            <p>Redirecting to dashboard...</p>
        </div>
    </div>

    <script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
