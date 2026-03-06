<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Email Verified</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ url('/modules/authentication/css/style.css') }}" rel="stylesheet">
    <style>
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

        .success-container {
            text-align: center;
            animation: fadeIn 0.5s ease-in;
        }

        .success-container h2 {
            color: #213F99;
            margin-bottom: 12px;
            font-size: 28px;
            font-weight: 700;
            animation: fadeIn 0.6s 0.4s ease-in backwards;
        }

        .success-container p {
            color: #64748b;
            margin-bottom: 32px;
            font-size: 16px;
            animation: fadeIn 0.6s 0.5s ease-in backwards;
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

        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }

        .fade-out-animation {
            animation: fadeOut 1s ease-out forwards;
        }

        /* Responsive Design for Success Page */
        @media (max-width: 640px) {
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

            .success-container h2 {
                font-size: 22px;
            }

            .success-container p {
                font-size: 14px;
                margin-bottom: 24px;
            }
        }

        @media (max-width: 480px) {
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

            .success-container h2 {
                font-size: 20px;
            }

            .success-container p {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container" id="success-container">
        <div class="background-section">
            <div class="logo-container">
                <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Federations Logo" class="large-logo">
                <h1 class="brand-title">SK Federations</h1>
                <p class="brand-subtitle">Santa Cruz Youth Leadership Portal</p>
            </div>

            <div class="login-form-container">
                <div class="success-container">
                    <div class="check-wrap" aria-hidden="true">
                        <span class="checkmark"></span>
                    </div>
                    <h2>Verified successfully</h2>
                    <p>Your SK Federation account is now verified. Redirecting to dashboard...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="success-modal">
        <div class="success-modal-content">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Verified Successfully!</h2>
            <p>Your account has been verified. Redirecting to dashboard...</p>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script>
        // Show modal immediately
        window.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('successModal');
            modal.style.display = 'flex';
            modal.classList.add('show');
            
            // Hide modal and show loading screen before redirect
            setTimeout(function() {
                modal.classList.remove('show');
                modal.classList.add('hide');
                
                setTimeout(function() {
                    LoadingScreen.show('Redirecting', 'Taking you to dashboard...');
                    setTimeout(function() {
                        window.location.href = "{{ route('dashboard', [], false) }}";
                    }, 500);
                }, 300);
            }, 3000);
        });
    </script>
    <style>
        /* Success Modal Styles */
        .success-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .success-modal.show {
            opacity: 1;
        }

        .success-modal.hide {
            opacity: 0;
        }

        .success-modal-content {
            background-color: #ffffff;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            max-width: 400px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            transform: translateY(30px);
            transition: transform 0.3s ease-out;
        }

        .success-modal.show .success-modal-content {
            transform: translateY(0);
        }

        .success-modal.hide .success-modal-content {
            transform: translateY(30px);
        }

        .success-icon {
            font-size: 64px;
            color: #22c55e;
            margin-bottom: 20px;
            animation: scaleIn 0.4s ease-out;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .success-modal-content h2 {
            margin: 0 0 12px 0;
            font-size: 24px;
            font-weight: 600;
            color: #1e293b;
        }

        .success-modal-content p {
            margin: 0;
            font-size: 16px;
            color: #64748b;
        }
    </style>
</body>
</html>
