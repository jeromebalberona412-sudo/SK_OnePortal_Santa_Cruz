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

        .form-group input {
            padding: 0.875rem 1.125rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: #f5c518;
            box-shadow: 0 0 0 4px rgba(245, 197, 24, 0.12);
        }

        .btn-submit {
            background: #1a1a2e !important;
            color: white !important;
            font-weight: 700 !important;
            border: none !important;
            box-shadow: 0 8px 24px rgba(26, 26, 46, 0.3) !important;
        }

        .btn-submit:hover:not(:disabled) {
            background: #2a2a3e !important;
            box-shadow: 0 12px 32px rgba(26, 26, 46, 0.4) !important;
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
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

                    <form method="POST" action="{{ route('sk_official.takeover.send') }}" class="takeover-form" id="sendCodeForm">
                        @csrf
                        <button type="submit" class="sk-submit-btn btn-submit" @disabled($resendLocked)>
                            Send Verification Code
                        </button>
                    </form>

                    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e2e8f0;">
                        <form method="POST" action="{{ route('sk_official.takeover.verify') }}" class="takeover-form" id="verifyCodeForm">
                            @csrf
                            <div class="form-group">
                                <label for="otp_code">Enter Verification Code</label>
                                <input 
                                    type="text" 
                                    id="otp_code" 
                                    name="otp_code" 
                                    maxlength="6" 
                                    placeholder="000000"
                                    required
                                    style="text-align: center; letter-spacing: 0.5em; font-size: 1.25rem; font-weight: 600;"
                                >
                            </div>
                            <button type="submit" class="sk-submit-btn btn-submit">
                                Verify Code
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script>
        document.getElementById('sendCodeForm').addEventListener('submit', function(e) {
            LoadingScreen.show('Sending Code', 'Please wait...');
        });

        document.getElementById('verifyCodeForm').addEventListener('submit', function(e) {
            LoadingScreen.show('Verifying Code', 'Please wait...');
        });
    </script>
    @vite(['app/Modules/Authentication/assets/js/loader.js'])
</body>
</html>
