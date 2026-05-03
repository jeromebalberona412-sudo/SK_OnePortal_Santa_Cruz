<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Password Reset Successful - SK Officials</title>
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/js/login.js',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <style>
        .success-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 60px 40px;
        }

        .check-wrap {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 10px 30px rgba(34, 197, 94, 0.3);
            animation: scaleIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .check-icon {
            color: white;
            font-size: 50px;
            line-height: 1;
            animation: checkmark 0.6s 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55) backwards;
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

        @keyframes checkmark {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-title {
            color: #213F99;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
            margin-top: 0;
            animation: fadeIn 0.6s 0.4s ease-in backwards;
        }

        .success-message {
            color: #64748b;
            font-size: 16px;
            margin-bottom: 12px;
            animation: fadeIn 0.6s 0.5s ease-in backwards;
            line-height: 1.5;
        }

        .next-step {
            color: #94a3b8;
            font-size: 14px;
            margin-bottom: 32px;
            animation: fadeIn 0.6s 0.6s ease-in backwards;
        }

        .success-btn {
            background: linear-gradient(135deg, #213F99 0%, #1a2f7a 100%);
            border: none;
            padding: 12px 32px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            color: white;
            transition: all 0.3s ease;
            animation: fadeIn 0.6s 0.7s ease-in backwards;
            cursor: pointer;
        }

        .success-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(33, 63, 153, 0.3);
            color: white;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 576px) {
            .success-content {
                padding: 40px 24px;
            }
            .success-title {
                font-size: 24px;
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

        <!-- Right Side - Success Card -->
        <div class="sk-login-section">
            <div class="sk-login-card">
                <div class="success-content">
                    <div class="check-wrap">
                        <span class="check-icon">✓</span>
                    </div>
                    <h1 class="success-title">Password Reset Successfully!</h1>
                    <p class="success-message">Your password has been updated successfully.</p>
                    <p class="success-message">You can now log in with your new password.</p>
                    <p class="next-step">Redirecting to login page...</p>
                    <a href="{{ route('login') }}" class="success-btn">Go to Login</a>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Auto-redirect after 3 seconds
        setTimeout(function() {
            LoadingScreen.show('Redirecting', 'Taking you to login...');
            setTimeout(() => {
                window.location.href = '{{ route('login') }}';
            }, 300);
        }, 3000);

        document.querySelector('.success-btn').addEventListener('click', function(e) {
            e.preventDefault();
            LoadingScreen.show('Redirecting', 'Taking you to login...');
            setTimeout(() => {
                window.location.href = this.href;
            }, 300);
        });
    </script>

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    @vite(['app/Modules/Authentication/assets/js/loader.js'])
</body>
</html>
