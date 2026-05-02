<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Email Verified - SK Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <style>
        .success-content {
            text-align: center;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 2rem;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 40px rgba(34, 197, 94, 0.3);
            animation: scaleIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
        }

        .success-icon::before {
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
            width: 40px;
            height: 80px;
            border-right: 6px solid white;
            border-bottom: 6px solid white;
            transform: rotate(45deg) scale(0);
            animation: draw-check 0.6s 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
            transform-origin: center center;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            margin-left: 10px;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
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

        .success-title {
            font-size: 2rem;
            font-weight: 800;
            color: #1a1a2e;
            margin-bottom: 1rem;
            animation: fadeInText 0.6s 0.4s ease-in backwards;
        }

        .success-message {
            font-size: 1rem;
            color: #64748b;
            margin-bottom: 2rem;
            line-height: 1.6;
            animation: fadeInText 0.6s 0.5s ease-in backwards;
        }

        .success-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-primary-success {
            background: #1a1a2e !important;
            color: white !important;
            font-weight: 700 !important;
            border: none !important;
            box-shadow: 0 8px 24px rgba(26, 26, 46, 0.3) !important;
            animation: fadeInText 0.6s 0.6s ease-in backwards;
        }

        .btn-primary-success:hover {
            background: #2a2a3e !important;
            box-shadow: 0 12px 32px rgba(26, 26, 46, 0.4) !important;
        }

        .btn-secondary-success {
            background: transparent !important;
            color: #1a1a2e !important;
            font-weight: 600 !important;
            border: 2px solid #e2e8f0 !important;
            box-shadow: none !important;
            animation: fadeInText 0.6s 0.7s ease-in backwards;
        }

        .btn-secondary-success:hover {
            background: #f8fafc !important;
            border-color: #cbd5e1 !important;
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
            .success-icon {
                width: 80px;
                height: 80px;
                margin: 0 auto 1.5rem;
            }

            .checkmark {
                width: 30px;
                height: 60px;
                border-right: 5px solid white;
                border-bottom: 5px solid white;
                margin-left: 8px;
                margin-bottom: 8px;
            }

            .success-title {
                font-size: 1.75rem;
            }

            .success-message {
                font-size: 0.95rem;
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

        <!-- Right Side - Success Card -->
        <div class="sk-login-section">
            <div class="sk-login-card">
                <div class="success-content">
                    <div class="success-icon">
                        <span class="checkmark"></span>
                    </div>

                    <h2 class="success-title">Email Verified!</h2>
                    <p class="success-message">
                        Your email has been successfully verified. You can now access your account and continue with the login process.
                    </p>
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
