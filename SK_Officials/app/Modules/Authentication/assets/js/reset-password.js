document.addEventListener('DOMContentLoaded', function () {
    const resetPasswordForm = document.getElementById('reset-password-form');
    const newPasswordInput = document.getElementById('new-password');
    const confirmPasswordInput = document.getElementById('confirm-password');
    const newPasswordError = document.getElementById('new-password-error');
    const confirmPasswordError = document.getElementById('confirm-password-error');
    const resetBtn = document.getElementById('resetBtn');
    const resetBtnLabel = resetBtn?.querySelector('span');
    const passwordRules = document.getElementById('passwordRules');

    if (!resetPasswordForm) {
        return;
    }

    const maxLength = Number.parseInt(resetPasswordForm.dataset.passwordMaxLength || '64', 10);
    const minLength = Number.parseInt(resetPasswordForm.dataset.passwordMinLength || '8', 10);

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

    function validatePasswordStrength(password) {
        const hasLowerCase = /[a-z]/.test(password);
        const hasUpperCase = /[A-Z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSpecial = /[^A-Za-z0-9]/.test(password);
        const hasMinLength = password.length >= minLength;

        return {
            isValid: hasMinLength && hasLowerCase && hasUpperCase && hasNumber && hasSpecial,
            hasLowerCase,
            hasUpperCase,
            hasNumber,
            hasSpecial,
            hasMinLength,
        };
    }

    function updatePasswordRules(password) {
        if (!passwordRules) {
            return;
        }

        const state = validatePasswordStrength(password);
        const rules = [
            { id: 'rule-length', ok: state.hasMinLength },
            { id: 'rule-lowercase', ok: state.hasLowerCase },
            { id: 'rule-uppercase', ok: state.hasUpperCase },
            { id: 'rule-number', ok: state.hasNumber },
            { id: 'rule-special', ok: state.hasSpecial },
        ];

        rules.forEach((rule) => {
            const node = document.getElementById(rule.id);
            if (!node) {
                return;
            }
            node.classList.toggle('ok', rule.ok);
        });

        const showRules = password.length > 0 && !state.isValid;
        passwordRules.classList.toggle('active', showRules);
        passwordRules.hidden = !showRules;
    }

    document.querySelectorAll('.toggle-password').forEach((button) => {
        button.addEventListener('click', function (event) {
            event.preventDefault();

            const wrapper = this.closest('.password-wrapper');
            const input = wrapper?.querySelector('.password-input');
            const eyeOpen = this.querySelector('.eye-open');
            const eyeClosed = this.querySelector('.eye-closed');

            if (!input || !eyeOpen || !eyeClosed) {
                return;
            }

            const showPlain = input.type === 'password';
            input.type = showPlain ? 'text' : 'password';
            eyeOpen.style.display = showPlain ? 'none' : 'flex';
            eyeClosed.style.display = showPlain ? 'flex' : 'none';
        });
    });

    newPasswordInput.addEventListener('input', function () {
        clearError(this, newPasswordError);
        updatePasswordRules(this.value);
    });

    confirmPasswordInput.addEventListener('input', function () {
        clearError(this, confirmPasswordError);
    });

    resetPasswordForm.addEventListener('submit', function (e) {
        const strength = validatePasswordStrength(newPasswordInput.value);

        let isValid = true;

        clearError(newPasswordInput, newPasswordError);
        clearError(confirmPasswordInput, confirmPasswordError);

        if (!strength.isValid) {
            e.preventDefault();
            showError(newPasswordInput, newPasswordError, 'Please meet all password requirements.');
            updatePasswordRules(newPasswordInput.value);
            isValid = false;
        } else if (newPasswordInput.value.length > maxLength) {
            e.preventDefault();
            showError(newPasswordInput, newPasswordError, `Password must not exceed ${maxLength} characters.`);
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
