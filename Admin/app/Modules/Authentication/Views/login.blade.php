<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login - Dashboard Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('modules/authentication/css/admin-auth.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('modules/authentication/css/preloader.css') }}?v={{ time() }}">
</head>
<body>
    <!-- Simple Preloader -->
    <div id="preloader" class="preloader">
        <div class="preloader-content">
            <div class="spinner-logo">
                <img src="{{ asset('modules/authentication/images/SKOneportal_logo.webp') }}" alt="SK Logo">
            </div>
            <h2>SK OnePortal</h2>
            <p>Loading...</p>
        </div>
    </div>

    <div class="background-animation">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-left">
                <div class="brand-section">
                    <div class="brand-logos-container">
                        <div class="brand-logo-circle">
                            <img src="{{ asset('modules/authentication/images/SKOneportal_logo.webp') }}" alt="SK One Portal Logo">
                        </div>
                    </div>
                    <h1 class="brand-title">Admin Portal</h1>
                    <p class="brand-subtitle">SK OnePortal Administration</p>
                </div>

                <div class="features">
                    <div class="feature-item">
                        <i class="fas fa-lock"></i>
                        <span>Bank-level Security</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Real-time Analytics</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-users-gear"></i>
                        <span>User Management</span>
                    </div>
                </div>

                <div class="testimonial">
                    <p>"Isang Sistema. Isang Layunin. Para sa Mas Maayos na Pamamahalang Pangkabataan."</p>
                    <div class="testimonial-author">
                        <strong>Paula Talabis</strong>
                        <span>SK OnePortal, Admin</span>
                    </div>
                </div>
            </div>

            <div class="login-right">
                <div class="login-form-container">
                    <div class="form-header">
                        <h2>Welcome Back</h2>
                        <p>Please enter your credentials to access the dashboard</p>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="login-form">
                        @csrf

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-wrapper">
                                <svg class="input-icon-left" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#1e293b">
                                    <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/>
                                </svg>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control has-left-icon @error('email') is-invalid @enderror"
                                    placeholder="admin@example.com"
                                    value="{{ old('email') }}"
                                    required
                                    autofocus
                                >
                            </div>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-wrapper">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control has-right-icon @error('password') is-invalid @enderror"
                                    placeholder="Enter your password"
                                    required
                                >
                                <button type="button" class="toggle-password" onclick="togglePassword()">
                                    <svg id="toggleIcon" class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="#1e293b">
                                        <path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-options">
                            <label class="checkbox-container">
                                <input type="checkbox" id="remember" name="remember">
                                <span class="checkmark"></span>
                                <span class="checkbox-label">Remember me</span>
                            </label>
                            <a href="#" class="forgot-link">Forgot Password?</a>
                        </div>

                        <button type="submit" class="btn-login">
                            <span>Sign In</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>

                    <div class="form-footer">
                        <p>Don't have an account? <a href="#">Request Access</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('modules/authentication/js/admin-login.js') }}"></script>
    <script>
        // Simple Preloader
        window.addEventListener('load', function() {
            setTimeout(() => {
                document.getElementById('preloader').classList.add('fade-out');
            }, 800);
        });
    </script>
</body>
</html>
