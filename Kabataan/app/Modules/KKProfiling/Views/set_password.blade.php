<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Set Password - KK Profiling - SK OnePortal</title>
    @vite([
        'app/Modules/Authentication/assets/css/youth-login.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <style>
        .youth-login-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            background: linear-gradient(135deg, #022a54, #0450a8 55%, #1a6fd4);
            overflow: hidden;
        }

        .youth-login-container {
            position: relative;
            z-index: 10;
            flex: 1;
            display: flex;
            min-height: 100vh;
            max-width: 1600px;
            margin: 0 auto;
            width: 100%;
            overflow: hidden;
            animation: pageFadeIn 0.6s ease forwards;
        }

        @keyframes pageFadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        .youth-branding-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 6rem;
        }

        .branding-content {
            max-width: 600px;
            text-align: center;
        }

        .logo-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 3rem;
            position: relative;
        }

        .logo-wrapper::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(68, 165, 62, 0.15) 0%, rgba(253, 192, 32, 0.10) 50%, transparent 70%);
            border-radius: 50%;
        }

        .youth-logo {
            width: 240px;
            height: 240px;
            object-fit: cover;
            border-radius: 50%;
            filter: drop-shadow(0 16px 32px rgba(0, 0, 0, 0.4));
            position: relative;
            z-index: 1;
            border: 4px solid rgba(253, 192, 32, 0.6);
        }

        .youth-main-title {
            font-size: 5rem;
            font-weight: 900;
            color: #ffffff;
            margin-bottom: 1.5rem;
            letter-spacing: -0.04em;
            line-height: 1;
            text-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
        }

        .youth-tagline {
            font-size: 1.5rem;
            color: #fdc020;
            font-weight: 600;
            line-height: 1.6;
            letter-spacing: 0.01em;
            text-shadow: 0 2px 12px rgba(0, 0, 0, 0.3);
        }

        .youth-login-section {
            width: 580px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            margin-left: auto;
            margin-right: 8rem;
            overflow: auto;
            max-height: 100vh;
        }

        .youth-login-card {
            width: 100%;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(40px);
            border-radius: 28px;
            padding: 2.5rem 3rem;
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .youth-login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #44a53e 0%, #fdc020 50%, #0450a8 100%);
        }

        .card-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .card-title {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #0450a8 0%, #0d5fc4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.625rem;
            letter-spacing: -0.03em;
            line-height: 1.2;
        }

        .card-subtitle {
            font-size: 0.95rem;
            color: #666;
            font-weight: 500;
            letter-spacing: 0.01em;
        }

        .youth-form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }

        .youth-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            color: #334155;
            letter-spacing: 0.01em;
        }

        .password-wrapper {
            position: relative;
        }

        .youth-input {
            width: 100%;
            padding: 0.875rem 1.125rem;
            font-size: 0.95rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: white;
            color: #0f172a;
            transition: border-color 0.3s;
            font-family: inherit;
            font-weight: 500;
            box-sizing: border-box;
        }

        .youth-input:focus {
            outline: none;
            border-color: #44a53e;
            box-shadow: 0 0 0 4px rgba(68, 165, 62, 0.08);
        }

        .youth-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .youth-input.error {
            border-color: #ef4444;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            color: #94a3b8;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            z-index: 10;
        }

        .toggle-password:hover {
            color: #44a53e;
        }

        .toggle-password svg {
            width: 20px;
            height: 20px;
        }

        .hint {
            font-size: 0.875rem;
            color: #64748b;
            margin-top: 0.375rem;
        }

        .error {
            font-size: 0.875rem;
            color: #ef4444;
            margin-top: 0.375rem;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
            padding: 0.875rem 1.125rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            padding: 0.875rem 1.125rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .youth-submit-btn {
            width: 100%;
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 700;
            color: white;
            background: linear-gradient(135deg, #44a53e 0%, #5cb854 100%);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: box-shadow 0.3s;
            box-shadow: 0 8px 24px rgba(68, 165, 62, 0.25);
            margin-top: 0.5rem;
            letter-spacing: 0.02em;
        }

        .youth-submit-btn:hover {
            box-shadow: 0 12px 32px rgba(68, 165, 62, 0.35);
        }

        .youth-submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        @media (max-width: 1024px) {
            .youth-login-container {
                flex-direction: column;
                padding: 0;
                overflow-y: auto;
                height: auto;
            }
            .youth-branding-section {
                padding: 4rem 3rem 3rem;
                text-align: center;
            }
            .youth-main-title { font-size: 4rem; }
            .youth-tagline { font-size: 1.35rem; max-width: 600px; }
            .youth-login-section {
                width: 100%;
                max-width: 580px;
                margin: 0 auto;
                padding: 0 3rem 4rem;
                overflow-y: visible;
            }
        }

        @media (max-width: 768px) {
            .youth-branding-section { padding: 3rem 2rem 2rem; }
            .youth-main-title { font-size: 3rem; }
            .youth-tagline { font-size: 1.2rem; }
            .logo-wrapper::before { width: 240px; height: 240px; }
            .youth-logo { width: 180px; height: 180px; }
            .youth-login-section { padding: 0 2rem 3rem; }
            .youth-login-card { padding: 3rem 2.5rem; border-radius: 28px; }
            .card-title { font-size: 2.25rem; }
        }

        @media (max-width: 640px) {
            .youth-branding-section { padding: 2.5rem 1.5rem 2rem; }
            .youth-main-title { font-size: 2.5rem; }
            .youth-tagline { font-size: 1.1rem; }
            .logo-wrapper { margin-bottom: 2rem; }
            .logo-wrapper::before { width: 200px; height: 200px; }
            .youth-logo { width: 150px; height: 150px; }
            .youth-login-section { padding: 0 1.5rem 2.5rem; }
            .youth-login-card { padding: 2.5rem 2rem; border-radius: 24px; }
            .card-header { margin-bottom: 2rem; }
            .card-title { font-size: 2rem; }
        }
    </style>
</head>
<body class="youth-login-page">
    @include('dashboard::loading')
    
    <!-- Animated Background -->
    <div class="youth-bg-wrapper">
        <div class="youth-bg-image"></div>
        <div class="youth-gradient-overlay"></div>
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>

    <main class="youth-login-container">
        <!-- Left Side - Logo & Branding -->
        <div class="youth-branding-section">
            <div class="branding-content">
                <div class="logo-wrapper">
                    <img
                        src="/images/skoneportal_logo.webp"
                        alt="SK OnePortal Logo"
                        class="youth-logo"
                    >
                </div>
                <h1 class="youth-main-title">SK OnePortal</h1>
                <p class="youth-tagline">Official Youth Portal – Santa Cruz, Laguna</p>
            </div>
        </div>

        <!-- Right Side - Card -->
        <div class="youth-login-section">
            <div class="youth-login-card">
                <div class="card-header">
                    <h2 class="card-title">Set Your Password 🔐</h2>
                    <p class="card-subtitle">Almost done! Create a password for your <strong>{{ $barangay }}</strong> KK account.</p>
                </div>

                @if(session('success'))
                    <div class="alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert-error">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('kkprofiling.store-password', ['barangay' => request()->route('barangay')]) }}" id="setPasswordForm" novalidate>
                    @csrf
                    
                    <div class="youth-form-group">
                        <label for="password" class="youth-label">
                            <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            Password
                        </label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" class="youth-input" placeholder="Minimum 8 characters">
                            <button type="button" class="toggle-password" onclick="toggleVisibility('password', this)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        <div class="hint">Minimum 8 characters</div>
                        <div class="error" id="password-error" style="display:none;"></div>
                    </div>

                    <div class="youth-form-group">
                        <label for="password_confirmation" class="youth-label">
                            <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0110 1.944 11.954 11.954 0 0117.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Confirm Password
                        </label>
                        <div class="password-wrapper">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="youth-input" placeholder="Re-enter your password">
                            <button type="button" class="toggle-password" onclick="toggleVisibility('password_confirmation', this)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        <div class="error" id="confirm-error" style="display:none;"></div>
                    </div>

                    <button type="submit" class="youth-submit-btn" id="submitBtn">Complete Registration</button>
                </form>
            </div>
        </div>
    </main>

    <!-- Load loading script AFTER the overlay HTML is rendered -->
    <script src="{{ url('/shared/js/loading.js') }}"></script>

    <script>
        function toggleVisibility(fieldId, btn) {
            const input = document.getElementById(fieldId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('setPasswordForm');
            const pwInput = document.getElementById('password');
            const confirmInput = document.getElementById('password_confirmation');
            const pwError = document.getElementById('password-error');
            const confirmError = document.getElementById('confirm-error');
            const submitBtn = document.getElementById('submitBtn');

            function clearError(input, errorEl) {
                input.classList.remove('error');
                errorEl.style.display = 'none';
                errorEl.textContent = '';
            }

            function showError(input, errorEl, message) {
                input.classList.add('error');
                errorEl.textContent = message;
                errorEl.style.display = 'block';
            }

            // Prevent browser validation messages
            pwInput.addEventListener('invalid', (e) => e.preventDefault());
            confirmInput.addEventListener('invalid', (e) => e.preventDefault());

            // Only validate on submit
            form.addEventListener('submit', function(e) {
                let isValid = true;

                // Clear all errors
                clearError(pwInput, pwError);
                clearError(confirmInput, confirmError);

                // Validate password
                const pw = pwInput.value.trim();
                if (!pw) {
                    isValid = false;
                    showError(pwInput, pwError, 'Password is required');
                } else if (pw.length < 8) {
                    isValid = false;
                    showError(pwInput, pwError, 'Password must be at least 8 characters');
                }

                // Validate confirmation
                const confirm = confirmInput.value.trim();
                if (!confirm) {
                    isValid = false;
                    showError(confirmInput, confirmError, 'Please confirm your password');
                } else if (pw !== confirm) {
                    isValid = false;
                    showError(confirmInput, confirmError, 'Passwords do not match');
                }

                if (!isValid) {
                    e.preventDefault();
                    return;
                }

                // Show loading on successful validation
                submitBtn.disabled = true;
                if (window.showLoading) {
                    window.showLoading('Creating your account...');
                }
            });
        });
    </script>
</body>
</html>
