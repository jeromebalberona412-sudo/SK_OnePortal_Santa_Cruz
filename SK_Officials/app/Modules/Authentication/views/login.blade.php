<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SK Officials Portal</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/js/login.js',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <style>
        .sk-main-title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 1024px) {
            .sk-main-title {
                font-size: 32px;
            }
        }

        @media (max-width: 768px) {
            .sk-main-title {
                font-size: 28px;
            }
        }

        @media (max-width: 480px) {
            .sk-main-title {
                font-size: 24px;
            }
        }
    </style>
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
        <!-- Left Side - Logo & Branding -->
        <div class="sk-branding-section">
            <div class="branding-content">
                <div class="logo-wrapper">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="SK Officials Logo"
                        class="sk-logo"
                    >
                </div>
                <h1 class="sk-main-title">SK OnePortal</h1>
                <p class="sk-tagline">SK Officials Portal – Santa Cruz, Laguna</p>
            </div>
        </div>

        <!-- Right Side - Login Card -->
        <div class="sk-login-section">
            <div class="sk-login-card">
                <div class="card-header">
                    <h2 class="card-title">
                        Welcome Back!
                        <span class="wave-emoji">👋</span>
                    </h2>
                    <p class="card-subtitle">Login to your account</p>
                </div>

                <!-- Login Form -->
                <form class="sk-login-form" id="loginForm" method="POST" action="{{ route('login', [], false) }}" novalidate>
                    @csrf

                    @if ($errors->any())
                        <div class="sk-alert sk-alert-error">
                            <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <!-- Email Field -->
                    <div class="sk-form-group">
                        <label for="email" class="sk-label">
                            <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            Email Address
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="sk-input"
                            value="{{ old('email') }}"
                            autofocus
                            autocomplete="email"
                            placeholder="Enter your email"
                            maxlength="150"
                        >
                        <div class="sk-field-error" id="email-error" hidden></div>
                    </div>

                    <!-- Password Field -->
                    <div class="sk-form-group">
                        <label for="password" class="sk-label">
                            <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            Password
                        </label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="sk-input password-input"
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                minlength="8"
                                maxlength="64"
                            >
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility" onclick="togglePassword()">
                                <svg class="eye-icon eye-open" id="eyeOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-icon eye-closed" id="eyeClosed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                        <div class="sk-field-error" id="password-error" hidden></div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="sk-form-options">
                        <label class="sk-checkbox">
                            <input type="checkbox" id="remember" name="remember">
                            <span class="checkbox-label">Remember me</span>
                        </label>
                        <button type="button" class="sk-link" id="forgotBtn">Forgot password?</button>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="sk-submit-btn" id="loginBtn">
                        <span>Sign In</span>
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const eyeOpen = document.getElementById('eyeOpen');
            const eyeClosed = document.getElementById('eyeClosed');
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

        // Form Validation
        (() => {
            const form = document.getElementById('loginForm');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const emailError = document.getElementById('email-error');
            const passwordError = document.getElementById('password-error');
            const loginBtn = document.getElementById('loginBtn');

            function validateEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            function showError(input, errorElement, message) {
                input.classList.add('error');
                errorElement.textContent = message;
                errorElement.hidden = false;
            }

            function clearError(input, errorElement) {
                input.classList.remove('error');
                errorElement.hidden = true;
            }

            emailInput.addEventListener('blur', () => {
                if (!emailInput.value.trim()) {
                    showError(emailInput, emailError, 'Email is required');
                } else if (!validateEmail(emailInput.value)) {
                    showError(emailInput, emailError, 'Please enter a valid email address');
                } else {
                    clearError(emailInput, emailError);
                }
            });

            emailInput.addEventListener('input', () => {
                if (emailInput.value.trim() && validateEmail(emailInput.value)) {
                    clearError(emailInput, emailError);
                }
            });

            passwordInput.addEventListener('blur', () => {
                if (!passwordInput.value.trim()) {
                    showError(passwordInput, passwordError, 'Password is required');
                } else if (passwordInput.value.length < 8) {
                    showError(passwordInput, passwordError, 'Password must be at least 8 characters');
                } else {
                    clearError(passwordInput, passwordError);
                }
            });

            passwordInput.addEventListener('input', () => {
                if (passwordInput.value.trim() && passwordInput.value.length >= 8) {
                    clearError(passwordInput, passwordError);
                }
            });

            form.addEventListener('submit', (e) => {
                let isValid = true;

                if (!emailInput.value.trim()) {
                    showError(emailInput, emailError, 'Email is required');
                    isValid = false;
                } else if (!validateEmail(emailInput.value)) {
                    showError(emailInput, emailError, 'Please enter a valid email address');
                    isValid = false;
                }

                if (!passwordInput.value.trim()) {
                    showError(passwordInput, passwordError, 'Password is required');
                    isValid = false;
                } else if (passwordInput.value.length < 8) {
                    showError(passwordInput, passwordError, 'Password must be at least 8 characters');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    return false;
                }

                LoadingScreen.show('Signing In', 'Please wait...');
            });

            document.getElementById('forgotBtn').addEventListener('click', (e) => {
                e.preventDefault();
                LoadingScreen.show('Redirecting', 'Taking you to password recovery...');
                setTimeout(() => {
                    window.location.href = '{{ route("password.request", [], false) }}';
                }, 300);
            });
        })();
    </script>

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    @vite(['app/Modules/Authentication/assets/js/loader.js'])
@if (session('verification_wait') && session()->has('sk_official_email_verification_pending'))
    <script>window.location.replace("{{ route('sk_official.verification.wait', [], false) }}");</script>
@endif
@if (session('takeover_wait') && session()->has('sk_official_takeover_pending'))
    <script>window.location.replace("{{ route('sk_official.takeover.wait', [], false) }}");</script>
@endif
</body>
</html>
