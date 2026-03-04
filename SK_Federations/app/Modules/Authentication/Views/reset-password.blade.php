<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Create New Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ url('/modules/authentication/css/style.css') }}" rel="stylesheet">
    <style>
        /* Remove browser validation styling */
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
    <script>
        // Prevent back navigation and redirect if authenticated
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
    <div class="login-container">
        <div class="background-section">
            <div class="logo-container">
                <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Federations Logo" class="large-logo">
                <h1 class="brand-title">SK Federations</h1>
                <p class="brand-subtitle">Santa Cruz Youth Leadership Portal</p>
            </div>

            <div class="login-form-container">
                <div class="form-header">
                    <h2>Create New Password</h2>
                    <p>Your new password must be different from previously used passwords.</p>
                </div>

                <form id="reset-password-form" class="login-form" novalidate>
                    <div class="form-group">
                        <label for="new-password">New Password</label>
                        <div class="password-input-container">
                            <input
                                type="password"
                                id="new-password"
                                name="new_password"
                                class="form-control"
                                required
                                placeholder="Enter new password"
                                minlength="8"
                                maxlength="25"
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
                        <div class="password-requirements"></div>
                        <div class="invalid-feedback" style="display: none;"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm-password">Confirm New Password</label>
                        <div class="password-input-container">
                            <input
                                type="password"
                                id="confirm-password"
                                name="confirm_password"
                                class="form-control"
                                required
                                placeholder="Confirm new password"
                                minlength="8"
                                maxlength="25"
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
                        <div class="invalid-feedback" style="display: none;"></div>
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

        document.getElementById('reset-password-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newPassword = document.getElementById('new-password');
            const confirmPassword = document.getElementById('confirm-password');
            const newPasswordError = newPassword.parentElement.nextElementSibling.nextElementSibling;
            const confirmPasswordError = confirmPassword.parentElement.nextElementSibling;
            
            let isValid = true;

            // Reset errors
            newPassword.classList.remove('is-invalid');
            confirmPassword.classList.remove('is-invalid');
            newPasswordError.style.display = 'none';
            confirmPasswordError.style.display = 'none';

            // Validate new password
            if (newPassword.value.length < 8) {
                newPassword.classList.add('is-invalid');
                newPasswordError.textContent = 'Password must be at least 8 characters.';
                newPasswordError.style.display = 'block';
                isValid = false;
            } else if (newPassword.value.length > 25) {
                newPassword.classList.add('is-invalid');
                newPasswordError.textContent = 'Password must not exceed 25 characters.';
                newPasswordError.style.display = 'block';
                isValid = false;
            }

            // Validate confirm password
            if (confirmPassword.value !== newPassword.value) {
                confirmPassword.classList.add('is-invalid');
                confirmPasswordError.textContent = 'Passwords do not match.';
                confirmPasswordError.style.display = 'block';
                isValid = false;
            }

            if (isValid) {
                // Prototype: Redirect to success page
                window.location.href = "{{ url('/password-reset-success') }}";
            }
        });

        // Remove error on input
        document.getElementById('new-password').addEventListener('input', function() {
            this.classList.remove('is-invalid');
            this.parentElement.nextElementSibling.nextElementSibling.style.display = 'none';
        });

        document.getElementById('confirm-password').addEventListener('input', function() {
            this.classList.remove('is-invalid');
            this.parentElement.nextElementSibling.style.display = 'none';
        });
    </script>
</body>
</html>
