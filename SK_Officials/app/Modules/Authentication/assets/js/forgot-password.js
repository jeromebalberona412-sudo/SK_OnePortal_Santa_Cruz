/**
 * SK Officials — Forgot Password Page JS
 */

document.addEventListener('DOMContentLoaded', function () {
    const form          = document.getElementById('forgotPasswordForm');
    const emailInput    = document.getElementById('email');
    const emailError    = document.getElementById('email-error');
    const submitBtn     = document.getElementById('submitBtn');
    const fpBtnText     = document.getElementById('fpBtnText');

    // ── Field helpers ────────────────────────────────────────────────────────
    function setInputError(input, errorEl, msg) {
        input.classList.add('is-invalid');
        errorEl.textContent = msg;
        errorEl.hidden = false;
    }

    document.querySelectorAll('.sk-field-error').forEach(function (el) {
        if (!el.hidden) el.setAttribute('data-server-error', 'true');
    });

    if (emailInput && emailError) {
        emailInput.addEventListener('input', function () {
            if (emailError.getAttribute('data-server-error') === 'true') return;
            this.classList.remove('is-invalid');
            emailError.hidden = true;
        });
    }

    // ── Main form submit ──────────────────────────────────────────────────────
    if (form && emailInput && emailError && submitBtn) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const email      = emailInput.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            emailInput.classList.remove('is-invalid');
            emailError.hidden = true;

            if (!email) {
                setInputError(emailInput, emailError, 'Please enter your email address.');
                return;
            }

            if (!emailRegex.test(email)) {
                setInputError(emailInput, emailError, 'Please enter a valid email address.');
                return;
            }

            if (submitBtn.disabled) return;

            submitBtn.disabled = true;
            if (fpBtnText) fpBtnText.textContent = 'Sending...';
            form.submit();
        });
    }
});
