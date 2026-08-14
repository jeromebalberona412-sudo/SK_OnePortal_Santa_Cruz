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
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Profile/assets/css/change-email.css',
        'app/Modules/Profile/assets/css/change-password.css',
        'app/Modules/Profile/assets/js/change-email-verify.js',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body class="sk-login-page">
    @include('loading')

    <main class="sk-login-container">
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
                <p class="sk-tagline">SK Officials Portal - Santa Cruz, Laguna</p>
            </div>
        </div>

        <div class="sk-login-section">
            <div class="sk-login-card ce-verify-card">
                <div id="ceVerifySection"
                     data-status-url="{{ route('change-email.verify.status', [], false) }}">
                    <div class="card-header" style="text-align:center;">
                        <p class="card-subtitle"
                           style="font-size:1.4rem;font-weight:800;color:#0f172a;letter-spacing:-0.02em;">
                            Verify Email Change
                        </p>
                        <p class="card-subtitle">We sent a confirmation link to your new email address.</p>
                    </div>

                    <div class="ce-verify-content">
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

                        <div class="sk-back-profile">
                            <a href="{{ route('profile') }}" class="sk-link" data-no-loading>Back to Profile</a>
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
