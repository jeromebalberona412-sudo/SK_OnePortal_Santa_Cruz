<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>OnePortal SK Federation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ url('/modules/authentication/css/style.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>
    <script>
        (function() {
            @auth
                window.location.replace("{{ route('dashboard') }}");
            @endauth
            window.history.pushState(null, "", window.location.href);
            window.onpopstate = function() {
                window.history.pushState(null, "", window.location.href);
            };
        })();
    </script>

    <div class="login-page">
        {{-- Background --}}
        <div class="bg-wrapper">
            <div class="bg-image"></div>
            <div class="gradient-overlay"></div>
            <div class="floating-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
            </div>
        </div>

        {{-- Main Split Layout --}}
        <div class="login-container">

            {{-- LEFT: Logo & Branding --}}
            <div class="logo-container">
                <div class="logo-glow-wrapper">
                    <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}"
                         alt="SK Federations Logo"
                         class="large-logo">
                </div>
                <h1 class="brand-title">SK OnePortal</h1>
                <p class="brand-subtitle">SK Federation Portal – Santa Cruz, Laguna</p>
            </div>

            {{-- RIGHT: Login Card --}}
            <div class="login-form-container">
                <div class="login-card-inner">
                    <div class="form-header">
                        <h2 class="nowrap">Welcome Back! <span class="wave-emoji">👋</span></h2>
                        <p>Sign in to your account</p>
                    </div>

                    <form method="POST" action="{{ route('login', [], false) }}" class="login-form" novalidate>
                        @csrf
                        <div class="form-group">
                            <label for="email">
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
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                autofocus
                                placeholder="Enter your email"
                                maxlength="150"
                            >
                            @error('email')
                                <div class="invalid-feedback" data-server-error="true">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">
                                <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                                Password
                            </label>
                            <div class="password-input-container">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter your password"
                                    minlength="8"
                                    maxlength="64"
                                >
                                <button type="button" class="pw-toggle-btn" id="pwToggleBtn" aria-label="Show password" tabindex="-1">
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
                                <div class="invalid-feedback d-block" data-server-error="true">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-options">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <a href="{{ url('/forgot-password') }}" class="forgot-password">Forgot Password?</a>
                        </div>

                        <button type="submit" class="login-btn btn btn-primary w-100">
                            Sign In
                        </button>
                    </form>


                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script src="{{ url('/modules/authentication/js/script.js') }}"></script>
    <script>
        const loginForm = document.querySelector('.login-form');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        function validateEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        function showError(input, message) {
            input.classList.add('is-invalid');
            let insertTarget = input.id === 'password'
                ? input.closest('.password-input-container').parentElement
                : input.parentElement;
            const existing = insertTarget.querySelector('.invalid-feedback:not([data-server-error])');
            if (existing) existing.remove();
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback d-block';
            errorDiv.textContent = message;
            insertTarget.appendChild(errorDiv);
        }

        function clearError(input) {
            input.classList.remove('is-invalid');
            let searchTarget = input.id === 'password'
                ? input.closest('.password-input-container').parentElement
                : input.parentElement;
            const errorDiv = searchTarget.querySelector('.invalid-feedback:not([data-server-error])');
            if (errorDiv) errorDiv.remove();
        }

        emailInput.addEventListener('input', function() { clearError(this); });
        passwordInput.addEventListener('input', function() { clearError(this); });

        loginForm.addEventListener('submit', function(e) {
            let isValid = true;
            clearError(emailInput);
            clearError(passwordInput);

            if (!emailInput.value.trim()) {
                showError(emailInput, 'Email address is required.');
                isValid = false;
            } else if (!validateEmail(emailInput.value.trim())) {
                showError(emailInput, 'Please enter a valid email address.');
                isValid = false;
            }

            if (!passwordInput.value) {
                showError(passwordInput, 'Password is required.');
                isValid = false;
            } else if (passwordInput.value.length < 8) {
                showError(passwordInput, 'Password must be at least 8 characters long.');
                isValid = false;
            } else if (passwordInput.value.length > 64) {
                showError(passwordInput, 'Password must not exceed 64 characters.');
                isValid = false;
            }

            if (!isValid) { e.preventDefault(); return false; }
            LoadingScreen.show('Signing In', 'Verifying your credentials...');
        });

        document.querySelector('.forgot-password').addEventListener('click', function(e) {
            e.preventDefault();
            LoadingScreen.show('Loading', 'Redirecting to password reset...');
            setTimeout(() => { window.location.href = this.href; }, 300);
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.invalid-feedback').forEach(function(el) {
                el.setAttribute('data-server-error', 'true');
            });

            // Password visibility toggle
            var btn   = document.getElementById('pwToggleBtn');
            var input = document.getElementById('password');
            if (btn && input) {
                btn.addEventListener('click', function () {
                    var show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                    btn.classList.toggle('pw-visible', show);
                });
            }
        });
    </script>
</body>
@if (session('verification_wait') && session()->has('sk_fed_email_verification_pending'))
    <script>window.location.replace("{{ route('skfed.verification.wait', [], false) }}");</script>
@endif
@if (session('takeover_wait') && session()->has('sk_fed_takeover_pending'))
    <script>window.location.replace("{{ route('skfed.takeover.wait', [], false) }}");</script>
@endif
</html>
