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
        'app/Modules/Authentication/assets/css/forgot-password.css',
        'app/Modules/Profile/assets/js/change-password.js',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body class="sk-login-page">
    @include('loading')

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

                <div class="card-header">
                    <h2 class="card-title">Change Password 🔐</h2>
                    <p class="card-subtitle">Create a new password for your account.</p>
                </div>

                <!-- Error Alert -->
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

                <!-- Change Password Form -->
                <form action="{{ route('password.change.update') }}" method="POST" class="sk-login-form" novalidate>
                    @csrf

                    <!-- New Password -->
                    <div class="sk-form-group">
                        <label for="password" class="sk-label">New Password</label>
                        <div class="password-wrapper">
                            <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="sk-input password-input @error('password') is-invalid @enderror"
                                placeholder="Enter new password (min 12 characters)"
                                autocomplete="new-password"
                                minlength="12"
                                maxlength="64"
                                required
                            >
                            <button type="button" class="pw-toggle-btn" data-target="password" aria-label="Show password" tabindex="-1">
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
                        @error('password')
                            <div class="sk-field-error">{{ $message }}</div>
                        @enderror
                        <div class="cp-strength-label" style="font-size: 0.875rem; margin-top: 0.5rem; color: #6B7280;">
                            Password must contain: letters, numbers, and symbols
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="sk-form-group">
                        <label for="password_confirmation" class="sk-label">Confirm New Password</label>
                        <div class="password-wrapper">
                            <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="sk-input password-input"
                                placeholder="Re-enter new password"
                                autocomplete="new-password"
                                minlength="12"
                                maxlength="64"
                                required
                            >
                            <button type="button" class="pw-toggle-btn" data-target="password_confirmation" aria-label="Show password" tabindex="-1">
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
                        @error('password_confirmation')
                            <div class="sk-field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="sk-submit-btn">
                        <span>Change Password</span>
                        <svg class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
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
        </div>

    </main>

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    @vite(['app/Modules/Authentication/assets/js/loader.js'])
</body>
</html>
