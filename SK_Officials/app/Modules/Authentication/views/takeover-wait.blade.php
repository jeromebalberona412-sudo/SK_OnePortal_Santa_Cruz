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
    <title>Session Verification - SK Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/css/takeover-wait.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body class="sk-login-page">
    @include('loading')
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
        <div class="sk-branding-section">
            <div class="branding-content">
                <div class="logo-wrapper">
                    <img src="{{ asset('images/logo.png') }}" alt="SK Officials Logo" class="sk-logo">
                </div>
                <h1 class="sk-main-title">SK OnePortal</h1>
                <p class="sk-tagline">SK Officials Portal – Santa Cruz, Laguna</p>
            </div>
        </div>
        <div class="sk-login-section">
            <div class="sk-login-card">
                <div class="takeover-content">
                    <div class="takeover-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm6-10V7a3 3 0 00-6 0v4h6z"/></svg>
                    </div>
                    <h2 class="takeover-title">Session Verification Required</h2>
                    <p class="takeover-message">Send a verification code to <strong>{{ $email }}</strong> to continue on this device.</p>
                    <form method="POST" action="{{ route('sk_official.takeover.send') }}" class="takeover-form" id="sendCodeForm">
                        @csrf
                        <div class="send-btn-wrap">
                            <button type="submit" class="sk-submit-btn btn-submit" id="sendCodeBtn" disabled>
                                <span class="btn-spinner"></span>
                                <span class="btn-label">Send Verification Code</span>
                            </button>
                            <span class="send-timer-text active" id="sendTimerText">Available in <span id="sendCountdown">1:00</span></span>
                        </div>
                    </form>
                    <div class="verify-section">
                        <form method="POST" action="{{ route('sk_official.takeover.verify') }}" class="takeover-form" id="verifyCodeForm">
                            @csrf
                            <input type="hidden" id="otp_code" name="otp_code">
                            <div class="form-group">
                                <label>Enter Verification Code</label>
                                <div class="otp-boxes" id="otpBoxes">
                                    <input class="otp-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" autocomplete="off">
                                    <input class="otp-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" autocomplete="off">
                                    <input class="otp-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" autocomplete="off">
                                    <input class="otp-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" autocomplete="off">
                                    <input class="otp-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" autocomplete="off">
                                    <input class="otp-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" autocomplete="off">
                                </div>
                            </div>
                            <button type="submit" class="sk-submit-btn btn-submit" id="verifyCodeBtn">
                                <span class="btn-spinner"></span>
                                <span class="btn-label">Verify Code</span>
                            </button>
                        </form>
                        <div class="form-footer">
                            <a href="{{ route('login', [], false) }}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                                Back to login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="{{ url('/shared/js/loading.js') }}"></script>
    @vite(['app/Modules/Authentication/assets/js/takeover-wait.js'])
</body>
</html>
