/**
 * SK Officials — Activate Account request with Cloudflare Turnstile
 */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('activateAccountForm');
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('email-error');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('fpBtnText');

    function showErr(msg) {
        emailInput.classList.add('is-invalid');
        emailError.textContent = msg;
        emailError.hidden = false;
    }

    function resetSubmitBtn() {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');
        }
        if (btnText) {
            btnText.textContent = 'Send Activation Link';
        }
    }

    if (emailInput && emailError) {
        emailInput.addEventListener('input', function () {
            emailInput.classList.remove('is-invalid');
            emailError.hidden = true;
        });
    }

    if (!form) {
        return;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const email = emailInput ? emailInput.value.trim() : '';
        if (emailInput) {
            emailInput.classList.remove('is-invalid');
        }
        if (emailError) {
            emailError.hidden = true;
        }

        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showErr('Invalid email or no email.');
            return;
        }

        if (submitBtn && submitBtn.disabled) {
            return;
        }

        const gate = window.SkOfficialsTurnstileGate;
        if (!gate || !gate.isEnabled || !gate.isEnabled()) {
            if (submitBtn) {
                submitBtn.disabled = true;
            }
            if (btnText) {
                btnText.textContent = 'Sending...';
            }
            form.submit();
            return;
        }

        if (submitBtn) {
            submitBtn.classList.add('waiting-for-turnstile');
        }

        gate.challenge().then(function (token) {
            gate.injectToken(form, token);
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.remove('waiting-for-turnstile');
                submitBtn.classList.add('loading');
            }
            if (btnText) {
                btnText.textContent = 'Sending...';
            }
            HTMLFormElement.prototype.submit.call(form);
        }).catch(function (err) {
            if (submitBtn) {
                submitBtn.classList.remove('waiting-for-turnstile');
            }
            resetSubmitBtn();
            if (err && err.message && err.message !== 'Verification cancelled.') {
                showErr(err.message);
            }
        });
    });
});
