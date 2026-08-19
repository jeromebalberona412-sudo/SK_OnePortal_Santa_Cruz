/**
 * SK Officials — Forgot Password with Cloudflare Turnstile
 */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('forgotPasswordForm');
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('email-error');
    const submitBtn = document.getElementById('submitBtn');
    const fpBtnText = document.getElementById('fpBtnText');

    function setInputError(input, errorEl, msg) {
        input.classList.add('is-invalid');
        errorEl.textContent = msg;
        errorEl.hidden = false;
    }

    function resetSubmitBtn() {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');
        }
        if (fpBtnText) {
            fpBtnText.textContent = 'Send Reset Link';
        }
    }

    document.querySelectorAll('.sk-field-error').forEach(function (el) {
        if (!el.hidden) {
            el.setAttribute('data-server-error', 'true');
        }
    });

    if (emailInput && emailError) {
        emailInput.addEventListener('input', function () {
            if (emailError.getAttribute('data-server-error') === 'true') {
                return;
            }
            this.classList.remove('is-invalid');
            emailError.hidden = true;
        });
    }

    if (!form || !emailInput || !emailError || !submitBtn) {
        return;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const email = emailInput.value.trim();
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

        if (submitBtn.disabled) {
            return;
        }

        const gate = window.SkOfficialsTurnstileGate;
        if (!gate || !gate.isEnabled || !gate.isEnabled()) {
            submitBtn.disabled = true;
            if (fpBtnText) {
                fpBtnText.textContent = 'Sending...';
            }
            form.submit();
            return;
        }

        gate.challenge().then(function (token) {
            gate.injectToken(form, token);
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            if (fpBtnText) {
                fpBtnText.textContent = 'Sending...';
            }
            HTMLFormElement.prototype.submit.call(form);
        }).catch(function (err) {
            resetSubmitBtn();
            if (err && err.message && err.message !== 'Verification cancelled.') {
                setInputError(emailInput, emailError, err.message);
            }
        });
    });
});
