<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Change Password - SK Officials</title>
    @vite([
        'app/modules/Authentication/assets/css/forgot-password.css',
        'app/modules/Profile/assets/js/change-password.js',
    ])
</head>
<body class="sk-login-page">

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

        <!-- Left Side — Logo & Branding -->
        <div class="sk-branding-section">
            <div class="branding-content">
                <div class="logo-wrapper">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="SK Officials Logo"
                         class="sk-logo">
                </div>
                <h1 class="sk-main-title">SK OnePortal</h1>
                <p class="sk-tagline">SK Officials Portal – Santa Cruz, Laguna</p>
            </div>
        </div>

        <!-- Right Side — Card -->
        <div class="sk-login-section">
            <div class="sk-login-card">

                {{-- ── STEP 1: Email form ─────────────────────────── --}}
                <div id="cpStep1">
                    <div class="card-header">
                        <h2 class="card-title">Change Password 🔐</h2>
                        <p class="card-subtitle">Enter your email address and we'll send you a link to reset your password.</p>
                    </div>

                    <!-- Success alert (step 1) -->
                    <div class="sk-alert sk-alert-success" id="cpSuccess" style="display:none;">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span id="cpSuccessText">Reset password link has been sent to your email.</span>
                    </div>

                    <!-- Error alert (step 1) -->
                    <div class="sk-alert sk-alert-error" id="cpError" style="display:none;">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span id="cpErrorText">No account found with that email address.</span>
                    </div>

                    <!-- Email form -->
                    <form class="sk-login-form" id="cpForm" novalidate>
                        @csrf
                        <div class="sk-form-group">
                            <label for="cpEmail" class="sk-label">Email Address</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                </svg>
                                <input
                                    type="email"
                                    id="cpEmail"
                                    name="email"
                                    class="sk-input"
                                    placeholder="Enter your email address"
                                    autocomplete="email"
                                    maxlength="100"
                                    autofocus
                                    required
                                >
                            </div>
                            <div class="sk-field-error" id="cpEmailError" hidden></div>
                        </div>

                        <button type="submit" class="sk-submit-btn" id="cpSubmitBtn">
                            <span id="cpBtnText">Send Reset Password Link</span>
                            <svg class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                        </button>
                    </form>

                    <div class="youth-register-section">
                        <p class="register-text">
                            Back to profile?
                            <a href="{{ route('profile') }}" class="register-link">Go to Profile</a>
                        </p>
                    </div>
                </div>

                {{-- ── STEP 2: Reset password form (shown after email verified) ── --}}
                <div id="cpStep2" style="display:none;">
                    <div class="card-header">
                        <h2 class="card-title">Set New Password 🔑</h2>
                        <p class="card-subtitle">Create a strong new password for your account.</p>
                    </div>

                    <!-- Success alert (step 2) -->
                    <div class="sk-alert sk-alert-success" id="cpResetSuccess" style="display:none;">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Password changed successfully! Logging you out…</span>
                    </div>

                    <!-- Reset form -->
                    <form class="sk-login-form" id="cpResetForm" novalidate>
                        @csrf

                        <!-- New Password -->
                        <div class="sk-form-group">
                            <label for="cpNewPassword" class="sk-label">New Password</label>
                            <div class="password-wrapper">
                                <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                                <input
                                    type="password"
                                    id="cpNewPassword"
                                    name="password"
                                    class="sk-input password-input"
                                    placeholder="Enter new password"
                                    autocomplete="new-password"
                                    minlength="8"
                                    maxlength="64"
                                    required
                                >
                                <button type="button" class="pw-toggle-btn" data-target="cpNewPassword" aria-label="Show password" tabindex="-1">
                                    <svg class="pw-eye pw-eye-show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <svg class="pw-eye pw-eye-hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                        <path d="M1 1l22 22"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="sk-field-error" id="cpNewPasswordError" hidden></div>
                            <!-- Password strength bar -->
                            <div class="cp-strength-bar" id="cpStrengthBar" style="display:none;">
                                <div class="cp-strength-fill" id="cpStrengthFill"></div>
                            </div>
                            <div class="cp-strength-label" id="cpStrengthLabel"></div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="sk-form-group">
                            <label for="cpConfirmPassword" class="sk-label">Confirm New Password</label>
                            <div class="password-wrapper">
                                <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                                <input
                                    type="password"
                                    id="cpConfirmPassword"
                                    name="password_confirmation"
                                    class="sk-input password-input"
                                    placeholder="Re-enter new password"
                                    autocomplete="new-password"
                                    minlength="8"
                                    maxlength="64"
                                    required
                                >
                                <button type="button" class="pw-toggle-btn" data-target="cpConfirmPassword" aria-label="Show password" tabindex="-1">
                                    <svg class="pw-eye pw-eye-show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <svg class="pw-eye pw-eye-hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                        <path d="M1 1l22 22"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="sk-field-error" id="cpConfirmPasswordError" hidden></div>
                        </div>

                        <button type="submit" class="sk-submit-btn" id="cpResetBtn">
                            <span id="cpResetBtnText">Change Password</span>
                            <svg class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </form>

                    <div class="youth-register-section">
                        <p class="register-text">
                            <a href="#" id="cpBackToEmail" class="register-link">← Use a different email</a>
                        </p>
                    </div>
                </div>

            </div>
        </div>

    </main>

    @vite(['app/modules/Authentication/assets/js/loader.js'])
</body>
</html>
