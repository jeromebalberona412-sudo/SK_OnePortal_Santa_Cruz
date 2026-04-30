<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Password Reset Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .success-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            margin: 20px;
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

        h1 {
            color: #213F99;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
            margin-top: 0;
            animation: fadeIn 0.6s 0.4s ease-in backwards;
        }

        .message {
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

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
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
            .success-container {
                padding: 40px 24px;
            }
            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="check-wrap">
            <span class="check-icon">✓</span>
        </div>
        <h1>Password Reset Successfully!</h1>
        <p class="message">Your password has been updated successfully.</p>
        <p class="message">You can now log in with your new password.</p>
        <p class="next-step">Redirecting to login page...</p>
        <a href="{{ route('login') }}" class="btn-primary">Go to Login</a>
    </div>

    <script>
        // Auto-redirect after 3 seconds
        setTimeout(function() {
            window.location.href = '{{ route('login') }}';
        }, 3000);
    </script>
</body>
</html>
