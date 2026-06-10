<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Waiting for Email Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ url('/modules/authentication/css/style.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>
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
                    <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Federations Logo" class="large-logo">
                </div>
                <h1 class="brand-title">SK Federation</h1>
                <p class="brand-subtitle">Santa Cruz Youth Leadership Portal</p>
            </div>

            <div class="login-form-container">
                <div class="login-card-inner">
                    <div class="form-header">
                        <h2>Verify Your Email to Continue</h2>
                        <p>Complete verification to access your account</p>
                    </div>

                    <div class="verify-content"
                         data-status-url="{{ route('skfed.verification.wait.status', [], false) }}"
                         data-resend-url="{{ route('skfed.verification.resend', [], false) }}"
                         data-dashboard-url="{{ route('dashboard', [], false) }}"
                         data-email="{{ $email }}"
                         data-user-id="{{ (int) ($userId ?? 0) }}"
                         data-session-key="{{ $sessionKey ?? '' }}"
                         data-fresh-session="{{ ($resendStarted ?? false) ? '0' : '1' }}"
                         data-resend-cooldown="{{ ($resendStarted ?? false) ? (int) $resendCooldown : 0 }}"
                         data-resend-just-sent="{{ ($resendStarted ?? false) ? '1' : '0' }}">

                        <div class="alert alert-info" role="alert" id="verification-state">
                            Waiting for email verification...
                        </div>

                        <p class="verify-wait-message">
                            We sent a verification link to <span class="email-highlight">{{ $email }}</span>.
                            This page will redirect to the dashboard automatically once verified.
                        </p>

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="resend-section">
                            <div class="resend-status" id="resend-status" hidden></div>
                            <div class="resend-cooldown" id="resend-cooldown" style="display: none;">
                                Resend available in <strong id="resend-cooldown-count">1:00</strong>
                            </div>
                            <button type="button" class="login-btn btn-resend" id="resend-btn">
                                <span class="btn-resend-spinner" id="resend-btn-spinner" hidden></span>
                                <span class="btn-resend-label" id="resend-btn-label">Resend Verification Email</span>
                            </button>
                        </div>

                        <div class="form-footer">
                            <a href="{{ route('skfed.verification.cancel', [], false) }}">
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

        <div class="success-modal-overlay" id="success-modal">
            <div class="success-modal">
                <div class="check-wrap" aria-hidden="true">
                    <span class="checkmark"></span>
                </div>
                <h2>Verified Successfully!</h2>
                <p>Redirecting to dashboard...</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script src="{{ url('/modules/authentication/js/verify-wait.js') }}"></script>
</body>
</html>
