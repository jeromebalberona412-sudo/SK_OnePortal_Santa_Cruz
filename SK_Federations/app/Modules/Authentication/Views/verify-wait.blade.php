<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Waiting for Email Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ url('/modules/authentication/css/style.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <style>
        .countdown-text {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 24px;
            text-align: center;
        }

        .email-highlight {
            color: #213F99;
            font-weight: 600;
        }

        .resend-cooldown {
            font-size: 13px;
            color: #dc3545;
            text-align: center;
            margin-top: 8px;
            margin-bottom: 16px;
        }

        .btn-resend:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Success Modal Styles */
        .success-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }

        .success-modal-overlay.show {
            display: flex;
        }

        .success-modal {
            background: white;
            border-radius: 16px;
            padding: 40px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease;
        }

        .check-wrap {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            display: grid;
            place-items: center;
            margin: 0 auto 32px;
            box-shadow: 0 10px 40px rgba(34, 197, 94, 0.3);
            animation: scaleIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
        }

        .check-wrap::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            opacity: 0.3;
            animation: pulse-ring 1.5s ease-out infinite;
        }

        .checkmark {
            width: 35px;
            height: 70px;
            border-right: 8px solid white;
            border-bottom: 8px solid white;
            transform: rotate(45deg) scale(0);
            animation: draw-check 0.6s 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
            transform-origin: center center;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            margin-left: 8px;
            margin-bottom: 8px;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0) rotate(-180deg);
                opacity: 0;
            }
            50% {
                transform: scale(1.1) rotate(10deg);
            }
            100% {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
        }

        @keyframes draw-check {
            0% {
                transform: rotate(45deg) scale(0);
                opacity: 0;
            }
            50% {
                transform: rotate(45deg) scale(1.1);
                opacity: 1;
            }
            100% {
                transform: rotate(45deg) scale(1);
                opacity: 1;
            }
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(1);
                opacity: 0.3;
            }
            50% {
                transform: scale(1.15);
                opacity: 0.1;
            }
            100% {
                transform: scale(1.3);
                opacity: 0;
            }
        }

        .success-modal h2 {
            color: #213F99;
            margin-bottom: 12px;
            font-size: 28px;
            font-weight: 700;
            animation: fadeInText 0.6s 0.4s ease-in backwards;
        }

        .success-modal p {
            color: #64748b;
            margin-bottom: 0;
            font-size: 16px;
            animation: fadeInText 0.6s 0.5s ease-in backwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInText {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        .success-modal-overlay.fade-out {
            animation: fadeOut 0.3s ease forwards;
        }

        /* Responsive Design */
        @media (max-width: 640px) {
            .countdown-text {
                font-size: 13px;
            }

            .email-highlight {
                display: inline-block;
                word-break: break-word;
            }

            .resend-cooldown {
                font-size: 12px;
            }

            .success-modal {
                padding: 30px 20px;
                width: 95%;
            }

            .check-wrap {
                width: 100px;
                height: 100px;
                margin-bottom: 24px;
            }

            .checkmark {
                width: 30px;
                height: 60px;
                border-right: 6px solid white;
                border-bottom: 6px solid white;
                margin-left: 6px;
                margin-bottom: 6px;
            }

            .success-modal h2 {
                font-size: 22px;
            }

            .success-modal p {
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .countdown-text {
                font-size: 12px;
            }

            .resend-cooldown {
                font-size: 11px;
            }

            .check-wrap {
                width: 90px;
                height: 90px;
                margin-bottom: 20px;
            }

            .checkmark {
                width: 25px;
                height: 50px;
                border-right: 5px solid white;
                border-bottom: 5px solid white;
                margin-left: 5px;
                margin-bottom: 5px;
            }

            .success-modal h2 {
                font-size: 20px;
            }

            .success-modal p {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="background-section">
            <div class="logo-container">
                <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Federations Logo" class="large-logo">
                <h1 class="brand-title">SK Federations</h1>
                <p class="brand-subtitle">Santa Cruz Youth Leadership Portal</p>
            </div>

            <div class="login-form-container">
                <div class="form-header">
                    <h2>Verify Your Email to Continue</h2>
                    <p>We sent a verification email to <span class="email-highlight">{{ $email }}</span>. You can verify on any device. This page will continue automatically when verification is complete.</p>
                </div>

                <div class="alert alert-info" role="alert" id="verification-state">
                    Waiting for verification...
                </div>
                <div id="inline-error-container"></div>

                <p class="countdown-text" id="countdown">
                    Email verification expires in: 10:00
                </p>

                <form method="POST" action="{{ route('skfed.verification.resend', [], false) }}" class="login-form" id="resend-form">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button type="submit" class="login-btn btn btn-primary w-100 mb-3 btn-resend" id="resend-btn">
                        Resend Verification Email
                    </button>
                </form>
                <div class="resend-cooldown" id="resend-cooldown" style="display: none;"></div>

                <div class="form-footer">
                    <a href="{{ route('login', [], false) }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        Back to login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="success-modal-overlay" id="success-modal">
        <div class="success-modal">
            <div class="check-wrap" aria-hidden="true">
                <span class="checkmark"></span>
            </div>
            <h2>Verified Successfully!</h2>
            <p>Redirecting to dashboard...</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script>
        (() => {
            const statusUrl = "{{ route('skfed.verification.wait.status', [], false) }}";
            const expiresAt = new Date("{{ $expiresAtIso }}");
            const stateElement = document.getElementById('verification-state');
            const countdownElement = document.getElementById('countdown');
            const resendBtn = document.getElementById('resend-btn');
            const resendCooldownElement = document.getElementById('resend-cooldown');
            const resendForm = document.getElementById('resend-form');
            const inlineErrorContainer = document.getElementById('inline-error-container');

            // Resend cooldown management using localStorage
            const COOLDOWN_KEY = 'sk_fed_resend_cooldown_{{ $email }}';
            const COOLDOWN_DURATION = 600; // 600 seconds = 10 minutes cooldown

            function getResendCooldownExpiry() {
                const stored = localStorage.getItem(COOLDOWN_KEY);
                return stored ? parseInt(stored, 10) : 0;
            }

            function setResendCooldownExpiry() {
                const expiryTime = Date.now() + (COOLDOWN_DURATION * 1000);
                localStorage.setItem(COOLDOWN_KEY, expiryTime.toString());
            }

            function clearResendCooldown() {
                localStorage.removeItem(COOLDOWN_KEY);
            }

            function getRemainingCooldown() {
                const expiry = getResendCooldownExpiry();
                if (expiry === 0) return 0;
                const remaining = Math.max(0, Math.ceil((expiry - Date.now()) / 1000));
                if (remaining === 0) {
                    clearResendCooldown();
                }
                return remaining;
            }

            function updateResendButton() {
                const remaining = getRemainingCooldown();
                if (remaining > 0) {
                    resendBtn.disabled = true;
                    resendCooldownElement.style.display = 'block';
                    resendCooldownElement.textContent = `You already requested a verification email. Please try again later.`;
                } else {
                    resendBtn.disabled = false;
                    resendCooldownElement.style.display = 'none';
                }
            }

            function showInlineError(message) {
                inlineErrorContainer.innerHTML = `<div class="invalid-feedback d-block" style="text-align: center; margin-bottom: 16px;">${message}</div>`;
                setTimeout(() => {
                    const errorEl = inlineErrorContainer.querySelector('.invalid-feedback');
                    if (errorEl) {
                        errorEl.classList.add('fade-out');
                        setTimeout(() => {
                            inlineErrorContainer.innerHTML = '';
                        }, 300);
                    }
                }, 5000);
            }

            // Handle form submission
            resendForm.addEventListener('submit', function(e) {
                const remaining = getRemainingCooldown();
                if (remaining > 0) {
                    e.preventDefault();
                    showInlineError(`You already requested a verification email. Please try again in 10 minutes.`);
                    return false;
                }
                setResendCooldownExpiry();
            });

            function renderCountdown() {
                const seconds = Math.max(0, Math.floor((expiresAt.getTime() - Date.now()) / 1000));
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = seconds % 60;
                const formattedMinutes = String(minutes).padStart(2, '0');
                const formattedSeconds = String(remainingSeconds).padStart(2, '0');
                countdownElement.textContent = `Email verification expires in: ${formattedMinutes}:${formattedSeconds}`;
            }

            async function checkStatus() {
                try {
                    const response = await fetch(statusUrl, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });

                    const payload = await response.json();

                    if (payload.state === 'verified' && payload.redirect) {
                        stateElement.className = 'alert alert-success';
                        stateElement.textContent = 'Email verified. Redirecting...';
                        clearResendCooldown();
                        
                        // Show success modal
                        const successModal = document.getElementById('success-modal');
                        successModal.classList.add('show');
                        
                        // Wait 3 seconds, then show loading screen and redirect
                        setTimeout(() => {
                            successModal.classList.add('fade-out');
                            setTimeout(() => {
                                LoadingScreen.show('Redirecting', 'Taking you to dashboard...');
                                setTimeout(() => {
                                    window.location.replace(payload.redirect);
                                }, 500);
                            }, 300);
                        }, 3000);
                        return;
                    }

                    if (payload.state === 'expired') {
                        stateElement.className = 'alert alert-warning';
                        stateElement.textContent = 'Verification window expired. Please sign in again.';
                        clearResendCooldown();
                        return;
                    }

                    if (payload.state === 'missing') {
                        stateElement.className = 'alert alert-warning';
                        stateElement.textContent = 'Verification session not found. Please sign in again.';
                        clearResendCooldown();
                        return;
                    }
                } catch (error) {
                    stateElement.className = 'alert alert-danger';
                    stateElement.textContent = 'Unable to check verification status. Retrying...';
                }

                renderCountdown();
                updateResendButton();
                setTimeout(checkStatus, 1000);
            }

            renderCountdown();
            updateResendButton();
            checkStatus();

            // Update resend button every second
            setInterval(updateResendButton, 1000);

            // Show loading on back to login
            document.querySelector('.form-footer a').addEventListener('click', function(e) {
                e.preventDefault();
                LoadingScreen.show('Redirecting', 'Taking you to login...');
                setTimeout(() => {
                    window.location.href = this.href;
                }, 300);
            });
        })();
    </script>
</body>
</html>
