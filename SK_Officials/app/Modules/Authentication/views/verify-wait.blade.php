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

                <div class="verify-content"
                     data-status-url="{{ route('sk_official.verification.wait.status', [], false) }}"
                     data-resend-url="{{ route('sk_official.verification.resend', [], false) }}"
                     data-dashboard-url="{{ route('dashboard', [], false) }}"
                     data-email="{{ $email }}"
                     data-user-id="{{ (int) ($userId ?? 0) }}"
                     data-session-key="{{ $sessionKey ?? '' }}"
                     data-fresh-session="{{ ($resendStarted ?? false) ? '0' : '1' }}"
                     data-resend-cooldown="{{ ($resendStarted ?? false) ? (int) $resendCooldown : 0 }}"
                     data-resend-just-sent="{{ ($resendStarted ?? false) ? '1' : '0' }}"
                     data-show-notification="{{ ($showNotification ?? false) ? '1' : '0' }}"
                     data-notify-title="SK Officials"
                     data-notify-body="{{ $notificationBody ?? 'Verification email sent. Check your inbox.' }}">

                    <div class="verification-state waiting" id="verification-state">
                        Waiting for email verification...
                    </div>

                    <p class="countdown-text" id="verify-wait-message">
                        We sent a verification link to <span class="email-highlight">{{ $email }}</span>
                    </p>

                    @if ($errors->any())
                        <div class="sk-alert sk-alert-error" style="margin-bottom: 1rem;">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="sk-alert sk-alert-success" style="margin-bottom: 1rem;">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="resend-section">
                        <div class="resend-status" id="resend-status" hidden></div>
                        <div class="resend-cooldown" id="resend-cooldown" style="display: none;">
                            Resend available in <strong id="resend-cooldown-count">1:00</strong>
                        </div>
                        <button type="button" class="sk-submit-btn btn-resend" id="resend-btn">
                            <span class="btn-resend-spinner" id="resend-btn-spinner" hidden></span>
                            <span class="btn-resend-label" id="resend-btn-label">Resend Verification Email</span>
                        </button>
                    </div>

                    <div class="form-footer" id="verify-wait-footer">
                        <p class="refresh-hint" id="refresh-hint" hidden>
                            Email verified! Redirecting you to the dashboard...
                        </p>
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
    @vite(['app/Modules/Authentication/assets/js/verify-wait.js'])
</body>
</html>
