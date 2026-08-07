/**
 * SK Officials — Forgot Password Check Email Page JS
 */

document.addEventListener('DOMContentLoaded', function () {
    const resendForm    = document.getElementById('fpResendForm');
    const resendBtn     = document.getElementById('fpResendBtn');
    const fpResendBtnText = document.getElementById('fpResendBtnText');
    const cooldownNotice  = document.getElementById('fpCooldownNotice');
    const cooldownCount   = document.getElementById('fpCooldownCount');
    const hiddenEmail   = document.getElementById('fpHiddenEmail');

    const COOLDOWN_DURATION = 60;
    const COOLDOWN_KEY      = 'fp_cooldown_until';
    let cooldownInterval    = null;

    // ── Cooldown helpers ─────────────────────────────────────────────────────
    function getCooldownKey() {
        const email = hiddenEmail?.value || '';
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
                if (resendBtn) resendBtn.disabled = false;
                if (fpResendBtnText) fpResendBtnText.textContent = 'Resend Reset Link';
                if (cooldownNotice) cooldownNotice.hidden = true;
                return;
            }

            if (resendBtn) resendBtn.disabled = true;
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

    // ── Init cooldown state ───────────────────────────────────────────────────
    // Only resume the cooldown if there's a valid email in the hidden field
    const hasEmail = hiddenEmail?.value?.trim();
    if (hasEmail) {
        // Always resume the cooldown from localStorage — works on refresh too.
        // If the timer already expired, buttons are simply left enabled.
        resumeCooldownIfActive();
    }

    // ── Resend form ───────────────────────────────────────────────────────────
    if (resendForm && resendBtn) {
        resendForm.addEventListener('submit', function (e) {
            if (resendBtn.disabled) {
                e.preventDefault();
                return;
            }
            resendBtn.disabled = true;
            if (fpResendBtnText) fpResendBtnText.textContent = 'Sending...';
            // Start a fresh cooldown for the resend — stored in localStorage
            // so it survives a page refresh.
            startCooldown();
        });
    }
});
