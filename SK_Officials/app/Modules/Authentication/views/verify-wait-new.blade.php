<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Email - SK Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <style>
        .verify-content {
            text-align: center;
        }

        .verify-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 2rem;
            background: linear-gradient(135deg, #f5c518 0%, #e6a800 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 40px rgba(245, 197, 24, 0.3);
            animation: pulse-icon 2s ease-in-out infinite;
        }

        .verify-icon svg {
            width: 40px;
            height: 40px;
            color: white;
        }

        @keyframes pulse-icon {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .countdown-text {
            font-size: 0.95rem;
            color: #64748b;
            margin: 1.5rem 0;
            font-weight: 500;
        }

        .email-highlight {
            color: #f5c518;
            font-weight: 700;
        }

        .resend-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }

        .resend-cooldown {
            font-size: 0.85rem;
            color: #dc3545;
            text-align: center;
            margin-top: 0.75rem;
            font-weight: 500;
        }

        .btn-resend {
            background: #f5c518 !important;
            color: #1a1a2e !important;
            font-weight: 700 !important;
            border: none !important;
            box-shadow: 0 8px 24px rgba(245, 197, 24, 0.3) !important;
        }

        .btn-resend:hover:not(:disabled) {
            background: #e6a800 !important;
            box-shadow: 0 12px 32px rgba(245, 197, 24, 0.4) !important;
        }

        .btn-resend:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .verification-state {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .verification-state.waiting {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(59, 130, 246, 0.12) 100%);
            color: #1e40af;
            border: 1.5px solid rgba(59, 130, 246, 0.2);
        }

        .verification-state.success {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.08) 0%, rgba(34, 197, 94, 0.12) 100%);
            color: #15803d;
            border: 1.5px solid rgba(34, 197, 94, 0.2);
        }

        .verification-state.warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(245, 158, 11, 0.12) 100%);
            color: #92400e;
            border: 1.5px solid rgba(245, 158, 11, 0.2);
        }

        .verification-state.error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.08) 0%, rgba(239, 68, 68, 0.12) 100%);
            color: #b91c1c;
            border: 1.5px solid rgba(239, 68, 68, 0.2);
        }

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

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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

        .success-modal h2 {
            color: #1a1a2e;
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

        @media (max-width: 640px) {
            .verify-icon {
                width: 70px;
                height: 70px;
                margin: 0 auto 1.5rem;
            }

            .verify-icon svg {
                width: 35px;
                height: 35px;
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
    </style>
</head>
<body class="sk-login-page">
    @include('loading')
    
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

        <!-- Right Side - Verification Card -->
        <div class="sk-login-section">
            <div class="sk-login-card">
                <div class="card-header">
                    <h2 class="card-title">Verify Your Email</h2>
                    <p class="card-subtitle">Complete verification to access your account</p>
                </div>

                <div class="verify-content">

                    <div class="verification-state waiting" id="verification-state">
                        Waiting for email verification...
                    </div>

                    <p class="countdown-text">
                        We sent a verification link to <span class="email-highlight">{{ $email }}</span>
                    </p>

                    <p class="countdown-text" id="countdown">
                        Expires in: <span id="countdown-timer">{{ sprintf('%02d:%02d', $waitMinutes, 0) }}</span>
                    </p>

                    <div class="resend-section">
                        <form method="POST" action="{{ route('sk_official.verification.resend', [], false) }}" id="resend-form">
                            @csrf
                            <input type="hidden" name="email" value="{{ $email }}">
                            <button type="submit" class="sk-submit-btn btn-resend" id="resend-btn">
                                Resend Verification Email
                            </button>
                        </form>
                        <div class="resend-cooldown" id="resend-cooldown" style="display: none;"></div>
                    </div>

                    <div class="form-footer" style="margin-top: 2rem;">
                        <a href="{{ route('login', [], false) }}" style="font-size: 0.95rem;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px; display: inline;">
                                <path d="M19 12H5M12 19l-7-7 7-7"/>
                            </svg>
                            Back to login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

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

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script>
        (() => {
            const statusUrl = "{{ route('sk_official.verification.wait.status', [], false) }}";
            const expiresAt = new Date("{{ $expiresAtIso }}");
            const stateElement = document.getElementById('verification-state');
            const countdownElement = document.getElementById('countdown-timer');
            const resendBtn = document.getElementById('resend-btn');
            const resendCooldownElement = document.getElementById('resend-cooldown');
            const resendForm = document.getElementById('resend-form');

            const COOLDOWN_KEY = 'sk_official_resend_cooldown_{{ $email }}';
            const COOLDOWN_DURATION = 600;

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
                    resendCooldownElement.textContent = `Please try again in ${remaining} seconds`;
                } else {
                    resendBtn.disabled = false;
                    resendCooldownElement.style.display = 'none';
                }
            }

            resendForm.addEventListener('submit', function(e) {
                const remaining = getRemainingCooldown();
                if (remaining > 0) {
                    e.preventDefault();
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
                countdownElement.textContent = `${formattedMinutes}:${formattedSeconds}`;
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
                        stateElement.className = 'verification-state success';
                        stateElement.textContent = 'Email verified successfully!';
                        clearResendCooldown();

                        const successModal = document.getElementById('success-modal');
                        successModal.classList.add('show');

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
                        stateElement.className = 'verification-state warning';
                        stateElement.textContent = 'Verification window expired. Please sign in again.';
                        clearResendCooldown();
                        return;
                    }

                    if (payload.state === 'missing') {
                        stateElement.className = 'verification-state warning';
                        stateElement.textContent = 'Verification session not found. Please sign in again.';
                        clearResendCooldown();
                        return;
                    }
                } catch (error) {
                    stateElement.className = 'verification-state error';
                    stateElement.textContent = 'Unable to check verification status. Retrying...';
                }

                renderCountdown();
                updateResendButton();
                setTimeout(checkStatus, 1000);
            }

            renderCountdown();
            updateResendButton();
            checkStatus();

            setInterval(updateResendButton, 1000);

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
