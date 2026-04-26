<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Change Email - SK Officials</title>
    @vite([
        'app/modules/Authentication/assets/css/forgot-password.css',
        'app/modules/Profile/assets/css/change-email.css',
        'app/modules/Profile/assets/js/change-email.js',
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

                {{-- ── STEP 1: Change Email Form ─────────────────── --}}
                <div id="ceStep1">
                    <div class="card-header">
                        <h2 class="card-title">Change Email ✉️</h2>
                        <p class="card-subtitle">Enter your current email, new email address, and current password to request a change.</p>
                    </div>

                    <!-- Error alert -->
                    <div class="sk-alert sk-alert-error" id="ceError" style="display:none;">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span id="ceErrorText">Something went wrong. Please try again.</span>
                    </div>

                    <!-- Form -->
                    <form class="sk-login-form" id="ceForm" novalidate>
                        @csrf

                        {{-- Current Email --}}
                        <div class="sk-form-group">
                            <label for="ceCurrentEmail" class="sk-label">Current Email</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                </svg>
                                <input
                                    type="email"
                                    id="ceCurrentEmail"
                                    name="current_email"
                                    class="sk-input"
                                    placeholder="Enter your current email"
                                    autocomplete="email"
                                    maxlength="100"
                                    autofocus
                                    required
                                >
                            </div>
                            <div class="sk-field-error" id="ceCurrentEmailError" hidden></div>
                        </div>

                        {{-- New Email Address --}}
                        <div class="sk-form-group">
                            <label for="ceNewEmail" class="sk-label">New Email Address</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                </svg>
                                <input
                                    type="email"
                                    id="ceNewEmail"
                                    name="new_email"
                                    class="sk-input"
                                    placeholder="Enter your new email address"
                                    autocomplete="off"
                                    maxlength="100"
                                    required
                                >
                            </div>
                            <div class="sk-field-error" id="ceNewEmailError" hidden></div>
                        </div>

                        {{-- Current Password --}}
                        <div class="sk-form-group">
                            <label for="cePassword" class="sk-label">Current Password</label>
                            <div class="password-wrapper">
                                <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
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
                                <button type="button" class="pw-toggle-btn cp-pw-toggle" data-target="cePassword" aria-label="Show password" tabindex="-1">
                                    <svg class="pw-eye pw-eye-show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <svg class="pw-eye pw-eye-hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                        <path d="M1 1l22 22"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="sk-field-error" id="cePasswordError" hidden></div>
                        </div>

                        <button type="submit" class="sk-submit-btn" id="ceSubmitBtn">
                            <span id="ceBtnText">Send Verification Link</span>
                            <svg class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                        </button>
                    </form>

                    <div class="youth-register-section ce-back-section">
                        <p class="register-text">
                            <a href="{{ route('profile') }}" class="register-link">← Back to Profile</a>
                        </p>
                    </div>
                </div>

                {{-- ── STEP 2: Verification Sent ─────────────────── --}}
                <div id="ceStep2" style="display:none;">

                    <!-- Sent header -->
                    <div class="ce-sent-header">
                        <div class="ce-sent-icon">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ce-sent-title">Verification Sent!</div>
                        <div class="ce-sent-sub">Check your new email inbox and click the link.</div>
                    </div>

                    <!-- Info box -->
                    <div class="ce-info-box">
                        Verification link sent! A confirmation link has been sent to <strong id="cePendingEmail"></strong>. Your current email remains active until you verify the new one.
                    </div>

                    <!-- Status table -->
                    <div class="ce-status-table">
                        <div class="ce-status-row">
                            <span class="ce-status-key">Current email</span>
                            <span class="ce-status-val" id="ceCurrentEmailVal">—</span>
                        </div>
                        <div class="ce-status-row">
                            <span class="ce-status-key">Pending email</span>
                            <span class="ce-status-val" id="cePendingEmailVal">—</span>
                        </div>
                        <div class="ce-status-row">
                            <span class="ce-status-key">Status</span>
                            <span class="ce-status-val">
                                <span class="ce-badge-awaiting">Awaiting verification</span>
                            </span>
                        </div>
                    </div>

                    <!-- Resend timer -->
                    <div class="ce-resend-timer" id="ceTimer" style="display:none;">
                        Resend available in <strong id="ceTimerCount">60</strong>s
                    </div>

                    <!-- Action buttons -->
                    <div class="ce-actions">
                        <button type="button" class="ce-btn-resend" id="ceResendBtn" disabled>
                            Resend Verification
                        </button>
                        <button type="button" class="ce-btn-cancel" id="ceCancelBtn">
                            Cancel Request
                        </button>
                    </div>

                    <div class="youth-register-section ce-back-section">
                        <p class="register-text">
                            <a href="{{ route('profile') }}" class="register-link">← Back to Profile</a>
                        </p>
                    </div>
                </div>

            </div>
        </div>

    </main>

    @vite(['app/modules/Authentication/assets/js/loader.js'])
</body>
</html>
