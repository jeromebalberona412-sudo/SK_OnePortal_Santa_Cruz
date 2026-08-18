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
    <title>Verify Password Change - SK Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Profile/assets/css/change-password.css',
        'app/Modules/Profile/assets/js/change-password-verify.js',
    ])
</head>
<body class="sk-login-page sk-page-scroll">

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
            <div class="sk-login-card">
                <div id="cpVerifySection"
                     data-status-url="{{ route('change-password.verify.status', [], false) }}"
                     data-resend-url="{{ route('change-password.resend', [], false) }}"
                     data-email="{{ $user->email }}">

                    <div class="card-header" style="text-align:center;">
                        <p class="card-subtitle"
                           style="font-size:1.4rem;font-weight:800;color:#0f172a;letter-spacing:-0.02em;">
                            Verify Password Change
                        </p>
                        <p class="card-subtitle">We sent a confirmation link to {{ $user->email }}.</p>
                    </div>

                    @if ($errors->any())
                        <div class="sk-alert sk-alert-error" id="cpFeedback" role="status">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @else
                        <div class="sk-alert sk-alert-success" id="cpFeedback" role="status">
                            Check your email and tap the confirmation link. This page will sign you out automatically after you confirm.
                        </div>
                    @endif

                    <div class="cp-verify-form" id="cpActions">
                        <form action="{{ route('change-password.resend') }}" method="POST" id="cpResendForm">
                            @csrf
                            <button type="button" class="sk-submit-btn" id="cpResendBtn">
                                <span id="cpResendBtnText">Resend Verification</span>
                            </button>
                        </form>
                        <form action="{{ route('change-password.cancel') }}" method="POST" id="cpCancelForm">
                            @csrf
                            <button type="submit" class="cp-cancel-btn" id="cpCancelBtn">
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
    </main>
    <script>
        window.cpResendCooldown = {{ (int) ($resendCooldown ?? 0) }};
    </script>
</body>
</html>
