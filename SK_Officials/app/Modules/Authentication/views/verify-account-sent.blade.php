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
    <title>Check Your Email - SK Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/css/forgot-password.css',
    ])
</head>
<body class="sk-login-page">

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
                <div class="card-header">
                    <h2 class="card-title">Check your email</h2>
                    <p class="card-subtitle">{{ $message }}</p>
                </div>

                <form class="sk-login-form" method="POST" action="{{ route('account.activation.send') }}" id="resendActivationForm">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button
                        type="submit"
                        class="sk-submit-btn"
                        id="resendActivationBtn"
                        data-no-loading
                        data-remaining="{{ (int) $cooldownRemaining }}"
                        data-cooldown="{{ (int) $cooldownSeconds }}"
                    >
                        <span id="resendActivationLabel">Resend activation email</span>
                    </button>
                </form>

                <div style="margin-top: 0.75rem;">
                    <a href="{{ route('account.activation.sent') }}" class="sk-secondary-btn" data-no-loading>Cancel</a>
                </div>
            </div>
        </div>
    </main>

    <script>
        (function () {
            var form = document.getElementById('resendActivationForm');
            var btn = document.getElementById('resendActivationBtn');
            var label = document.getElementById('resendActivationLabel');
            if (!form || !btn || !label) {
                return;
            }

            var remaining = parseInt(btn.getAttribute('data-remaining'), 10) || 0;

            function formatTime(seconds) {
                var mins = Math.floor(seconds / 60);
                var secs = seconds % 60;
                return mins + ':' + String(secs).padStart(2, '0');
            }

            function tick() {
                if (remaining <= 0) {
                    btn.disabled = false;
                    label.textContent = 'Resend activation email';
                    return;
                }

                btn.disabled = true;
                label.textContent = 'Resend in ' + formatTime(remaining);
                remaining -= 1;
                window.setTimeout(tick, 1000);
            }

            tick();

            form.addEventListener('submit', function (e) {
                if (btn.disabled) {
                    e.preventDefault();
                    return;
                }
                btn.disabled = true;
                label.textContent = 'Sending...';
            });
        }());
    </script>

</body>
</html>
