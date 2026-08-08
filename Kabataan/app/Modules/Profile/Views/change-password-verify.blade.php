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
    <title>Verify Password Change - SK OnePortal</title>
    @vite([
        'app/Modules/Authentication/assets/css/sign-in.css',
        'app/Modules/Profile/assets/css/change-email.css',
        'app/Modules/Profile/assets/js/change-password-verify.js',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body class="youth-login-page">
    @include('dashboard::loading')

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
        <div class="youth-branding-section">
            <div class="branding-content">
                <div class="logo-wrapper">
                    <img src="/images/skoneportal_logo.webp" alt="SK OnePortal Logo" class="youth-logo">
                </div>
                <h1 class="youth-main-title">SK OnePortal</h1>
                <p class="youth-tagline">Official Youth Portal – Santa Cruz, Laguna</p>
            </div>
        </div>

        <div class="youth-login-section">
            <div class="youth-login-card">
                <div id="cpVerifySection"
                     data-status-url="{{ route('change-password.verify.status', [], false) }}"
                     data-email="{{ $user->email }}">

                    <div class="card-header">
                        <h2 class="card-title">Verify Password Change 🔐</h2>
                        <p class="card-subtitle">Check your email and tap the confirmation link. This page will detect it automatically.</p>
                    </div>

                    <div class="ce-verify-content">
                        <div class="ce-sent-header">
                            <div class="ce-sent-icon" id="cpStatusIcon">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ce-sent-title" id="cpStatusTitle">Verification Sent!</div>
                            <div class="ce-sent-sub" id="cpStatusSub">Open your inbox and tap the confirmation link.</div>
                        </div>

                        <div class="cp-listening-badge" id="cpListeningBadge">
                            <span class="cp-listening-dot"></span>
                            Listening for email confirmation…
                        </div>

                        @if ($errors->any())
                            <div class="youth-alert youth-alert-error">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        @if (session('status'))
                            <div class="ce-info-box">{{ session('status') }}</div>
                        @endif

                        <div class="ce-info-box" id="cpInfoBox">
                            A confirmation link has been sent to <strong>{{ $user->email }}</strong>. Your current password stays active until you verify. After confirming on any device, you will be signed out automatically on this page.
                        </div>

                        <div class="ce-status-table">
                            <div class="ce-status-row">
                                <span class="ce-status-key">Account email</span>
                                <span class="ce-status-val">{{ $user->email }}</span>
                            </div>
                            <div class="ce-status-row">
                                <span class="ce-status-key">Status</span>
                                <span class="ce-status-val">
                                    <span class="ce-badge-awaiting" id="cpStatusBadge">Awaiting verification</span>
                                </span>
                            </div>
                        </div>

                        <div class="ce-resend-timer" id="cpTimer" @if($resendCooldown <= 0) style="display:none;" @endif>
                            Resend available in <strong id="cpTimerCount">{{ $resendCooldown > 0 ? sprintf('%d:%02d', intdiv($resendCooldown, 60), $resendCooldown % 60) : '1:00' }}</strong>
                        </div>

                        <div class="ce-actions" id="cpActions">
                            <form action="{{ route('change-password.resend') }}" method="POST" id="cpResendForm">
                                @csrf
                                <button type="submit" class="ce-btn-resend" id="cpResendBtn" @if($resendCooldown > 0) disabled @endif>
                                    Resend Verification
                                </button>
                            </form>
                            <form action="{{ route('change-password.cancel') }}" method="POST" id="cpCancelForm">
                                @csrf
                                <button type="submit" class="ce-btn-cancel" id="cpCancelBtn">
                                    Cancel Request
                                </button>
                            </form>
                        </div>

                        <div class="youth-register-section ce-back-section">
                            <p class="register-text">
                                <a href="{{ route('profile') }}" class="register-link">← Back to Profile</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        window.cpResendCooldown = {{ (int) $resendCooldown }};
    </script>
    <script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
