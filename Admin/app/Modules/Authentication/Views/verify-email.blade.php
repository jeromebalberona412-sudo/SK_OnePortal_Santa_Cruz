<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SK OnePortal Admin — Verify Email</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/js/login.js',
        'resources/js/theme.js',
    ])
    <script>
        (function () {
            var t = localStorage.getItem('op_theme');
            var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (t === 'dark' || (!t && d)) { document.documentElement.classList.add('dark'); }
        })();
    </script>
</head>
<body class="login-page">

    <div class="bg-wrapper">
        <div class="bg-image"></div>
        <div class="gradient-overlay"></div>
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>

    <div class="login-container">

        {{-- LEFT: Logo --}}
        <div class="logo-container">
            <div class="logo-glow-wrapper">
                <img src="{{ asset('Images/image.png') }}" alt="SK OnePortal Admin Logo" class="large-logo">
            </div>
            <h1 class="brand-title">SK OnePortal Admin</h1>
            <p class="brand-subtitle">Municipality of Santa Cruz, Laguna</p>
        </div>

        {{-- RIGHT: Card --}}
        <div class="login-form-container">
            <div class="login-card-inner">

                {{-- Icon --}}
                <div style="display:flex;align-items:center;justify-content:center;width:64px;height:64px;border-radius:50%;background:rgba(8,48,128,0.08);margin:0 auto 1.25rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="var(--op-blue)" viewBox="0 0 16 16">
                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                    </svg>
                </div>

                <div class="form-header" style="text-align:center;">
                    <h2>Verify Your Email</h2>
                    <p>A verification link has been sent to your email address. Please check your inbox and click the link to activate your account.</p>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <div class="login-alert login-alert--success" role="alert">
                        <strong>Email sent!</strong> A new verification link has been sent to your email address.
                    </div>
                @endif

                @if ($errors->any())
                    <div class="login-alert login-alert--danger" role="alert">{{ $errors->first() }}</div>
                @endif

                {{-- Info box --}}
                <div style="background:#f8fafc;border:1px solid var(--op-gray-200);border-radius:12px;padding:1rem;margin-bottom:1.25rem;font-size:0.875rem;color:var(--op-gray-700);">
                    <p style="margin:0 0 0.5rem;font-weight:600;color:var(--op-gray-900);">Didn't receive the email?</p>
                    <ul style="margin:0;padding-left:1.25rem;line-height:1.8;">
                        <li>Check your spam or junk folder</li>
                        <li>Make sure your email address is correct</li>
                        <li>Click the button below to resend</li>
                    </ul>
                </div>

                {{-- Resend form --}}
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="login-btn">
                        Resend Verification Email
                    </button>
                </form>

                <div class="form-footer">
                    <p>
                        Wrong account?
                        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                            @csrf
                            <button type="submit" style="background:none;border:none;padding:0;color:var(--op-blue);font-weight:600;font-size:inherit;cursor:pointer;font-family:inherit;">
                                Sign out
                            </button>
                        </form>
                    </p>
                </div>

            </div>
        </div>
    </div>

</body>
</html>
