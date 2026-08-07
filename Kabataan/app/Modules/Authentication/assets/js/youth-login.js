/**
 * SK OnePortal - Youth Login JavaScript
 * Modern interactive features for 2026
 */

document.addEventListener('DOMContentLoaded', function() {

    // ============================================
    // Password Toggle Functionality (Disabled - handled in individual pages)
    // ============================================
    // Note: Password toggle is now handled directly in each blade file
    // to avoid conflicts between login, register, and reset-password pages

    // ============================================
    // Form Validation Enhancement (Only for login page)
    // ============================================
    const loginForm = document.querySelector('.youth-login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const isResetPasswordPage = document.getElementById('resetPasswordForm');
    const turnstileContainer = document.getElementById('turnstile-container');
    const turnstileWidget = document.getElementById('turnstile-widget');
    const turnstileError = document.getElementById('turnstile-error');
    const submitBtn = document.getElementById('loginBtn');

    // Turnstile state
    let turnstileLoaded = false;
    let turnstileWidgetId = null;

    // Load Turnstile widget dynamically
    function loadTurnstileWidget() {
        if (turnstileLoaded || typeof turnstile === 'undefined') return;

        turnstileLoaded = true;
        turnstileContainer.style.display = 'block';

        turnstileWidgetId = turnstile.render('#turnstile-widget', {
            sitekey: window.turnstileSiteKey,
            callback: function(token) {
                // Turnstile completed successfully
                clearTurnstileError();
                submitLoginForm();
            },
            'error-callback': function() {
                // Turnstile failed
                showTurnstileError('Security verification failed. Please try again.');
                resetFormState();
            },
            'expired-callback': function() {
                // Turnstile expired
                showTurnstileError('Security verification expired. Please try again.');
                resetFormState();
            }
        });
    }

    function resetFormState() {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span>Login</span>';
        }
        if (emailInput) emailInput.disabled = false;
        if (passwordInput) passwordInput.disabled = false;
    }

    function submitLoginForm() {
        // Create hidden input for Turnstile token
        let tokenInput = document.querySelector('input[name="cf-turnstile-response"]');
        if (!tokenInput) {
            tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = 'cf-turnstile-response';
            loginForm.appendChild(tokenInput);
        }

        // Get the token from Turnstile
        const response = turnstile.getResponse(turnstileWidgetId);
        tokenInput.value = response;

        // Disable form elements and show loading state
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="spinner" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <circle class="spinner-circle" cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span>Authenticating...</span>
            `;
        }

        if (emailInput) emailInput.disabled = true;
        if (passwordInput) passwordInput.disabled = true;
        if (turnstileContainer) {
            turnstileContainer.style.pointerEvents = 'none';
            turnstileContainer.style.opacity = '0.5';
        }

        // Submit the form
        loginForm.submit();
    }

    function showTurnstileError(message) {
        if (turnstileError) {
            turnstileError.textContent = message;
            turnstileError.hidden = false;
            turnstileError.style.display = 'block';
        }
    }

    function clearTurnstileError() {
        if (turnstileError) {
            turnstileError.hidden = true;
            turnstileError.style.display = 'none';
        }
    }

    // Only apply validation if NOT on reset password page
    if (loginForm && !isResetPasswordPage) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();

            let isValid = true;

            // Clear all previous errors
            clearAllErrors();
            clearTurnstileError();

            // Email validation
            if (emailInput && !isValidEmail(emailInput.value.trim())) {
                isValid = false;
                showInputError(emailInput, 'Invalid Email or Password');
            }

            // Password validation for prototype login
            if (passwordInput && passwordInput.value.trim().length === 0) {
                isValid = false;
                showInputError(passwordInput, 'Invalid Email or Password');
            }

            if (!isValid) return false;

            // If Turnstile is enabled and not yet loaded, load it now
            if (turnstileContainer && window.turnstileSiteKey && !turnstileLoaded) {
                // Show loading state while preparing Turnstile
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span>Loading Security Check...</span>';
                }
                if (emailInput) emailInput.disabled = true;
                if (passwordInput) passwordInput.disabled = true;

                loadTurnstileWidget();

                // Reset button state after widget loads
                setTimeout(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<span>Login</span>';
                    }
                    if (emailInput) emailInput.disabled = false;
                    if (passwordInput) passwordInput.disabled = false;
                }, 500);

                return false;
            }

            // If Turnstile is already loaded but not completed, show error
            if (turnstileContainer && turnstileLoaded) {
                const response = turnstile.getResponse(turnstileWidgetId);
                if (!response) {
                    showTurnstileError('Please complete the security verification.');
                    return false;
                }
            }

            // If Turnstile is not enabled, submit directly
            if (!turnstileContainer) {
                // Original loading state logic
                const emailVal = emailInput ? emailInput.value.trim() : '';
                const passwordVal = passwordInput ? passwordInput.value.trim() : '';
                if (!emailVal || !isValidEmail(emailVal) || !passwordVal) {
                    return;
                }

                if (submitBtn && !submitBtn.disabled) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <svg class="spinner" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <circle class="spinner-circle" cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <span>Authenticating...</span>
                    `;
                }
                loginForm.submit();
            }
        });

        // Real-time validation on blur
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                if (this.value.trim() && !isValidEmail(this.value.trim())) {
                    showInputError(this, 'Invalid Email or Password');
                }
            });

            emailInput.addEventListener('input', function() {
                clearInputError(this);
            });
        }

        if (passwordInput) {
            passwordInput.addEventListener('blur', function() {
                if (this.value.trim().length === 0) {
                    showInputError(this, 'Invalid Email or Password');
                }
            });

            passwordInput.addEventListener('input', function() {
                clearInputError(this);
            });
        }
    }
    
    // ============================================
    // Input Animation on Focus
    // ============================================
    const allInputs = document.querySelectorAll('.youth-input');
    
    allInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('input-focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('input-focused');
        });
    });
    
    // ============================================
    // Alert Auto-dismiss
    // ============================================
    const alerts = document.querySelectorAll('.youth-alert');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.animation = 'alertSlideOut 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards';
            setTimeout(() => {
                alert.remove();
            }, 400);
        }, 5000);
    });
    
    // ============================================
    // Helper Functions
    // ============================================
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function showInputError(input, message) {
        // Add error class to input
        input.classList.add('input-error');
        input.style.borderColor = '#ef4444';
        
        // Remove existing error message
        const existingError = input.closest('.youth-form-group').querySelector('.input-error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Add error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'input-error-message';
        errorDiv.textContent = message;
        errorDiv.style.cssText = `
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            font-weight: 500;
            animation: errorSlideIn 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        `;
        
        // Add error icon
        const errorIcon = document.createElement('svg');
        errorIcon.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="flex-shrink: 0;">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
        `;
        errorDiv.insertBefore(errorIcon.firstElementChild, errorDiv.firstChild);
        
        // Insert error message after the input or password wrapper
        const wrapper = input.closest('.password-wrapper') || input;
        wrapper.parentElement.appendChild(errorDiv);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (errorDiv && errorDiv.parentElement) {
                errorDiv.style.animation = 'errorSlideOut 0.3s ease forwards';
                setTimeout(() => {
                    if (errorDiv && errorDiv.parentElement) {
                        errorDiv.remove();
                    }
                }, 300);
            }
        }, 5000);
    }
    
    function clearInputError(input) {
        input.classList.remove('input-error');
        input.style.borderColor = '';
        const errorMessage = input.closest('.youth-form-group').querySelector('.input-error-message');
        if (errorMessage) {
            errorMessage.style.animation = 'errorSlideOut 0.3s ease forwards';
            setTimeout(() => {
                if (errorMessage && errorMessage.parentElement) {
                    errorMessage.remove();
                }
            }, 300);
        }
    }
    
    function clearAllErrors() {
        const allInputs = document.querySelectorAll('.youth-input');
        allInputs.forEach(input => {
            input.classList.remove('input-error');
            input.style.borderColor = '';
        });
        
        const allErrors = document.querySelectorAll('.input-error-message');
        allErrors.forEach(error => {
            error.remove();
        });
    }
    
    // ============================================
    // Keyboard Shortcuts
    // ============================================
    document.addEventListener('keydown', function(e) {
        // Alt + L to focus email input
        if (e.altKey && e.key === 'l') {
            e.preventDefault();
            if (emailInput) emailInput.focus();
        }
    });

    // Add spinner CSS
    if (!document.getElementById('youth-login-spinner-style')) {
        const style = document.createElement('style');
        style.id = 'youth-login-spinner-style';
        style.textContent = `
            @keyframes youthLoginSpin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
            .youth-submit-btn .spinner {
                display: inline-block;
                vertical-align: middle;
                transform-origin: center center;
                animation: youthLoginSpin 0.9s linear infinite;
            }
        `;
        document.head.appendChild(style);
    }

    // Expose reset function for error handling
    window.resetTurnstileState = function() {
        if (turnstileLoaded && turnstileWidgetId && typeof turnstile !== 'undefined') {
            turnstile.reset(turnstileWidgetId);
        }
        if (turnstileContainer) {
            turnstileContainer.style.display = 'none';
        }
        turnstileLoaded = false;
        turnstileWidgetId = null;
        resetFormState();
    };
});

// ============================================
// Alert Slide Out Animation
// ============================================
const style = document.createElement('style');
style.textContent = `
    @keyframes alertSlideOut {
        to {
            opacity: 0;
            transform: translateY(-10px);
        }
    }
    
    @keyframes errorSlideIn {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes errorSlideOut {
        to {
            opacity: 0;
            transform: translateY(-8px);
        }
    }
    
    .input-error {
        border-color: #ef4444 !important;
        animation: shake 0.4s ease;
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
        20%, 40%, 60%, 80% { transform: translateX(4px); }
    }
`;
document.head.appendChild(style);
