/**
 * SK Officials — Change Password / Set Password
 */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('change-password-form');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const passwordRules = document.getElementById('passwordRules');
    const clientError = document.getElementById('password-client-error');
    const matchMsg = document.getElementById('password-match-msg');
    const submitBtn = document.getElementById('cpSubmitBtn');
    const btnText = document.getElementById('cpBtnText');

    if (!form || !passwordInput) {
        return;
    }

    const maxLength = Number.parseInt(form.dataset.passwordMaxLength || '64', 10);
    const minLength = 8;

    function showSubmitLoading() {
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('is-loading');
        }
        if (btnText) {
            btnText.textContent = form.dataset.loadingText || 'Changing password...';
        }
    }

    function validatePasswordStrength(password) {
        return {
            hasMinLength: password.length >= minLength,
            hasLowerCase: /[a-z]/.test(password),
            hasUpperCase: /[A-Z]/.test(password),
            hasNumber: /[0-9]/.test(password),
            hasSpecial: /[^A-Za-z0-9]/.test(password),
        };
    }

    function isStrong(state) {
        return state.hasMinLength && state.hasLowerCase && state.hasUpperCase && state.hasNumber && state.hasSpecial;
    }

    function setRuleMark(node, ok) {
        node.classList.toggle('ok', ok);
        const mark = node.querySelector('.rule-mark');
        if (mark) {
            mark.textContent = ok ? '✓' : '✕';
        }
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
                setRuleMark(node, password.length > 0 && rule.ok);
            }
        });

        const hideRules = password.length === 0 || isStrong(state);
        passwordRules.hidden = hideRules;
        passwordRules.classList.toggle('hidden-rules', hideRules);
    }

    function updateMatchMessage() {
        if (!matchMsg || !confirmInput) {
            return;
        }

        const password = passwordInput.value;
        const confirm = confirmInput.value;

        if (!confirm) {
            matchMsg.hidden = true;
            matchMsg.textContent = '';
            matchMsg.classList.remove('is-error', 'is-ok');
            confirmInput.classList.remove('is-invalid');
            return;
        }

        if (password === confirm) {
            matchMsg.hidden = true;
            matchMsg.textContent = '';
            matchMsg.classList.remove('is-error', 'is-ok');
            confirmInput.classList.remove('is-invalid');
            return;
        }

        matchMsg.hidden = false;
        matchMsg.textContent = 'Passwords do not match.';
        matchMsg.classList.add('is-error');
        matchMsg.classList.remove('is-ok');
        confirmInput.classList.add('is-invalid');
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
        updateMatchMessage();
    });

    if (confirmInput) {
        confirmInput.addEventListener('input', function () {
            clearClientError();
            updateMatchMessage();
        });
    }

    form.addEventListener('submit', function (e) {
        const strength = validatePasswordStrength(passwordInput.value);
        clearClientError();
        updatePasswordRules(passwordInput.value);
        updateMatchMessage();

        if (!isStrong(strength)) {
            e.preventDefault();
            showClientError('Please meet all password requirements.');
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
            confirmInput.focus();
            return;
        }

        showSubmitLoading();
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
