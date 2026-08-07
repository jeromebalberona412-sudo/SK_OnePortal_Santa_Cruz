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

    // Turnstile elements (may be absent if Turnstile is disabled)
    const turnstileContainer = document.getElementById('fp-turnstile-container');
    const turnstileError = document.getElementById('fp-turnstile-error');

    const COOLDOWN_DURATION = 60;
    const COOLDOWN_KEY = 'fp_cooldown_until';
    let cooldownInterval = null;

    // ── Turnstile state ──────────────────────────────────────────────────────
    let turnstileLoaded = false;
    let turnstileWidgetId = null;

    function loadTurnstileWidget() {
        if (turnstileLoaded) return;

        if (typeof turnstile === 'undefined') {
            // Script hasn't loaded yet
            if (submitBtn) submitBtn.disabled = false;
            if (fpBtnText) fpBtnText.textContent = 'Send Reset Link';
            if (emailInput) emailInput.disabled = false;
            if (turnstileError) {
                turnstileError.textContent = 'Security check failed to load. Please refresh the page and try again.';
                turnstileError.hidden = false;
            }
            return;
        }

        turnstileLoaded = true;

        if (turnstileContainer) turnstileContainer.style.display = 'block';

        // Restore form state so the user can see/interact with the widget
        if (submitBtn) submitBtn.disabled = false;
        if (fpBtnText) fpBtnText.textContent = 'Send Reset Link';
        if (emailInput) emailInput.disabled = false;

        turnstileWidgetId = turnstile.render('#fp-turnstile-widget', {
            sitekey: window.turnstileSiteKey,
            callback: function (token) {
                if (turnstileError) turnstileError.hidden = true;
                submitForgotPasswordForm();
            },
            'error-callback': function () {
                if (turnstileError) {
                    turnstileError.textContent = 'Security verification failed. Please try again.';
                    turnstileError.hidden = false;
                }
                resetSubmitBtn();
            },
            'expired-callback': function () {
                if (turnstileError) {
                    turnstileError.textContent = 'Security verification expired. Please try again.';
                    turnstileError.hidden = false;
                }
                resetSubmitBtn();
            },
        });
    }

    function resetSubmitBtn() {
        if (submitBtn) submitBtn.disabled = false;
        if (fpBtnText) fpBtnText.textContent = 'Send Reset Link';
        if (emailInput) emailInput.disabled = false;
    }

    function submitForgotPasswordForm() {
        // Attach the token as a hidden input
        let tokenInput = form.querySelector('input[name="cf-turnstile-response"]');
        if (!tokenInput) {
            tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = 'cf-turnstile-response';
            form.appendChild(tokenInput);
        }
        tokenInput.value = turnstile.getResponse(turnstileWidgetId);

        // Lock the form while submitting
        if (submitBtn) submitBtn.disabled = true;
        if (fpBtnText) fpBtnText.textContent = 'Sending...';
        if (emailInput) emailInput.disabled = true;
        if (turnstileContainer) {
            turnstileContainer.style.pointerEvents = 'none';
            turnstileContainer.style.opacity = '0.5';
        }

        form.submit();
    }

    // ── Cooldown helpers ─────────────────────────────────────────────────────
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

    function navigationType() {
        const entries = performance.getEntriesByType('navigation');
        if (entries.length > 0) return entries[0].type;
        if (performance.navigation && performance.navigation.type === 1) return 'reload';
        return 'navigate';
    }

    function disableResendAfterRefresh() {
        const navType = navigationType();
        if (navType === 'reload' || navType === 'back_forward') {
            if (resendBtn) resendBtn.disabled = true;
            if (fpResendBtnText) fpResendBtnText.textContent = 'Resend Disabled After Refresh';
            if (cooldownNotice) {
                cooldownNotice.hidden = false;
                cooldownNotice.textContent = 'Reset link resend is disabled after refreshing the page.';
            }
        }
    }

    // ── Init cooldown state ──────────────────────────────────────────────────
    const isSentStep = step2 && !step2.hidden;
    const isReloadNavigation = navigationType() === 'reload' || navigationType() === 'back_forward';

    if (isSentStep && !isReloadNavigation) {
        startCooldown();
    } else {
        resumeCooldownIfActive();
    }

    if (isReloadNavigation) {
        disableResendAfterRefresh();
    }

    // ── Field error helpers ──────────────────────────────────────────────────
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

    // ── Form submit ──────────────────────────────────────────────────────────
    if (form && emailInput && emailError && submitBtn) {
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

            if (submitBtn.disabled) return;

            // If Turnstile is enabled, show widget first (if not yet loaded)
            if (turnstileContainer && window.turnstileSiteKey) {
                if (!turnstileLoaded) {
                    if (submitBtn) submitBtn.disabled = true;
                    if (fpBtnText) fpBtnText.textContent = 'Loading Security Check...';
                    if (emailInput) emailInput.disabled = true;
                    loadTurnstileWidget();
                    return;
                }

                // Widget already visible — check if completed
                const response = turnstile.getResponse(turnstileWidgetId);
                if (!response) {
                    if (turnstileError) {
                        turnstileError.textContent = 'Please complete the security verification.';
                        turnstileError.hidden = false;
                    }
                    return;
                }

                // Already completed — submit immediately
                submitForgotPasswordForm();
                return;
            }

            // Turnstile disabled — submit normally
            submitBtn.disabled = true;
            if (fpBtnText) fpBtnText.textContent = 'Sending...';
            form.submit();
        });
    }

    // ── Resend form ──────────────────────────────────────────────────────────
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
