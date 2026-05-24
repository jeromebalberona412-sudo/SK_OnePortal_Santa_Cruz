<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OnePortal Admin — Set New Password</title>
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
        .fp-pw-title {
            font-size: 1.4rem; font-weight: 800; color: #0f172a;
            text-align: center; margin-bottom: .5rem; letter-spacing: -.01em;
        }
        .fp-pw-subtitle {
            font-size: .9rem; color: #64748b;
            text-align: center; margin-bottom: 1.75rem;
        }
        .fp-pw-group { margin-bottom: 1.25rem; }
        .fp-pw-label {
            display: block; font-size: .82rem; font-weight: 600;
            color: #475569; margin-bottom: .45rem;
        }
        .fp-pw-wrap { position: relative; }
        .fp-pw-input {
            width: 100%; padding: .8rem 2.75rem .8rem 1rem;
            background: #f8fafc; border: 2px solid #e2e8f0;
            border-radius: 12px; color: #0f172a; font-size: .95rem; font-family: inherit;
            outline: none; transition: border-color .18s, background .18s, box-shadow .18s;
            box-sizing: border-box;
        }
        .fp-pw-input::placeholder { color: #94a3b8; }
        .fp-pw-input:focus {
            border-color: #083080; background: rgba(8,48,128,.03);
            box-shadow: 0 0 0 3px rgba(8,48,128,.08);
        }
        .fp-pw-toggle {
            position: absolute; right: .75rem; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: #94a3b8;
            cursor: pointer; padding: 0; display: flex; align-items: center;
        }
        .fp-pw-toggle:hover { color: #475569; }
        .fp-pw-toggle svg { width: 18px; height: 18px; }
        .fp-pw-strength { margin-top: .5rem; min-height: 1.2rem; }
        .fp-pw-match { font-size: .8rem; font-weight: 600; margin-top: .4rem; min-height: 1.1rem; }
        .fp-pw-save-btn {
            width: 100%; padding: .9rem;
            background: linear-gradient(135deg, #083080 0%, #0d47a1 100%);
            border: none; border-radius: 14px;
            color: #fff; font-size: 1rem; font-weight: 700; cursor: pointer;
            transition: opacity .2s, transform .15s, box-shadow .2s; font-family: inherit;
            box-shadow: 0 4px 16px rgba(8,48,128,.25); margin-top: .5rem;
        }
        .fp-pw-save-btn:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(8,48,128,.35); }
        .fp-pw-save-btn:disabled { opacity: .4; cursor: not-allowed; transform: none; }
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
            <p class="signin-overlay-title">Saving Password</p>
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

                    <h2 class="fp-pw-title">Set New Password</h2>
                    <p class="fp-pw-subtitle">Create a strong password for your account.</p>

                    @if ($errors->any())
                        <div class="login-alert login-alert--danger" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('password.set-new') }}" novalidate id="fpPwForm">
                        @csrf

                        <div class="fp-pw-group">
                            <label class="fp-pw-label" for="fpNewPassword">New Password</label>
                            <div class="fp-pw-wrap">
                                <input type="password" id="fpNewPassword" name="password" class="fp-pw-input"
                                    placeholder="Enter new password" autocomplete="new-password" minlength="8" required>
                                <button type="button" class="fp-pw-toggle" id="fpPwToggle1" aria-label="Toggle password visibility">
                                    <svg class="pw-eye-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="pw-eye-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                            <div class="fp-pw-strength" id="fpPwStrength"></div>
                        </div>

                        <div class="fp-pw-group">
                            <label class="fp-pw-label" for="fpConfirmPassword">Confirm Password</label>
                            <div class="fp-pw-wrap">
                                <input type="password" id="fpConfirmPassword" name="password_confirmation" class="fp-pw-input"
                                    placeholder="Re-enter new password" autocomplete="new-password" required>
                                <button type="button" class="fp-pw-toggle" id="fpPwToggle2" aria-label="Toggle password visibility">
                                    <svg class="pw-eye-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="pw-eye-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                            <p class="fp-pw-match" id="fpPwMatch"></p>
                        </div>

                        <button type="submit" class="fp-pw-save-btn" id="fpSaveBtn" disabled>
                            Save New Password
                        </button>

                    </form>

                    <div class="form-footer" style="text-align:center;margin-top:1.25rem;">
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
        var pwInput   = document.getElementById('fpNewPassword');
        var pwConfirm = document.getElementById('fpConfirmPassword');
        var saveBtn   = document.getElementById('fpSaveBtn');
        var matchMsg  = document.getElementById('fpPwMatch');
        var strengthEl = document.getElementById('fpPwStrength');
        var form      = document.getElementById('fpPwForm');
        var overlay   = document.getElementById('signin-overlay');

        /* ── Strength ── */
        function getStrength(pw) {
            var s = 0;
            if (pw.length >= 8)  s++;
            if (pw.length >= 12) s++;
            if (/[A-Z]/.test(pw)) s++;
            if (/[0-9]/.test(pw)) s++;
            if (/[^A-Za-z0-9]/.test(pw)) s++;
            return s;
        }
        function renderStrength(score) {
            var labels = ['','Very Weak','Weak','Fair','Strong','Very Strong'];
            var colors = ['','#ef4444','#f97316','#eab308','#22c55e','#16a34a'];
            var pct = (score / 5) * 100;
            strengthEl.innerHTML =
                '<div style="height:4px;border-radius:99px;background:#e2e8f0;overflow:hidden;margin-bottom:.3rem;">' +
                '<div style="height:100%;width:' + pct + '%;background:' + (colors[score]||'#083080') + ';border-radius:99px;transition:width .3s,background .3s;"></div></div>' +
                (score > 0 ? '<span style="font-size:.75rem;color:' + colors[score] + ';font-weight:600;">' + labels[score] + '</span>' : '');
        }

        /* ── Match ── */
        function checkMatch() {
            var pw = pwInput.value, cpw = pwConfirm.value;
            if (!cpw) { matchMsg.textContent = ''; return false; }
            if (pw === cpw) { matchMsg.textContent = '✓ Passwords match'; matchMsg.style.color = '#16a34a'; return true; }
            matchMsg.textContent = '✗ Passwords do not match'; matchMsg.style.color = '#dc2626'; return false;
        }

        function checkReady() {
            saveBtn.disabled = !(pwInput.value.length >= 8 && pwInput.value === pwConfirm.value);
        }

        pwInput.addEventListener('input', function () { renderStrength(getStrength(pwInput.value)); checkMatch(); checkReady(); });
        pwConfirm.addEventListener('input', function () { checkMatch(); checkReady(); });

        /* ── Toggle visibility ── */
        function bindToggle(btn, input) {
            if (!btn || !input) return;
            btn.addEventListener('click', function () {
                var hide = input.type === 'password';
                input.type = hide ? 'text' : 'password';
                btn.querySelector('.pw-eye-show').style.display = hide ? 'none' : '';
                btn.querySelector('.pw-eye-hide').style.display = hide ? '' : 'none';
            });
        }
        bindToggle(document.getElementById('fpPwToggle1'), pwInput);
        bindToggle(document.getElementById('fpPwToggle2'), pwConfirm);

        /* ── Submit overlay ── */
        form.addEventListener('submit', function () {
            if (overlay) { overlay.removeAttribute('hidden'); overlay.classList.add('is-visible'); }
        });

    })();
    </script>

</body>
</html>
