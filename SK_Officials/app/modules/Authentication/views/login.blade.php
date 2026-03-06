<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SK Officials Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['app/modules/Authentication/assets/css/login.css'])
</head>
<body>
    <div class="login-container">
        <!-- Left Side - Large Logo -->
        <div class="logo-section">
            <img src="{{ asset('images/logo.png') }}" alt="SK Officials Logo" class="large-logo">
            <h1 class="portal-title">SK Officials Portal</h1>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="form-section">
            <div class="login-card">
                <h2 class="login-title">Welcome Back</h2>
                <p class="login-subtitle">Please login to your account</p>
                
                <form id="loginForm" class="login-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <svg class="input-icon-left" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#1e293b">
                                <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/>
                            </svg>
                            <input type="email" id="email" name="email" class="has-left-icon" required placeholder="Enter your email">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <svg class="input-icon-left" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="#1e293b">
                                <path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/>
                            </svg>
                            <input type="password" id="password" name="password" class="has-left-icon has-right-icon" required placeholder="Enter your password">
                            <button type="button" class="toggle-password eye-btn" onclick="togglePassword()">
                                <svg id="eyeIcon" class="eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    <line id="slashLine" x1="3" y1="3" x2="21" y2="21" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="error-message" id="errorMessage" style="display: none;">
                        The credentials are incorrect.
                    </div>
                    
                    <button type="submit" class="login-btn" id="loginBtn">
                        <span class="btn-text">Login</span>
                    </button>
                    
                    <div class="forgot-password">
                        <button type="button" class="forgot-btn" id="forgotBtn">Forgot Password?</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const slashLine = document.getElementById('slashLine');
            if (!passwordInput || !slashLine) return;

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                slashLine.style.display = 'none';
            } else {
                passwordInput.type = 'password';
                slashLine.style.display = 'block';
            }
        }
    </script>
    
    @vite(['app/modules/Authentication/assets/js/loader.js', 'app/modules/Authentication/assets/js/login.js'])
</body>
</html>