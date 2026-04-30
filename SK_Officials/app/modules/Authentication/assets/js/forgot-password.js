/**
 * SK Officials — Forgot Password Page JS
 * Step 1: Email → send reset link (POST to server)
 * Step 2: New password + confirm password form (shown after success)
 */

document.addEventListener('DOMContentLoaded', function () {

    /* ── Step 1 elements ─────────────────────────────────── */
    const step1         = document.getElementById('fpStep1');
    const step2         = document.getElementById('fpStep2');
    const form          = document.getElementById('forgotPasswordForm');
    const emailInput    = document.getElementById('email');
    const emailError    = document.getElementById('email-error');
    const submitBtn     = document.getElementById('submitBtn');
    const fpBtnText     = document.getElementById('fpBtnText');
    const fpSuccess     = document.getElementById('fpSuccess');
    const fpSuccessText = document.getElementById('fpSuccessText');

    /* ── Step 2 elements ─────────────────────────────────── */
    const resetForm         = document.getElementById('fpResetForm');
    const newPwInput        = document.getElementById('fpNewPassword');
    const newPwError        = document.getElementById('fpNewPasswordError');
    const confirmPwInput    = document.getElementById('fpConfirmPassword');
    const confirmPwError    = document.getElementById('fpConfirmPasswordError');
    const resetBtn          = document.getElementById('fpResetBtn');
    const resetBtnText      = document.getElementById('fpResetBtnText');
    const resetSuccess      = document.getElementById('fpResetSuccess');
    const backToEmail       = document.getElementById('fpBackToEmail');
    const strengthBar       = document.getElementById('fpStrengthBar');
    const strengthFill      = document.getElementById('fpStrengthFill');
    const strengthLabel     = document.getElementById('fpStrengthLabel');

    if (!form) return;

    /* ── Helpers ─────────────────────────────────────────── */
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
        if (pw.length >= 8)           score++;
        if (pw.length >= 12)          score++;
        if (/[A-Z]/.test(pw))         score++;
        if (/[0-9]/.test(pw))         score++;
        if (/[^A-Za-z0-9]/.test(pw))  score++;
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
            { label: 'Very Weak',   color: '#ef4444', width: '20%'  },
            { label: 'Weak',        color: '#f97316', width: '40%'  },
            { label: 'Fair',        color: '#eab308', width: '60%'  },
            { label: 'Strong',      color: '#22c55e', width: '80%'  },
            { label: 'Very Strong', color: '#16a34a', width: '100%' },
        ];
        const lvl = levels[Math.min(score - 1, 4)] || levels[0];
        strengthFill.style.width      = lvl.width;
        strengthFill.style.background = lvl.color;
        strengthLabel.textContent     = lvl.label;
        strengthLabel.style.color     = lvl.color;
    }

    /* ── Step 1: Email form ──────────────────────────────── */

    // Mark server-side errors so they survive the first input clear
    document.querySelectorAll('.sk-field-error').forEach(function (el) {
        if (!el.hidden) el.setAttribute('data-server-error', 'true');
    });

    emailInput.addEventListener('input', function () {
        this.classList.remove('is-invalid');
        emailError.hidden = true;
    });

    form.addEventListener('submit', function (e) {
        const email = emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        emailInput.classList.remove('is-invalid');
        emailError.hidden = true;

        if (!email) {
            e.preventDefault();
            setInputError(emailInput, emailError, 'Please enter your email address.');
            return;
        }

        if (!emailRegex.test(email)) {
            e.preventDefault();
            setInputError(emailInput, emailError, 'Please enter a valid email address.');
            return;
        }

        // Disable only the button (not inputs — disabling inputs strips CSRF → 419)
        if (submitBtn) {
            submitBtn.disabled = true;
            if (fpBtnText) fpBtnText.textContent = 'Sending...';
        }

        // After the server responds with session('status'), the page reloads.
        // If the status message is present on load, show Step 2 automatically.
    });

    // If the server returned a status message on page load, transition to Step 2.
    const serverStatus = document.querySelector('#fpStep1 .sk-alert-success');
    if (serverStatus && !fpSuccess.contains(serverStatus)) {
        // Real server success — show step 2 after a short delay
        setTimeout(function () {
            step1.style.display = 'none';
            step2.style.display = 'block';
            if (newPwInput) newPwInput.focus();
        }, 1600);
    }

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

                // Redirect to login after showing success
                setTimeout(function () {
                    window.location.href = '/login';
                }, 2000);
            }, 800);
        });
    }

    /* ── Back to email link ──────────────────────────────── */
    if (backToEmail) {
        backToEmail.addEventListener('click', function (e) {
            e.preventDefault();
            step2.style.display = 'none';
            step1.style.display = 'block';
            fpSuccess.style.display = 'none';
            emailInput.value = '';
            emailInput.focus();
        });
    }

    /* ── Eye toggle buttons ──────────────────────────────── */
    document.querySelectorAll('.pw-toggle-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = btn.getAttribute('data-target');
            const input    = document.getElementById(targetId);
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            btn.classList.toggle('pw-visible', show);
        });
    });

    /* ── Back to login link (smooth) ────────────────────── */
    document.querySelector('.register-link')?.addEventListener('click', function (e) {
        if (this.href && !this.id) {
            e.preventDefault();
            setTimeout(() => { window.location.href = this.href; }, 300);
        }
    });

});
