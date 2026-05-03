<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Session Verification - SK Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <style>
        .takeover-content {
            text-align: center;
        }

        .takeover-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 2rem;
            background: linear-gradient(135deg, #f5c518 0%, #e6a800 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 40px rgba(245, 197, 24, 0.3);
        }

        .takeover-icon svg {
            width: 40px;
            height: 40px;
            color: white;
        }

        .takeover-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #1a1a2e;
            margin-bottom: 1rem;
        }

        .takeover-message {
            font-size: 0.95rem;
            color: #64748b;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .takeover-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1a1a2e;
        }

        /* ── OTP 6-box input ── */
        .otp-boxes {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 0.25rem 0;
        }

        .otp-box {
            width: 48px;
            height: 56px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            color: #1a1a2e;
            background: #f8fafc;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            caret-color: #f5c518;
            outline: none;
        }

        .otp-box:focus {
            border-color: #f5c518;
            box-shadow: 0 0 0 4px rgba(245, 197, 24, 0.15);
            background: #fff;
        }

        .otp-box.filled {
            border-color: #1a1a2e;
            background: #fff;
        }

        /* ── Send button with timer ── */
        .send-btn-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .send-timer-text {
            font-size: 0.82rem;
            color: #94a3b8;
            font-weight: 500;
            min-height: 1.2em;
        }

        .send-timer-text.active {
            color: #f5c518;
            font-weight: 700;
        }

        /* ── Verify button loading state ── */
        .btn-submit {
            background: #1a1a2e !important;
            color: white !important;
            font-weight: 700 !important;
            border: none !important;
            box-shadow: 0 8px 24px rgba(26, 26, 46, 0.3) !important;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit:hover:not(:disabled) {
            background: #2a2a3e !important;
            box-shadow: 0 12px 32px rgba(26, 26, 46, 0.4) !important;
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Spinner */
        .btn-spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2.5px solid rgba(255,255,255,0.35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            flex-shrink: 0;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .btn-submit.loading .btn-spinner { display: inline-block; }
        .btn-submit.loading .btn-label  { opacity: 0.75; }
    </style>
</head>
<body class="sk-login-page">
    <!-- Animated Background -->
    <div class="sk-bg-wrapper">
        <div class="sk-bg-image"></div>
        <div class="sk-gradient-overlay"></div>
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>

    <main class="sk-login-container">
        <!-- Left Side - Logo & Branding -->
        <div class="sk-branding-section">
            <div class="branding-content">
                <div class="logo-wrapper">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="SK Officials Logo"
                        class="sk-logo"
                    >
                </div>
                <h1 class="sk-main-title">SK OnePortal</h1>
                <p class="sk-tagline">SK Officials Portal – Santa Cruz, Laguna</p>
            </div>
        </div>

        <!-- Right Side - Takeover Card -->
        <div class="sk-login-section">
            <div class="sk-login-card">
                <div class="takeover-content">
                    <div class="takeover-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm6-10V7a3 3 0 00-6 0v4h6z"/>
                        </svg>
                    </div>

                    <h2 class="takeover-title">Session Verification Required</h2>
                    <p class="takeover-message">
                        Send a verification code to <strong>{{ $email }}</strong> to continue on this device.
                    </p>

                    <!-- Send Code form with 1-minute countdown -->
                    <form method="POST" action="{{ route('sk_official.takeover.send') }}" class="takeover-form" id="sendCodeForm">
                        @csrf
                        <div class="send-btn-wrap">
                            <button type="submit" class="sk-submit-btn btn-submit" id="sendCodeBtn" disabled>
                                <span class="btn-spinner"></span>
                                <span class="btn-label">Send Verification Code</span>
                            </button>
                            <span class="send-timer-text active" id="sendTimerText">Available in <span id="sendCountdown">1:00</span></span>
                        </div>
                    </form>

                    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e2e8f0;">
                        <form method="POST" action="{{ route('sk_official.takeover.verify') }}" class="takeover-form" id="verifyCodeForm">
                            @csrf
                            <!-- Hidden input that collects the 6-digit code -->
                            <input type="hidden" id="otp_code" name="otp_code">

                            <div class="form-group">
                                <label>Enter Verification Code</label>
                                <div class="otp-boxes" id="otpBoxes">
                                    <input class="otp-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" autocomplete="off" data-idx="0">
                                    <input class="otp-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" autocomplete="off" data-idx="1">
                                    <input class="otp-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" autocomplete="off" data-idx="2">
                                    <input class="otp-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" autocomplete="off" data-idx="3">
                                    <input class="otp-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" autocomplete="off" data-idx="4">
                                    <input class="otp-box" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" autocomplete="off" data-idx="5">
                                </div>
                            </div>

                            <button type="submit" class="sk-submit-btn btn-submit" id="verifyCodeBtn">
                                <span class="btn-spinner"></span>
                                <span class="btn-label">Verify Code</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script>
    (() => {
        // ── 1-minute countdown before Send Verification Code is enabled ──────
        const sendBtn       = document.getElementById('sendCodeBtn');
        const sendTimerText = document.getElementById('sendTimerText');
        const sendCountdown = document.getElementById('sendCountdown');
        const SEND_DELAY    = 60; // seconds

        let remaining = SEND_DELAY;

        function updateSendTimer() {
            const m = Math.floor(remaining / 60);
            const s = remaining % 60;
            sendCountdown.textContent = `${m}:${String(s).padStart(2, '0')}`;

            if (remaining <= 0) {
                sendBtn.disabled = false;
                sendTimerText.textContent = '';
                sendTimerText.classList.remove('active');
            } else {
                remaining--;
                setTimeout(updateSendTimer, 1000);
            }
        }

        updateSendTimer();

        // Show loading spinner when Send Code is clicked
        document.getElementById('sendCodeForm').addEventListener('submit', function() {
            sendBtn.classList.add('loading');
            sendBtn.disabled = true;
        });

        // ── OTP 6-box behaviour ───────────────────────────────────────────────
        const boxes      = Array.from(document.querySelectorAll('.otp-box'));
        const hiddenCode = document.getElementById('otp_code');

        boxes.forEach((box, idx) => {
            box.addEventListener('input', (e) => {
                // Allow only digits
                box.value = box.value.replace(/\D/g, '').slice(-1);
                box.classList.toggle('filled', box.value !== '');

                // Advance to next box
                if (box.value && idx < boxes.length - 1) {
                    boxes[idx + 1].focus();
                }

                syncHidden();
            });

            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace') {
                    if (!box.value && idx > 0) {
                        boxes[idx - 1].value = '';
                        boxes[idx - 1].classList.remove('filled');
                        boxes[idx - 1].focus();
                    }
                    syncHidden();
                }
                // Allow paste via Ctrl+V / Cmd+V
            });

            box.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData)
                    .getData('text').replace(/\D/g, '').slice(0, 6);
                pasted.split('').forEach((ch, i) => {
                    if (boxes[i]) {
                        boxes[i].value = ch;
                        boxes[i].classList.add('filled');
                    }
                });
                const nextEmpty = boxes.findIndex(b => !b.value);
                (nextEmpty >= 0 ? boxes[nextEmpty] : boxes[5]).focus();
                syncHidden();
            });
        });

        function syncHidden() {
            hiddenCode.value = boxes.map(b => b.value).join('');
        }

        // ── Verify Code — loading spinner ─────────────────────────────────────
        const verifyBtn  = document.getElementById('verifyCodeBtn');
        const verifyForm = document.getElementById('verifyCodeForm');

        verifyForm.addEventListener('submit', function(e) {
            syncHidden();

            // Basic client-side validation: all 6 digits must be filled
            if (hiddenCode.value.length < 6) {
                e.preventDefault();
                boxes[hiddenCode.value.length]?.focus();
                return;
            }

            verifyBtn.classList.add('loading');
            verifyBtn.disabled = true;
        });
    })();
    </script>
    @vite(['app/Modules/Authentication/assets/js/loader.js'])
</body>
</html>
