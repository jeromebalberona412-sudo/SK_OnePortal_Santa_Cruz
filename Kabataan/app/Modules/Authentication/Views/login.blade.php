<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OnePortal Youth Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/youth-login.css',
        'app/Modules/Authentication/assets/css/auth-legal.css',
        'app/Modules/Authentication/assets/js/youth-login.js',
        'app/Modules/Authentication/assets/js/auth-legal.js',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <style>
        .youth-main-title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 1024px) {
            .youth-main-title {
                font-size: 32px;
            }
        }

        @media (max-width: 768px) {
            .youth-main-title {
                font-size: 28px;
            }
        }

        @media (max-width: 480px) {
            .youth-main-title {
                font-size: 24px;
            }
        }
    </style>
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
        <!-- Left Side - Logo & Branding -->
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

        <!-- Right Side - Login Card -->
        <div class="youth-login-section">
            <div class="youth-login-card">
                <div class="card-header">
                    <p class="card-subtitle">Sign in to your account</p>
                </div>

                @if (session('login_error'))
                    <div class="youth-alert youth-alert-error" role="alert">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('login_error') }}</span>
                    </div>
                @endif

                @if (session('success'))
                    <div class="youth-alert youth-alert-success" role="alert">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <!-- Login Form -->
                <form class="youth-login-form" id="loginForm" method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Field -->
                    <div class="youth-form-group">
                        <label for="email" class="youth-label">
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
                            class="youth-input"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="Enter example@gmail.com"
                        >
                        <div class="youth-field-error" id="email-error" hidden style="display: none !important;"></div>
                    </div>

                    <!-- Password Field -->
                    <div class="youth-form-group">
                        <label for="password" class="youth-label">
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
                                class="youth-input password-input"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                            >
                            <button type="button" class="pw-toggle-btn" id="pwToggleBtn" aria-label="Show password" tabindex="-1">
                                {{-- Eye open (password hidden) --}}
                                <svg class="pw-eye pw-eye-show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                {{-- Eye closed (password visible) --}}
                                <svg class="pw-eye pw-eye-hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                    <path d="M1 1l22 22"/>
                                </svg>
                            </button>
                        </div>
                        <div class="youth-field-error" id="password-error" hidden style="display: none !important;"></div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="youth-form-options">
                        <label class="youth-checkbox">
                            <input
                                type="checkbox"
                                id="remember"
                                name="remember"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <span class="checkbox-label">Remember me</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="youth-link" id="forgotBtn">Forgot password?</a>
                    </div>

                    @include('authentication::partials.login-legal-consent')

                    <!-- Submit Button -->
                    <button type="submit" class="youth-submit-btn" id="loginBtn">
                        <span>Login</span>
                    </button>

                </form>

                <!-- Registration Link -->
                <div class="youth-register-section">
                    <p class="register-text">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="register-link">Sign Up here</a>
                    </p>
                    <a href="{{ route('homepage') }}" class="register-link register-home-link" id="homepageBtn">
                        Back to Homepage
                    </a>
                </div>
            </div>
        </div>
    </main>

    @include('authentication::partials.legal-modals')
    @include('authentication::partials.login-legal-prompt')

    <!-- Load loading script AFTER the overlay HTML is rendered -->
    <script src="{{ url('/shared/js/loading.js') }}"></script>

    <script>
        (function () {
            var btn   = document.getElementById('pwToggleBtn');
            var input = document.getElementById('password');
            if (!btn || !input) return;
            btn.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                btn.classList.toggle('pw-visible', show);
            });
        })();

        // Form Validation
        (() => {
            const form = document.getElementById('loginForm');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const emailError = document.getElementById('email-error');
            const passwordError = document.getElementById('password-error');

            function validateEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            function showError(input, errorElement, message) {
                input.classList.add('error');
                errorElement.textContent = message;
                errorElement.hidden = false;
                errorElement.style.display = 'block';
            }

            function clearError(input, errorElement) {
                input.classList.remove('error');
                errorElement.hidden = true;
                errorElement.style.display = 'none';
            }



            // Validate on submit — only show loading when form will actually submit
            form.addEventListener('submit', (e) => {
                let isValid = true;

                clearError(emailInput, emailError);
                clearError(passwordInput, passwordError);

                if (!emailInput.value.trim()) {
                    showError(emailInput, emailError, 'Email is required');
                    isValid = false;
                } else if (!validateEmail(emailInput.value.trim())) {
                    showError(emailInput, emailError, 'Please enter a valid email address');
                    isValid = false;
                }

                if (!passwordInput.value) {
                    showError(passwordInput, passwordError, 'Password is required');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    return false;
                }

                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    const label = submitBtn.querySelector('span');
                    if (label) label.textContent = 'Signing In...';
                }

                if (typeof showLoading === 'function') {
                    showLoading('Signing In');
                }
            });

            document.getElementById('forgotBtn').addEventListener('click', (e) => {
                e.preventDefault();
                if (typeof showLoading !== 'undefined') {
                    showLoading('Redirecting to password recovery');
                    setTimeout(() => {
                        window.location.href = e.target.href;
                    }, 300);
                } else {
                    window.location.href = e.target.href;
                }
            });

            document.getElementById('homepageBtn')?.addEventListener('click', (e) => {
                if (typeof showLoading !== 'undefined') {
                    showLoading('Redirecting to Homepage');
                }
            });
        })();
    </script>
</body>
</html>
