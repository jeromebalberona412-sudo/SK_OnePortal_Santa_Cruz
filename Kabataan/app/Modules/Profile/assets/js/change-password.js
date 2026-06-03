/**
 * Change Password JavaScript
 * Handles password validation, toggle visibility, and form submission
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Password toggle functionality
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const wrapper = this.closest('.password-wrapper');
            const input = wrapper.querySelector('.password-input');
            const eyeOpen = this.querySelector('.eye-open');
            const eyeClosed = this.querySelector('.eye-closed');
            
            if (input.type === 'password') {
                input.type = 'text';
                // Hide eye-open with animation
                eyeOpen.style.opacity = '0';
                eyeOpen.style.transform = 'scale(0.8) rotate(10deg)';
                setTimeout(() => {
                    eyeOpen.style.display = 'none';
                }, 200);
                
                // Show eye-closed with animation
                eyeClosed.style.display = 'block';
                setTimeout(() => {
                    eyeClosed.style.opacity = '1';
                    eyeClosed.style.transform = 'scale(1) rotate(0deg)';
                }, 10);
            } else {
                input.type = 'password';
                // Hide eye-closed with animation
                eyeClosed.style.opacity = '0';
                eyeClosed.style.transform = 'scale(0.8) rotate(-10deg)';
                setTimeout(() => {
                    eyeClosed.style.display = 'none';
                }, 200);
                
                // Show eye-open with animation
                eyeOpen.style.display = 'block';
                setTimeout(() => {
                    eyeOpen.style.opacity = '1';
                    eyeOpen.style.transform = 'scale(1) rotate(0deg)';
                }, 10);
            }
        });
    });

    // Password strength validation
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
    const passwordInput = document.getElementById('new_password');
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

    // Form validation and submission
    const changePasswordForm = document.getElementById('changePasswordForm');
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const passwordConfirmation = document.getElementById('password_confirmation').value;
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // Clear previous errors
            const errorElement = document.getElementById('confirmPasswordError');
            if (errorElement) {
                errorElement.style.display = 'none';
            }
            
            // Validate password strength
            const strength = validatePasswordStrength(newPassword);
            
            if (!strength.isValid) {
                alert('Password does not meet the requirements. Please ensure it has at least 8 characters, one uppercase letter, one lowercase letter, one number, and one special character.');
                return;
            }
            
            if (newPassword !== passwordConfirmation) {
                if (errorElement) {
                    errorElement.textContent = 'Passwords do not match.';
                    errorElement.style.display = 'block';
                }
                return;
            }
            
            if (currentPassword === newPassword) {
                alert('New password must be different from current password.');
                return;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            if (window.showLoading) {
                window.showLoading('Changing password...');
            }
            submitBtn.innerHTML = `
                <svg class="spinner" style="width: 20px; height: 20px; animation: spin 1s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="12" cy="12" r="10" stroke-width="4" stroke-opacity="0.25"/>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke-width="4" stroke-linecap="round"/>
                </svg>
                <span>Changing Password...</span>
            `;
            
            // Submit form
            this.submit();
        });
    }
});
