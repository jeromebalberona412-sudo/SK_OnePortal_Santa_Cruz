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
    
    // Only apply validation if NOT on reset password page
    if (loginForm && !isResetPasswordPage) {
        loginForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear all previous errors
            clearAllErrors();
            
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
            
            if (!isValid) {
                e.preventDefault();
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
    
    // ============================================
    // Loading State on Submit (Only for login page)
    // Only animate if inputs are valid (non-empty)
    // ============================================
    if (loginForm && !document.getElementById('resetPasswordForm')) {
        loginForm.addEventListener('submit', function(e) {
            // Do not show loading if validation will block the submit
            const emailVal    = emailInput    ? emailInput.value.trim()    : '';
            const passwordVal = passwordInput ? passwordInput.value.trim() : '';
            if (!emailVal || !isValidEmail(emailVal) || !passwordVal) {
                return; // validation listener already called e.preventDefault()
            }

            const submitBtn = this.querySelector('.youth-submit-btn');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg class="spinner" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke-width="4" stroke-opacity="0.25"/>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke-width="4" stroke-linecap="round"/>
                    </svg>
                    <span>Logging in...</span>
                `;

                const style = document.createElement('style');
                style.textContent = `
                    @keyframes spin { to { transform: rotate(360deg); } }
                    .spinner { animation: spin 1s linear infinite; }
                `;
                document.head.appendChild(style);
            }
        });
    }
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
