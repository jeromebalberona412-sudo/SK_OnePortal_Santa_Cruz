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
                                    <path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/>
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
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.innerHTML = '<path d="M38.8 5.1C28 4.2 16.3 7.5 7.4 14.9C3.1 18.5 0 24.1 0 30.2C0 35.8 2.7 41 7.4 44.6C16.3 52 28 55.3 38.8 54.4C49.7 53.5 61 49.2 69.9 41.8C74.1 38.2 77.3 32.6 77.3 26.5C77.3 20.9 74.5 15.7 69.9 12.1C61 4.7 49.7 1 38.8 0zM39 8.1C48.3 8.9 58.4 12.5 66.3 19.1C69.7 22 71.3 24.7 71.3 26.5C71.3 28.8 69.7 31.5 66.3 34.4C58.4 41 48.3 44.6 39 45.4C29.7 46.2 19.6 42.6 11.7 36C8.3 33.1 6.7 30.4 6.7 28.2C6.7 25.9 8.3 23.2 11.7 20.3C19.6 13.7 29.7 10.1 39 8.1zM39 16.1C33.5 16.1 28.7 20.9 28.7 26.4C28.7 31.9 33.5 36.7 39 36.7C44.5 36.7 49.3 31.9 49.3 26.4C49.3 20.9 44.5 16.1 39 16.1zM39 22.1C41.2 22.1 43 23.9 43 26.1C43 28.3 41.2 30.1 39 30.1C36.8 30.1 35 28.3 35 26.1C35 23.9 36.8 22.1 39 22.1z"/>';
            } else {
                passwordInput.type = 'password';
                toggleIcon.innerHTML = '<path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/>';
            }
        }
    </script>
    
    @vite(['app/modules/Authentication/assets/js/loader.js', 'app/modules/Authentication/assets/js/login.js'])
</body>
</html>