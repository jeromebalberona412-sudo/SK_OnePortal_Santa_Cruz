function togglePasswordField(inputId, openId, closedId) {
    const input = document.getElementById(inputId);
    const eyeOpen = document.getElementById(openId);
    const eyeClosed = document.getElementById(closedId);
    if (!input || !eyeOpen || !eyeClosed) return;

    const showPlain = input.type === 'password';
    input.type = showPlain ? 'text' : 'password';
    eyeOpen.style.display = showPlain ? 'none' : 'flex';
    eyeClosed.style.display = showPlain ? 'flex' : 'none';
}

function toggleNewPassword() {
    togglePasswordField('new-password', 'newEyeOpen', 'newEyeClosed');
}

function toggleConfirmPassword() {
    togglePasswordField('confirm-password', 'confirmEyeOpen', 'confirmEyeClosed');
}

document.addEventListener('DOMContentLoaded', function () {
    const resetPasswordForm = document.getElementById('reset-password-form');
    const newPasswordInput = document.getElementById('new-password');
    const confirmPasswordInput = document.getElementById('confirm-password');
    const newPasswordError = document.getElementById('new-password-error');
    const confirmPasswordError = document.getElementById('confirm-password-error');
    const resetBtn = document.getElementById('resetBtn');
    const resetBtnLabel = resetBtn?.querySelector('span');

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

    newPasswordInput.addEventListener('input', function () {
        clearError(this, newPasswordError);
    });

    confirmPasswordInput.addEventListener('input', function () {
        clearError(this, confirmPasswordError);
    });

    resetPasswordForm.addEventListener('submit', function (e) {
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

        if (isValid && resetBtn && resetBtnLabel) {
            resetBtn.disabled = true;
            resetBtnLabel.textContent = 'Resetting...';
        }
    });
});
