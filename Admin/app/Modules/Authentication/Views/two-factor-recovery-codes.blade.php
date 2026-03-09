<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Recovery Codes</title>
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
                @if (isset($regenerated) && $regenerated)
                    <h1 id="portal-heading" class="auth-title">Recovery Codes Refreshed</h1>
                @else
                    <h1 id="portal-heading" class="auth-title">Two-Factor Enabled</h1>
                @endif
                <p class="auth-subtitle">Santa Cruz, Laguna - Integrated Government Services</p>
            </header>

            <p class="inline-note">Store each recovery code in a secure location. Every code can be used only once.</p>

            <section class="auth-section">
                <h2>Recovery Codes</h2>
                <ul class="code-grid">
                    @foreach ($recoveryCodes as $code)
                        <li><code>{{ $code }}</code></li>
                    @endforeach
                </ul>
            </section>

            <div class="auth-links">
                <a class="auth-link" href="{{ route('dashboard') }}">Continue to dashboard</a>
            </div>
        </section>
    </main>

    <footer class="gov-auth-warning">
        You are accessing an official information system of the Municipality of Santa Cruz, Laguna.
        Unauthorized access is prohibited. All activities are monitored and logged.
        Use of this system constitutes consent to monitoring and auditing.
    </footer>
</body>
</html>
