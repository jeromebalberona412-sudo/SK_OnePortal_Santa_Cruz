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
    <title>Change Password - SK OnePortal</title>
    @vite([
        'app/Modules/Authentication/assets/css/youth-login.css',
        'app/Modules/Profile/assets/css/change-password.css',
        'app/Modules/Profile/assets/js/change-password.js',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body class="youth-login-page">
    @include('dashboard::loading')

    <!-- Animated Background -->
    <div class="youth-bg-wrapper">
        <div class="youth-bg-image"></div>
        <div class="youth-gradient-overlay"></div>
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>

    <main class="youth-login-container">

        <!-- Left Side — Logo & Branding -->
        <div class="youth-branding-section">
            <div class="branding-content">
                <div class="logo-wrapper">
                    <img
                        src="/images/skoneportal_logo.webp"
                        alt="SK OnePortal Logo"
                        class="youth-logo"
                    >
                </div>
                <h1 class="youth-main-title">SK OnePortal</h1>
                <p class="youth-tagline">Official Youth Portal – Santa Cruz, Laguna</p>
            </div>
        </div>

        <!-- Right Side — Card -->
        <div class="youth-login-section">
            <div class="youth-login-card">
                <div class="card-header">
                    <h2 class="card-title">
                        Change Password 🔐
                    </h2>
                    <p class="card-subtitle">Secure your account with a new password</p>
                </div>

                <!-- Change Password Form -->
                <form class="youth-login-form" method="POST" action="{{ route('change-password.post') }}" id="changePasswordForm">
                    @csrf

                    <!-- Current Password Field -->
                    <div class="youth-form-group">
                        <label for="current_password" class="youth-label">
                            <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            Current Password
                        </label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="youth-input password-input"
                                required
                                maxlength="50"
                                placeholder="Enter your current password"
                            >
                            <button type="button" class="toggle-password pw-toggle-btn" aria-label="Toggle password visibility">
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
                    </div>

                    <!-- New Password Field -->
                    <div class="youth-form-group">
                        <label for="new_password" class="youth-label">
                            <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            New Password
                        </label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                class="youth-input password-input"
                                required
                                maxlength="50"
                                placeholder="Minimum 8 characters"
                            >
                            <button type="button" class="toggle-password pw-toggle-btn" aria-label="Toggle password visibility">
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
                        <ul class="password-rules" id="passwordRules" aria-live="polite">
                            <li id="rule-length">At least 8 characters</li>
                            <li id="rule-lowercase">At least one lowercase letter</li>
                            <li id="rule-uppercase">At least one uppercase letter</li>
                            <li id="rule-number">At least one number</li>
                            <li id="rule-special">At least one special character</li>
                        </ul>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="youth-form-group">
                        <label for="password_confirmation" class="youth-label">
                            <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Confirm Password
                        </label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="youth-input password-input"
                                required
                                maxlength="50"
                                placeholder="Re-enter your new password"
                            >
                            <button type="button" class="toggle-password pw-toggle-btn" aria-label="Toggle password visibility">
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
                        <span class="inline-error" id="confirmPasswordError" style="display: none; color: #ef4444; font-size: 0.875rem; margin-top: 0.5rem;"></span>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="youth-submit-btn">
                        <span>Change Password</span>
                    </button>
                </form>

                <!-- Back to Profile Link -->
                <div class="youth-register-section">
                    <p class="register-text">
                        <a href="{{ route('profile') }}" class="register-link">← Back to Profile</a>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ url('/shared/js/loading.js') }}"></script>

    <style>
        .password-rules {
            list-style: none;
            margin: 0.75rem 0 0;
            padding: 0.6rem 0.7rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .password-rules.active {
            opacity: 1;
            max-height: 220px;
        }

        .password-rules li {
            font-size: 0.875rem;
            color: #64748b;
            padding: 2px 0 2px 20px;
            position: relative;
        }

        .password-rules li::before {
            content: '•';
            position: absolute;
            left: 6px;
            color: #94a3b8;
        }

        .password-rules li.ok {
            color: #16a34a;
        }

        .password-rules li.ok::before {
            content: '✓';
            color: #16a34a;
            font-weight: 700;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .spinner {
            animation: spin 1s linear infinite;
        }
    </style>
</body>
</html>
