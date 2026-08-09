(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const resendForm = document.getElementById('fpResendForm');
        const resendBtn = document.getElementById('fpResendBtn');
        const resendBtnText = document.getElementById('fpResendBtnText');
        const hiddenEmail = document.getElementById('fpHiddenEmail');

        if (!resendForm) {
            return;
        }

        const COOLDOWN_KEY = 'fp_cooldown_until';

        if (window.__skFpCooldownInterval) {
            clearInterval(window.__skFpCooldownInterval);
            window.__skFpCooldownInterval = null;
        }

        function getCooldownKey() {
            const email = hiddenEmail && hiddenEmail.value ? hiddenEmail.value : '';
            return COOLDOWN_KEY + '_' + email.trim().toLowerCase();
        }

        function clearCooldownIntervalRef() {
            if (window.__skFpCooldownInterval) {
                clearInterval(window.__skFpCooldownInterval);
                window.__skFpCooldownInterval = null;
            }
        }

        function runCooldownTick(untilTimestampMs) {
            clearCooldownIntervalRef();

            function tick() {
                const remainingMs = untilTimestampMs - Date.now();
                const remaining = remainingMs > 0 ? Math.ceil(remainingMs / 1000) : 0;

                if (remaining <= 0) {
                    clearCooldownIntervalRef();
                    try {
                        localStorage.removeItem(getCooldownKey());
                    } catch (_) {}
                    if (resendBtn) resendBtn.disabled = false;
                    if (resendBtnText) resendBtnText.textContent = 'Resend Reset Link';
                    return;
                }

                if (resendBtn) resendBtn.disabled = true;
                if (resendBtnText) resendBtnText.textContent = 'Resend available in ' + remaining + 's';
            }

            tick();
            window.__skFpCooldownInterval = setInterval(tick, 1000);
        }

        function resolveCooldownUntilMs() {
            const serverAttr = resendForm.getAttribute('data-resend-available-at');
            const email = hiddenEmail && hiddenEmail.value ? hiddenEmail.value.trim().toLowerCase() : '';

            const fromServerSeconds = serverAttr ? Number.parseInt(String(serverAttr), 10) : 0;
            let fromServerMs = 0;
            if (!Number.isNaN(fromServerSeconds) && fromServerSeconds > 0) {
                fromServerMs = fromServerSeconds * 1000;
            }

            let fromLocalMs = 0;
            try {
                const raw = localStorage.getItem(getCooldownKey());
                if (raw) {
                    const parsed = Number.parseInt(raw, 10);
                    if (!Number.isNaN(parsed) && parsed > Date.now()) {
                        fromLocalMs = parsed;
                    } else if (raw) {
                        localStorage.removeItem(getCooldownKey());
                    }
                }
            } catch (_) {}

            let resolvedMs = 0;
            if (fromServerMs > 0 && fromLocalMs > 0) {
                resolvedMs = Math.max(fromServerMs, fromLocalMs);
            } else if (fromServerMs > 0) {
                resolvedMs = fromServerMs;
            } else if (fromLocalMs > 0) {
                resolvedMs = fromLocalMs;
            }

            if (fromServerMs > 0 && email) {
                try {
                    localStorage.setItem(getCooldownKey(), String(fromServerMs));
                } catch (_) {}
            }

            return resolvedMs;
        }

        const hasEmail = hiddenEmail && hiddenEmail.value && hiddenEmail.value.trim();
        if (hasEmail) {
            const untilMs = resolveCooldownUntilMs();
            if (untilMs > Date.now()) {
                runCooldownTick(untilMs);
            } else {
                if (resendBtn) resendBtn.disabled = false;
                if (resendBtnText) resendBtnText.textContent = 'Resend Reset Link';
            }
        }

        let submitInProgress = false;
        if (resendForm && resendBtn) {
            resendForm.addEventListener('submit', function (e) {
                if (submitInProgress) {
                    e.preventDefault();
                    return;
                }

                if (resendBtn.disabled) {
                    e.preventDefault();
                    return;
                }

                submitInProgress = true;
                resendBtn.disabled = true;
                if (resendBtnText) resendBtnText.textContent = 'Sending...';

                try {
                    localStorage.removeItem(getCooldownKey());
                } catch (_) {}
            });
        }
    });
})();
