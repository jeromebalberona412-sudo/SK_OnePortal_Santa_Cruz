<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Email Change - SK OnePortal</title>
    @vite([
        'app/Modules/Authentication/assets/css/youth-login.css',
        'app/Modules/Profile/assets/css/change-email.css',
        'app/Modules/Profile/assets/js/change-email-verify.js',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body class="youth-login-page">
    @include('dashboard::loading')

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

        <!-- Left Side — Logo & Branding -->
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

        <!-- Right Side — Card -->
        <div class="youth-login-section">
            <div class="youth-login-card">

                {{-- Verification Sent Section --}}
                <div id="ceVerifySection">

                    <!-- Sent header -->
                    <div class="ce-sent-header">
                        <div class="ce-sent-icon">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ce-sent-title">Verification Sent!</div>
                        <div class="ce-sent-sub">Check your new email inbox and click the link.</div>
                    </div>

                    <!-- Info box -->
                    <div class="ce-info-box">
                        Verification link sent! A confirmation link has been sent to <strong id="cePendingEmail">{{ session('pending_email') ?? '—' }}</strong>. Your current email remains active until you verify the new one.
                    </div>

                    <!-- Status table -->
                    <div class="ce-status-table">
                        <div class="ce-status-row">
                            <span class="ce-status-key">Current email</span>
                            <span class="ce-status-val" id="ceCurrentEmailVal">{{ $user->email ?? '—' }}</span>
                        </div>
                        <div class="ce-status-row">
                            <span class="ce-status-key">Pending email</span>
                            <span class="ce-status-val" id="cePendingEmailVal">{{ session('pending_email') ?? '—' }}</span>
                        </div>
                        <div class="ce-status-row">
                            <span class="ce-status-key">Status</span>
                            <span class="ce-status-val">
                                <span class="ce-badge-awaiting">Awaiting verification</span>
                            </span>
                        </div>
                    </div>

                    <!-- Resend timer -->
                    <div class="ce-resend-timer" id="ceTimer">
                        Resend available in <strong id="ceTimerCount">60</strong>s
                    </div>

                    <!-- Action buttons -->
                    <div class="ce-actions">
                        <button type="button" class="ce-btn-resend" id="ceResendBtn" disabled>
                            Resend Verification
                        </button>
                        <button type="button" class="ce-btn-cancel" id="ceCancelBtn">
                            Cancel Request
                        </button>
                    </div>

                    <div class="youth-register-section ce-back-section">
                        <p class="register-text">
                            <a href="{{ route('profile') }}" class="register-link">← Back to Profile</a>
                        </p>
                    </div>
                </div>

            </div>
        </div>

    </main>

    <script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
