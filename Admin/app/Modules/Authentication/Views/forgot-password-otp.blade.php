<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OnePortal Admin — Verify Code</title>
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
            if (t === 'dark' || (!t && d)) document.documentElement.classList.add('dark');
        })();
    </script>
    <style>
        /* ── OTP page styles ── */
        .fp-otp-title {
            font-size: 1.4rem; font-weight: 800; color: #0f172a;
            text-align: center; margin-bottom: 1.25rem; letter-spacing: -.01em;
        }
        .fp-otp-body {
            font-size: .9rem; color: #64748b;
            text-align: center; line-height: 1.55; margin-bottom: .3rem;
        }
        .fp-otp-email-highlight {
            display: block; font-size: .95rem; font-weight: 700;
            color: #083080; text-align: center;
            margin-bottom: .5rem; word-break: break-all;
        }
        .fp-otp-hint {
            font-size: .78rem; color: #94a3b8;
            text-align: center; margin-bottom: 1.75rem;
        }

        /* 6 boxes */
        .fp-otp-inputs { display: flex; gap: .45rem; justify-content: center; margin-bottom: 1rem; }
        .fp-otp-box {
            width: 50px; height: 56px;
            border: 2px solid #e2e8f0; border-radius: 14px;
            background: #f8fafc;
            font-size: 1.55rem; font-weight: 700; color: #0f172a;
            text-align: center; caret-color: #083080; outline: none;
            transition: border-color .18s, background .18s, box-shadow .18s;
            -webkit-appearance: none; font-family: inherit;
        }
        .fp-otp-box:focus {
            border-color: #083080; background: rgba(8,48,128,.04);
            box-shadow: 0 0 0 3px rgba(8,48,128,.1);
        }
        .fp-otp-box.is-filled { border-color: #083080; background: rgba(8,48,128,.06); }
        .fp-otp-box.is-error  { border-color: #ef4444; background: rgba(239,68,68,.05); animation: otp-shake .35s ease; }
        @keyframes otp-shake { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-6px)} 60%{transform:translateX(6px)} }

        /* Error */
        .fp-otp-error {
            text-align: center; font-size: .82rem; font-weight: 600;
            color: #ef4444; margin-bottom: .75rem; min-height: 1.2rem;
        }

        /* Resend */
        .fp-otp-resend { text-align: center; margin-bottom: 1.5rem; }
        .fp-otp-resend-label { font-size: .875rem; color: #64748b; }
        .fp-otp-timer { font-weight: 700; color: #083080; }
        .fp-otp-resend-link {
            font-weight: 700; color: #083080; cursor: pointer;
            text-decoration: underline; text-underline-offset: 3px;
        }

        /* Buttons */
        .fp-otp-actions { display: flex; gap: .75rem; margin-bottom: 1.25rem; }
        .fp-otp-cancel-btn {
            flex: 1; padding: .875rem 1rem;
            background: transparent; border: 2px solid #e2e8f0; border-radius: 14px;
            color: #475569; font-size: .95rem; font-weight: 600; cursor: pointer;
            transition: border-color .2s, background .2s; font-family: inherit;
        }
        .fp-otp-cancel-btn:hover { border-color: #94a3b8; background: #f8fafc; }
        .fp-otp-verify-btn {
            flex: 1; padding: .875rem 1rem;
            background: linear-gradient(135deg, #083080 0%, #0d47a1 100%);
            border: none; border-radius: 14px;
            color: #fff; font-size: .95rem; font-weight: 700; cursor: pointer;
            transition: opacity .2s, transform .15s, box-shadow .2s; font-family: inherit;
            box-shadow: 0 4px 16px rgba(8,48,128,.25);
        }
        .fp-otp-verify-btn:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(8,48,128,.35); }
        .fp-otp-verify-btn:disabled { opacity: .4; cursor: not-allowed; transform: none; }
    </style>
</head>
<body class="login-page">

    {{-- Loading overlay --}}
    <div id="signin-overlay" class="signin-overlay" aria-hidden="true" hidden>
        <div class="signin-overlay-inner">
            <div class="signin-spinner">
                <div class="signin-spinner-ring"></div>
                <div class="signin-spinner-ring signin-spinner-ring--2"></div>
                <div class="signin-spinner-dot"></div>
            </div>
            <p class="signin-overlay-title">Verifying Code</p>
            <p class="signin-overlay-sub">Please wait...</p>
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

            <div class="login-form-container">
                <div class="login-card-inner">

                    <h2 class="fp-otp-title">Check Your Email</h2>
                    <p class="fp-otp-body">Please enter the code we just sent to</p>
                    <span class="fp-otp-email-highlight">{{ $email }}</span>
                    <p class="fp-otp-hint">Check your Junk folder if the email isn't in your Inbox.</p>

                    {{-- Server-side error --}}
                    @if ($errors->has('code'))
                        <p class="fp-otp-error" style="display:block;">{{ $errors->first('code') }}</p>
                    @else
                        <p class="fp-otp-error" id="fpOtpError"></p>
                    @endif

                    {{-- Hidden form that submits the code --}}
                    <form method="POST" action="{{ route('password.verify-otp') }}" id="fpOtpForm" novalidate>
                        @csrf
                        <input type="hidden" name="code" id="fpCodeHidden">

                        {{-- 6 digit boxes --}}
                        <div class="fp-otp-inputs">
                            <input type="text" maxlength="1" class="fp-otp-box" data-idx="0" inputmode="numeric" autocomplete="off" aria-label="Digit 1">
                            <input type="text" maxlength="1" class="fp-otp-box" data-idx="1" inputmode="numeric" autocomplete="off" aria-label="Digit 2">
                            <input type="text" maxlength="1" class="fp-otp-box" data-idx="2" inputmode="numeric" autocomplete="off" aria-label="Digit 3">
                            <input type="text" maxlength="1" class="fp-otp-box" data-idx="3" inputmode="numeric" autocomplete="off" aria-label="Digit 4">
                            <input type="text" maxlength="1" class="fp-otp-box" data-idx="4" inputmode="numeric" autocomplete="off" aria-label="Digit 5">
                            <input type="text" maxlength="1" class="fp-otp-box" data-idx="5" inputmode="numeric" autocomplete="off" aria-label="Digit 6">
                        </div>

                        {{-- Resend countdown --}}
                        <div class="fp-otp-resend">
                            <span class="fp-otp-resend-label" id="fpResendLabel">
                                Resend code in <span class="fp-otp-timer" id="fpOtpTimer">29s</span>
                            </span>
                        </div>

                        {{-- Action buttons --}}
                        <div class="fp-otp-actions">
                            <a href="{{ route('password.request') }}" class="fp-otp-cancel-btn" style="display:flex;align-items:center;justify-content:center;text-decoration:none;">
                                Cancel
                            </a>
                            <button type="submit" class="fp-otp-verify-btn" id="fpVerifyBtn" disabled>
                                Verify Code
                            </button>
                        </div>

                    </form>

                    <div class="form-footer" style="text-align:center;">
                        <p>
                            <a href="{{ route('login') }}" style="display:inline-flex;align-items:center;gap:.35rem;font-size:.875rem;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                                Back to Login
                            </a>
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        var COOLDOWN   = 29;
        var boxes      = document.querySelectorAll('.fp-otp-box');
        var hidden     = document.getElementById('fpCodeHidden');
        var verifyBtn  = document.getElementById('fpVerifyBtn');
        var form       = document.getElementById('fpOtpForm');
        var errorEl    = document.getElementById('fpOtpError');
        var resendLbl  = document.getElementById('fpResendLabel');
        var timerEl    = document.getElementById('fpOtpTimer');
        var overlay    = document.getElementById('signin-overlay');
        var secondsLeft = COOLDOWN;
        var countdownId = null;

        /* ── Box navigation ── */
        boxes.forEach(function (box, idx) {
            box.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace') {
                    if (!box.value && idx > 0) {
                        boxes[idx - 1].value = '';
                        boxes[idx - 1].classList.remove('is-filled');
                        boxes[idx - 1].focus();
                    }
                    syncAndCheck();
                    return;
                }
                if (e.key === 'Enter') { if (!verifyBtn.disabled) form.requestSubmit ? form.requestSubmit() : form.submit(); return; }
                if (e.key === 'ArrowLeft'  && idx > 0)                  { boxes[idx - 1].focus(); return; }
                if (e.key === 'ArrowRight' && idx < boxes.length - 1)   { boxes[idx + 1].focus(); return; }
                if (!/^\d$/.test(e.key) && !['Tab','Delete'].includes(e.key)) { e.preventDefault(); return; }
                box.value = '';
            });

            box.addEventListener('input', function () {
                var v = box.value.replace(/\D/g, '');
                box.value = v ? v[0] : '';
                box.classList.toggle('is-filled', !!box.value);
                /* Last box: don't auto-advance, just lock the value */
                if (box.value && idx < boxes.length - 1) boxes[idx + 1].focus();
                syncAndCheck();
            });

            box.addEventListener('paste', function (e) {
                e.preventDefault();
                var digits = (e.clipboardData || window.clipboardData)
                    .getData('text').replace(/\D/g, '').slice(0, boxes.length);
                digits.split('').forEach(function (d, i) {
                    if (boxes[i]) { boxes[i].value = d; boxes[i].classList.add('is-filled'); }
                });
                var last = Math.min(digits.length, boxes.length) - 1;
                if (last >= 0) boxes[last].focus();
                syncAndCheck();
            });
        });

        function syncAndCheck() {
            var code = Array.from(boxes).map(function (b) { return b.value; }).join('');
            if (hidden) hidden.value = code;
            verifyBtn.disabled = code.length < boxes.length;
        }

        /* ── Form submit — show overlay ── */
        form.addEventListener('submit', function () {
            if (overlay) { overlay.removeAttribute('hidden'); overlay.classList.add('is-visible'); }
        });

        /* ── Countdown ── */
        function startCountdown() {
            secondsLeft = COOLDOWN;
            if (timerEl) timerEl.textContent = secondsLeft + 's';
            if (countdownId) clearInterval(countdownId);
            countdownId = setInterval(function () {
                secondsLeft--;
                if (timerEl) timerEl.textContent = secondsLeft + 's';
                if (secondsLeft <= 0) {
                    clearInterval(countdownId);
                    if (resendLbl) {
                        resendLbl.innerHTML = '<span class="fp-otp-resend-link" id="fpResendLink">Resend code</span>';
                        var link = document.getElementById('fpResendLink');
                        if (link) link.addEventListener('click', function () {
                            resendLbl.innerHTML = 'Resend code in <span class="fp-otp-timer" id="fpOtpTimer">29s</span>';
                            timerEl = document.getElementById('fpOtpTimer');
                            startCountdown();
                        });
                    }
                }
            }, 1000);
        }

        startCountdown();

        /* Focus first box on load */
        if (boxes.length) boxes[0].focus();

    })();
    </script>

</body>
</html>
