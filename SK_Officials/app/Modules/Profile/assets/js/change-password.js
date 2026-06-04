/**
 * SK Officials — Change Password (first login / profile)
 */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('change-password-form');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const passwordRules = document.getElementById('passwordRules');
    const clientError = document.getElementById('password-client-error');

    if (!form || !passwordInput) {
        return;
    }

    const maxLength = Number.parseInt(form.dataset.passwordMaxLength || '64', 10);
    const minLength = 8;

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
            if (node) {
                node.classList.toggle('ok', rule.ok);
            }
        });

        const showRules = password.length > 0 && !state.isValid;
        passwordRules.classList.toggle('active', showRules);
        passwordRules.hidden = !showRules;
    }

    function showClientError(message) {
        if (!clientError) {
            return;
        }
        clientError.textContent = message;
        clientError.hidden = false;
        passwordInput.classList.add('is-invalid');
    }

    function clearClientError() {
        if (clientError) {
            clientError.textContent = '';
            clientError.hidden = true;
        }
        passwordInput.classList.remove('is-invalid');
    }

    passwordInput.addEventListener('input', function () {
        clearClientError();
        updatePasswordRules(this.value);
    });

    if (confirmInput) {
        confirmInput.addEventListener('input', clearClientError);
    }

    form.addEventListener('submit', function (e) {
        const strength = validatePasswordStrength(passwordInput.value);
        clearClientError();

        if (!strength.isValid) {
            e.preventDefault();
            showClientError('Please meet all password requirements.');
            updatePasswordRules(passwordInput.value);
            return;
        }

        if (passwordInput.value.length > maxLength) {
            e.preventDefault();
            showClientError(`Password must not exceed ${maxLength} characters.`);
            return;
        }

        if (confirmInput && passwordInput.value !== confirmInput.value) {
            e.preventDefault();
            showClientError('Passwords do not match.');
            confirmInput.classList.add('is-invalid');
            confirmInput.focus();
        }
    });

    document.querySelectorAll('.pw-toggle-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = btn.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (!input) {
                return;
            }
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            btn.classList.toggle('pw-visible', show);
        });
    });
});
