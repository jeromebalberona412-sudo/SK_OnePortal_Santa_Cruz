<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK Federations Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ url('/modules/authentication/css/style.css') }}" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="background-section">
            <div class="logo-container">
                <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Federations Logo" class="large-logo">
                <h1 class="brand-title">SK Federations</h1>
                <p class="brand-subtitle">Santa Cruz Youth Leadership Portazl</p>
            </div>

            <div class="login-form-container">
                <div class="form-header">
                    <h2>Welcome Back</h2>
                    <p>Sign in to SK Federation</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-info" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session()->has('sk_fed_takeover_pending'))
                    <div class="alert alert-warning" role="alert">
                        <div>Account currently active on another device. Verify ownership to continue.</div>
                        <form method="POST" action="{{ route('skfed.takeover.send', [], false) }}" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-dark">Send Email Verification Code</button>
                        </form>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login', [], false) }}" class="login-form" novalidate>
                    @csrf
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            autofocus
                            placeholder="Enter your email"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-input-container">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <svg id="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg id="eye-off-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                            <label class="form-check-label" for="remember">Remember this device</label>
                        </div>
                        <div class="forgot-password-container">
                            <a href="{{ route('skfed.verification.notice', [], false) }}" class="forgot-password">Verify Email</a>
                        </div>
                    </div>

                    <button type="submit" class="login-btn btn btn-primary w-100">
                        Sign In
                    </button>
                </form>

                <div class="form-footer">
                    <p>Accounts are provisioned by Admin only.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ url('/modules/authentication/js/script.js') }}"></script>
</body>
@if (session('verification_wait') && session()->has('sk_fed_email_verification_pending'))
    <script>
        window.location.replace("{{ route('skfed.verification.wait', [], false) }}");
    </script>
@endif
@if (session('takeover_wait') && session()->has('sk_fed_takeover_pending'))
    <script>
        window.location.replace("{{ route('skfed.takeover.wait', [], false) }}");
    </script>
@endif
</html>
