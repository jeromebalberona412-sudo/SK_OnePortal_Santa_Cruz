<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OnePortal Admin — Set New Password</title>
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
                <img src="{{ asset('Images/image.png') }}" alt="OnePortal Logo" class="large-logo">
            </div>
            <h1 class="brand-title">OnePortal Admin</h1>
            <p class="brand-subtitle">Municipality of Santa Cruz, Laguna</p>
        </div>

        <div class="login-form-container">
            <div class="login-card-inner">
                <div class="form-header">
                    <h2>Set New Password</h2>
                    <p>Your email was updated to <strong>{{ $user->email }}</strong>. Create a new password to finish and access your dashboard.</p>
                </div>

                @if (session('status'))
                    <div class="login-alert login-alert--success" role="alert">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="login-alert login-alert--danger" role="alert">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('profile.change-email.set-password.update', ['id' => $user->id, 'token' => $token]) }}" novalidate id="setPasswordForm">
                    @csrf

                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="Enter new password (min. 12 characters)" autocomplete="new-password" required>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                            placeholder="Re-enter new password" autocomplete="new-password" required>
                    </div>

                    <button type="submit" class="login-btn" id="setPasswordBtn">Set Password &amp; Continue</button>
                </form>

                <div class="form-footer">
                    <p><a href="{{ route('login') }}">Back to Login</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        var form = document.getElementById('setPasswordForm');
        var btn = document.getElementById('setPasswordBtn');
        if (form && btn) {
            form.addEventListener('submit', function () {
                btn.disabled = true;
                btn.textContent = 'Saving…';
            });
        }
    })();
    </script>

</body>
</html>
