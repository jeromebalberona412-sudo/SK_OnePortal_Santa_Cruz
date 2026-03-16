<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password - SK OnePortal</title>
    @vite([
        'app/Modules/Authentication/assets/css/youth-login.css',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])
</head>
<body class="youth-login-page">
    @include('dashboard::loading')
    <!-- Animated Background -->
    <div class="youth-bg-wrapper">
        <div class="youth-bg-image"></div>
        <div class="youth-gradient-overlay"></div>
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>

    <main class="youth-login-container">
        <!-- Left Side - Logo & Branding -->
        <div class="youth-branding-section">
            <div class="branding-content">
                <div class="logo-wrapper">
                    <img
                        src="/images/skoneportal_logo.webp"
                        alt="SK OnePortal Logo"
                        class="youth-logo"
                    >
                </div>
                <h1 class="youth-main-title">SK OnePortal</h1>
                <p class="youth-tagline">Official Youth Portal – Santa Cruz, Laguna</p>
            </div>
        </div>

        <!-- Right Side - Forgot Password Card -->
        <div class="youth-login-section">
            <div class="youth-login-card">
                <div class="card-header">
                    <h2 class="card-title">
                        Forgot Your Password? 🔑
                    </h2>
                    <p class="card-subtitle">Enter the email address associated with your account and we will send you a link to reset your password.</p>
                </div>

                <!-- Alert Messages -->
                @if (session('status'))
                    <div class="youth-alert youth-alert-success">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="youth-alert youth-alert-error">
                        <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ $errors->first() }}
                    </div>
                @endif

                <!-- Forgot Password Form -->
                <form class="youth-login-form" id="forgotPasswordForm">
                    @csrf

                    <!-- Email Field -->
                    <div class="youth-form-group">
                        <label for="email" class="youth-label">
                            <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            Email Address
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="youth-input"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            placeholder="juan@example.com"
                        >
                        <span class="inline-error" id="emailError" style="display: none; color: #ef4444; font-size: 0.875rem; margin-top: 0.5rem; display: block;"></span>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="youth-submit-btn">
                        <span>Send Reset Link</span>
                        <svg class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                        </svg>
                    </button>
                </form>

                <!-- Back to Login Link -->
                <div class="youth-register-section">
                    <p class="register-text">
                        Remember your password? 
                        <a href="{{ route('login') }}" class="register-link">Back to Login</a>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Function to show inline error with auto-dismiss
        function showInlineError(elementId, message) {
            const errorElement = document.getElementById(elementId);
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                errorElement.style.display = 'none';
            }, 5000);
        }

        // Prototype: Accept any email and redirect to reset password page
        document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            // Clear previous errors
            document.getElementById('emailError').style.display = 'none';
            
            // Validate email format
            if (!emailRegex.test(email)) {
                showInlineError('emailError', 'Please enter a valid email address.');
                return;
            }
            
            // Redirect to reset password page with email parameter
            if (window.showLoading) showLoading('Sending reset link');
            window.location.href = '{{ route("password.reset", ["token" => "demo-token"]) }}?email=' + encodeURIComponent(email);
        });
    </script>
</body>
</html>
