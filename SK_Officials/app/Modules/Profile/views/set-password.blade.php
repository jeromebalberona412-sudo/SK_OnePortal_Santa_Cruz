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
    <title>Set Password - SK Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Profile/assets/css/change-password.css',
        'app/Modules/Profile/assets/js/change-password.js',
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
                <div class="card-header" style="text-align:center;">
                    <p class="card-subtitle"
                       style="font-size:1.4rem;font-weight:800;color:#0f172a;letter-spacing:-0.02em;">
                        Set New Password
                    </p>
                    <p class="card-subtitle">
                        Create a new password to finish changing your email to
                        <strong>{{ $user->pending_email ?: $user->email }}</strong>.
                        Your email will update only after this password is saved.
                    </p>
                </div>

                @if (session('status'))
                    <div class="sk-alert sk-alert-success">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="sk-alert sk-alert-error">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form action="{{ route('change-email.set-password.update', ['id' => $user->id, 'token' => $token]) }}" method="POST" class="sk-login-form" id="change-password-form" data-password-max-length="{{ (int) config('sk_official_auth.password_reset.password.max_length', 64) }}" data-loading-text="Setting password..." novalidate>
                    @csrf

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
                                placeholder="Enter new password"
                                autocomplete="new-password"
                                minlength="8"
                                maxlength="{{ (int) config('sk_official_auth.password_reset.password.max_length', 64) }}"
                                required
                            >
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility" onclick="togglePassword('password')">
                                <svg class="eye-icon eye-open" id="eyeOpen-password" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-icon eye-closed" id="eyeClosed-password" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                        <ul class="password-rules hidden-rules" id="passwordRules" aria-live="polite">
                            <li id="rule-length"><span class="rule-mark">✕</span> At least 8 characters</li>
                            <li id="rule-lowercase"><span class="rule-mark">✕</span> At least one lowercase letter</li>
                            <li id="rule-uppercase"><span class="rule-mark">✕</span> At least one uppercase letter</li>
                            <li id="rule-number"><span class="rule-mark">✕</span> At least one number</li>
                            <li id="rule-special"><span class="rule-mark">✕</span> At least one special character</li>
                        </ul>
                        @error('password')
                            <div class="sk-field-error">{{ $message }}</div>
                        @enderror
                        <div class="sk-field-error" id="password-client-error" hidden></div>
                    </div>

                    <div class="sk-form-group">
                        <label for="password_confirmation" class="sk-label">
                            <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            Confirm New Password
                        </label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="sk-input password-input"
                                placeholder="Re-enter new password"
                                autocomplete="new-password"
                                minlength="8"
                                maxlength="{{ (int) config('sk_official_auth.password_reset.password.max_length', 64) }}"
                                required
                            >
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility" onclick="togglePassword('password_confirmation')">
                                <svg class="eye-icon eye-open" id="eyeOpen-password_confirmation" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-icon eye-closed" id="eyeClosed-password_confirmation" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <div class="sk-field-error">{{ $message }}</div>
                        @enderror
                        <div class="sk-match-msg" id="password-match-msg" hidden></div>
                    </div>

                    <button type="submit" class="sk-submit-btn" id="cpSubmitBtn">
                        <span id="cpBtnText">Set Password</span>
                    </button>
                </form>

                <div class="sk-back-profile">
                    <a href="{{ route('login') }}" class="sk-link">Sign In</a>
                </div>
            </div>
        </div>
    </main>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const eyeOpen = document.getElementById('eyeOpen-' + inputId);
            const eyeClosed = document.getElementById('eyeClosed-' + inputId);
            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
            } else {
                input.type = 'password';
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
            }
        }
    </script>
</body>
</html>
