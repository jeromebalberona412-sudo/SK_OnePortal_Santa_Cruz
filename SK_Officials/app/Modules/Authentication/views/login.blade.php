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
    <title>SK Officials Portal</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/css/auth-legal.css',
        'app/Modules/Authentication/assets/js/login.js',
        'app/Modules/Authentication/assets/js/auth-legal.js',
    ])

    @if (config('turnstile.enabled') && config('turnstile.site_key'))
        {{--
            render=explicit: prevents Cloudflare from auto-scanning the DOM and
            rendering any widget it finds. Our JS calls turnstile.render() manually
            after waiting for the API to be ready, eliminating the load-race bug.
        --}}
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
    @endif

    <style>
        .sk-main-title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 1024px) {
            .sk-main-title { font-size: 32px; }
        }
        @media (max-width: 768px) {
            .sk-main-title { font-size: 28px; }
        }
        @media (max-width: 480px) {
            .sk-main-title { font-size: 24px; }
        }
    </style>
</head>
<body class="sk-login-page">

    {{-- ─── Turnstile Modal Overlay ─────────────────────────────────────────────
         Rendered in the DOM at all times when Turnstile is enabled.
         Visibility is controlled purely by the .turnstile-modal-visible class
         that login.js adds/removes. The widget itself (#turnstile-container) is
         empty until JS calls turnstile.render() on the first reveal.
    ──────────────────────────────────────────────────────────────────────────── --}}
    @if (config('turnstile.enabled') && config('turnstile.site_key'))
        <div id="turnstile-modal" class="turnstile-modal" role="dialog" aria-modal="true" aria-label="Human verification">

            {{-- Semi-transparent backdrop — click closes the modal --}}
            <div id="turnstile-modal-backdrop" class="turnstile-modal-backdrop"></div>

            {{-- Centered verification card --}}
            <div class="turnstile-modal-card">

                {{-- Card header --}}
                <div class="turnstile-modal-header">
                    <div class="turnstile-modal-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0
                                     01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332
                                     9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="turnstile-modal-title">Verify you're human</h2>
                        <p class="turnstile-modal-subtitle">Complete the security check to continue signing in.</p>
                    </div>
                    {{-- Close button --}}
                    <button id="turnstile-close-btn"
                            class="turnstile-close-btn"
                            type="button"
                            aria-label="Cancel verification">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414
                                     1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293
                                     4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>

                {{-- Widget mount point --}}
                <div class="turnstile-modal-body">
                    <div id="turnstile-container"></div>
                </div>

                {{-- Cancel link --}}
                <div class="turnstile-modal-footer">
                    <button id="turnstile-cancel-btn" type="button" class="turnstile-cancel-link">
                        Cancel and go back
                    </button>
                </div>

            </div>{{-- /.turnstile-modal-card --}}
        </div>{{-- /#turnstile-modal --}}
    @endif

    <main class="sk-login-container">

        {{-- ─── Left Side — Logo & Branding ─────────────────────────────────────── --}}
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

        {{-- ─── Right Side — Login Card ──────────────────────────────────────────── --}}
        <div class="sk-login-section">
            <div class="sk-login-card">

                <div class="card-header" style="text-align:center;">
                    <p class="card-subtitle"
                       style="font-size:1.4rem;font-weight:800;color:#0f172a;letter-spacing:-0.02em;">
                        Sign in to your account
                    </p>
                </div>

                <form class="sk-login-form" id="loginForm"
                      method="POST"
                      action="{{ route('login', [], false) }}"
                      novalidate
                      @if (config('turnstile.enabled') && config('turnstile.site_key'))
                          data-turnstile-enabled="true"
                          data-turnstile-sitekey="{{ config('turnstile.site_key') }}"
                      @endif>
                    @csrf

                    {{-- Server-side alerts --}}
                    @if (session('access_denied'))
                        <div class="sk-alert sk-alert-error access-denied-alert">
                            <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0
                                         00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414
                                         1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414
                                         10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586
                                         8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <strong>{{ session('access_denied.title', 'Access Denied') }}</strong>
                                <p style="margin:0.35rem 0 0;">{{ session('access_denied.message') }}</p>
                            </div>
                        </div>
                    @elseif ($errors->has('email') || $errors->has('password'))
                        {{--
                            Only show credential errors here, NOT the Turnstile error.
                            The Turnstile error (key: cf-turnstile-response) is shown
                            inside the modal via #turnstile-server-error below.
                        --}}
                        <div class="sk-alert sk-alert-error">
                            <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0
                                         00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414
                                         1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414
                                         10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586
                                         8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            {{ $errors->first('email') ?: $errors->first('password') }}
                        </div>
                    @elseif ($errors->any())
                        <div class="sk-alert sk-alert-error">
                            <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0
                                         00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414
                                         1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414
                                         10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586
                                         8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            {{ $errors->first() }}
                        </div>
                    @endif

                    {{-- Email Field --}}
                    <div class="sk-form-group">
                        <label for="email" class="sk-label">
                            <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            Email Address
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="sk-input"
                               value="{{ old('email') }}"
                               autofocus
                               autocomplete="email"
                               placeholder="Enter example@gmail.com"
                               maxlength="150">
                        <div class="sk-field-error" id="email-error" hidden></div>
                    </div>

                    {{-- Password Field --}}
                    <div class="sk-form-group">
                        <label for="password" class="sk-label">
                            <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0
                                         01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                      clip-rule="evenodd"/>
                            </svg>
                            Password
                        </label>
                        <div class="password-wrapper">
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="sk-input password-input"
                                   autocomplete="current-password"
                                   placeholder="Enter your password"
                                   minlength="8"
                                   maxlength="64">
                            <button type="button"
                                    class="toggle-password"
                                    aria-label="Toggle password visibility"
                                    onclick="togglePassword()">
                                <svg class="eye-icon eye-open" id="eyeOpen"
                                     viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-icon eye-closed" id="eyeClosed"
                                     viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2"
                                     style="display:none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45
                                             18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0
                                             11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1
                                             1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                        <div class="sk-field-error" id="password-error" hidden></div>
                    </div>

                    {{-- Remember Me & Forgot Password --}}
                    <div class="sk-form-options">
                        <label class="sk-checkbox">
                            <input type="checkbox" id="remember" name="remember" value="1">
                            <span class="checkbox-label">Remember me</span>
                        </label>
                        <button type="button" class="sk-link" id="forgotBtn">Forgot password?</button>
                    </div>

                    @include('authentication::partials.login-legal-consent')

                    {{--
                        Hidden Turnstile server-error anchor.
                        When the backend rejects the token, it redirects back with the
                        'cf-turnstile-response' error key. JS detects #turnstile-server-error
                        on page load and auto-opens the modal so the user can re-verify.
                    --}}
                    @error('cf-turnstile-response')
                        <div id="turnstile-server-error"
                             class="sk-alert sk-alert-error"
                             style="display:none;"
                             aria-hidden="true">
                            {{ $message }}
                        </div>
                    @enderror

                    {{-- Submit Button --}}
                    <button type="submit" class="sk-submit-btn" id="loginBtn">
                        <span>Sign In</span>
                    </button>

                </form>
            </div>{{-- /.sk-login-card --}}
        </div>{{-- /.sk-login-section --}}

    </main>

    @include('authentication::partials.legal-modals')
    @include('authentication::partials.login-legal-prompt')

    <script>
        function togglePassword() {
            const input    = document.getElementById('password');
            const eyeOpen  = document.getElementById('eyeOpen');
            const eyeClosed = document.getElementById('eyeClosed');
            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.style.display   = 'none';
                eyeClosed.style.display = 'block';
            } else {
                input.type = 'password';
                eyeOpen.style.display   = 'block';
                eyeClosed.style.display = 'none';
            }
        }
    </script>

</body>

@if (session('verification_wait') && session()->has('sk_official_email_verification_pending'))
    <script>
        if (typeof window.hideLoading === 'function') {
            window.hideLoading();
        }
        window.location.replace("{{ route('sk_official.verification.wait', [], false) }}");
    </script>
@endif

</html>
