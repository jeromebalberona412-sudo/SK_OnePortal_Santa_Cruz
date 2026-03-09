<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Password Recovery Portal</title>
    @vite([
        'app/Modules/Authentication/assets/css/gov-auth.css',
        'app/Modules/Authentication/assets/js/gov-auth.js',
    ])
</head>
<body class="gov-auth-page">
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
                <p class="auth-kicker">Municipality of Santa Cruz, Laguna</p>
                <h1 id="portal-heading" class="auth-title">Password Recovery</h1>
                <p class="auth-subtitle">Santa Cruz, Laguna - Integrated Government Services</p>
            </header>

            <p class="inline-note">Submit your registered government email to receive password reset instructions.</p>

            @if ($errors->any())
                <p class="auth-alert error">{{ $errors->first() }}</p>
            @endif

            @if (session('status'))
                <p class="auth-alert success">{{ session('status') }}</p>
            @endif

            <form class="auth-form" method="POST" action="{{ route('password.email') }}">
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
                            autocomplete="email"
                            autofocus
                            placeholder="user@sccl.gov.ph"
                        >
                    </div>
                </div>

                <div class="auth-actions">
                    <button class="auth-button" type="submit">Send Reset Link</button>
                </div>

                <div class="auth-links">
                    <a class="auth-link" href="{{ route('login') }}">Back to login</a>
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
