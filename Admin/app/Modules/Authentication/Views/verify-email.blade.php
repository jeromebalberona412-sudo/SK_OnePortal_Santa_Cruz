<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OnePortal Admin — Verify Email</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/css/forgot-password.css',
        'app/Modules/Profile/assets/css/profile-verify.css',
        'app/Modules/Authentication/assets/js/verify-email.js',
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
            <div class="login-card-inner login-card-inner--verify" id="evVerifySection"
                 data-status-url="{{ route('verification.status', [], false) }}"
                 data-email="{{ $user->email }}">

                <div class="fp-success-wrap">
                    <p class="fp-success-title" id="evStatusTitle">Verify Your Email</p>
                    <p class="fp-success-msg" id="evStatusSub">
                        We sent a verification link to <strong>{{ $user->email }}</strong>.
                        Open your inbox, click the link, and you will be redirected to the dashboard automatically.
                    </p>

                    <div class="cp-listening-badge" id="evListeningBadge">
                        <span class="cp-listening-dot"></span>
                        Waiting for email verification…
                    </div>

                    @if ($errors->any())
                        <div class="login-alert login-alert--danger" role="alert">{{ $errors->first() }}</div>
                    @endif

                    @if (session('status') === 'verification-link-sent')
                        <div class="login-alert login-alert--success" role="alert">Verification email sent.</div>
                    @endif

                    <div class="fp-resend-box">
                        <div class="fp-timer-row" id="evTimer" @if($resendCooldown <= 0) style="display:none;" @endif>
                            <span>Resend available in</span>
                            <span class="fp-timer-badge" id="evTimerCount">{{ $resendCooldown > 0 ? sprintf('%d:%02d', intdiv($resendCooldown, 60), $resendCooldown % 60) : '1:00' }}</span>
                        </div>

                        <form action="{{ route('verification.send') }}" method="POST" id="evResendForm">
                            @csrf
                            <button type="submit" class="fp-resend-btn" id="evResendBtn">Resend Verification Email</button>
                        </form>
                    </div>
                </div>

                <div class="form-footer">
                    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                        @csrf
                        <button type="submit" style="background:none;border:none;padding:0;color:var(--op-blue,#1565c0);font-weight:600;font-size:0.84rem;cursor:pointer;font-family:inherit;">
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>window.evResendCooldown = {{ (int) $resendCooldown }};</script>

</body>
</html>
