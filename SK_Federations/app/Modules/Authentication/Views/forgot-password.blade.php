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
                    <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Federations Logo" class="large-logo">
                </div>
                <h1 class="brand-title">SK Federation</h1>
                <p class="brand-subtitle">Santa Cruz Youth Leadership Portal</p>
            </div>

            {{-- RIGHT: Content Card --}}
            <div class="login-form-container">
                <div class="login-card-inner">
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
