// Password Toggle Functionality — same animation as Reset Password page
var _fedEyeTransitionSet = false;
function togglePassword() {
    var passwordInput = document.getElementById('password');
    var eyeIcon    = document.getElementById('eye-icon');
    var eyeOffIcon = document.getElementById('eye-off-icon');
    var btn        = document.querySelector('.password-toggle');

    // Set transition once on first click
    if (!_fedEyeTransitionSet) {
        eyeIcon.style.transition    = 'opacity 0.2s ease, transform 0.2s ease';
        eyeOffIcon.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
        _fedEyeTransitionSet = true;
    }

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        if (btn) btn.setAttribute('aria-label', 'Hide password');

        // Animate out eye-open
        eyeIcon.style.opacity   = '0';
        eyeIcon.style.transform = 'scale(0.8) rotate(10deg)';
        setTimeout(function() { eyeIcon.style.display = 'none'; }, 200);

        // Animate in eye-off
        eyeOffIcon.style.display = 'block';
        setTimeout(function() {
            eyeOffIcon.style.opacity   = '1';
            eyeOffIcon.style.transform = 'scale(1) rotate(0deg)';
        }, 10);
    } else {
        passwordInput.type = 'password';
        if (btn) btn.setAttribute('aria-label', 'Show password');

        // Animate out eye-off
        eyeOffIcon.style.opacity   = '0';
        eyeOffIcon.style.transform = 'scale(0.8) rotate(-10deg)';
        setTimeout(function() { eyeOffIcon.style.display = 'none'; }, 200);

        // Animate in eye-open
        eyeIcon.style.display = 'block';
        setTimeout(function() {
            eyeIcon.style.opacity   = '1';
            eyeIcon.style.transform = 'scale(1) rotate(0deg)';
        }, 10);
    }
}

// Form validation feedback with HTML5 constraint validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.login-form');

    if (form) {
        const email = document.getElementById('email');
        const password = document.getElementById('password');

        // Set custom validation messages
        if (email) {
            email.addEventListener('invalid', function(e) {
                if (this.validity.valueMissing) {
                    this.setCustomValidity('Please enter your email address');
                } else if (this.validity.typeMismatch) {
                    this.setCustomValidity('Please enter a valid email address with @');
                } else if (this.validity.tooLong) {
                    this.setCustomValidity('Email must not exceed 150 characters');
                } else {
                    this.setCustomValidity('Please enter a valid email address');
                }
            });

            email.addEventListener('input', function() {
                this.setCustomValidity('');
            });
        }

        if (password) {
            password.addEventListener('invalid', function(e) {
                if (this.validity.valueMissing) {
                    this.setCustomValidity('Please enter your password');
                } else if (this.validity.tooShort) {
                    this.setCustomValidity('Password must be at least 8 characters');
                } else if (this.validity.tooLong) {
                    this.setCustomValidity('Password must not exceed 64 characters');
                } else {
                    this.setCustomValidity('Please enter a valid password');
                }
            });

            password.addEventListener('input', function() {
                this.setCustomValidity('');
            });
        }

        // Let browser handle validation with constraint validation API
        form.addEventListener('submit', function(e) {
            // Browser will automatically show validation tooltips
            // Only prevent if form is invalid
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }

    // Auto-dismiss error alerts after 5 seconds
    const errorAlert = document.getElementById('error-alert');
    if (errorAlert) {
        setTimeout(function() {
            errorAlert.style.transition = 'opacity 0.3s ease';
            errorAlert.style.opacity = '0';
            setTimeout(function() {
                errorAlert.remove();
            }, 300);
        }, 5000);
    }

    // Auto-dismiss inline error messages after 5 seconds
    const inlineErrors = document.querySelectorAll('.invalid-feedback');
    if (inlineErrors.length > 0) {
        setTimeout(function() {
            inlineErrors.forEach(function(error) {
                error.classList.add('fade-out');
                setTimeout(function() {
                    error.remove();
                    // Remove is-invalid class from input
                    const input = error.previousElementSibling;
                    if (input && input.classList.contains('is-invalid')) {
                        input.classList.remove('is-invalid');
                    }
                }, 300);
            });
        }, 5000);
    }
});

