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
    <link href="{{ url('/modules/authentication/css/style.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <style>
        /* Remove browser validation styling */
        input[type="email"] {
            background-image: none !important;
            background-color: white !important;
            border: 2px solid #e2e8f0 !important;
        }

        input[type="email"]:valid,
        input[type="email"]:invalid {
            background-image: none !important;
            border-color: #e2e8f0 !important;
            background-color: white !important;
            box-shadow: none !important;
        }

        input[type="email"]:focus {
            border-color: #213F99 !important;
            box-shadow: 0 0 0 4px rgba(33, 63, 153, 0.1) !important;
            background-image: none !important;
            outline: none !important;
        }

        input[type="email"].is-invalid {
            border-color: #dc3545 !important;
        }

        input[type="email"].is-invalid:focus {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.1) !important;
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
        // Prevent back navigation and redirect if authenticated
        (function() {
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
                    <h2>Reset Your Password</h2>
                    <p>Enter your email address and we'll send you a link to reset your password.</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-info" role="alert" style="margin-bottom: 20px; border-radius: 12px;">
                        {{ session('status') }}
                    </div>
                @endif

                <form id="forgot-password-form" class="login-form" method="POST" action="{{ route('password.email', [], false) }}" data-turnstile-enabled="{{ config('services.turnstile.enabled', true) ? '1' : '0' }}" novalidate>
                    @csrf
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            required
                            placeholder="Enter your email"
                            maxlength="100"
                            autocomplete="email"
                            value="{{ old('email') }}"
                        >
                        <div class="invalid-feedback" id="email-error" @if (! $errors->has('email')) hidden @endif>{{ $errors->first('email') }}</div>
                    </div>

                    @include('authentication::components.turnstile')

                    <button type="submit" class="login-btn btn btn-primary w-100">
                        Send Reset Link
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
    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script>
        const forgotPasswordForm = document.getElementById('forgot-password-form');
        const turnstileEnabled = forgotPasswordForm.dataset.turnstileEnabled === '1';
        const forgotPasswordEmail = document.getElementById('email');
        const forgotPasswordError = document.getElementById('email-error');

        forgotPasswordForm.addEventListener('submit', function(e) {
            const email = forgotPasswordEmail.value.trim();
            const turnstileTokenField = document.querySelector('input[name="cf-turnstile-response"]');
            const turnstileError = document.getElementById('turnstile-error');

            // Reset error state
            forgotPasswordEmail.classList.remove('is-invalid');
            forgotPasswordError.hidden = true;

            if (turnstileError) {
                turnstileError.style.display = 'none';
            }

            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!email) {
                e.preventDefault();
                forgotPasswordEmail.classList.add('is-invalid');
                forgotPasswordError.textContent = 'Please enter your email address.';
                forgotPasswordError.hidden = false;
                return;
            }

            if (!emailRegex.test(email)) {
                e.preventDefault();
                forgotPasswordEmail.classList.add('is-invalid');
                forgotPasswordError.textContent = 'Please enter a valid email address.';
                forgotPasswordError.hidden = false;
                return;
            }

            if (turnstileEnabled && (!turnstileTokenField || !turnstileTokenField.value)) {
                e.preventDefault();

                if (turnstileError) {
                    turnstileError.textContent = 'Bot verification failed.';
                    turnstileError.style.display = 'block';
                }

                return;
            }

            // Show loading
            LoadingScreen.show('Sending Reset Link', 'Please wait...');
        });

        // Remove error on input
        forgotPasswordEmail.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            forgotPasswordError.hidden = true;
        });

        // Show loading on back to login
        document.querySelector('.form-footer a').addEventListener('click', function(e) {
            e.preventDefault();
            LoadingScreen.show('Loading', 'Redirecting to login...');
            setTimeout(() => {
                window.location.href = this.href;
            }, 300);
        });
    </script>
</body>
</html>
