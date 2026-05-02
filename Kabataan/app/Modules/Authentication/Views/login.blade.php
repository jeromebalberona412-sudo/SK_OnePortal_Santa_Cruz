<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OnePortal Youth Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/youth-login.css',
        'app/Modules/Authentication/assets/js/youth-login.js',
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
                    <h2 class="card-title">
                        Welcome, Kabataan! 
                        <span class="wave-emoji">👋</span>
                    </h2>
                    <p class="card-subtitle">Login to your account</p>
                </div>

                <!-- Alert Messages -->
                @if (session('success'))
                    <div class="youth-alert youth-alert-success">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="youth-alert youth-alert-error">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ $errors->first() }}
                    </div>
                @endif

                <!-- Login Form -->
                <form class="youth-login-form" id="loginForm" method="POST" action="{{ route('login') }}" novalidate>
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
                            autofocus
                            autocomplete="email"
                            placeholder="Enter your email"
                        >
                        <div class="youth-field-error" id="email-error" hidden></div>
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
                        <div class="youth-field-error" id="password-error" hidden></div>
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

                    <!-- Submit Button -->
                    <button type="submit" class="youth-submit-btn">
                        <span>Login</span>
                    </button>
                </form>

                <!-- Registration Link -->
                <div class="youth-register-section" style="display:none;"></div>
            </div>
        </div>
    </main>

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
                } else {
                    clearError(passwordInput, passwordError);
                }
            });

            passwordInput.addEventListener('input', () => {
                if (passwordInput.value.trim()) {
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
                    window.location.href = this.href;
                }, 300);
            });
        })();
    </script>

    <script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
