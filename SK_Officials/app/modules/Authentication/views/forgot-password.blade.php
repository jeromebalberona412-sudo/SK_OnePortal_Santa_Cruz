<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - SK Officials</title>
    @vite(['app/modules/Authentication/assets/css/forgot-password.css'])
</head>
<body>
    <div class="forgot-password-container">
        <!-- Left Side - Logo Section -->
        <div class="logo-section">
            <img src="{{ asset('images/logo.png') }}" alt="SK Officials Logo" class="large-logo">
            <h1 class="portal-title">SK Officials Portal</h1>
        </div>
        
        <!-- Right Side - Forgot Password Form -->
        <div class="form-section">
            <div class="forgot-password-card">
                <h2 class="forgot-password-title">Forgot Password?</h2>
                <p class="forgot-password-subtitle">Enter your email address and we'll send you instructions to reset your password.</p>
                
                <form id="forgotPasswordForm" class="forgot-password-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required placeholder="Enter your email address">
                    </div>
                    
                    <div class="error-message" id="errorMessage" style="display: none;">
                        Please enter a valid email address.
                    </div>
                    
                    <div class="success-message" id="successMessage" style="display: none;">
                        Password reset instructions have been sent to your email.
                    </div>
                    
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span class="btn-text">Send Reset Instructions</span>
                    </button>
                </form>
                
                <div class="back-link">
                    <a href="/login" class="back-btn">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
    
    @vite(['app/modules/Authentication/assets/js/loader.js', 'app/modules/Authentication/assets/js/forgot-password.js'])
</body>
</html>
