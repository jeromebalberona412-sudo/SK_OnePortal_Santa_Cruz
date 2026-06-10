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
                <img src="{{ asset('Images/image.png') }}" alt="SK OnePortal Admin Logo" class="large-logo">
            </div>
            <h1 class="brand-title">SK OnePortal Admin</h1>
            <p class="brand-subtitle">Municipality of Santa Cruz, Laguna</p>
        </div>

        <div class="login-form-container login-form-container--verify">
            <div class="login-card-inner login-card-inner--verify" id="cpVerifySection"
                 data-status-url="{{ route('profile.change-password.verify.status', [], false) }}"
                 data-email="{{ $user->email }}"
                 data-resend-cooldown="{{ (int) $resendCooldown }}">

                <div class="form-header">
                    <h2 id="cpStatusTitle">Check Your Email <span class="wave-emoji">✉️</span></h2>
                    <p id="cpStatusSub">
                        A reset link was sent to <strong>{{ $user->email }}</strong>.
                        Open the email, click the link, then set your new password.
                    </p>
                </div>

                <div class="ev-listening-badge" id="cpListeningBadge">
                    <span class="ev-listening-dot"></span>
                    Waiting for password change…
                </div>

                @if ($errors->any())
                    <div class="login-alert login-alert--danger" role="alert">{{ $errors->first() }}</div>
                @endif

                @if (session('status'))
                    <div class="login-alert login-alert--success" role="alert">{{ session('status') }}</div>
                @endif

                <div class="pv-status-pill" id="cpStatusBadge">Awaiting verification</div>

                <div class="ev-resend-section">
                    <div class="ev-timer-row" id="cpTimer" style="display:none;">
                        <span>Resend available in</span>
                        <span class="ev-timer-badge" id="cpTimerCount">1:00</span>
                    </div>

                    <form action="{{ route('profile.change-password.resend') }}" method="POST" id="cpResendForm">
                        @csrf
                        <button type="submit" class="login-btn w-100 ev-resend-btn visible" id="cpResendBtn">Resend Reset Link</button>
                    </form>

                    <form action="{{ route('profile.change-password.cancel') }}" method="POST" id="cpCancelForm">
                        @csrf
                        <button type="submit" class="pv-cancel-btn" id="cpCancelBtn">Cancel Request</button>
                    </form>
                </div>

                <div class="form-footer">
                    <p><a href="{{ route('profile') }}">← Back to Profile</a></p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
