<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OnePortal Admin — Verification Sent</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/css/forgot-password.css',
        'app/Modules/Profile/assets/css/change-email.css',
        'app/Modules/Profile/assets/css/profile-verify.css',
        'app/Modules/Profile/assets/js/change-email-verify.js',
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
            <div class="login-card-inner login-card-inner--verify" id="ceVerifySection">
                <div class="fp-success-wrap">
                    <div class="ce-sent-header ce-sent-header--center">
                        <div class="ce-sent-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#16a34a" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="ce-verify-title">Verification Sent!</h2>
                            <p class="ce-verify-sub">Check <strong>{{ $user->pending_email }}</strong> and click the link.</p>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="login-alert login-alert--danger" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <div class="cp-status-strip">
                        <span class="cp-status-label">Pending</span>
                        <span class="cp-status-email">{{ $user->pending_email }}</span>
                    </div>

                    <div class="fp-resend-box">
                        <div class="fp-timer-row" id="ceTimer" @if($resendCooldown <= 0) style="display:none;" @endif>
                            <span>Resend available in</span>
                            <span class="fp-timer-badge" id="ceTimerCount">{{ $resendCooldown > 0 ? sprintf('%d:%02d', intdiv($resendCooldown, 60), $resendCooldown % 60) : '1:00' }}</span>
                        </div>

                        <form action="{{ route('profile.change-email.resend') }}" method="POST" id="ceResendForm">
                            @csrf
                            <button type="submit" class="fp-resend-btn" id="ceResendBtn">Resend Verification</button>
                        </form>

                        <form action="{{ route('profile.change-email.cancel') }}" method="POST" id="ceCancelForm">
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

    <script>window.ceResendCooldown = {{ (int) $resendCooldown }};</script>

</body>
</html>
