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
    <title>Verify Email Change - SK Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/forgot-password.css',
        'app/Modules/Profile/assets/css/change-email.css',
        'app/Modules/Profile/assets/js/change-email-verify.js',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body class="sk-login-page">
    @include('loading')

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
        <div class="sk-branding-section">
            <div class="branding-content">
                <div class="logo-wrapper">
                    <img src="{{ asset('images/logo.png') }}" alt="SK Officials Logo" class="sk-logo">
                </div>
                <h1 class="sk-main-title">SK OnePortal</h1>
                <p class="sk-tagline">SK Officials Portal – Santa Cruz, Laguna</p>
            </div>
        </div>

        <div class="sk-login-section">
            <div class="sk-login-card ce-verify-card">
                <div id="ceVerifySection">
                    <div class="card-header">
                        <h2 class="card-title">Verify Email Change ✉️</h2>
                        <p class="card-subtitle">We sent a confirmation link to your new email address.</p>
                    </div>

                    <div class="ce-verify-content">
                        <div class="ce-sent-header">
                            <div class="ce-sent-icon">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ce-sent-title">Verification Sent!</div>
                            <div class="ce-sent-sub">Open your inbox and tap the confirmation link.</div>
                        </div>

                        @if ($errors->any())
                            <div class="sk-alert sk-alert-error">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        @if (session('status'))
                            <div class="ce-info-box">{{ session('status') }}</div>
                        @endif

                        <div class="ce-info-box">
                            A confirmation link has been sent to <strong id="cePendingEmail">{{ $user->pending_email }}</strong>. Your current email stays active until you verify the new one.
                        </div>

                        <div class="ce-status-table">
                            <div class="ce-status-row">
                                <span class="ce-status-key">Current email</span>
                                <span class="ce-status-val" id="ceCurrentEmailVal">{{ $user->email }}</span>
                            </div>
                            <div class="ce-status-row">
                                <span class="ce-status-key">Pending email</span>
                                <span class="ce-status-val" id="cePendingEmailVal">{{ $user->pending_email }}</span>
                            </div>
                            <div class="ce-status-row">
                                <span class="ce-status-key">Status</span>
                                <span class="ce-status-val">
                                    <span class="ce-badge-awaiting">Awaiting verification</span>
                                </span>
                            </div>
                        </div>

                        <div class="ce-resend-timer" id="ceTimer" @if($resendCooldown <= 0) style="display:none;" @endif>
                            Resend available in <strong id="ceTimerCount">{{ $resendCooldown > 0 ? $resendCooldown : 60 }}</strong>s
                        </div>

                        <div class="ce-actions">
                            <form action="{{ route('change-email.resend') }}" method="POST" id="ceResendForm">
                                @csrf
                                <button type="submit" class="ce-btn-resend" id="ceResendBtn" @if($resendCooldown > 0) disabled @endif>
                                    Resend Verification
                                </button>
                            </form>
                            <form action="{{ route('change-email.cancel') }}" method="POST" id="ceCancelForm">
                                @csrf
                                <button type="submit" class="ce-btn-cancel" id="ceCancelBtn">
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
        window.ceResendCooldown = {{ (int) $resendCooldown }};
    </script>
    <script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
