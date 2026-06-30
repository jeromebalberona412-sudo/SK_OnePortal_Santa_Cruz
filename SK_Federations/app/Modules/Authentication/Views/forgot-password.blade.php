<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Reset Your Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite([
        'app/Modules/Authentication/assets/css/style.css',
    ])
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

    @php
        $emailSent = session('password_reset_sent') || session('status');
        $resetEmail = old('email', session('password_reset_email', ''));
    @endphp

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
                <div class="collab-logo-wrapper">
                    <div class="logo-glow-wrapper logo-left">
                        <img src="{{ asset('images/SK_OnePortal_logo.png') }}" alt="SK OnePortal Logo" class="collab-logo">
                    </div>
                    <div class="logo-glow-wrapper logo-right">
                        <img src="{{ asset('images/SK_Federations_logo.jpg') }}" alt="SK Federations Logo" class="collab-logo">
                    </div>
                </div>
                <h1 class="brand-title">SK OnePortal</h1>
                <p class="brand-subtitle">SK Federation Portal – Santa Cruz, Laguna</p>
            </div>

            <div class="login-form-container">
                <div class="login-card-inner">
                    <div class="form-header">
                        @if ($emailSent)
                            <h2>Check Your Email</h2>
                            <p>We sent a password reset link to <strong>{{ $resetEmail }}</strong>.</p>
                        @else
                            <h2>Forgot Password? 🔑</h2>
                            <p>Enter your email address and we'll send you a link to reset your password.</p>
                        @endif
                    </div>

                    @if ($emailSent)
                        <div class="alert alert-info fp-success-alert" role="alert" style="margin-bottom: 20px; border-radius: 12px;">
                            {{ session('status', 'A password reset link has been sent to your email address.') }}
                        </div>
                    @endif

                    @if ($errors->any() && ! $emailSent)
                        <div class="alert alert-danger" role="alert" style="margin-bottom: 20px; border-radius: 12px;">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form id="forgotPasswordForm" class="login-form" method="POST" action="{{ route('password.email', [], false) }}" data-turnstile-enabled="{{ app(\App\Modules\Authentication\Services\TurnstileService::class)->isConfigured() ? '1' : '0' }}" data-email-sent="{{ $emailSent ? '1' : '0' }}" novalidate>
                        @csrf

                        @if ($emailSent)
                            <input type="hidden" name="email" id="email" value="{{ $resetEmail }}">
                        @else
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    required
                                    placeholder="Enter your email"
                                    maxlength="100"
                                    autocomplete="email"
                                    value="{{ old('email') }}"
                                    autofocus
                                >
                                <div class="invalid-feedback" id="email-error" @if(! $errors->has('email')) hidden @endif>{{ $errors->first('email') }}</div>
                            </div>
                        @endif

                        @include('authentication::components.turnstile')

                        <button type="submit" class="login-btn btn btn-primary w-100" id="submitBtn">
                            <span id="fpBtnText">{{ $emailSent ? 'Resend Reset Link' : 'Send Reset Link' }}</span>
                        </button>

                        <p class="fp-cooldown-notice" id="fpCooldownNotice" hidden style="text-align: center; margin-top: 12px; font-size: 0.9rem; color: #64748b;">
                            You can resend the link in <strong id="fpCooldownCount">60</strong>s.
                        </p>
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
    <script src="{{ url('/modules/authentication/js/forgot-password.js') }}"></script>
</body>
</html>
