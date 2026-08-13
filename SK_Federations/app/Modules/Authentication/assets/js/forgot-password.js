/**
 * SK Federations — Forgot Password JS
 *
 * Handles two pages:
 *
 * 1. /forgot-password        (data-email-sent="0")
 *    - Email input + Send Reset Link button.
 *    - No timer on load. Any stale timer is cleared on load.
 *
 * 2. /forgot-password/verify-email  (data-email-sent="1")
 *    - Resend Reset Link button with 60-second cooldown.
 *    - Timer rules:
 *        • First arrival (nothing in localStorage yet) → start fresh 60s
 *          because the server just sent the email.
 *        • Page refresh while timer is running → resume from where it left off.
 *        • Clicking Resend → start a new 60s from that moment.
 *        • Back to /forgot-password (different email) → old timer is irrelevant
 *          (different key); new one starts when they submit again.
 */
document.addEventListener('DOMContentLoaded', function () {
    const form          = document.getElementById('forgotPasswordForm');
    const emailInput    = document.getElementById('email');
    const emailError    = document.getElementById('email-error');
    const submitBtn     = document.getElementById('submitBtn');
    const fpBtnText     = document.getElementById('fpBtnText');
    const cooldownNotice = document.getElementById('fpCooldownNotice');
    const cooldownCount  = document.getElementById('fpCooldownCount');

    if (!form) return;

    const COOLDOWN_DURATION = 60;
    const emailSent = form.dataset.emailSent === '1';

    // Use an email-scoped key so each submission gets its own independent timer.
    // The key is embedded in the blade via data-cooldown-key.
    const COOLDOWN_KEY = form.dataset.cooldownKey || 'sk_fed_fp_cooldown_until';

    let cooldownInterval = null;

    // ── Helpers ───────────────────────────────────────────────────────────────

    function setInputError(input, errorEl, msg) {
        if (!input || !errorEl) return;
        input.classList.add('is-invalid');
        errorEl.textContent = msg;
        errorEl.hidden = false;
    }

    function clearInputError(input, errorEl) {
        if (!input || !errorEl) return;
        input.classList.remove('is-invalid');
        errorEl.textContent = '';
        errorEl.hidden = true;
    }

    function fmt(s) {
        return String(Math.max(0, s));
    }

    // ── CSRF Token Refresh ────────────────────────────────────────────────────

    function refreshCsrfToken() {
        const csrfInput = form.querySelector('input[name="_token"]');
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        
        if (csrfInput && csrfMeta) {
            csrfInput.value = csrfMeta.content;
        }
    }

    // Refresh CSRF token every 5 minutes to prevent expiration
    setInterval(refreshCsrfToken, 5 * 60 * 1000);

    // ── Cooldown timer ────────────────────────────────────────────────────────

    function getStoredUntil() {
        const v = localStorage.getItem(COOLDOWN_KEY);
        if (!v) return 0;
        const ts = parseInt(v, 10);
        return Number.isNaN(ts) ? 0 : ts;
    }

    function storeUntil(ms) {
        localStorage.setItem(COOLDOWN_KEY, String(ms));
    }

    function clearStored() {
        localStorage.removeItem(COOLDOWN_KEY);
    }

    /** Run the tick loop against a fixed `until` timestamp. */
    function runTick(until) {
        clearInterval(cooldownInterval);

        function tick() {
            const remaining = Math.ceil((until - Date.now()) / 1000);

            if (remaining <= 0) {
                clearInterval(cooldownInterval);
                clearStored();
                if (submitBtn)     submitBtn.disabled = false;
                if (fpBtnText)     fpBtnText.textContent = 'Resend Reset Link';
                if (cooldownNotice) cooldownNotice.hidden = true;
                return;
            }

            if (submitBtn)      submitBtn.disabled = true;
            if (fpBtnText)      fpBtnText.textContent = 'Resend Reset Link';
            if (cooldownNotice) { cooldownNotice.hidden = false; }
            if (cooldownCount)  cooldownCount.textContent = fmt(remaining);
        }

        tick();
        cooldownInterval = setInterval(tick, 1000);
    }

    /** Start a brand-new 60-second cooldown from right now. */
    function startFreshCooldown() {
        const until = Date.now() + COOLDOWN_DURATION * 1000;
        storeUntil(until);
        runTick(until);
    }

    /** Resume an existing cooldown if one is stored and still active. */
    function resumeCooldownIfActive() {
        const until = getStoredUntil();
        if (until <= Date.now()) {
            clearStored();
            return false;
        }
        runTick(until);
        return true;
    }

    // ── Bootstrap ─────────────────────────────────────────────────────────────
    //
    // verify-email page (/forgot-password/verify-email):
    //   - If no timer stored yet → first arrival → start fresh 60s.
    //   - If timer already stored and still running → resume (page was refreshed).
    //   - If timer stored but expired → clear it, leave button enabled.
    //
    // forgot-password page (/forgot-password):
    //   - Always clear any stale timer so the button is ready to use.

    if (emailSent) {
        const until = getStoredUntil();
        if (until > Date.now()) {
            // Refresh scenario: resume from stored expiry
            runTick(until);
        } else {
            // First arrival: start fresh
            startFreshCooldown();
        }
    } else {
        // /forgot-password page — clear any stale key so a future visit is clean
        clearStored();
    }

    // ── Email field events (only on /forgot-password) ─────────────────────────

    if (emailInput && emailInput.type === 'email') {
        emailInput.addEventListener('input', function () {
            this.classList.remove('is-invalid');
            if (emailError) emailError.hidden = true;
        });
    }

    // ── Submit handler ────────────────────────────────────────────────────────

    form.addEventListener('submit', function (e) {
        // Refresh CSRF token before submission
        refreshCsrfToken();

        const email = emailInput ? emailInput.value.trim() : '';
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        // Validate only on the /forgot-password page (email input is visible)
        if (emailInput && emailInput.type === 'email') {
            emailInput.classList.remove('is-invalid');
            if (emailError) emailError.hidden = true;

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

        // Block if cooldown is still active
        if (submitBtn && submitBtn.disabled) {
            e.preventDefault();
            return;
        }

        // Proceed — show sending state
        if (submitBtn)  submitBtn.disabled = true;
        if (fpBtnText)  fpBtnText.textContent = 'Sending...';
    });
});
