<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Check Your Email - SK Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <style>
        .notice-content {
            text-align: center;
        }

        .notice-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 2rem;
            background: linear-gradient(135deg, #f5c518 0%, #e6a800 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 40px rgba(245, 197, 24, 0.3);
            animation: scaleIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .notice-icon svg {
            width: 50px;
            height: 50px;
            color: white;
        }

        .notice-title {
            font-size: 2rem;
            font-weight: 800;
            color: #1a1a2e;
            margin-bottom: 1rem;
            animation: fadeInText 0.6s 0.4s ease-in backwards;
        }

        .notice-message {
            font-size: 1rem;
            color: #64748b;
            margin-bottom: 2rem;
            line-height: 1.6;
            animation: fadeInText 0.6s 0.5s ease-in backwards;
        }

        .email-highlight {
            color: #f5c518;
            font-weight: 700;
        }

        .notice-steps {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: left;
            animation: fadeInText 0.6s 0.6s ease-in backwards;
        }

        .notice-steps h3 {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 1rem;
        }

        .notice-steps ol {
            margin: 0;
            padding-left: 1.5rem;
        }

        .notice-steps li {
            font-size: 0.9rem;
            color: #475569;
            margin-bottom: 0.75rem;
            line-height: 1.5;
        }

        .notice-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-primary-notice {
            background: #1a1a2e !important;
            color: white !important;
            font-weight: 700 !important;
            border: none !important;
            box-shadow: 0 8px 24px rgba(26, 26, 46, 0.3) !important;
            animation: fadeInText 0.6s 0.7s ease-in backwards;
        }

        .btn-primary-notice:hover {
            background: #2a2a3e !important;
            box-shadow: 0 12px 32px rgba(26, 26, 46, 0.4) !important;
        }

        .btn-secondary-notice {
            background: transparent !important;
            color: #1a1a2e !important;
            font-weight: 600 !important;
            border: 2px solid #e2e8f0 !important;
            box-shadow: none !important;
            animation: fadeInText 0.6s 0.8s ease-in backwards;
        }

        .btn-secondary-notice:hover {
            background: #f8fafc !important;
            border-color: #cbd5e1 !important;
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
            .notice-icon {
                width: 80px;
                height: 80px;
                margin: 0 auto 1.5rem;
            }

            .notice-icon svg {
                width: 40px;
                height: 40px;
            }

            .notice-title {
                font-size: 1.75rem;
            }

            .notice-message {
                font-size: 0.95rem;
            }

            .notice-steps {
                padding: 1.25rem;
            }

            .notice-steps h3 {
                font-size: 0.9rem;
            }

            .notice-steps li {
                font-size: 0.85rem;
            }
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

                    <p class="notice-message" style="font-size: 0.9rem; color: #94a3b8;">
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
    <script>
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.href && !this.target) {
                    e.preventDefault();
                    LoadingScreen.show('Redirecting', 'Please wait...');
                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 300);
                }
            });
        });
    </script>
</body>
</html>
