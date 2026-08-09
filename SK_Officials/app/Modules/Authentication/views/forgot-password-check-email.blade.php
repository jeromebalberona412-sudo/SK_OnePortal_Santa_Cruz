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
    <title>Check Your Email - SK Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/css/forgot-password.css',
        'app/Modules/Authentication/assets/js/forgot-password-check-email.js',
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
                    <h2 class="card-title">Check Your Email</h2>
                    <p class="card-subtitle">
                        A password reset link was sent to
                        <strong id="fpSentEmail">{{ $email }}</strong>.
                        Open your inbox and follow the link to set a new password.
                    </p>
                </div>

                @if (session('status'))
                    <div class="sk-alert sk-alert-success">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="sk-alert sk-alert-error">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form class="sk-login-form" id="fpResendForm" method="POST" action="{{ route('password.email') }}" novalidate
                      data-email="{{ $email }}"
                      data-resend-available-at="{{ $resendAvailableAt ?? 0 }}"
                      data-resend-remaining="{{ $resendRemainingSeconds ?? 0 }}"
                      data-cooldown-seconds="{{ $cooldownSeconds ?? 60 }}">
                    @csrf
                    <input type="hidden" name="email" id="fpHiddenEmail" value="{{ $email }}">

                    <div class="fp-actions">
                        <button type="submit" class="sk-submit-btn" id="fpResendBtn">
                            <span id="fpResendBtnText">Resend Reset Link</span>
                        </button>
                    </div>
                </form>

                <div class="fp-cancel-section">
                    <a href="{{ route('password.request') }}"
                       class="fp-cancel-link"
                       data-no-loading>Cancel</a>
                </div>

                <div class="youth-register-section">
                    <p class="register-text">
                        Remember your password?
                        <a href="{{ route('login') }}" class="register-link" data-no-loading>Back to Sign In</a>
                    </p>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
