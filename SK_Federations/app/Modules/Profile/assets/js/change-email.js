document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('ceForm');
    const submitBtn = document.getElementById('ceSubmitBtn');
    const btnText = document.getElementById('ceBtnText');
    const passwordInput = document.getElementById('cePassword');
    const passwordRules = document.getElementById('cePasswordRules');

    if (!form) return;

    // Password toggle functionality
    document.querySelectorAll('.pw-toggle-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const target = document.getElementById(btn.dataset.target || '');
            if (!target) return;

            const eyeOpen = btn.querySelector('.pw-eye-show');
            const eyeClosed = btn.querySelector('.pw-eye-hide');
            const isPassword = target.type === 'password';

            target.type = isPassword ? 'text' : 'password';

            if (eyeOpen && eyeClosed) {
                eyeOpen.style.display = isPassword ? 'none' : 'block';
                eyeClosed.style.display = isPassword ? 'block' : 'none';
            }
        });
    });

    // Password strength validation
    function validatePassword(password) {
        const rules = {
            length: password.length >= 12,
            lowercase: /[a-z]/.test(password),
            uppercase: /[A-Z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };
        return rules;
    }

    function updatePasswordRules(password) {
        if (!passwordRules) return;

        const rules = validatePassword(password);

        // Show rules when typing
        if (password.length > 0) {
            passwordRules.hidden = false;
        } else {
            passwordRules.hidden = true;
        }

        // Update each rule's visual state
        const ruleElements = {
            'ce-rule-length': rules.length,
            'ce-rule-lowercase': rules.lowercase,
            'ce-rule-uppercase': rules.uppercase,
            'ce-rule-number': rules.number,
            'ce-rule-special': rules.special
        };

        for (const [ruleId, isValid] of Object.entries(ruleElements)) {
            const ruleElement = document.getElementById(ruleId);
            if (ruleElement) {
                if (isValid) {
                    ruleElement.classList.add('valid');
                } else {
                    ruleElement.classList.remove('valid');
                }
            }
        }
    }

    // Real-time password validation
    if (passwordInput) {
        passwordInput.addEventListener('input', () => {
            updatePasswordRules(passwordInput.value);
        });

        passwordInput.addEventListener('blur', () => {
            if (passwordInput.value.length > 0) {
                passwordRules.hidden = false;
            }
        });
    }

    function setFieldError(inputId, errorId, msg) {
        const input = document.getElementById(inputId);
        const err = document.getElementById(errorId);
        if (input) input.classList.add('is-invalid');
        if (err) {
            err.textContent = msg;
            err.hidden = false;
        }
    }

    function clearFieldError(inputId, errorId) {
        const input = document.getElementById(inputId);
        const err = document.getElementById(errorId);
        if (input) input.classList.remove('is-invalid');
        if (err) {
            err.textContent = '';
            err.hidden = true;
        }
    }

    form.addEventListener('submit', (e) => {
        const currentEmail = document.getElementById('ceCurrentEmail')?.value.trim() || '';
        const newEmail = document.getElementById('ceNewEmail')?.value.trim() || '';
        const password = document.getElementById('cePassword')?.value || '';

        let valid = true;

        clearFieldError('ceCurrentEmail', 'ceCurrentEmailError');
        clearFieldError('ceNewEmail', 'ceNewEmailError');
        clearFieldError('cePassword', 'cePasswordError');

        if (!currentEmail) {
            setFieldError('ceCurrentEmail', 'ceCurrentEmailError', 'Current email is required.');
            valid = false;
        }

        if (!newEmail) {
            setFieldError('ceNewEmail', 'ceNewEmailError', 'New email address is required.');
            valid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(newEmail)) {
            setFieldError('ceNewEmail', 'ceNewEmailError', 'Please enter a valid email address.');
            valid = false;
        } else if (newEmail.toLowerCase() === currentEmail.toLowerCase()) {
            setFieldError('ceNewEmail', 'ceNewEmailError', 'New email must be different from current email.');
            valid = false;
        }

        // Password validation with strength check
        if (!password) {
            setFieldError('cePassword', 'cePasswordError', 'Current password is required.');
            valid = false;
        } else {
            const rules = validatePassword(password);
            if (!rules.length) {
                setFieldError('cePassword', 'cePasswordError', 'Password must be at least 12 characters long.');
                valid = false;
            } else if (!rules.lowercase || !rules.uppercase || !rules.number || !rules.special) {
                setFieldError('cePassword', 'cePasswordError', 'Password must contain uppercase, lowercase, number, and special character.');
                valid = false;
            }
        }

        if (!valid) {
            e.preventDefault();
            return;
        }

        if (submitBtn) submitBtn.disabled = true;
        if (btnText) btnText.textContent = 'Sending…';
    });
});
