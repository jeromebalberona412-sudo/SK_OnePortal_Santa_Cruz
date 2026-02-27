<!DOCTYPE html>
<html lang="en">
<head>
    <title>SK FederationsLogin</title>
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
                    <p>Login your account</p>
                </div>

                @if ($errors->any())
                    <div class="error-messages">
                        @foreach ($errors->all() as $error)
                            <div class="error-item">{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="login-form">
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
                        <div class="forgot-password-container">
                            <a href="" class="forgot-password">
                                Forgot Password?
                            </a>
                        </div>
                    </div>

                    <button type="submit" class="login-btn btn btn-primary w-100">
                        Login
                    </button>
                </form>

                <div class="form-footer">
                    <p>Don't have an account? <a href="">Contact Admin</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ url('/modules/authentication/js/script.js') }}"></script>
</body>
</html>
