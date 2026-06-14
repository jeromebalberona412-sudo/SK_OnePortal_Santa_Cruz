/**
 * SK Federation — Change Password (email step, then password + verification)
 */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('change-password-form');
    const emailInput = document.getElementById('cpEmail');
    const emailError = document.getElementById('cpEmailClientError');
    const stepEmail = document.getElementById('cpStepEmail');
    const stepPassword = document.getElementById('cpStepPassword');
    const continueBtn = document.getElementById('cpContinueBtn');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const passwordRules = document.getElementById('passwordRules');
    const clientError = document.getElementById('password-client-error');
    const submitBtn = document.getElementById('cpSubmitBtn');
    const btnText = document.getElementById('cpBtnText');

    if (!form || !emailInput) {
        return;
    }

    const maxLength = Number.parseInt(form.dataset.passwordMaxLength || '64', 10);
    const minLength = Number.parseInt(form.dataset.passwordMinLength || '12', 10);
    const accountEmail = (emailInput.defaultValue || emailInput.value || '').trim().toLowerCase();

    const hasPasswordErrors = form.querySelector('.is-invalid[name="password"], .sk-field-error:not([hidden])');
    if (hasPasswordErrors || document.getElementById('password')?.value) {
        showPasswordStep();
    }

    function showPasswordStep() {
        if (stepPassword) stepPassword.hidden = false;
        if (continueBtn) continueBtn.hidden = true;
        if (submitBtn) submitBtn.hidden = false;
        if (passwordInput) {
            passwordInput.required = true;
            passwordInput.focus();
        }
        if (confirmInput) confirmInput.required = true;
    }

    function showEmailError(message) {
        if (!emailError) return;
        emailError.textContent = message;
        emailError.hidden = false;
        emailInput.classList.add('is-invalid');
    }

    function clearEmailError() {
        if (emailError) {
            emailError.textContent = '';
            emailError.hidden = true;
        }
        emailInput.classList.remove('is-invalid');
    }

    function validateEmailStep() {
        clearEmailError();
        const value = emailInput.value.trim();
        if (!value) {
            showEmailError('Please enter your account email.');
            emailInput.focus();
            return false;
        }
        if (accountEmail && value.toLowerCase() !== accountEmail) {
            showEmailError('The email address does not match your account.');
            emailInput.focus();
            return false;
        }
        return true;
    }

    if (continueBtn) {
        continueBtn.addEventListener('click', function () {
            if (validateEmailStep()) {
                showPasswordStep();
            }
        });
    }

    emailInput.addEventListener('input', clearEmailError);

    function showSubmitLoading() {
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('is-loading');
        }
        if (btnText) {
            btnText.textContent = 'Sending verification...';
        }
        if (window.showLoading) {
            window.showLoading('Sending verification email...');
        }
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
        if (!passwordRules) return;

        const state = validatePasswordStrength(password);
        [
            { id: 'rule-length', ok: state.hasMinLength },
            { id: 'rule-lowercase', ok: state.hasLowerCase },
            { id: 'rule-uppercase', ok: state.hasUpperCase },
            { id: 'rule-number', ok: state.hasNumber },
            { id: 'rule-special', ok: state.hasSpecial },
        ].forEach(function (rule) {
            const node = document.getElementById(rule.id);
            if (node) node.classList.toggle('ok', rule.ok);
        });

        const showRules = password.length > 0 && !state.isValid;
        passwordRules.classList.toggle('active', showRules);
        passwordRules.hidden = !showRules;
    }

    function showClientError(message) {
        if (!clientError || !passwordInput) return;
        clientError.textContent = message;
        clientError.hidden = false;
        passwordInput.classList.add('is-invalid');
    }

    function clearClientError() {
        if (clientError) {
            clientError.textContent = '';
            clientError.hidden = true;
        }
        if (passwordInput) passwordInput.classList.remove('is-invalid');
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', function () {
            clearClientError();
            updatePasswordRules(this.value);
        });
    }

    if (confirmInput) {
        confirmInput.addEventListener('input', clearClientError);
    }

    form.addEventListener('submit', function (e) {
        if (!validateEmailStep()) {
            e.preventDefault();
            if (stepPassword) stepPassword.hidden = true;
            if (continueBtn) continueBtn.hidden = false;
            if (submitBtn) submitBtn.hidden = true;
            return;
        }

        if (stepPassword && stepPassword.hidden) {
            e.preventDefault();
            showPasswordStep();
            return;
        }

        if (!passwordInput) return;

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
            showClientError('Password must not exceed ' + maxLength + ' characters.');
            return;
        }

        if (confirmInput && passwordInput.value !== confirmInput.value) {
            e.preventDefault();
            showClientError('Passwords do not match.');
            confirmInput.classList.add('is-invalid');
            confirmInput.focus();
            return;
        }

        showSubmitLoading();
    });

    document.querySelectorAll('.pw-toggle-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = btn.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            btn.classList.toggle('pw-visible', show);
        });
    });
});
