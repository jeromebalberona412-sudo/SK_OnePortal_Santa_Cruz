document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('forgotPasswordForm');
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('email-error');
    const submitBtn = document.getElementById('submitBtn');
    const fpBtnText = document.getElementById('fpBtnText');
    const cooldownNotice = document.getElementById('fpCooldownNotice');
    const cooldownCount = document.getElementById('fpCooldownCount');

    if (!form) {
        return;
    }

    const COOLDOWN_DURATION = 60;
    const COOLDOWN_KEY = 'sk_fed_fp_cooldown_until';
    const emailSent = form.dataset.emailSent === '1';
    let cooldownInterval = null;

    function setInputError(input, errorEl, msg) {
        if (!input || !errorEl) {
            return;
        }
        input.classList.add('is-invalid');
        errorEl.textContent = msg;
        errorEl.hidden = false;
    }

    function clearInputError(input, errorEl) {
        if (!input || !errorEl) {
            return;
        }
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
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
                if (fpBtnText) {
                    fpBtnText.textContent = emailSent ? 'Resend Reset Link' : 'Send Reset Link';
                }
                if (cooldownNotice) {
                    cooldownNotice.hidden = true;
                }
                return;
            }

            if (submitBtn) {
                submitBtn.disabled = true;
            }
            if (fpBtnText) {
                fpBtnText.textContent = emailSent ? 'Resend Reset Link' : 'Send Reset Link';
            }
            if (cooldownNotice) {
                cooldownNotice.hidden = false;
                if (cooldownCount) {
                    cooldownCount.textContent = String(remaining);
                }
            }
        }

        tick();
        cooldownInterval = setInterval(tick, 1000);
    }

    function resumeCooldownIfActive() {
        const stored = localStorage.getItem(COOLDOWN_KEY);
        if (!stored) {
            return false;
        }

        const until = parseInt(stored, 10);
        if (Number.isNaN(until) || Date.now() >= until) {
            localStorage.removeItem(COOLDOWN_KEY);
            return false;
        }

        runCooldownTick(until);
        return true;
    }

    if (emailSent || document.querySelector('.fp-success-alert')) {
        startCooldown();
    } else {
        // Not on the "email sent" page — clear any stale cooldown so the timer
        // does NOT persist across a fresh page load of the forgot-password form.
        localStorage.removeItem(COOLDOWN_KEY);
    }

    if (emailInput && emailInput.type === 'email') {
        emailInput.addEventListener('input', function () {
            this.classList.remove('is-invalid');
            if (emailError) {
                emailError.hidden = true;
            }
        });
    }

    form.addEventListener('submit', function (e) {
        const turnstileEnabled = form.dataset.turnstileEnabled === '1';
        const email = emailInput ? emailInput.value.trim() : '';
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (emailInput && emailInput.type === 'email') {
            emailInput.classList.remove('is-invalid');
            if (emailError) {
                emailError.hidden = true;
            }

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
        }

        if (turnstileEnabled) {
            const turnstileTokenField = document.querySelector('input[name="cf-turnstile-response"]');
            const turnstileError = document.getElementById('turnstile-error');

            if (!turnstileTokenField || !turnstileTokenField.value) {
                e.preventDefault();
                if (turnstileError) {
                    turnstileError.style.display = 'block';
                    turnstileError.textContent = 'Please complete the bot verification.';
                }
                return;
            }
        }

        if (submitBtn && submitBtn.disabled) {
            e.preventDefault();
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
        }
        if (fpBtnText) {
            fpBtnText.textContent = 'Sending...';
        }

        if (typeof LoadingScreen !== 'undefined') {
            LoadingScreen.show('Sending Reset Link', 'Please wait...');
        }
    });
});
