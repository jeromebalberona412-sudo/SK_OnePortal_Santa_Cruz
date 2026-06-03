/**
 * SK Officials — Forgot Password Page JS
 */

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('forgotPasswordForm');
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('email-error');
    const submitBtn = document.getElementById('submitBtn');
    const fpBtnText = document.getElementById('fpBtnText');
    const cooldownNotice = document.getElementById('fpCooldownNotice');
    const cooldownCount = document.getElementById('fpCooldownCount');

    if (!form) return;

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

    function startCooldown() {
        const until = Date.now() + COOLDOWN_DURATION * 1000;
        localStorage.setItem(COOLDOWN_KEY, String(until));
        runCooldownTick(until);
    }

    function runCooldownTick(until) {
        clearInterval(cooldownInterval);

        function tick() {
            const remaining = Math.ceil((until - Date.now()) / 1000);

            if (remaining <= 0) {
                clearInterval(cooldownInterval);
                localStorage.removeItem(COOLDOWN_KEY);
                submitBtn.disabled = false;
                fpBtnText.textContent = 'Send Reset Link';
                if (cooldownNotice) cooldownNotice.hidden = true;
                return;
            }

            submitBtn.disabled = true;
            fpBtnText.textContent = 'Send Reset Link';
            if (cooldownNotice) {
                cooldownNotice.hidden = false;
                if (cooldownCount) cooldownCount.textContent = remaining;
            }
        }

        tick();
        cooldownInterval = setInterval(tick, 1000);
    }

    function resumeCooldownIfActive() {
        const stored = localStorage.getItem(COOLDOWN_KEY);
        if (!stored) return false;

        const until = parseInt(stored, 10);
        if (Number.isNaN(until) || Date.now() >= until) {
            localStorage.removeItem(COOLDOWN_KEY);
            return false;
        }

        runCooldownTick(until);
        return true;
    }

    const successAlert = document.querySelector('.sk-alert-success');
    const resumed = resumeCooldownIfActive();

    if (successAlert && !resumed) {
        startCooldown();
    }

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

        if (submitBtn.disabled) {
            e.preventDefault();
            return;
        }

        submitBtn.disabled = true;
        fpBtnText.textContent = 'Sending...';

        const until = Date.now() + COOLDOWN_DURATION * 1000;
        localStorage.setItem(COOLDOWN_KEY, String(until));
        runCooldownTick(until);
    });
});
