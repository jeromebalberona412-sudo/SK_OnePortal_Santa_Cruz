<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OnePortal Admin — Check Your Email</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/css/forgot-password.css',
        'app/Modules/Profile/assets/css/profile-verify.css',
        'app/Modules/Profile/assets/js/change-password-verify.js',
        'resources/js/theme.js',
    ])
    <script>
        (function () {
            var t = localStorage.getItem('op_theme');
            var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (t === 'dark' || (!t && d)) document.documentElement.classList.add('dark');
        })();
    </script>
</head>
<body class="login-page profile-verify-page">

    <div id="signin-overlay" class="signin-overlay" aria-hidden="true" hidden>
        <div class="signin-overlay-inner">
            <div class="signin-spinner">
                <div class="signin-spinner-ring"></div>
                <div class="signin-spinner-ring signin-spinner-ring--2"></div>
                <div class="signin-spinner-dot"></div>
            </div>
            <p class="signin-overlay-title">Please wait</p>
            <p class="signin-overlay-sub">Processing...</p>
        </div>
    </div>

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
                <img src="{{ asset('Images/image.png') }}" alt="OnePortal Logo" class="large-logo">
            </div>
            <h1 class="brand-title">OnePortal Admin</h1>
            <p class="brand-subtitle">Municipality of Santa Cruz, Laguna</p>
        </div>

        <div class="login-form-container login-form-container--verify">
            <div class="login-card-inner login-card-inner--verify" id="cpVerifySection"
                 data-status-url="{{ route('profile.change-password.verify.status', [], false) }}"
                 data-email="{{ $user->email }}">

                <div class="fp-success-wrap">
                    <p class="fp-success-title" id="cpStatusTitle">Check Your Email</p>
                    <p class="fp-success-msg" id="cpStatusSub">
                        A reset link was sent to <strong>{{ $user->email }}</strong>.
                        Open the email, click the link, then set your new password.
                    </p>

                    <div class="cp-listening-badge" id="cpListeningBadge">
                        <span class="cp-listening-dot"></span>
                        Waiting for password change…
                    </div>

                    @if ($errors->any())
                        <div class="login-alert login-alert--danger" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <div class="cp-status-strip">
                        <span class="cp-status-label">Status</span>
                        <span class="ce-status-badge" id="cpStatusBadge">Awaiting verification</span>
                    </div>

                    <div class="fp-resend-box">
                        <div class="fp-timer-row" id="cpTimer" @if($resendCooldown <= 0) style="display:none;" @endif>
                            <span>Resend available in</span>
                            <span class="fp-timer-badge" id="cpTimerCount">{{ $resendCooldown > 0 ? sprintf('%d:%02d', intdiv($resendCooldown, 60), $resendCooldown % 60) : '1:00' }}</span>
                        </div>

                        <form action="{{ route('profile.change-password.resend') }}" method="POST" id="cpResendForm">
                            @csrf
                            <button type="submit" class="fp-resend-btn" id="cpResendBtn">Resend Reset Link</button>
                        </form>

                        <form action="{{ route('profile.change-password.cancel') }}" method="POST" id="cpCancelForm">
                            @csrf
                            <button type="submit" class="fp-cancel-btn">Cancel Request</button>
                        </form>
                    </div>
                </div>

                <div class="form-footer">
                    <p><a href="{{ route('profile') }}">← Back to Profile</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>window.cpResendCooldown = {{ (int) $resendCooldown }};</script>

</body>
</html>
