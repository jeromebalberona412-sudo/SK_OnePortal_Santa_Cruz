/**
 * Admin Forgot Password - Form Handling
 */
document.addEventListener('DOMContentLoaded', function() {
    const forgotPasswordForm = document.querySelector('.forgot-password-form');
    const sendResetBtn = document.getElementById('sendResetBtn');
    const emailInput = document.getElementById('email');
    
    if (forgotPasswordForm) {
        // Form submission handler
        forgotPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = emailInput.value.trim();
            
            if (!email) {
                showError('Please enter your email address');
                emailInput.focus();
                return;
            }
            
            if (!isValidEmail(email)) {
                showError('Please enter a valid email address');
                emailInput.focus();
                return;
            }
            
            showLoadingState();
            forgotPasswordForm.submit();
        });
        
        // Enter key support for email input
        if (emailInput) {
            emailInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (sendResetBtn) {
                        sendResetBtn.click();
                    }
                }
            });
        }
    }
});

/**
 * Validate email format
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Show error message
 */
function showError(message) {
    // Remove existing error messages
    const existingError = document.querySelector('.alert-danger');
    if (existingError) {
        existingError.remove();
    }
    
    // Create and show new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger';
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-triangle"></i>
        <span>${message}</span>
    `;
    
    const form = document.querySelector('.forgot-password-form');
    if (form) {
        form.insertBefore(errorDiv, form.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 5000);
    }
}

/**
 * Show loading state on button
 */
function showLoadingState() {
    const sendResetBtn = document.getElementById('sendResetBtn');
    if (sendResetBtn) {
        sendResetBtn.disabled = true;
        sendResetBtn.innerHTML = `
            <i class="fas fa-spinner fa-spin"></i>
            <span>Sending...</span>
        `;
    }
}
