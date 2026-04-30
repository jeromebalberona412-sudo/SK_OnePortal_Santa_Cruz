<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Reset Your Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite([
        'app/Modules/Authentication/assets/css/forgot-password.css',
        'app/Modules/Authentication/assets/js/loader.js',
    ])
    <style>
        input[type="password"] {
            background-image: none !important;
            background-color: white !important;
            border: 2px solid #e2e8f0 !important;
        }

        input[type="password"]:valid,
        input[type="password"]:invalid {
            background-image: none !important;
            border-color: #e2e8f0 !important;
            background-color: white !important;
            box-shadow: none !important;
        }

        input[type="password"]:focus {
            border-color: #213F99 !important;
            box-shadow: 0 0 0 4px rgba(33, 63, 153, 0.1) !important;
            background-image: none !important;
            outline: none !important;
        }

        input[type="password"].is-invalid {
            border-color: #dc3545 !important;
        }

        input[type="password"].is-invalid:focus {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.1) !important;
        }

        .password-requirements {
            font-size: 13px;
            color: #64748b;
            margin-top: 6px;
        }
    </style>
</head>
<body>
    @auth
        <script>
            window.location.replace("{{ route('dashboard') }}");
        </script>
    @endauth
    <script>
        (function() {
            window.history.pushState(null, "", window.location.href);
            window.onpopstate = function() {
                window.history.pushState(null, "", window.location.href);
            };
        })();
    </script>

    <div class="login-page">
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
            <div class="logo-container">
                <div class="logo-glow-wrapper">
                    <img src="{{ asset('images/logo.png') }}" alt="SK Officials Logo" class="large-logo">
                </div>
                <h1 class="brand-title">SK OnePortal</h1>
                <p class="brand-subtitle">SK Officials Portal – Santa Cruz, Laguna</p>
            </div>

            <div class="login-form-container">
                <div class="login-card-inner">
                    <div class="form-header">
                        <h2>Reset Your Password</h2>
                        <p>Use at least 12 characters with letters, numbers, and symbols.</p>
                    </div>

                    @if ($errors->has('email') || $errors->has('reset'))
                        <div class="alert alert-danger" role="alert" style="margin-bottom: 20px; border-radius: 12px;">
                            {{ $errors->first('email') ?: $errors->first('reset') }}
                        </div>
                    @endif

                    <form id="reset-password-form" class="login-form" method="POST" action="{{ route('password.update', [], false) }}" data-password-min-length="{{ (int) config('sk_official_auth.password_reset.password.min_length', 12) }}" data-password-max-length="{{ (int) config('sk_official_auth.password_reset.password.max_length', 64) }}" novalidate>
                        @csrf
                        <input type="hidden" name="token" value="{{ old('token', $token) }}">
                        <input type="hidden" name="email" value="{{ old('email', $email) }}">

                        <div class="form-group">
                            <label for="new-password">New Password</label>
                            <div class="password-input-container">
                                <input
                                    type="password"
                                    id="new-password"
                                    name="password"
                                    class="form-control"
                                    required
                                    placeholder="Enter new password"
                                    minlength="{{ (int) config('sk_official_auth.password_reset.password.min_length', 12) }}"
                                    maxlength="{{ (int) config('sk_official_auth.password_reset.password.max_length', 64) }}"
                                    autocomplete="new-password"
                                >
                                <button type="button" class="password-toggle" onclick="togglePasswordField('new-password', 'new-eye-icon', 'new-eye-off-icon')">
                                    <svg id="new-eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg id="new-eye-off-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                            <div class="password-requirements">Minimum 12 characters with letters, numbers, and symbols.</div>
                            <div class="invalid-feedback" id="new-password-error" @if (! $errors->has('password')) hidden @endif>{{ $errors->first('password') }}</div>
                        </div>

                        <div class="form-group">
                            <label for="confirm-password">Confirm New Password</label>
                            <div class="password-input-container">
                                <input
                                    type="password"
                                    id="confirm-password"
                                    name="password_confirmation"
                                    class="form-control"
                                    required
                                    placeholder="Confirm new password"
                                    minlength="{{ (int) config('sk_official_auth.password_reset.password.min_length', 12) }}"
                                    maxlength="{{ (int) config('sk_official_auth.password_reset.password.max_length', 64) }}"
                                    autocomplete="new-password"
                                >
                                <button type="button" class="password-toggle" onclick="togglePasswordField('confirm-password', 'confirm-eye-icon', 'confirm-eye-off-icon')">
                                    <svg id="confirm-eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg id="confirm-eye-off-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                            <div class="invalid-feedback" id="confirm-password-error" @if (! $errors->has('password_confirmation')) hidden @endif>{{ $errors->first('password_confirmation') }}</div>
                        </div>

                        <button type="submit" class="login-btn btn btn-primary w-100">
                            Reset Password
                        </button>
                    </form>

                    <div class="form-footer">
                        <a href="{{ route('login', [], false) }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                                <path d="M19 12H5M12 19l-7-7 7-7"/>
                            </svg>
                            Back to login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePasswordField(inputId, eyeIconId, eyeOffIconId) {
            const input = document.getElementById(inputId);
            const eyeIcon = document.getElementById(eyeIconId);
            const eyeOffIcon = document.getElementById(eyeOffIconId);

            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                input.type = 'password';
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        }

        const resetPasswordForm = document.getElementById('reset-password-form');

        resetPasswordForm.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new-password');
            const confirmPassword = document.getElementById('confirm-password');
            const newPasswordError = document.getElementById('new-password-error');
            const confirmPasswordError = document.getElementById('confirm-password-error');
            const minLength = Number.parseInt(resetPasswordForm.dataset.passwordMinLength || '12', 10);
            const maxLength = Number.parseInt(resetPasswordForm.dataset.passwordMaxLength || '64', 10);
            const hasLetters = /[A-Za-z]/.test(newPassword.value);
            const hasNumbers = /\d/.test(newPassword.value);
            const hasSymbols = /[^A-Za-z0-9]/.test(newPassword.value);

            let isValid = true;

            newPassword.classList.remove('is-invalid');
            confirmPassword.classList.remove('is-invalid');
            newPasswordError.hidden = true;
            confirmPasswordError.hidden = true;

            if (newPassword.value.length < minLength) {
                e.preventDefault();
                newPassword.classList.add('is-invalid');
                newPasswordError.textContent = `Password must be at least ${minLength} characters.`;
                newPasswordError.hidden = false;
                isValid = false;
            } else if (newPassword.value.length > maxLength) {
                e.preventDefault();
                newPassword.classList.add('is-invalid');
                newPasswordError.textContent = `Password must not exceed ${maxLength} characters.`;
                newPasswordError.hidden = false;
                isValid = false;
            } else if (!(hasLetters && hasNumbers && hasSymbols)) {
                e.preventDefault();
                newPassword.classList.add('is-invalid');
                newPasswordError.textContent = 'Password must include letters, numbers, and symbols.';
                newPasswordError.hidden = false;
                isValid = false;
            }

            if (confirmPassword.value !== newPassword.value) {
                e.preventDefault();
                confirmPassword.classList.add('is-invalid');
                confirmPasswordError.textContent = 'Passwords do not match.';
                confirmPasswordError.hidden = false;
                isValid = false;
            }

            if (isValid) {
                LoadingScreen.show('Resetting Password', 'Please wait...');
            }
        });

        document.getElementById('new-password').addEventListener('input', function() {
            this.classList.remove('is-invalid');
            document.getElementById('new-password-error').hidden = true;
        });

        document.getElementById('confirm-password').addEventListener('input', function() {
            this.classList.remove('is-invalid');
            document.getElementById('confirm-password-error').hidden = true;
        });

        document.querySelector('.form-footer a').addEventListener('click', function(e) {
            e.preventDefault();
            LoadingScreen.show('Redirecting', 'Taking you to login...');
            setTimeout(() => {
                window.location.href = this.href;
            }, 300);
        });
    </script>
</body>
</html>
