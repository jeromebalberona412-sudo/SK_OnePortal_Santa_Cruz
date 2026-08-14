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
    <title>Change Email - SK Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Profile/assets/css/change-password.css',
        'app/Modules/Profile/assets/js/change-email.js',
    ])
</head>
<body class="sk-login-page">

    <main class="sk-login-container">

        <!-- Left Side — Logo & Branding -->
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

        <!-- Right Side — Card -->
        <div class="sk-login-section">
            <div class="sk-login-card">

                <div id="ceStep1">
                    <div class="card-header" style="text-align:center;">
                        <p class="card-subtitle"
                           style="font-size:1.4rem;font-weight:800;color:#0f172a;letter-spacing:-0.02em;">
                            Change Email
                        </p>
                        <p class="card-subtitle">Enter your current email, new email address, and current password to request a change.</p>
                    </div>

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

                    <form class="sk-login-form" id="ceForm" action="{{ route('change-email.request') }}" method="POST" novalidate>
                        @csrf

                        {{-- Current Email --}}
                        <div class="sk-form-group">
                            <label for="ceCurrentEmail" class="sk-label">
                                <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                </svg>
                                Current Email
                            </label>
                            <input
                                type="email"
                                id="ceCurrentEmail"
                                name="current_email"
                                class="sk-input"
                                placeholder="Enter your current email"
                                autocomplete="email"
                                maxlength="100"
                                value="{{ old('current_email', $user->email ?? '') }}"
                                autofocus
                                required
                            >
                            <div class="sk-field-error" id="ceCurrentEmailError" hidden></div>
                        </div>

                        {{-- New Email Address --}}
                        <div class="sk-form-group">
                            <label for="ceNewEmail" class="sk-label">
                                <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                </svg>
                                New Email Address
                            </label>
                            <input
                                type="email"
                                id="ceNewEmail"
                                name="new_email"
                                class="sk-input"
                                placeholder="Enter your new email address"
                                autocomplete="off"
                                maxlength="100"
                                value="{{ old('new_email') }}"
                                required
                            >
                            <div class="sk-field-error" id="ceNewEmailError" hidden></div>
                        </div>

                        {{-- Current Password --}}
                        <div class="sk-form-group">
                            <label for="cePassword" class="sk-label">
                                <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                          clip-rule="evenodd"/>
                                </svg>
                                Current Password
                            </label>
                            <div class="password-wrapper">
                                <input
                                    type="password"
                                    id="cePassword"
                                    name="password"
                                    class="sk-input password-input"
                                    placeholder="Enter your current password"
                                    autocomplete="current-password"
                                    maxlength="64"
                                    required
                                >
                                <button type="button" class="toggle-password" aria-label="Toggle password visibility" onclick="togglePassword('cePassword')">
                                    <svg class="eye-icon eye-open" id="eyeOpen-cePassword"
                                         viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg class="eye-icon eye-closed" id="eyeClosed-cePassword"
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
                            <div class="sk-field-error" id="cePasswordError" hidden></div>
                        </div>

                        <button type="submit" class="sk-submit-btn" id="ceSubmitBtn">
                            <span id="ceBtnText">Send Verification Link</span>
                        </button>
                    </form>

                    <div class="sk-back-profile">
                        <a href="{{ route('profile') }}" class="sk-link" data-no-loading>Back to Profile</a>
                    </div>
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
