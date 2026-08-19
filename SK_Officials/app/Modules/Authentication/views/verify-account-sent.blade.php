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
        'app/Modules/Authentication/assets/js/turnstile-gate.js',
        'app/Modules/Authentication/assets/js/verify-account-sent.js',
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
                    <h2 class="card-title">Check your email</h2>
                    <p class="card-subtitle">{{ $message }}</p>
                </div>

                @if ($errors->any())
                    <div class="sk-alert sk-alert-error">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form class="sk-login-form" method="POST" action="{{ route('account.activation.send') }}" id="resendActivationForm">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button
                        type="submit"
                        class="sk-submit-btn"
                        id="resendActivationBtn"
                        data-no-loading
                        data-remaining="{{ (int) $cooldownRemaining }}"
                        data-cooldown="{{ (int) $cooldownSeconds }}"
                    >
                        <span id="resendActivationLabel">Resend activation email</span>
                    </button>
                </form>

                <div style="margin-top: 0.75rem;">
                    <a href="{{ route('account.activation.request') }}" class="sk-secondary-btn" data-no-loading>Cancel</a>
                </div>
            </div>
        </div>
    </main>

    @include('authentication::partials.turnstile-gate', [
        'turnstileSubtitle' => 'Complete the security check before we resend your activation link.',
    ])

</body>
</html>
