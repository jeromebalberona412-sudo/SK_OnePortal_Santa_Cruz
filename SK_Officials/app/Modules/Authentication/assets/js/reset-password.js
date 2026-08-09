(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const resetPasswordForm = document.getElementById('resetPasswordForm');
        const newPasswordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');
        const newPasswordError = document.getElementById('password-error');
        const confirmPasswordError = document.getElementById('password-confirmation-error');
        const resetBtn = document.getElementById('resetBtn');
        const resetBtnText = document.getElementById('resetBtnText');
        const passwordRules = document.getElementById('passwordRules');
        const ruleLengthVal = document.getElementById('rule-length-val');

        if (!resetPasswordForm || !newPasswordInput) {
            return;
        }

        const maxLength = Number.parseInt(resetPasswordForm.dataset.passwordMaxLength || '64', 10);
        const minLength = Number.parseInt(resetPasswordForm.dataset.passwordMinLength || '8', 10);

        if (ruleLengthVal) {
            ruleLengthVal.textContent = String(minLength);
        }

        let isSubmitting = false;
        let rulesRevealed = false;

        const ruleConfig = [
            { id: 'rule-length', check: (pw) => pw.length >= minLength },
            { id: 'rule-lowercase', check: (pw) => /[a-z]/.test(pw) },
            { id: 'rule-uppercase', check: (pw) => /[A-Z]/.test(pw) },
            { id: 'rule-number', check: (pw) => /[0-9]/.test(pw) },
            { id: 'rule-special', check: (pw) => /[^A-Za-z0-9]/.test(pw) },
        ];

        function clearError(input, errorElement) {
            input.classList.remove('is-invalid');
            if (errorElement) {
                errorElement.hidden = true;
                errorElement.textContent = '';
            }
        }

        function showError(input, errorElement, message) {
            input.classList.add('is-invalid');
            if (errorElement) {
                errorElement.textContent = message;
                errorElement.hidden = false;
            }
        }

        function validatePasswordStrength(password) {
            const results = {};
            let allPasswordRulesPass = true;

            ruleConfig.forEach((rule) => {
                const ok = rule.check(password);
                results[rule.id] = ok;
                if (!ok) allPasswordRulesPass = false;
            });

            return {
                passwordRulesValid: password.length > 0 && allPasswordRulesPass,
                withinMaxLength: password.length <= maxLength,
                results,
            };
        }

        function setRuleMark(node, ok, opts) {
            const options = opts || {};
            const mark = node.querySelector('.rule-mark');
            const isNeutral = options.neutral === true;
            node.classList.toggle('ok', !!ok && !isNeutral);
            node.classList.toggle('neutral', isNeutral);
            if (mark) {
                mark.textContent = ok && !isNeutral ? '✓' : '✕';
            }
        }

        function updatePasswordRules(password, confirmValue) {
            if (!passwordRules) {
                return { allValid: false, matchOk: false };
            }

            const { passwordRulesValid, withinMaxLength, results } = validatePasswordStrength(password);

            const pwNotEmpty = password.length > 0;
            const confirmNotEmpty = typeof confirmValue === 'string' && confirmValue.length > 0;
            const bothFilled = pwNotEmpty && confirmNotEmpty;

            const matchOk = bothFilled && password === confirmValue;
            const matchInvalid = bothFilled && password !== confirmValue;

            ruleConfig.forEach((rule) => {
                const node = document.getElementById(rule.id);
                if (node) {
                    const ok = results[rule.id] && pwNotEmpty;
                    setRuleMark(node, ok, { neutral: !pwNotEmpty });
                }
            });

            const allValid = passwordRulesValid && withinMaxLength && matchOk;
            passwordRules.classList.toggle('all-valid', allValid);

            // Always show rules once user starts typing
            if (pwNotEmpty) {
                rulesRevealed = true;
            }
            // KEEP RULES VISIBLE - never hide them once revealed
            passwordRules.classList.toggle('hidden-rules', !rulesRevealed);

            // Update confirm password match status
            updateConfirmMatchStatus(matchOk, matchInvalid, bothFilled);

            return {
                allValid: allValid,
                matchOk: matchOk,
                matchInvalid: matchInvalid,
                withinMaxLength: withinMaxLength,
                passwordRulesValid: passwordRulesValid,
                bothFilled: bothFilled,
            };
        }

        function updateConfirmMatchStatus(matchOk, matchInvalid, bothFilled) {
            const confirmMatchStatus = document.getElementById('confirmMatchStatus');
            const confirmMatchMark = document.getElementById('confirmMatchMark');
            const confirmMatchText = document.getElementById('confirmMatchText');

            if (!confirmMatchStatus || !confirmMatchMark || !confirmMatchText) {
                return;
            }

            if (!bothFilled) {
                confirmMatchStatus.hidden = true;
                confirmMatchStatus.classList.remove('match-ok', 'match-bad');
                return;
            }

            confirmMatchStatus.hidden = false;

            if (matchOk) {
                confirmMatchStatus.classList.remove('match-bad');
                confirmMatchStatus.classList.add('match-ok');
                confirmMatchMark.textContent = '✓';
                confirmMatchText.textContent = 'Passwords match';
            } else {
                confirmMatchStatus.classList.remove('match-ok');
                confirmMatchStatus.classList.add('match-bad');
                confirmMatchMark.textContent = '✕';
                confirmMatchText.textContent = 'Passwords do not match';
            }
        }

        function validateAll() {
            const pw = newPasswordInput.value;
            const confirm = confirmPasswordInput ? confirmPasswordInput.value : '';
            return updatePasswordRules(pw, confirm);
        }

        function setRuleInitialMarks() {
            if (!passwordRules) return;

            const nodes = passwordRules.querySelectorAll('li');
            nodes.forEach((node) => {
                setRuleMark(node, false, { neutral: true });
            });

            passwordRules.classList.add('hidden-rules');
            passwordRules.classList.remove('all-valid');
            rulesRevealed = false;
        }

        setRuleInitialMarks();

        newPasswordInput.addEventListener('input', function () {
            clearError(this, newPasswordError);
            const confirmValue = confirmPasswordInput ? confirmPasswordInput.value : '';
            updatePasswordRules(this.value, confirmValue);
        });

        newPasswordInput.addEventListener('focus', function () {
            const confirmValue = confirmPasswordInput ? confirmPasswordInput.value : '';
            updatePasswordRules(this.value, confirmValue);
        });

        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function () {
                clearError(this, confirmPasswordError);
                clearError(newPasswordInput, newPasswordError);
                updatePasswordRules(newPasswordInput.value, this.value);
            });

            confirmPasswordInput.addEventListener('focus', function () {
                updatePasswordRules(newPasswordInput.value, this.value);
            });
        }

        resetPasswordForm.addEventListener('submit', function (e) {
            if (isSubmitting) {
                e.preventDefault();
                return;
            }

            const pw = newPasswordInput.value;
            const confirm = confirmPasswordInput ? confirmPasswordInput.value : '';
            const status = updatePasswordRules(pw, confirm);

            let isValid = true;

            clearError(newPasswordInput, newPasswordError);
            if (confirmPasswordInput) clearError(confirmPasswordInput, confirmPasswordError);

            if (!status.passwordRulesValid) {
                e.preventDefault();
                showError(newPasswordInput, newPasswordError, 'Please meet all password requirements.');
                isValid = false;
            } else if (!status.withinMaxLength) {
                e.preventDefault();
                showError(newPasswordInput, newPasswordError, 'Password must not exceed ' + maxLength + ' characters.');
                isValid = false;
            }

            if (confirmPasswordInput && status.bothFilled && pw !== confirm) {
                e.preventDefault();
                showError(confirmPasswordInput, confirmPasswordError, 'Passwords do not match.');
                isValid = false;
            }

            if (isValid && resetBtn && resetBtnText) {
                isSubmitting = true;
                resetBtn.disabled = true;
                resetBtnText.textContent = 'Resetting...';
            }
        });

        let toggleBound = false;
        if (!toggleBound) {
            toggleBound = true;
            document.querySelectorAll('.toggle-password').forEach((button) => {
                button.addEventListener('click', function (event) {
                    event.preventDefault();

                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const eyeOpen = this.querySelector('.eye-open');
                    const eyeClosed = this.querySelector('.eye-closed');

                    if (!input || !eyeOpen || !eyeClosed) {
                        return;
                    }

                    const showPlain = input.type === 'password';
                    input.type = showPlain ? 'text' : 'password';
                    eyeOpen.style.display = showPlain ? 'none' : 'block';
                    eyeClosed.style.display = showPlain ? 'block' : 'none';
                });
            });
        }

        const initialPw = newPasswordInput.value || '';
        const initialConfirm = confirmPasswordInput ? confirmPasswordInput.value || '' : '';
        if (initialPw.length > 0 || initialConfirm.length > 0) {
            validateAll();
        }
    });
})();
