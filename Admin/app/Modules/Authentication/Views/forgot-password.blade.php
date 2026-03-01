<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password - Admin Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('modules/authentication/css/admin-auth.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('modules/authentication/css/admin-two-factor.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('modules/authentication/css/admin-forgot-password.css') }}?v={{ time() }}">
</head>
<body>
    <div class="background-animation">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="two-factor-wrapper">
        <div class="two-factor-container">
            <div class="two-factor-header">
                <div class="header-icon">
                    <img src="{{ asset('modules/authentication/images/SKOneportal_logo.webp') }}" alt="SK One Portal Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                </div>
                <h1>Forgot Password</h1>
                <p>Enter your admin email address to reset your password</p>
            </div>

            <div class="two-factor-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form class="forgot-password-form" method="POST" action="{{ route('password.email') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            Email Address
                        </label>
                        <div class="code-input-container" style="justify-content: stretch;">
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="code-input" 
                                style="width: 100%; max-width: 100%;"
                                placeholder="Enter your admin email address"
                                required
                                autocomplete="email"
                                autofocus
                            >
                        </div>
                        <div class="helper-text">
                            We'll send password reset instructions to this email
                        </div>
                    </div>

                    <button type="submit" class="btn-verify" id="sendResetBtn">
                        <span>Send Reset Link</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>

                    <div class="back-to-login">
                        <a href="{{ route('login') }}">
                            <i class="fas fa-arrow-left"></i>
                            <span>Back to Login</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('modules/authentication/js/admin-forgot-password.js') }}"></script>
</body>
</html>
