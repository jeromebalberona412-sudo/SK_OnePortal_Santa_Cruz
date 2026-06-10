<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SK OnePortal Admin — Password Setup</title>
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
            if (t === 'dark' || (!t && d)) document.documentElement.classList.add('dark');
        })();
    </script>
</head>
<body class="login-page">

    <div class="login-page">
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
            <div class="logo-container">
                <div class="logo-glow-wrapper">
                    <img src="{{ asset('Images/image.png') }}" alt="SK OnePortal Admin Logo" class="large-logo">
                </div>
                <h1 class="brand-title">SK OnePortal Admin</h1>
                <p class="brand-subtitle">Municipality of Santa Cruz, Laguna</p>
            </div>

            <div class="login-form-container">
                <div class="login-card-inner">

                    @if ($awaitingEmail ?? false)
                        <div class="form-header" style="text-align:center;">
                            <h2>Check Your Email</h2>
                            <p>
                                A secure password setup link has been sent to your administrator email address.
                                Click the link in the email to create your password before accessing the dashboard.
                            </p>
                        </div>

                        @if (session('status') === 'setup-link-sent')
                            <div class="login-alert login-alert--success" role="alert">
                                A new password setup link has been sent.
                            </div>
                        @endif

                        @auth
                            <form method="POST" action="{{ route('setup-password.resend') }}" style="margin-top:1rem;">
                                @csrf
                                <button type="submit" class="login-btn">Resend Setup Link</button>
                            </form>
                        @endauth

                        <div class="form-footer">
                            <p>
                                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" style="background:none;border:none;color:inherit;cursor:pointer;text-decoration:underline;padding:0;font:inherit;">Sign out</button>
                                </form>
                            </p>
                        </div>

                    @elseif ($hasValidToken ?? false)
                        <div class="form-header">
                            <h2>Create Administrator Password</h2>
                            <p>Set a strong password to complete your first-time administrator setup.</p>
                        </div>

                        @if ($errors->any())
                            <div class="login-alert login-alert--danger" role="alert">{{ $errors->first() }}</div>
                        @endif

                        <form method="POST" action="{{ route('setup-password.store') }}" novalidate>
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <input type="hidden" name="email" value="{{ $email }}">

                            <div class="form-group">
                                <label for="password">New Password</label>
                                <div class="password-input-container">
                                    <input type="password" id="password" name="password"
                                        class="form-control login-input @error('password') is-invalid @enderror"
                                        placeholder="Min. 12 chars with upper, lower, number & symbol"
                                        required autocomplete="new-password" autofocus>
                                    <button type="button" class="password-toggle" aria-label="Toggle password" tabindex="-1">
                                        <svg class="pw-icon-show" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/></svg>
                                        <svg class="pw-icon-hide" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/><path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/></svg>
                                    </button>
                                </div>
                                @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation">Confirm Password</label>
                                <div class="password-input-container">
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        class="form-control login-input"
                                        placeholder="Confirm new password" required autocomplete="new-password">
                                    <button type="button" class="password-toggle" aria-label="Toggle confirm password" tabindex="-1">
                                        <svg class="pw-icon-show" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/></svg>
                                        <svg class="pw-icon-hide" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/><path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48-.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/></svg>
                                    </button>
                                </div>
                            </div>

                            <p style="font-size:0.78rem;color:#8fa0b8;margin-bottom:1rem;">
                                Example: AdminPortal@2026
                            </p>

                            <button type="submit" class="login-btn">Configure Password</button>
                        </form>

                    @else
                        <div class="form-header" style="text-align:center;">
                            <h2>Invalid or Expired Link</h2>
                            <p>This password setup link is invalid or has expired. Please sign in again to receive a new link.</p>
                        </div>
                        <a href="{{ route('login') }}" class="login-btn" style="display:block;text-align:center;text-decoration:none;">Back to Login</a>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.password-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var container = btn.closest('.password-input-container');
                var input = container.querySelector('input');
                var showIcon = btn.querySelector('.pw-icon-show');
                var hideIcon = btn.querySelector('.pw-icon-hide');
                if (input.type === 'password') {
                    input.type = 'text';
                    showIcon.style.display = 'none';
                    hideIcon.style.display = '';
                } else {
                    input.type = 'password';
                    showIcon.style.display = '';
                    hideIcon.style.display = 'none';
                }
            });
        });
    </script>

</body>
</html>
