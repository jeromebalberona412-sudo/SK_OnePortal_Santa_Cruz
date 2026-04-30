<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OnePortal Admin — Two-Factor Verification</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/js/login.js',
    ])
</head>
<body class="login-page">

    <div id="signin-overlay" class="signin-overlay" aria-hidden="true" hidden>
        <div class="signin-overlay-inner">
            <div class="signin-spinner">
                <div class="signin-spinner-ring"></div>
                <div class="signin-spinner-ring signin-spinner-ring--2"></div>
                <div class="signin-spinner-dot"></div>
            </div>
            <p class="signin-overlay-title">Signing In</p>
            <p class="signin-overlay-sub" id="signin-overlay-sub">Verifying credentials...</p>
        </div>
    </div>

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

                    <div class="form-header">
                        <h2>Two-Factor Authentication</h2>
                        <p>Enter the 6-digit code from your authenticator app.</p>
                    </div>

                    {{-- Timer --}}
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-3 challenge-timer-wrap"
                         data-challenge-expiry-seconds="600">
                        <span style="font-size:0.8rem;color:#6b7a99;">Session expires in</span>
                        <span id="challenge-timer"
                              style="background:var(--op-blue-pale);color:var(--op-blue);font-size:0.85rem;padding:5px 10px;border-radius:6px;font-weight:600;"
                              aria-live="polite">10:00</span>
                    </div>

                    <div class="login-alert login-alert--danger" id="challenge-expired-message" hidden role="alert">
                        This verification window has expired. Please
                        <a href="{{ route('login') }}" class="login-forgot">return to login</a> and try again.
                    </div>

                    @if ($errors->any())
                        <div class="login-alert login-alert--danger" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ url('/two-factor-challenge') }}" novalidate id="twoFactorForm">
                        @csrf
                        @php($oldCode = preg_replace('/\D/', '', (string) old('code', '')))

                        <div class="form-group">
                            <label style="text-align:center;display:block;">Authentication Code</label>
                            <div class="otp-input-group" data-otp-group>
                                <input type="hidden" id="code" name="code" value="{{ $oldCode }}">
                                @for ($i = 0; $i < 6; $i++)
                                    <input class="otp-digit" type="text" inputmode="numeric" pattern="[0-9]*"
                                        maxlength="1" autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                                        value="{{ $oldCode[$i] ?? '' }}" aria-label="OTP digit {{ $i + 1 }}" required>
                                @endfor
                            </div>
                        </div>

                        <button type="submit" class="login-btn" id="twoFactorSubmitBtn">Authenticate</button>
                    </form>

                    <div class="form-footer">
                        <p><a href="{{ route('login') }}">Back to Login</a></p>
                    </div>

                </div>
            </div>
        </div>
    </div>

</body>
</html>
