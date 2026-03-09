<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>One-Time Password Verification</title>
    @vite([
        'app/Modules/Authentication/assets/css/gov-auth.css',
        'app/Modules/Authentication/assets/js/gov-auth.js',
    ])
</head>
<body class="gov-auth-page gov-auth-challenge">
    <div class="gov-auth-bg" aria-hidden="true"></div>
    <div class="gov-auth-tint" aria-hidden="true"></div>
    <div class="gov-auth-vignette" aria-hidden="true"></div>
    <div class="scanline-overlay" aria-hidden="true"></div>

    <main class="gov-auth-shell">
        <section class="gov-auth-panel" aria-labelledby="portal-heading">
            <header class="auth-header">
                <h1 id="portal-heading" class="auth-title">Use the Authentication App</h1>
            </header>

            <div class="challenge-timer-wrap" data-challenge-expiry-seconds="600">
                <span class="challenge-timer-value" id="challenge-timer" aria-live="polite">10:00</span>
            </div>
            <p class="auth-alert error challenge-expired-message" id="challenge-expired-message" hidden>
                This verification window has expired. Please return to login and start again.
            </p>

            @if ($errors->any())
                <p class="auth-alert error">{{ $errors->first() }}</p>
            @endif

            <section class="auth-section">
                <form class="auth-form" method="POST" action="{{ url('/two-factor-challenge') }}">
                    @csrf
                    @php($oldCode = preg_replace('/\D/', '', (string) old('code', '')))

                    <div class="auth-form-row challenge-otp-row">
                        <div class="field-wrap otp-input-group" data-otp-group>
                            <input type="hidden" id="code" name="code" value="{{ $oldCode }}">
                            @for ($i = 0; $i < 6; $i++)
                                <input
                                    class="otp-digit"
                                    type="text"
                                    id="otp-digit-{{ $i + 1 }}"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    maxlength="1"
                                    autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                                    value="{{ $oldCode[$i] ?? '' }}"
                                    aria-label="OTP digit {{ $i + 1 }}"
                                    required
                                >
                            @endfor
                        </div>
                    </div>

                    <div class="auth-actions">
                        <button class="auth-button" type="submit">Authenticate</button>
                    </div>
                </form>
            </section>

            <div class="auth-links">
                <a class="auth-link" href="{{ route('login') }}">Back to login</a>
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
