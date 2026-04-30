/**
 * SK Officials — Change Password Page JS
 * Step 1: Email → send reset link
 * Step 2: New password + confirm password form
 */

document.addEventListener('DOMContentLoaded', function () {

    /* ── Step 1 elements ─────────────────────────────────── */
    const step1       = document.getElementById('cpStep1');
    const step2       = document.getElementById('cpStep2');
    const form        = document.getElementById('cpForm');
    const emailInput  = document.getElementById('cpEmail');
    const emailError  = document.getElementById('cpEmailError');
    const submitBtn   = document.getElementById('cpSubmitBtn');
    const btnText     = document.getElementById('cpBtnText');
    const successBox  = document.getElementById('cpSuccess');
    const errorBox    = document.getElementById('cpError');
    const errorText   = document.getElementById('cpErrorText');

    /* ── Step 2 elements ─────────────────────────────────── */
    const resetForm        = document.getElementById('cpResetForm');
    const newPwInput       = document.getElementById('cpNewPassword');
    const newPwError       = document.getElementById('cpNewPasswordError');
    const confirmPwInput   = document.getElementById('cpConfirmPassword');
    const confirmPwError   = document.getElementById('cpConfirmPasswordError');
    const resetBtn         = document.getElementById('cpResetBtn');
    const resetBtnText     = document.getElementById('cpResetBtnText');
    const resetSuccess     = document.getElementById('cpResetSuccess');
    const backToEmail      = document.getElementById('cpBackToEmail');
    const strengthBar      = document.getElementById('cpStrengthBar');
    const strengthFill     = document.getElementById('cpStrengthFill');
    const strengthLabel    = document.getElementById('cpStrengthLabel');

    if (!form) return;

    /* ── Demo registered emails ──────────────────────────── */
    const REGISTERED_EMAILS = [
        'kianpaula4@gmail.com',
        'maria.santos@email.com',
        'robert.tan@email.com',
        'jerome.balberona@email.com',
        'gabriel.garcia@email.com',
        'frankien.belangoy@email.com',
        'juan.delacruz@email.com',
        'jane.doe@email.com',
        'mark.reyes@email.com',
    ];

    /* ── Helpers ─────────────────────────────────────────── */
    function isValidEmail(val) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val.trim());
    }

    function showFieldError(el, msg) {
        el.textContent = msg;
        el.hidden = false;
        el.previousElementSibling && el.previousElementSibling.classList.add('is-invalid');
    }

    function clearFieldError(el) {
        el.textContent = '';
        el.hidden = true;
    }

    function setInputError(input, errorEl, msg) {
        input.classList.add('is-invalid');
        errorEl.textContent = msg;
        errorEl.hidden = false;
    }

    function clearInputError(input, errorEl) {
        input.classList.remove('is-invalid');
        errorEl.textContent = '';
        errorEl.hidden = true;
    }

    /* ── Password strength ───────────────────────────────── */
    function getStrength(pw) {
        let score = 0;
        if (pw.length >= 8)  score++;
        if (pw.length >= 12) score++;
        if (/[A-Z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;
        return score;
    }

    function updateStrength(pw) {
        if (!pw) {
            strengthBar.style.display = 'none';
            strengthLabel.textContent = '';
            return;
        }
        strengthBar.style.display = 'block';
        const score = getStrength(pw);
        const levels = [
            { label: 'Very Weak',  color: '#ef4444', width: '20%' },
            { label: 'Weak',       color: '#f97316', width: '40%' },
            { label: 'Fair',       color: '#eab308', width: '60%' },
            { label: 'Strong',     color: '#22c55e', width: '80%' },
            { label: 'Very Strong',color: '#16a34a', width: '100%' },
        ];
        const lvl = levels[Math.min(score - 1, 4)] || levels[0];
        strengthFill.style.width = lvl.width;
        strengthFill.style.background = lvl.color;
        strengthLabel.textContent = lvl.label;
        strengthLabel.style.color = lvl.color;
    }

    /* ── Step 1: Email form ──────────────────────────────── */
    emailInput.addEventListener('input', function () {
        clearFieldError(emailError);
        emailInput.classList.remove('is-invalid');
        successBox.style.display = 'none';
        errorBox.style.display   = 'none';
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        successBox.style.display = 'none';
        errorBox.style.display   = 'none';
        clearFieldError(emailError);
        emailInput.classList.remove('is-invalid');

        const val = emailInput.value.trim();

        if (!val) {
            setInputError(emailInput, emailError, 'Email address is required.');
            emailInput.focus();
            return;
        }
        if (!isValidEmail(val)) {
            setInputError(emailInput, emailError, 'Please enter a valid email address.');
            emailInput.focus();
            return;
        }

        submitBtn.disabled = true;
        btnText.textContent = 'Sending…';

        setTimeout(function () {
            submitBtn.disabled = false;
            btnText.textContent = 'Send Reset Password Link';

            const found = REGISTERED_EMAILS.includes(val.toLowerCase());
            if (found) {
                /* Show success then reveal step 2 after a short delay */
                document.getElementById('cpSuccessText').textContent =
                    'Reset password link has been sent to your email.';
                successBox.style.display = 'flex';

                setTimeout(function () {
                    step1.style.display = 'none';
                    step2.style.display = 'block';
                    newPwInput.focus();
                }, 1800);
            } else {
                errorText.textContent  = 'No account found with that email address. Please try again.';
                errorBox.style.display = 'flex';
            }
        }, 700);
    });

    /* ── Step 2: Reset password form ────────────────────── */
    if (newPwInput) {
        newPwInput.addEventListener('input', function () {
            clearInputError(newPwInput, newPwError);
            updateStrength(newPwInput.value);
        });
    }

    if (confirmPwInput) {
        confirmPwInput.addEventListener('input', function () {
            clearInputError(confirmPwInput, confirmPwError);
        });
    }

    if (resetForm) {
        resetForm.addEventListener('submit', function (e) {
            e.preventDefault();
            let valid = true;

            const pw  = newPwInput.value;
            const cpw = confirmPwInput.value;

            clearInputError(newPwInput, newPwError);
            clearInputError(confirmPwInput, confirmPwError);

            if (!pw) {
                setInputError(newPwInput, newPwError, 'New password is required.');
                valid = false;
            } else if (pw.length < 8) {
                setInputError(newPwInput, newPwError, 'Password must be at least 8 characters.');
                valid = false;
            } else if (pw.length > 64) {
                setInputError(newPwInput, newPwError, 'Password must not exceed 64 characters.');
                valid = false;
            }

            if (!cpw) {
                setInputError(confirmPwInput, confirmPwError, 'Please confirm your new password.');
                valid = false;
            } else if (pw && cpw && pw !== cpw) {
                setInputError(confirmPwInput, confirmPwError, 'Passwords do not match.');
                valid = false;
            }

            if (!valid) return;

            resetBtn.disabled = true;
            resetBtnText.textContent = 'Saving…';

            setTimeout(function () {
                resetBtn.disabled = false;
                resetBtnText.textContent = 'Change Password';
                resetSuccess.style.display = 'flex';
                resetForm.style.display    = 'none';

                /* Logout immediately then redirect to login */
                var csrfToken = document.querySelector('meta[name="csrf-token"]');
                var token = csrfToken ? csrfToken.getAttribute('content') : '';

                fetch('/logout', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Content-Type': 'application/json',
                    },
                    credentials: 'same-origin',
                }).finally(function () {
                    window.location.href = '/login';
                });
            }, 800);
        });
    }

    /* ── Back to email link ──────────────────────────────── */
    if (backToEmail) {
        backToEmail.addEventListener('click', function (e) {
            e.preventDefault();
            step2.style.display = 'none';
            step1.style.display = 'block';
            successBox.style.display = 'none';
            emailInput.value = '';
            emailInput.focus();
        });
    }

    /* ── Eye toggle buttons ──────────────────────────────── */
    document.querySelectorAll('.pw-toggle-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = btn.getAttribute('data-target');
            var input    = document.getElementById(targetId);
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            btn.classList.toggle('pw-visible', show);
        });
    });

});
