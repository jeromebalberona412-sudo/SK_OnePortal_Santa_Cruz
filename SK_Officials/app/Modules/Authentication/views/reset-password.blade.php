<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - SK Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/js/login.js',
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
        <!-- Left Side - Logo & Branding -->
        <div class="sk-branding-section">
            <div class="branding-content">
                <div class="collab-logo-wrapper">
                    <div class="logo-glow-wrapper logo-left">
                        <img src="{{ asset('images/skoneportal_logo.webp') }}"
                             alt="SK OnePortal Logo"
                             class="collab-logo">
                    </div>
                    <div class="logo-glow-wrapper logo-right">
                        <img src="{{ asset('images/logo.png') }}"
                             alt="SK Officials Logo"
                             class="collab-logo">
                    </div>
                </div>
                <h1 class="sk-main-title">SK OnePortal</h1>
                <p class="sk-tagline">SK Officials Portal – Santa Cruz, Laguna</p>
            </div>
        </div>

        <!-- Right Side - Reset Password Card -->
        <div class="sk-login-section">
            <div class="sk-login-card">
                <div class="card-header">
                    <h2 class="card-title">
                        Reset Password
                        <span class="wave-emoji">🔐</span>
                    </h2>
                    <p class="card-subtitle">Create a new secure password</p>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="sk-alert sk-alert-error">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ $errors->first('email') ?: $errors->first('reset') ?: $errors->first() }}
                    </div>
                @endif

                <!-- Reset Password Form -->
                <form id="reset-password-form" method="POST" action="{{ route('password.update', [], false) }}" class="sk-login-form" data-password-min-length="{{ (int) config('sk_official_auth.password_reset.password.min_length', 12) }}" data-password-max-length="{{ (int) config('sk_official_auth.password_reset.password.max_length', 64) }}" novalidate>
                    @csrf
                    <input type="hidden" name="token" value="{{ old('token', $token) }}">
                    <input type="hidden" name="email" value="{{ old('email', $email) }}">

                    <!-- New Password Field -->
                    <div class="sk-form-group">
                        <label for="new-password" class="sk-label">
                            <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            New Password
                        </label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="new-password"
                                name="password"
                                class="sk-input password-input @error('password') is-invalid @enderror"
                                required
                                placeholder="Enter new password"
                                minlength="{{ (int) config('sk_official_auth.password_reset.password.min_length', 12) }}"
                                maxlength="{{ (int) config('sk_official_auth.password_reset.password.max_length', 64) }}"
                                autocomplete="new-password"
                            >
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility" onclick="toggleNewPassword()">
                                <svg class="eye-icon eye-open" id="newEyeOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-icon eye-closed" id="newEyeClosed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 6px;">Minimum 12 characters with letters, numbers, and symbols.</div>
                        @error('password')
                            <div class="sk-field-error d-block">{{ $message }}</div>
                        @enderror
                        <div class="sk-field-error" id="new-password-error" hidden></div>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="sk-form-group">
                        <label for="confirm-password" class="sk-label">
                            <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            Confirm Password
                        </label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="confirm-password"
                                name="password_confirmation"
                                class="sk-input password-input @error('password_confirmation') is-invalid @enderror"
                                required
                                placeholder="Confirm new password"
                                minlength="{{ (int) config('sk_official_auth.password_reset.password.min_length', 12) }}"
                                maxlength="{{ (int) config('sk_official_auth.password_reset.password.max_length', 64) }}"
                                autocomplete="new-password"
                            >
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility" onclick="toggleConfirmPassword()">
                                <svg class="eye-icon eye-open" id="confirmEyeOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-icon eye-closed" id="confirmEyeClosed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <div class="sk-field-error d-block">{{ $message }}</div>
                        @enderror
                        <div class="sk-field-error" id="confirm-password-error" hidden></div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="sk-submit-btn" id="resetBtn">
                        <span>Reset Password</span>
                    </button>
                </form>

                <!-- Back to Login Link -->
                <div style="text-align: center; margin-top: 20px;">
                    <a href="{{ route('login', [], false) }}" style="color: #213F99; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 4px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        Back to login
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleNewPassword() {
            const input = document.getElementById('new-password');
            const eyeOpen = document.getElementById('newEyeOpen');
            const eyeClosed = document.getElementById('newEyeClosed');
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

        function toggleConfirmPassword() {
            const input = document.getElementById('confirm-password');
            const eyeOpen = document.getElementById('confirmEyeOpen');
            const eyeClosed = document.getElementById('confirmEyeClosed');
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

        const resetPasswordForm = document.getElementById('reset-password-form');
        const newPasswordInput = document.getElementById('new-password');
        const confirmPasswordInput = document.getElementById('confirm-password');
        const newPasswordError = document.getElementById('new-password-error');
        const confirmPasswordError = document.getElementById('confirm-password-error');

        function clearError(input, errorElement) {
            input.classList.remove('is-invalid');
            errorElement.hidden = true;
            errorElement.textContent = '';
        }

        function showError(input, errorElement, message) {
            input.classList.add('is-invalid');
            errorElement.textContent = message;
            errorElement.hidden = false;
        }

        newPasswordInput.addEventListener('input', function() {
            clearError(this, newPasswordError);
        });

        confirmPasswordInput.addEventListener('input', function() {
            clearError(this, confirmPasswordError);
        });

        resetPasswordForm.addEventListener('submit', function(e) {
            const minLength = Number.parseInt(resetPasswordForm.dataset.passwordMinLength || '12', 10);
            const maxLength = Number.parseInt(resetPasswordForm.dataset.passwordMaxLength || '64', 10);
            const hasLetters = /[A-Za-z]/.test(newPasswordInput.value);
            const hasNumbers = /\d/.test(newPasswordInput.value);
            const hasSymbols = /[^A-Za-z0-9]/.test(newPasswordInput.value);
            
            let isValid = true;

            clearError(newPasswordInput, newPasswordError);
            clearError(confirmPasswordInput, confirmPasswordError);

            if (newPasswordInput.value.length < minLength) {
                e.preventDefault();
                showError(newPasswordInput, newPasswordError, `Password must be at least ${minLength} characters.`);
                isValid = false;
            } else if (newPasswordInput.value.length > maxLength) {
                e.preventDefault();
                showError(newPasswordInput, newPasswordError, `Password must not exceed ${maxLength} characters.`);
                isValid = false;
            } else if (!(hasLetters && hasNumbers && hasSymbols)) {
                e.preventDefault();
                showError(newPasswordInput, newPasswordError, 'Password must include letters, numbers, and symbols.');
                isValid = false;
            }

            if (confirmPasswordInput.value !== newPasswordInput.value) {
                e.preventDefault();
                showError(confirmPasswordInput, confirmPasswordError, 'Passwords do not match.');
                isValid = false;
            }

            if (isValid) {
                LoadingScreen.show('Resetting Password', 'Please wait...');
            }
        });

        document.querySelector('a[href*="login"]').addEventListener('click', function(e) {
            e.preventDefault();
            LoadingScreen.show('Redirecting', 'Taking you to login...');
            setTimeout(() => {
                window.location.href = this.href;
            }, 300);
        });
    </script>

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    @vite(['app/Modules/Authentication/assets/js/loader.js'])
</body>
</html>