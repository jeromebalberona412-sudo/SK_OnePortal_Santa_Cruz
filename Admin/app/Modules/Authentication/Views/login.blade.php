<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SK OnePortal Admin — Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/js/login.js',
    ])
</head>
<body class="login-page">

    {{-- Sign-in loading overlay --}}
    <div id="signin-overlay" class="signin-overlay" aria-hidden="true" hidden>
        <div class="signin-overlay-inner">
            <div class="signin-spinner">
                <div class="signin-spinner-ring"></div>
                <div class="signin-spinner-ring signin-spinner-ring--2"></div>
                <div class="signin-spinner-dot"></div>
            </div>
            <p class="signin-overlay-title">Signing In</p>
            <p class="signin-overlay-sub">Verifying credentials...</p>
        </div>
    </div>

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

        <div class="login-container">
            {{-- LEFT: Logo --}}
            <div class="logo-container">
                <div class="logo-glow-wrapper">
                    <img src="{{ asset('Images/image.png') }}" alt="SK OnePortal Admin Logo" class="large-logo">
                </div>
                <h1 class="brand-title">SK OnePortal Admin</h1>
                <p class="brand-subtitle">Municipality of Santa Cruz, Laguna</p>
            </div>

            {{-- RIGHT: Card --}}
            <div class="login-form-container">
                <div class="login-card-inner">

                    <div class="form-header">
                        <h2>Welcome, Admin <span class="wave-emoji">👋</span></h2>
                        <p>Sign in to SK OnePortal Admin</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}" novalidate>
                        @csrf

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" placeholder="admin@sccl.gov.ph"
                                required autofocus autocomplete="email">
                            @error('email')
                                <div class="invalid-feedback" data-server-error="true">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="password-input-container">
                                <input type="password" id="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Enter your password"
                                    required autocomplete="current-password"
                                    minlength="8" maxlength="64">
                                <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                    <svg id="pw-show" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <svg id="pw-hide" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                        <line x1="1" y1="1" x2="23" y2="23"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback" data-server-error="true">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-options">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">Remember this device</label>
                            </div>
                        </div>

                        <button type="submit" id="login-submit-btn" class="login-btn w-100" disabled>Sign In</button>
                    </form>

                    <div class="form-footer">
                        <p>Accounts are provisioned by Admin only.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var emailInput   = document.getElementById('email');
            var passwordInput = document.getElementById('password');
            var submitBtn    = document.getElementById('login-submit-btn');

            // Mark any server-side errors so they are not removed by clearError
            document.querySelectorAll('.invalid-feedback').forEach(function (el) {
                el.setAttribute('data-server-error', 'true');
            });

            function validateEmail(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            }

            function showError(input, message) {
                input.classList.add('is-invalid');
                var parent = input.closest('.password-input-container')
                    ? input.closest('.password-input-container').parentElement
                    : input.parentElement;
                var existing = parent.querySelector('.invalid-feedback:not([data-server-error])');
                if (existing) existing.remove();
                var div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = message;
                parent.appendChild(div);
            }

            function clearError(input) {
                input.classList.remove('is-invalid');
                var parent = input.closest('.password-input-container')
                    ? input.closest('.password-input-container').parentElement
                    : input.parentElement;
                var existing = parent.querySelector('.invalid-feedback:not([data-server-error])');
                if (existing) existing.remove();
            }

            function toggleSubmitBtn() {
                submitBtn.disabled = !(emailInput.value.trim() && passwordInput.value);
            }

            toggleSubmitBtn();

            emailInput.addEventListener('input', function () { clearError(this); toggleSubmitBtn(); });
            passwordInput.addEventListener('input', function () { clearError(this); toggleSubmitBtn(); });

            emailInput.addEventListener('blur', function () {
                if (!this.value.trim()) {
                    showError(this, 'Email address is required.');
                } else if (!validateEmail(this.value.trim())) {
                    showError(this, 'Please enter a valid email address.');
                }
            });

            passwordInput.addEventListener('blur', function () {
                if (!this.value) {
                    showError(this, 'Password is required.');
                } else if (this.value.length < 8) {
                    showError(this, 'Password must be at least 8 characters.');
                }
            });

            document.querySelector('form').addEventListener('submit', function (e) {
                var valid = true;
                clearError(emailInput);
                clearError(passwordInput);

                if (!emailInput.value.trim()) {
                    showError(emailInput, 'Email address is required.');
                    valid = false;
                } else if (!validateEmail(emailInput.value.trim())) {
                    showError(emailInput, 'Please enter a valid email address.');
                    valid = false;
                }

                if (!passwordInput.value) {
                    showError(passwordInput, 'Password is required.');
                    valid = false;
                } else if (passwordInput.value.length < 8) {
                    showError(passwordInput, 'Password must be at least 8 characters.');
                    valid = false;
                }

                if (!valid) { e.preventDefault(); return false; }
            });

            // Password visibility toggle
            var toggleBtn = document.querySelector('.password-toggle');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    var input = document.getElementById('password');
                    var show  = document.getElementById('pw-show');
                    var hide  = document.getElementById('pw-hide');
                    if (input.type === 'password') {
                        input.type = 'text';
                        show.style.display = 'none';
                        hide.style.display = '';
                    } else {
                        input.type = 'password';
                        show.style.display = '';
                        hide.style.display = 'none';
                    }
                });
            }
        });
    </script>

</body>
</html>
