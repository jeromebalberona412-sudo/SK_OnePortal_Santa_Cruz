<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - SK OnePortal</title>
    @vite([
        'app/Modules/Authentication/assets/css/sign-in.css',
        'app/Modules/Authentication/assets/js/sign-in.js',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
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
                <p class="youth-tagline">Official Youth Portal � Santa Cruz, Laguna</p>
            </div>
        </div>

        <!-- Right Side - Reset Password Card -->
        <div class="youth-login-section">
            <div class="youth-login-card">
                <div class="card-header">
                    <h2 class="card-title">
                        Reset Your Password ??
                    </h2>
                    <p class="card-subtitle">Enter your new password below</p>
                </div>



                <!-- Reset Password Form -->
                <form class="youth-login-form" method="POST" action="{{ route('password.update') }}" id="resetPasswordForm" novalidate>
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
                            <button type="button" class="toggle-password pw-toggle-btn" aria-label="Toggle password visibility">
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
                        <ul class="password-rules" id="passwordRules" aria-live="polite">
                            <li id="rule-length">At least 8 characters</li>
                            <li id="rule-lowercase">At least one lowercase letter</li>
                            <li id="rule-uppercase">At least one uppercase letter</li>
                            <li id="rule-number">At least one number</li>
                            <li id="rule-special">At least one special character</li>
                        </ul>
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
                            <button type="button" class="toggle-password pw-toggle-btn" aria-label="Toggle password visibility">
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
                    </button>
                </form>

                <!-- Back to Login Link -->
                <div class="youth-register-section">
                    <p class="register-text">
                        Remember your password? 
                        <a href="{{ route('sign-in') }}" class="register-link">Back to Login</a>
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
                <h2 style="font-size: 2rem; font-weight: 800; color: #0450a8; margin-bottom: 1rem;">Password Reset Successful! ??</h2>
                <p style="font-size: 1.125rem; color: #334155; margin-bottom: 0.75rem; font-weight: 500;">
                    Your password has been reset successfully.
                </p>
                <p style="font-size: 1rem; color: #475569; margin-bottom: 2rem;">
                    Redirecting to sign in page in <span id="countdown">3</span> seconds...
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

            // Password rules validation
            function validatePasswordStrength(password) {
                const hasLowerCase = /[a-z]/.test(password);
                const hasUpperCase = /[A-Z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                const hasSpecial = /[^A-Za-z0-9]/.test(password);
                const hasMinLength = password.length >= 8;
                
                return {
                    isValid: hasUpperCase && hasNumber && hasMinLength && hasLowerCase && hasSpecial,
                    hasLowerCase,
                    hasUpperCase,
                    hasNumber,
                    hasSpecial,
                    hasMinLength
                };
            }

            // Password rules live checklist
            const passwordInput = document.getElementById('password');
            const passwordRules = document.getElementById('passwordRules');
            
            if (passwordInput && passwordRules) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    const state = validatePasswordStrength(password);
                    const rules = [
                        { id: 'rule-length', ok: state.hasMinLength },
                        { id: 'rule-lowercase', ok: state.hasLowerCase },
                        { id: 'rule-uppercase', ok: state.hasUpperCase },
                        { id: 'rule-number', ok: state.hasNumber },
                        { id: 'rule-special', ok: state.hasSpecial }
                    ];

                    passwordRules.classList.toggle('active', password.length > 0);
                    rules.forEach(rule => {
                        const node = document.getElementById(rule.id);
                        if (!node) return;
                        node.classList.toggle('ok', rule.ok);
                    });
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
                            if (window.showLoading) showLoading('Redirecting to sign in');
                            window.location.href = '{{ route("sign-in") }}';
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
        
        .password-rules {
            list-style: none;
            margin: 0.75rem 0 0;
            padding: 0.6rem 0.7rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .password-rules.active {
            opacity: 1;
            max-height: 220px;
        }

        .password-rules li {
            font-size: 0.875rem;
            color: #64748b;
            padding: 2px 0 2px 20px;
            position: relative;
        }

        .password-rules li::before {
            content: '�';
            position: absolute;
            left: 6px;
            color: #94a3b8;
        }

        .password-rules li.ok {
            color: #16a34a;
        }

        .password-rules li.ok::before {
            content: '?';
            color: #16a34a;
            font-weight: 700;
        }

        /* Spinner animation */
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .spinner {
            animation: spin 1s linear infinite;
        }
    </style>

    <script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
