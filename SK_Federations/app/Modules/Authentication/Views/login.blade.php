<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>SK Federations Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ url('/modules/authentication/css/style.css') }}" rel="stylesheet">
</head>

<style>
        /* Force hide browser password reveal icons */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        
        /* CRITICAL: Remove ALL browser validation styling (green borders and checkmarks) */
        input[type="email"],
        input[type="password"],
        input[type="text"] {
            background-image: none !important;
            background-color: white !important;
            border: 2px solid #e2e8f0 !important;
        }

        /* Remove validation styling in ALL states */
        input[type="email"]:valid,
        input[type="password"]:valid,
        input[type="text"]:valid,
        input[type="email"]:invalid,
        input[type="password"]:invalid,
        input[type="text"]:invalid {
            background-image: none !important;
            border-color: #e2e8f0 !important;
            background-color: white !important;
            box-shadow: none !important;
        }

        /* Focus state - blue border (not green) */
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="text"]:focus {
            border-color: #213F99 !important;
            box-shadow: 0 0 0 4px rgba(33, 63, 153, 0.1) !important;
            background-image: none !important;
            outline: none !important;
        }

        input[type="email"]:valid:focus,
        input[type="password"]:valid:focus,
        input[type="text"]:valid:focus {
            border-color: #213F99 !important;
            box-shadow: 0 0 0 4px rgba(33, 63, 153, 0.1) !important;
            background-image: none !important;
        }

        /* Remove browser autofill green styling */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active,
        input:-webkit-autofill:valid {
            -webkit-box-shadow: 0 0 0 30px white inset !important;
            -webkit-text-fill-color: #1e293b !important;
            border: 2px solid #e2e8f0 !important;
            transition: background-color 5000s ease-in-out 0s !important;
            background-image: none !important;
        }

        input:-webkit-autofill:focus {
            border-color: #213F99 !important;
            -webkit-box-shadow: 0 0 0 4px rgba(33, 63, 153, 0.1), 0 0 0 30px white inset !important;
        }

        /* Error state - red border (highest priority) */
        input.is-invalid,
        input.is-invalid:hover,
        input.is-invalid:active,
        input.is-invalid:valid,
        input.is-invalid:-webkit-autofill,
        input.is-invalid:-webkit-autofill:hover,
        input.is-invalid:-webkit-autofill:focus,
        input.is-invalid:-webkit-autofill:active {
            border-color: #dc3545 !important;
            background-image: none !important;
            -webkit-box-shadow: 0 0 0 30px white inset !important;
        }

        input.is-invalid:focus,
        input.is-invalid:-webkit-autofill:focus {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.1), 0 0 0 30px white inset !important;
            -webkit-box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.1), 0 0 0 30px white inset !important;
        }

        /* Disable browser form validation UI completely */
        .login-form {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .form-control {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
        
        /* Override Bootstrap checkbox alignment */
        .form-options {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-left: 0 !important;
            padding-left: 0 !important;
        }
        
        .form-check {
            padding-left: 0 !important;
            margin-bottom: 0 !important;
            margin-left: 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
        }
        
        .form-check-input {
            margin-left: 0 !important;
            margin-top: 0 !important;
            float: none !important;
        }
        
        .form-check-label {
            padding-left: 0 !important;
            margin-left: 0 !important;
            color: #1e293b !important;
        }

        .forgot-password {
            font-size: 14px;
            color: #213F99;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            white-space: nowrap;
        }

        .forgot-password:hover {
            color: #d0242b;
        }
    </style>

<body>
    <script>
        // Prevent back navigation to login page when already logged in
        (function() {
            // Check if user is authenticated by checking if we can access dashboard
            @auth
                // If authenticated and on login page, redirect to dashboard
                window.location.replace("{{ route('dashboard') }}");
            @endauth
            
            // Prevent back button navigation
            window.history.pushState(null, "", window.location.href);
            window.onpopstate = function() {
                window.history.pushState(null, "", window.location.href);
            };
        })();
    </script>
    <div class="login-container">
        <div class="background-section">
            <div class="logo-container">
                <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Federations Logo" class="large-logo">
                <h1 class="brand-title">SK Federations</h1>
                <p class="brand-subtitle">Santa Cruz Youth Leadership Portal</p>
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

                <form method="POST" action="{{ route('login', [], false) }}" class="login-form" novalidate>
                    @csrf
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            autofocus
                            placeholder="Enter your email"
                            maxlength="150"
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-input-container">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                minlength="8"
                                maxlength="64"
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
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-options">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                            <label class="form-check-label" for="remember">Remember this device</label>
                        </div>
                        <a href="{{ url('/forgot-password') }}" class="forgot-password">Forgot Password?</a>
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
