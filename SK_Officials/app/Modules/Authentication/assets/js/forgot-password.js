/**
 * SK Officials — Forgot Password Page JS
 */

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('forgotPasswordForm');
    const resendForm = document.getElementById('fpResendForm');
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('email-error');
    const submitBtn = document.getElementById('submitBtn');
    const fpBtnText = document.getElementById('fpBtnText');
    const resendBtn = document.getElementById('fpResendBtn');
    const fpResendBtnText = document.getElementById('fpResendBtnText');
    const cooldownNotice = document.getElementById('fpCooldownNotice');
    const cooldownCount = document.getElementById('fpCooldownCount');
    const step1 = document.getElementById('fpStep1');
    const step2 = document.getElementById('fpStep2');
    const hiddenEmail = document.getElementById('fpHiddenEmail');
    const sentEmailEl = document.getElementById('fpSentEmail');

    const COOLDOWN_DURATION = 60;
    const COOLDOWN_KEY = 'fp_cooldown_until';
    let cooldownInterval = null;

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

    function getCooldownKey() {
        const email = hiddenEmail?.value || emailInput?.value || '';
        return `${COOLDOWN_KEY}_${email.trim().toLowerCase()}`;
    }

    function startCooldown() {
        const until = Date.now() + COOLDOWN_DURATION * 1000;
        localStorage.setItem(getCooldownKey(), String(until));
        runCooldownTick(until);
    }

    function runCooldownTick(until) {
        clearInterval(cooldownInterval);

        function tick() {
            const remaining = Math.ceil((until - Date.now()) / 1000);

            if (remaining <= 0) {
                clearInterval(cooldownInterval);
                localStorage.removeItem(getCooldownKey());
                if (submitBtn) submitBtn.disabled = false;
                if (resendBtn) resendBtn.disabled = false;
                if (fpBtnText) fpBtnText.textContent = 'Send Reset Link';
                if (fpResendBtnText) fpResendBtnText.textContent = 'Resend Reset Link';
                if (cooldownNotice) cooldownNotice.hidden = true;
                return;
            }

            if (submitBtn) submitBtn.disabled = true;
            if (resendBtn) resendBtn.disabled = true;
            if (fpBtnText) fpBtnText.textContent = 'Send Reset Link';
            if (fpResendBtnText) fpResendBtnText.textContent = 'Resend Reset Link';
            if (cooldownNotice) {
                cooldownNotice.hidden = false;
                if (cooldownCount) cooldownCount.textContent = remaining;
            }
        }

        tick();
        cooldownInterval = setInterval(tick, 1000);
    }

    function resumeCooldownIfActive() {
        const stored = localStorage.getItem(getCooldownKey());
        if (!stored) return false;

        const until = parseInt(stored, 10);
        if (Number.isNaN(until) || Date.now() >= until) {
            localStorage.removeItem(getCooldownKey());
            return false;
        }

        runCooldownTick(until);
        return true;
    }

    function showSentStep(email) {
        if (step1) step1.hidden = true;
        if (step2) step2.hidden = false;
        if (hiddenEmail && email) hiddenEmail.value = email;
        if (sentEmailEl && email) sentEmailEl.textContent = email;
    }

    const isSentStep = step2 && !step2.hidden;

    if (isSentStep) {
        startCooldown();
    } else {
        resumeCooldownIfActive();
    }

    document.querySelectorAll('.sk-field-error').forEach(function (el) {
        if (!el.hidden) {
            el.setAttribute('data-server-error', 'true');
        }
    });

    if (emailInput && emailError) {
        emailInput.addEventListener('input', function () {
            if (emailError.getAttribute('data-server-error') === 'true') return;
            this.classList.remove('is-invalid');
            emailError.hidden = true;
        });
    }

    if (form && emailInput && emailError && submitBtn) {
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

            if (submitBtn.disabled) {
                e.preventDefault();
                return;
            }

            submitBtn.disabled = true;
            if (fpBtnText) fpBtnText.textContent = 'Sending...';
        });
    }

    if (resendForm && resendBtn) {
        resendForm.addEventListener('submit', function (e) {
            if (resendBtn.disabled) {
                e.preventDefault();
                return;
            }

            resendBtn.disabled = true;
            if (fpResendBtnText) fpResendBtnText.textContent = 'Sending...';
        });
    }
});
