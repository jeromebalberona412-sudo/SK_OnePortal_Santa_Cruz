<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Municipal Login Portal</title>
    @vite([
        'app/Modules/Authentication/assets/css/gov-auth.css',
        'app/Modules/Authentication/assets/js/gov-auth.js',
    ])
</head>
<body class="gov-auth-page gov-auth-login">
    <div class="gov-auth-bg" aria-hidden="true"></div>
    <div class="gov-auth-tint" aria-hidden="true"></div>
    <div class="gov-auth-vignette" aria-hidden="true"></div>
    <div class="scanline-overlay" aria-hidden="true"></div>

    <main class="gov-auth-shell">
        <section class="gov-auth-panel" aria-labelledby="portal-heading">
            <header class="auth-header">
                <img
                    class="auth-logo"
                    src="{{ Vite::asset('app/Modules/Authentication/assets/Oneportal_logo-removebg-preview.png') }}"
                    alt="OnePortal emblem"
                >
                <h1 id="portal-heading" class="auth-title">OnePortal Login</h1>
            </header>

            @if (session('success'))
                <p class="auth-alert success">{{ session('success') }}</p>
            @endif

            @if ($errors->any())
                <p class="auth-alert error">{{ $errors->first() }}</p>
            @endif

            <form class="auth-form" method="POST" action="{{ route('login') }}">
                @csrf

                <div class="auth-form-row">
                    <label for="email">User ID</label>
                    <div class="field-wrap">
                        <input
                            class="auth-input"
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="user@sccl.gov.ph"
                        >
                    </div>
                </div>

                <div class="auth-form-row password-row">
                    <label for="password">Password</label>
                    <div class="field-wrap">
                        <input
                            class="auth-input"
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Enter secure password"
                        >
                    </div>
                </div>

                <div class="remember-row">
                    <label for="remember">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        Keep this device trusted
                    </label>
                </div>

                <div class="auth-actions">
                    <button class="auth-button" type="submit">Access Portal</button>
                </div>

                <div class="forgot-password-row">
                    <a class="forgot-password-link" href="{{ route('password.request') }}">Forgot password?</a>
                </div>
            </form>
        </section>
    </main>

    <footer class="gov-auth-warning">
        You are accessing an official information system of the Municipality of Santa Cruz, Laguna.
        Unauthorized access is prohibited. All activities are monitored and logged.
        Use of this system constitutes consent to monitoring and auditing.
    </footer>
</body>
</html>
