/**
 * SK Officials — Forgot Password Page JS
 * Sends reset link request and keeps password reset in the email-token flow.
 */

document.addEventListener('DOMContentLoaded', function () {

    /* ── Form elements ─────────────────────────────────── */
    const form          = document.getElementById('forgotPasswordForm');
    const emailInput    = document.getElementById('email');
    const emailError    = document.getElementById('email-error');
    const submitBtn     = document.getElementById('submitBtn');
    const fpBtnText     = document.getElementById('fpBtnText');

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

    /* ── Email form ──────────────────────────────── */

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

        // Server will reload and show success message.
    });

    /* ── Back to login link (smooth) ────────────────────── */
    document.querySelector('.register-link')?.addEventListener('click', function (e) {
        if (this.href && !this.id) {
            e.preventDefault();
            setTimeout(() => { window.location.href = this.href; }, 300);
        }
    });

});
