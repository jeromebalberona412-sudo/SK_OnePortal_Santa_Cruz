<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Email Verification Required</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ url('/modules/authentication/css/style.css') }}" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="background-section">
            <div class="logo-container">
                <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Federations Logo" class="large-logo">
                <h1 class="brand-title">SK Federations</h1>
                <p class="brand-subtitle">Santa Cruz Youth Leadership Portal</p>
            </div>

            <div class="login-form-container">
                <div class="form-header">
                    <h2>Verify Your Email</h2>
                    <p>Your SK Federation account requires email verification before secure access can continue.</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-info" role="alert">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('skfed.verification.resend', [], false) }}" class="login-form">
                    @csrf
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $email) }}"
                            required
                            placeholder="Enter your email"
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="login-btn btn btn-primary w-100">Resend Verification Email</button>
                </form>

                <div class="form-footer">
                    <a href="{{ route('login', [], false) }}">Back to login</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ url('/modules/authentication/js/script.js') }}"></script>
</body>
</html>
