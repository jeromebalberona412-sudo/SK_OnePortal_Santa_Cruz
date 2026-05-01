/**
 * SK Officials — Forgot Password Page JS
 * Sends reset link request and keeps password reset in the email-token flow.
 */

document.addEventListener('DOMContentLoaded', function () {

    /* ── Form elements ─────────────────────────────────── */
    const form           = document.getElementById('forgotPasswordForm');
    const emailInput     = document.getElementById('email');
    const emailError     = document.getElementById('email-error');
    const submitBtn      = document.getElementById('submitBtn');
    const fpBtnText      = document.getElementById('fpBtnText');
    const cooldownNotice = document.getElementById('fpCooldownNotice');
    const cooldownCount  = document.getElementById('fpCooldownCount');

    if (!form) return;

    /* ── Cooldown constants ──────────────────────────────── */
    const COOLDOWN_DURATION = 60; // seconds
    const COOLDOWN_KEY      = 'fp_cooldown_until';

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

    /* ── Cooldown timer ──────────────────────────────────── */
    let cooldownInterval = null;

    /**
     * Persist the cooldown end-time and start ticking.
     * Only called when the success alert is visible on the page.
     */
    function startCooldown() {
        const until = Date.now() + COOLDOWN_DURATION * 1000;
        localStorage.setItem(COOLDOWN_KEY, until);
        runCooldownTick(until);
    }

    /**
     * Drive the countdown UI from a given end-timestamp.
     * Button stays disabled; countdown shown in the notice element below it.
     */
    function runCooldownTick(until) {
        clearInterval(cooldownInterval);

        function tick() {
            const remaining = Math.ceil((until - Date.now()) / 1000);

            if (remaining <= 0) {
                clearInterval(cooldownInterval);
                localStorage.removeItem(COOLDOWN_KEY);
                // Re-enable button, hide notice
                submitBtn.disabled = false;
                fpBtnText.textContent = 'Send Reset Link';
                if (cooldownNotice) cooldownNotice.hidden = true;
                return;
            }

            // Keep button disabled; update notice — leave button label unchanged
            submitBtn.disabled = true;
            fpBtnText.textContent = 'Send Reset Link';
            if (cooldownNotice) {
                cooldownNotice.hidden = false;
                if (cooldownCount) cooldownCount.textContent = remaining;
            }
        }

        tick(); // immediate first tick — no 1-second blank
        cooldownInterval = setInterval(tick, 1000);
    }

    /**
     * On page load: if a stored cooldown is still active, resume it.
     * Returns true if a live cooldown was found, false otherwise.
     */
    function resumeCooldownIfActive() {
        const stored = localStorage.getItem(COOLDOWN_KEY);
        if (!stored) return false;

        const until = parseInt(stored, 10);
        if (Date.now() < until) {
            runCooldownTick(until);
            return true;
        }

        localStorage.removeItem(COOLDOWN_KEY);
        return false;
    }

    /* ── Only activate cooldown when the success message is visible ── */
    const successAlert = document.querySelector('.sk-alert-success');

    // Always ensure notice is hidden on load unless we're about to show it
    if (cooldownNotice) cooldownNotice.hidden = true;

    if (successAlert) {
        // Page reloaded after a successful POST — resume existing or start fresh
        if (!resumeCooldownIfActive()) {
            startCooldown();
        }
    } else {
        // No success message — clear any stale cooldown key so it never leaks
        localStorage.removeItem(COOLDOWN_KEY);
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
        submitBtn.disabled = true;
        fpBtnText.textContent = 'Sending...';

        // Server will reload and show success message — cooldown starts then.
    });

    /* ── Back to login link (smooth) ────────────────────── */
    document.querySelector('.register-link')?.addEventListener('click', function (e) {
        if (this.href && !this.id) {
            e.preventDefault();
            setTimeout(() => { window.location.href = this.href; }, 300);
        }
    });

});
