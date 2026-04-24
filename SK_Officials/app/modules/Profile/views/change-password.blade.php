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

    <!-- Animated Background (identical to forgot-password) -->
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

        <!-- Right Side — Change Password Card -->
        <div class="sk-login-section">
            <div class="sk-login-card">

                <div class="card-header">
                    <h2 class="card-title">Change Password 🔐</h2>
                    <p class="card-subtitle">Enter your email address and we'll send you a link to reset your password.</p>
                </div>

                <!-- Success alert -->
                <div class="sk-alert sk-alert-success" id="cpSuccess" style="display:none;">
                    <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span id="cpSuccessText">Reset password link has been sent to your email.</span>
                </div>

                <!-- Error alert -->
                <div class="sk-alert sk-alert-error" id="cpError" style="display:none;">
                    <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span id="cpErrorText">No account found with that email address.</span>
                </div>

                <!-- Form -->
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

                <!-- Back to Profile -->
                <div class="youth-register-section">
                    <p class="register-text">
                        Back to profile?
                        <a href="{{ route('profile') }}" class="register-link">Go to Profile</a>
                    </p>
                </div>

            </div>
        </div>

    </main>

    @vite(['app/modules/Authentication/assets/js/loader.js'])
</body>
</html>
