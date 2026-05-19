<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OnePortal Admin — Forgot Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/js/login.js',
        'resources/js/theme.js',
    ])
    <script>
        (function () {
            var t = localStorage.getItem('op_theme');
            var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (t === 'dark' || (!t && d)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <style>
        /* ── Forgot-password specific styles ── */
        .fp-icon-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(8,48,128,0.08) 0%, rgba(13,71,161,0.12) 100%);
            border: 1.5px solid rgba(8,48,128,0.12);
            margin-bottom: 1.25rem;
        }
        .fp-icon-wrap svg {
            width: 28px;
            height: 28px;
            color: #083080;
        }

        /* Success state */
        .fp-success-state { display: none; }
        .fp-success-state.is-visible { display: block; }
        .fp-form-state.is-hidden { display: none; }

        .fp-success-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(34,197,94,0.1);
            border: 2px solid rgba(34,197,94,0.25);
            margin: 0 auto 1.25rem;
        }
        .fp-success-icon svg {
            width: 32px;
            height: 32px;
            color: #15803d;
        }
        .fp-success-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .fp-success-body {
            font-size: 0.875rem;
            color: #64748b;
            text-align: center;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .fp-success-email {
            font-weight: 700;
            color: #083080;
        }

        /* Resend row */
        .fp-resend-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }
        .fp-resend-label {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 500;
        }
        .fp-resend-btn {
            background: none;
            border: none;
            padding: 0;
            font-size: 0.875rem;
            font-weight: 700;
            color: #083080;
            cursor: pointer;
            text-decoration: underline;
            text-underline-offset: 3px;
            transition: color 150ms;
        }
        .fp-resend-btn:hover:not(:disabled) { color: #051e52; }
        .fp-resend-btn:disabled {
            color: #94a3b8;
            cursor: not-allowed;
            text-decoration: none;
        }

        /* Countdown badge */
        .fp-countdown-wrap {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #64748b;
        }
        .fp-countdown-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            background: rgba(8,48,128,0.07);
            border: 1.5px solid rgba(8,48,128,0.12);
            font-size: 0.8rem;
            font-weight: 700;
            color: #083080;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.02em;
        }
        .fp-countdown-badge.is-expiring {
            background: rgba(220,53,69,0.07);
            border-color: rgba(220,53,69,0.2);
            color: #b91c1c;
        }

        /* Progress bar */
        .fp-timer-bar-wrap {
            width: 100%;
            height: 4px;
            background: rgba(8,48,128,0.08);
            border-radius: 99px;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .fp-timer-bar {
            height: 100%;
            width: 100%;
            background: linear-gradient(90deg, #083080, #0d47a1);
            border-radius: 99px;
            transform-origin: left;
            transition: width 1s linear, background 0.5s;
        }
        .fp-timer-bar.is-expiring {
            background: linear-gradient(90deg, #dc3545, #f87171);
        }

        /* Sent confirmation flash */
        .fp-resent-flash {
            display: none;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #15803d;
            background: rgba(34,197,94,0.08);
            border: 1.5px solid rgba(34,197,94,0.2);
            border-radius: 8px;
            padding: 0.45rem 0.75rem;
            margin-bottom: 1rem;
        }
        .fp-resent-flash.is-visible { display: flex; }
        .fp-resent-flash svg { flex-shrink: 0; }
    </style>
</head>
<body class="login-page">

    {{-- Sign-in loading overlay --}}
    <div id="signin-overlay" class="signin-overlay" aria-hidden="true" hidden>
        <div class="signin-overlay-inner">
            <div class="signin-spinner">
                <div class="signin-spinner-ring"></div>
                <div class="signin-spinner-ring signin-spinner-ring--2"></div>
                <div class="signin-spinner-dot"></div>
            </div>
            <p class="signin-overlay-title">Sending Reset Link</p>
            <p class="signin-overlay-sub" id="signin-overlay-sub">Please wait...</p>
        </div>
    </div>

    {{-- Theme toggle --}}
    <button data-theme-toggle class="theme-toggle-btn" aria-label="Switch to dark mode" title="Switch to dark mode">
        <span class="theme-icon-dark" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" viewBox="0 0 16 16">
                <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/>
            </svg>
        </span>
        <span class="theme-icon-light" aria-hidden="true" style="display:none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6m0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
            </svg>
        </span>
        <span class="theme-label">Dark Mode</span>
    </button>

    <div class="login-page">
        {{-- Background --}}
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
            {{-- LEFT: Logo --}}
            <div class="logo-container">
                <div class="logo-glow-wrapper">
                    <img src="{{ asset('Images/image.png') }}" alt="OnePortal Logo" class="large-logo">
                </div>
                <h1 class="brand-title">OnePortal Admin</h1>
                <p class="brand-subtitle">Municipality of Santa Cruz, Laguna</p>
            </div>

            {{-- RIGHT: Card --}}
            <div class="login-form-container">
                <div class="login-card-inner">

                    {{-- ── FORM STATE ── --}}
                    <div class="fp-form-state" id="fpFormState">

                        <div class="fp-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>

                        <div class="form-header">
                            <h2>Forgot Password?</h2>
                            <p>Enter your registered email and we'll send you a reset link.</p>
                        </div>

                        @if ($errors->any())
                            <div class="login-alert login-alert--danger" role="alert">{{ $errors->first() }}</div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}" novalidate id="fpForm">
                            @csrf

                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" placeholder="Enter your registered email"
                                    required autofocus autocomplete="email">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="login-btn" id="fpSubmitBtn">
                                Send Reset Link
                            </button>
                        </form>

                        <div class="form-footer">
                            <p>Remember your password? <a href="{{ route('login') }}">Back to Login</a></p>
                        </div>

                    </div>

                    {{-- ── SUCCESS STATE ── --}}
                    <div class="fp-success-state" id="fpSuccessState" aria-live="polite">

                        <div class="fp-success-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                        </div>

                        <p class="fp-success-title">Check your inbox!</p>
                        <p class="fp-success-body">
                            We sent a password reset link to<br>
                            <span class="fp-success-email" id="fpSuccessEmail"></span>
                        </p>

                        {{-- Resent flash --}}
                        <div class="fp-resent-flash" id="fpResentFlash" role="status">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                            Email resent successfully!
                        </div>

                        {{-- Timer progress bar --}}
                        <div class="fp-timer-bar-wrap" aria-hidden="true">
                            <div class="fp-timer-bar" id="fpTimerBar"></div>
                        </div>

                        {{-- Resend row --}}
                        <div class="fp-resend-row">
                            <span class="fp-resend-label">Didn't receive it?</span>
                            <button type="button" class="fp-resend-btn" id="fpResendBtn" disabled>
                                Resend email
                            </button>
                            <span class="fp-countdown-wrap" id="fpCountdownWrap">
                                in <span class="fp-countdown-badge" id="fpCountdownBadge">1:00</span>
                            </span>
                        </div>

                        <div class="form-footer">
                            <p>
                                <a href="{{ route('login') }}" style="display:inline-flex;align-items:center;gap:0.35rem;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                                    Back to Login
                                </a>
                            </p>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        var COOLDOWN_SECONDS = 60;

        var formState    = document.getElementById('fpFormState');
        var successState = document.getElementById('fpSuccessState');
        var form         = document.getElementById('fpForm');
        var submitBtn    = document.getElementById('fpSubmitBtn');
        var emailInput   = document.getElementById('email');

        var resendBtn      = document.getElementById('fpResendBtn');
        var countdownWrap  = document.getElementById('fpCountdownWrap');
        var countdownBadge = document.getElementById('fpCountdownBadge');
        var timerBar       = document.getElementById('fpTimerBar');
        var successEmail   = document.getElementById('fpSuccessEmail');
        var resentFlash    = document.getElementById('fpResentFlash');

        var countdownInterval = null;
        var secondsLeft = COOLDOWN_SECONDS;

        /* ── Format mm:ss ── */
        function formatTime(s) {
            var m = Math.floor(s / 60);
            var sec = s % 60;
            return m + ':' + (sec < 10 ? '0' : '') + sec;
        }

        /* ── Start / restart the 1-minute countdown ── */
        function startCountdown() {
            secondsLeft = COOLDOWN_SECONDS;
            resendBtn.disabled = true;
            countdownWrap.style.display = '';
            countdownBadge.textContent = formatTime(secondsLeft);
            timerBar.style.width = '100%';
            timerBar.classList.remove('is-expiring');
            countdownBadge.classList.remove('is-expiring');

            if (countdownInterval) clearInterval(countdownInterval);

            countdownInterval = setInterval(function () {
                secondsLeft--;

                var pct = (secondsLeft / COOLDOWN_SECONDS) * 100;
                timerBar.style.width = pct + '%';
                countdownBadge.textContent = formatTime(secondsLeft);

                var expiring = secondsLeft <= 10;
                timerBar.classList.toggle('is-expiring', expiring);
                countdownBadge.classList.toggle('is-expiring', expiring);

                if (secondsLeft <= 0) {
                    clearInterval(countdownInterval);
                    countdownInterval = null;
                    resendBtn.disabled = false;
                    countdownWrap.style.display = 'none';
                    timerBar.style.width = '0%';
                }
            }, 1000);
        }

        /* ── Show success state ── */
        function showSuccess(email) {
            successEmail.textContent = email || 'your email address';
            formState.classList.add('is-hidden');
            successState.classList.add('is-visible');
            startCountdown();
        }

        /* ── Resend button ── */
        resendBtn.addEventListener('click', function () {
            /* Show flash */
            resentFlash.classList.add('is-visible');
            setTimeout(function () {
                resentFlash.classList.remove('is-visible');
            }, 3000);

            /* Restart timer */
            startCountdown();
        });

        /* ── Form submit (UI demo — intercept to show success state) ── */
        @if (session('status'))
            /* Server already confirmed — show success immediately */
            document.addEventListener('DOMContentLoaded', function () {
                showSuccess('{{ old('email') }}');
            });
        @else
            form.addEventListener('submit', function (e) {
                var email = emailInput.value.trim();
                if (!email) return; /* let native validation handle */

                /* Show loading overlay if present */
                var overlay = document.getElementById('signin-overlay');
                if (overlay) {
                    overlay.removeAttribute('hidden');
                    overlay.classList.add('is-visible');
                }

                /* For pure UI demo: prevent default and show success state */
                /* Remove the next two lines when backend is wired up */
                e.preventDefault();
                setTimeout(function () {
                    if (overlay) {
                        overlay.classList.remove('is-visible');
                        overlay.setAttribute('hidden', '');
                    }
                    showSuccess(email);
                }, 1200);
            });
        @endif

        /* ── Basic email validation for submit button ── */
        function toggleSubmit() {
            submitBtn.disabled = !emailInput.value.trim();
        }
        toggleSubmit();
        emailInput.addEventListener('input', toggleSubmit);

    })();
    </script>

</body>
</html>
