// Toggle new password visibility
function toggleNewPassword() {
    const input = document.getElementById('new-password');
    const eyeOpen = document.getElementById('newEyeOpen');
    const eyeClosed = document.getElementById('newEyeClosed');
    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.style.display = 'none';
        eyeClosed.style.display = 'block';
    } else {
        input.type = 'password';
        eyeOpen.style.display = 'block';
        eyeClosed.style.display = 'none';
    }
}

// Toggle confirm password visibility
function toggleConfirmPassword() {
    const input = document.getElementById('confirm-password');
    const eyeOpen = document.getElementById('confirmEyeOpen');
    const eyeClosed = document.getElementById('confirmEyeClosed');
    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.style.display = 'none';
        eyeClosed.style.display = 'block';
    } else {
        input.type = 'password';
        eyeOpen.style.display = 'block';
        eyeClosed.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const resetPasswordForm = document.getElementById('reset-password-form');
    const newPasswordInput = document.getElementById('new-password');
    const confirmPasswordInput = document.getElementById('confirm-password');
    const newPasswordError = document.getElementById('new-password-error');
    const confirmPasswordError = document.getElementById('confirm-password-error');

    if (!resetPasswordForm) return;

    function clearError(input, errorElement) {
        input.classList.remove('is-invalid');
        errorElement.hidden = true;
        errorElement.textContent = '';
    }

    function showError(input, errorElement, message) {
        input.classList.add('is-invalid');
        errorElement.textContent = message;
        errorElement.hidden = false;
    }

    newPasswordInput.addEventListener('input', function() {
        clearError(this, newPasswordError);
    });

    confirmPasswordInput.addEventListener('input', function() {
        clearError(this, confirmPasswordError);
    });

    resetPasswordForm.addEventListener('submit', function(e) {
        const minLength = Number.parseInt(resetPasswordForm.dataset.passwordMinLength || '12', 10);
        const maxLength = Number.parseInt(resetPasswordForm.dataset.passwordMaxLength || '64', 10);
        const hasLetters = /[A-Za-z]/.test(newPasswordInput.value);
        const hasNumbers = /\d/.test(newPasswordInput.value);
        const hasSymbols = /[^A-Za-z0-9]/.test(newPasswordInput.value);
        
        let isValid = true;

        clearError(newPasswordInput, newPasswordError);
        clearError(confirmPasswordInput, confirmPasswordError);

        if (newPasswordInput.value.length < minLength) {
            e.preventDefault();
            showError(newPasswordInput, newPasswordError, `Password must be at least ${minLength} characters.`);
            isValid = false;
        } else if (newPasswordInput.value.length > maxLength) {
            e.preventDefault();
            showError(newPasswordInput, newPasswordError, `Password must not exceed ${maxLength} characters.`);
            isValid = false;
        } else if (!(hasLetters && hasNumbers && hasSymbols)) {
            e.preventDefault();
            showError(newPasswordInput, newPasswordError, 'Password must include letters, numbers, and symbols.');
            isValid = false;
        }

        if (confirmPasswordInput.value !== newPasswordInput.value) {
            e.preventDefault();
            showError(confirmPasswordInput, confirmPasswordError, 'Passwords do not match.');
            isValid = false;
        }

        if (isValid) {
            LoadingScreen.show('Resetting Password', 'Please wait...');
        }
    });

    document.querySelector('a[href*="login"]')?.addEventListener('click', function(e) {
        e.preventDefault();
        LoadingScreen.show('Redirecting', 'Taking you to login...');
        setTimeout(() => {
            window.location.href = this.href;
        }, 300);
    });
});
