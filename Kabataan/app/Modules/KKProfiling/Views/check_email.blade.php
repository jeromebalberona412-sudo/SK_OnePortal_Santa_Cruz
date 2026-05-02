<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Your Email - KK Profiling</title>
    @vite(['app/Modules/Homepage/assets/css/homepage.css'])
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 2rem;
        }
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            padding: 3rem;
            text-align: center;
        }
        .icon {
            width: 80px;
            height: 80px;
            background: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        .icon svg {
            width: 40px;
            height: 40px;
            color: white;
        }
        h1 {
            font-size: 1.75rem;
            color: #1a1a1a;
            margin-bottom: 1rem;
        }
        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        .email {
            background: #f3f4f6;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            font-weight: 600;
            color: #667eea;
            margin: 1.5rem 0;
        }
        .btn {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.75rem 1.5rem;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
        </div>
        <h1>Check Your Email</h1>
        <p>Thank you for submitting your KK Profiling form!</p>
        <p>We've sent a verification link to:</p>
        <div class="email">{{ $email }}</div>
        <p>Please check your email and click the verification link to continue with your registration.</p>
        <p style="font-size: 0.9rem; color: #999;">The link will expire in 24 hours.</p>
        <a href="{{ route('kkprofiling.signup') }}" class="btn">Back to Signup</a>
    </div>
</body>
</html>
