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
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <svg id="toggleIcon" class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="#1e293b">
                                    <!-- Default: eye -->
                                    <path id="togglePath" d="M572.52 241.4C518.9 135.5 417.41 64 288 64 158.6 64 57.1 135.5 3.48 241.4a48.35 48.35 0 000 45.2C57.1 376.5 158.6 448 288 448c129.41 0 230.9-71.5 284.52-161.4a48.35 48.35 0 000-45.2zM288 400c-97.05 0-177.19-48.7-223.37-128C110.81 192.7 190.95 144 288 144s177.19 48.7 223.37 128C465.19 351.3 385.05 400 288 400zm0-208a80 80 0 1080 80 80.09 80.09 0 00-80-80zm0 128a48 48 0 1148-48 48.05 48.05 0 01-48 48z"/>
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
            const path = document.getElementById('togglePath');
            const icon = document.getElementById('toggleIcon');
            
            if (!passwordInput || !path || !icon) return;

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                // Eye-slash icon path (same viewBox)
                path.setAttribute('d', 'M320 192a64 64 0 0 0-64-64 63.12 63.12 0 0 0-25.37 5.29l84.08 84.08A63.12 63.12 0 0 0 320 192zm252.71 278.63-110.55-110.6C498.67 322.4 527.44 287 540.52 264.6a47.78 47.78 0 0 0 0-45.2C486.9 113.5 385.41 42 256 42A306.36 306.36 0 0 0 131.79 72.16L45.25 9.37A16 16 0 0 0 22.63 12l-20 25.3A16 16 0 0 0 5 60.92l86.66 69.53C57.61 173.87 32.82 209.08 19.48 235.4a47.78 47.78 0 0 0 0 45.2C73.1 386.5 174.59 458 304 458a317.5 317.5 0 0 0 147.27-37.46l83.46 67.51a16 16 0 0 0 22.62-2.66l20-25.3a16 16 0 0 0-4.64-22.46zM256 146a110.93 110.93 0 0 1 110.77 110.77 108.53 108.53 0 0 1-5.15 32.84l-45.42-36.74A63.83 63.83 0 0 0 320 192a64 64 0 0 0-80-62.17L213.7 113A111 111 0 0 1 256 146zM64.4 264.6C81.59 234.1 136.73 170 256 170q11.39 0 22.14 1.06l-39.62-32.07A261.39 261.39 0 0 0 256 154C161.44 154 96 209.29 64.4 247.4a16 16 0 0 0 0 18.2z');
                icon.classList.add('is-visible');
            } else {
                passwordInput.type = 'password';
                // Back to eye icon
                path.setAttribute('d', 'M572.52 241.4C518.9 135.5 417.41 64 288 64 158.6 64 57.1 135.5 3.48 241.4a48.35 48.35 0 000 45.2C57.1 376.5 158.6 448 288 448c129.41 0 230.9-71.5 284.52-161.4a48.35 48.35 0 000-45.2zM288 400c-97.05 0-177.19-48.7-223.37-128C110.81 192.7 190.95 144 288 144s177.19 48.7 223.37 128C465.19 351.3 385.05 400 288 400zm0-208a80 80 0 1080 80 80.09 80.09 0 00-80-80zm0 128a48 48 0 1148-48 48.05 48.05 0 01-48 48z');
                icon.classList.remove('is-visible');
            }
        }
    </script>
    
    @vite(['app/modules/Authentication/assets/js/loader.js', 'app/modules/Authentication/assets/js/login.js'])
</body>
</html>