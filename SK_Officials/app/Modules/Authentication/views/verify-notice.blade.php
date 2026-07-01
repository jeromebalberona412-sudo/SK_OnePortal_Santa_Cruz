<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Check Your Email - SK Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/css/verify-notice.css',
        'app/Modules/Authentication/assets/js/verify-notice.js',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
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

        <!-- Right Side - Notice Card -->
        <div class="sk-login-section">
            <div class="sk-login-card">
                <div class="notice-content">
                    <div class="notice-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>

                    <h2 class="notice-title">Check Your Email</h2>
                    <p class="notice-message">
                        We've sent a verification link to <span class="email-highlight">{{ $email ?? 'your email address' }}</span>
                    </p>

                    <div class="notice-steps">
                        <h3>What to do next:</h3>
                        <ol>
                            <li>Check your email inbox for a message from SK Officials</li>
                            <li>Click the verification link in the email</li>
                            <li>Your account will be activated immediately</li>
                            <li>Return here to complete your login</li>
                        </ol>
                    </div>

                    <p class="notice-message tip">
                        <strong>Tip:</strong> If you don't see the email, check your spam or junk folder.
                    </p>

                    <div class="notice-actions">
                        <a href="{{ route('login', [], false) }}" class="sk-submit-btn btn-primary-notice">
                            Back to Login
                        </a>
                        <button type="button" class="sk-submit-btn btn-secondary-notice" onclick="location.reload()">
                            Refresh Page
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
