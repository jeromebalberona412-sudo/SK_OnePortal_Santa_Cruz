<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - SK OnePortal</title>
    @vite([
        'app/Modules/Authentication/assets/css/youth-login.css',
        'app/Modules/Authentication/assets/js/youth-login.js',
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

        <!-- Right Side - Reset Password Card -->
        <div class="youth-login-section">
            <div class="youth-login-card">
                <div class="card-header">
                    <h2 class="card-title">
                        Reset Your Password 🔐
                    </h2>
                    <p class="card-subtitle">Enter your new password below</p>
                </div>

                <!-- Alert Messages (removed - not needed for reset password page) -->

                <!-- Reset Password Form -->
                <form class="youth-login-form" id="resetPasswordForm">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token ?? '' }}">
                    <input type="hidden" name="email" value="{{ $email ?? request()->email }}">

                    <!-- New Password Field -->
                    <div class="youth-form-group">
                        <label for="password" class="youth-label">
                            <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            New Password
                        </label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="youth-input password-input"
                                required
                                maxlength="50"
                                placeholder="Minimum 8 characters"
                            >
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                <svg class="eye-icon eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-icon eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                        <p class="field-hint" style="font-size: 0.875rem; color: #64748b; margin-top: 0.5rem;">Must contain: 8+ characters, 1 uppercase letter, 1 number</p>
                        <div class="password-strength" id="password_strength" style="margin-top: 0.75rem;"></div>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="youth-form-group">
                        <label for="password_confirmation" class="youth-label">
                            <svg class="label-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Confirm Password
                        </label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="youth-input password-input"
                                required
                                maxlength="50"
                                placeholder="Re-enter your password"
                            >
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                <svg class="eye-icon eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-icon eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                        <span class="inline-error" id="confirmPasswordError" style="display: none; color: #ef4444; font-size: 0.875rem; margin-top: 0.5rem; display: block;"></span>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="youth-submit-btn">
                        <span>Reset Password</span>
                        <svg class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
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

    <!-- Success Modal -->
    <div class="success-modal-overlay" id="successModal" style="display: none;">
        <div class="success-modal" style="background: white; border-radius: 24px; padding: 3rem 2.5rem; max-width: 500px; width: 90%; box-shadow: 0 24px 64px rgba(0, 0, 0, 0.3);">
            <div class="success-modal-content" style="text-align: center;">
                <div style="width: 100px; height: 100px; margin: 0 auto 2rem; background: linear-gradient(135deg, #44a53e 0%, #5cb854 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 32px rgba(68, 165, 62, 0.3);">
                    <svg style="width: 60px; height: 60px; color: white; stroke-width: 3;" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <path d="M9 12l2 2 4-4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h2 style="font-size: 2rem; font-weight: 800; color: #0450a8; margin-bottom: 1rem;">Password Reset Successful! 🎉</h2>
                <p style="font-size: 1.125rem; color: #334155; margin-bottom: 0.75rem; font-weight: 500;">
                    Your password has been reset successfully.
                </p>
                <p style="font-size: 1rem; color: #475569; margin-bottom: 2rem;">
                    Redirecting to login page in <span id="countdown">3</span> seconds...
                </p>
            </div>
        </div>
    </div>

    <script>
        // Password toggle functionality - must be loaded after DOM
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevent any default behavior
                    const wrapper = this.closest('.password-wrapper');
                    const input = wrapper.querySelector('.password-input');
                    const eyeOpen = this.querySelector('.eye-open');
                    const eyeClosed = this.querySelector('.eye-closed');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        // Hide eye-open
                        eyeOpen.style.opacity = '0';
                        eyeOpen.style.transform = 'scale(0.8) rotate(10deg)';
                        setTimeout(() => {
                            eyeOpen.style.display = 'none';
                        }, 200);
                        
                        // Show eye-closed
                        eyeClosed.style.display = 'block';
                        setTimeout(() => {
                            eyeClosed.style.opacity = '1';
                            eyeClosed.style.transform = 'scale(1) rotate(0deg)';
                        }, 10);
                    } else {
                        input.type = 'password';
                        // Hide eye-closed
                        eyeClosed.style.opacity = '0';
                        eyeClosed.style.transform = 'scale(0.8) rotate(-10deg)';
                        setTimeout(() => {
                            eyeClosed.style.display = 'none';
                        }, 200);
                        
                        // Show eye-open
                        eyeOpen.style.display = 'block';
                        setTimeout(() => {
                            eyeOpen.style.opacity = '1';
                            eyeOpen.style.transform = 'scale(1) rotate(0deg)';
                        }, 10);
                    }
                });
            });

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

            // Password strength validation
            function validatePasswordStrength(password) {
                const hasUpperCase = /[A-Z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                const hasMinLength = password.length >= 8;
                
                return {
                    isValid: hasUpperCase && hasNumber && hasMinLength,
                    hasUpperCase,
                    hasNumber,
                    hasMinLength
                };
            }

            // Calculate password strength for meter
            function calculatePasswordStrength(password) {
                let score = 0;
                const hasMinLength = password.length >= 8;
                const hasUpperCase = /[A-Z]/.test(password);
                const hasNumber = /\d/.test(password);
                
                if (hasMinLength) score++;
                if (password.length >= 12) score++;
                if (/[a-z]/.test(password) && hasUpperCase) score++;
                if (hasNumber) score++;
                if (/[^a-zA-Z\d]/.test(password)) score++;
                
                // Check required criteria
                const meetsRequirements = hasMinLength && hasUpperCase && hasNumber;
                
                if (!meetsRequirements) {
                    return { level: 'weak', text: 'Must have 8+ chars, 1 uppercase, 1 number' };
                } else if (score <= 3) {
                    return { level: 'medium', text: 'Medium strength' };
                } else {
                    return { level: 'strong', text: 'Strong password' };
                }
            }

            // Password strength meter
            const passwordInput = document.getElementById('password');
            const passwordStrength = document.getElementById('password_strength');
            
            if (passwordInput && passwordStrength) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    const strength = calculatePasswordStrength(password);
                    
                    if (password.length > 0) {
                        passwordStrength.classList.add('active');
                        passwordStrength.innerHTML = `
                            <div class="strength-bar">
                                <div class="strength-fill ${strength.level}"></div>
                            </div>
                            <span class="strength-text ${strength.level}">${strength.text}</span>
                        `;
                    } else {
                        passwordStrength.classList.remove('active');
                        passwordStrength.innerHTML = '';
                    }
                });
            }

            // Prototype: Validate and show success modal
            document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const password = document.getElementById('password').value;
                const passwordConfirmation = document.getElementById('password_confirmation').value;
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalBtnContent = submitBtn.innerHTML;
                
                // Clear previous errors
                document.getElementById('confirmPasswordError').style.display = 'none';
                
                // Validate password strength
                const strength = validatePasswordStrength(password);
                
                if (!strength.isValid) {
                    // Don't show loading, password meter will indicate the issue
                    return;
                }
                
                if (password !== passwordConfirmation) {
                    showInlineError('confirmPasswordError', 'Passwords do not match.');
                    return;
                }
                
                // Show loading state only when validation passes
                submitBtn.disabled = true;
                if (window.showLoading) showLoading('Resetting password');
                submitBtn.innerHTML = `
                    <svg class="spinner" style="width: 20px; height: 20px; animation: spin 1s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke-width="4" stroke-opacity="0.25"/>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke-width="4" stroke-linecap="round"/>
                    </svg>
                    <span>Resetting Password...</span>
                `;
                
                // Simulate password reset (in production, this would be an actual API call)
                setTimeout(() => {
                    // Hide loading before showing success modal
                    if (window.hideLoading) hideLoading();

                    // Show success modal
                    const modal = document.getElementById('successModal');
                    const countdownEl = document.getElementById('countdown');
                    
                    modal.style.display = 'flex';
                    
                    let seconds = 3;
                    const interval = setInterval(() => {
                        seconds--;
                        countdownEl.textContent = seconds;
                        
                        if (seconds <= 0) {
                            clearInterval(interval);
                            if (window.showLoading) showLoading('Redirecting to login');
                            window.location.href = '{{ route("login") }}';
                        }
                    }, 1000);
                }, 1500); // Simulate API delay
            });
        });
    </script>

    <style>
        /* Success Modal Overlay */
        .success-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        /* Password Strength Meter Styles */
        .password-strength {
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .password-strength.active {
            opacity: 1;
            max-height: 60px;
        }
        
        .strength-bar {
            width: 100%;
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }
        
        .strength-fill {
            height: 100%;
            transition: all 0.4s ease;
            border-radius: 3px;
        }
        
        .strength-fill.weak {
            width: 33%;
            background: linear-gradient(90deg, #ef4444 0%, #f87171 100%);
        }
        
        .strength-fill.medium {
            width: 66%;
            background: linear-gradient(90deg, #fdc020 0%, #fbbf24 100%);
        }
        
        .strength-fill.strong {
            width: 100%;
            background: linear-gradient(90deg, #44a53e 0%, #5cb854 100%);
        }
        
        .strength-text {
            font-size: 0.875rem;
            font-weight: 600;
            display: block;
        }
        
        .strength-text.weak {
            color: #ef4444;
        }
        
        .strength-text.medium {
            color: #fdc020;
        }
        
        .strength-text.strong {
            color: #44a53e;
        }

        /* Spinner animation */
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .spinner {
            animation: spin 1s linear infinite;
        }
    </style>

</body>
</html>
