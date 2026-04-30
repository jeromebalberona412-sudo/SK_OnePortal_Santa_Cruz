/* ============================================================
   Change Email — Admin Profile
   UI-driven multi-step flow
   ============================================================ */

(function () {
    'use strict';

    /* ── helpers ── */
    function ceShow(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.style.display = 'block';
        el.classList.remove('ce-fade-in');
        void el.offsetWidth; // reflow
        el.classList.add('ce-fade-in');
    }

    function ceHide(id) {
        var el = document.getElementById(id);
        if (el) el.style.display = 'none';
    }

    function isValidEmail(val) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
    }

    /* ── Step 1 → 2: Send verification ── */
    window.ceSendVerification = function () {
        var newEmail  = (document.getElementById('ce-new-email')  || {}).value || '';
        var password  = (document.getElementById('ce-password')   || {}).value || '';
        var emailErr  = document.getElementById('ce-email-err');
        var pwErr     = document.getElementById('ce-pw-err');
        var btn       = document.getElementById('ce-send-btn');

        newEmail = newEmail.trim();

        if (emailErr) emailErr.style.display = 'none';
        if (pwErr)    pwErr.style.display    = 'none';

        var hasError = false;

        if (!newEmail || !isValidEmail(newEmail)) {
            if (emailErr) emailErr.style.display = 'block';
            hasError = true;
        }

        if (!password) {
            if (pwErr) pwErr.style.display = 'block';
            hasError = true;
        }

        if (hasError) return;

        if (btn) {
            btn.disabled    = true;
            btn.textContent = 'Sending…';
        }

        setTimeout(function () {
            var pendingDisplay    = document.getElementById('ce-pending-email-display');
            var pendingNewDisplay = document.getElementById('ce-pending-new-display');
            if (pendingDisplay)    pendingDisplay.textContent    = newEmail;
            if (pendingNewDisplay) pendingNewDisplay.textContent = newEmail;

            ceHide('ce-step1');
            ceShow('ce-step2');

            // Start the 60s resend cooldown immediately
            ceStartCooldown();

            if (btn) {
                btn.disabled    = false;
                btn.textContent = 'Send Verification Link';
            }
        }, 900);
    };

    /* ── 60-second resend cooldown ── */
    var ceCountdownTimer = null;

    function ceStartCooldown() {
        var resendBtn  = document.getElementById('ce-resend-btn');
        var timerEl    = document.getElementById('ce-resend-timer');
        var countEl    = document.getElementById('ce-timer-count');
        var resendMsg  = document.getElementById('ce-resend-msg');

        if (resendMsg)  resendMsg.style.display  = 'none';
        if (timerEl)    timerEl.style.display    = 'block';
        if (resendBtn)  { resendBtn.disabled = true; }

        var secs = 60;
        if (countEl) countEl.textContent = secs;

        if (ceCountdownTimer) clearInterval(ceCountdownTimer);

        ceCountdownTimer = setInterval(function () {
            secs--;
            if (countEl) countEl.textContent = secs;

            if (secs <= 0) {
                clearInterval(ceCountdownTimer);
                ceCountdownTimer = null;
                if (timerEl)   timerEl.style.display  = 'none';
                if (resendBtn) { resendBtn.disabled = false; }
            }
        }, 1000);
    }

    /* ── Resend verification ── */
    window.ceResend = function () {
        var btn       = document.getElementById('ce-resend-btn');
        var resendMsg = document.getElementById('ce-resend-msg');

        if (!btn || btn.disabled) return;

        btn.disabled    = true;
        btn.textContent = 'Sending…';

        setTimeout(function () {
            btn.textContent = 'Resend Verification';

            if (resendMsg) {
                resendMsg.style.display = 'block';
                setTimeout(function () {
                    resendMsg.style.display = 'none';
                }, 3000);
            }

            // Restart cooldown
            ceStartCooldown();
        }, 800);
    };

    /* ── Cancel request ── */
    window.ceCancel = function () {
        ceHide('ce-step2');
        ceShow('ce-step1');

        // Clear inputs
        var newEmailInput = document.getElementById('ce-new-email');
        var passwordInput = document.getElementById('ce-password');
        if (newEmailInput) newEmailInput.value = '';
        if (passwordInput) passwordInput.value = '';
    };

    /* ── Simulate confirm (for demo — normally triggered by email link) ── */
    window.ceConfirm = function () {
        ceHide('ce-step2');
        ceShow('ce-step3');
    };

})();
