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
    <title>Activate Your Account - SK Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/css/reset-password.css',
        'app/Modules/Authentication/assets/js/reset-password.js',
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
                    <h2 class="card-title">Activate Your Account</h2>
                    <p class="card-subtitle">Set a password to activate your SK Officials account.</p>
                </div>

                @if ($errors->any())
                    <div class="sk-alert sk-alert-error">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form class="sk-login-form" id="resetPasswordForm" method="POST" action="{{ route('account.activation.activate') }}" novalidate
                      data-password-min-length="{{ (int) config('sk_official_auth.password_reset.password.min_length', 8) }}"
                      data-password-max-length="{{ (int) config('sk_official_auth.password_reset.password.max_length', 64) }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="sk-form-group">
                        <label for="registered_email" class="sk-label">
                            <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            Email Address
                        </label>
                        <input
                            type="email"
                            id="registered_email"
                            class="sk-input"
                            value="{{ $email }}"
                            readonly
                            tabindex="-1"
                        >
                    </div>

                    <div class="sk-form-group">
                        <label for="password" class="sk-label">
                            <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            New Password
                        </label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="sk-input password-input @error('password') is-invalid @enderror"
                                autocomplete="new-password"
                                placeholder="Enter your new password"
                                minlength="8"
                                maxlength="64"
                                autofocus
                            >
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility" data-target="password">
                                <svg class="eye-icon eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-icon eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                        <ul class="password-rules" id="passwordRules">
                            <li id="rule-length"><span class="rule-mark">✕</span> Minimum <span id="rule-length-val">8</span> characters</li>
                            <li id="rule-lowercase"><span class="rule-mark">✕</span> Lowercase letter (a-z)</li>
                            <li id="rule-uppercase"><span class="rule-mark">✕</span> Uppercase letter (A-Z)</li>
                            <li id="rule-number"><span class="rule-mark">✕</span> Number (0-9)</li>
                            <li id="rule-special"><span class="rule-mark">✕</span> Special character (!@#$%^&amp;* etc.)</li>
                        </ul>
                        <div class="sk-field-error" id="password-error" @error('password') @else hidden @enderror>
                            @error('password'){{ $message }}@enderror
                        </div>
                    </div>

                    <div class="sk-form-group">
                        <label for="password_confirmation" class="sk-label">
                            <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            Confirm Password
                        </label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="sk-input password-input @error('password') is-invalid @enderror"
                                autocomplete="new-password"
                                placeholder="Re-enter your new password"
                                minlength="8"
                                maxlength="64"
                            >
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility" data-target="password_confirmation">
                                <svg class="eye-icon eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-icon eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                        <div class="confirm-match-status" id="confirmMatchStatus" hidden>
                            <span class="confirm-match-mark" id="confirmMatchMark">✕</span>
                            <span id="confirmMatchText">Passwords do not match</span>
                        </div>
                        <div class="sk-field-error" id="password-confirmation-error" hidden></div>
                    </div>

                    <button type="submit" class="sk-submit-btn" id="resetBtn">
                        <span id="resetBtnText">Activate Account</span>
                    </button>
                </form>

                <div class="youth-register-section">
                    <p class="register-text">
                        <a href="{{ route('login') }}" class="register-link" data-no-loading>Back to Login</a>
                    </p>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
