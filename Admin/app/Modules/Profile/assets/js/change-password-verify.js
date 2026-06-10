document.addEventListener('DOMContentLoaded', function () {
    const COOLDOWN_SECONDS = 60;
    const POLL_INTERVAL_MS = 3000;

    const verifySection = document.getElementById('cpVerifySection');
    const statusUrl = verifySection?.dataset.statusUrl || '';
    const accountEmail = verifySection?.dataset.email || 'default';
    const cooldownKey = `op_admin_password_change_resend_${accountEmail}`;

    const timerElement = document.getElementById('cpTimer');
    const timerCountElement = document.getElementById('cpTimerCount');
    const resendBtn = document.getElementById('cpResendBtn');
    const resendForm = document.getElementById('cpResendForm');
    const listeningBadge = document.getElementById('cpListeningBadge');
    const statusTitle = document.getElementById('cpStatusTitle');
    const statusSub = document.getElementById('cpStatusSub');
    const statusBadge = document.getElementById('cpStatusBadge');
    const overlay = document.getElementById('signin-overlay');

    let timerInterval = null;
    let confirmationHandled = false;
    const serverCooldown = Number(window.cpResendCooldown || 0);

    function showPageLoading(message) {
        if (!overlay) return;
        overlay.removeAttribute('hidden');
        overlay.classList.add('is-visible');
        const title = overlay.querySelector('.signin-overlay-title');
        if (title && message) title.textContent = message;
    }

    function clearResendCooldown() {
        localStorage.removeItem(cooldownKey);
    }

    function setResendCooldownExpiry(seconds) {
        const duration = Math.max(1, seconds || COOLDOWN_SECONDS);
        localStorage.setItem(cooldownKey, String(Date.now() + duration * 1000));
    }

    function getRemainingSeconds() {
        const expiry = Number.parseInt(localStorage.getItem(cooldownKey) || '0', 10);
        if (expiry > Date.now()) {
            return Math.max(0, Math.ceil((expiry - Date.now()) / 1000));
        }

        return serverCooldown > 0 ? serverCooldown : 0;
    }

    function formatCountdown(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${String(secs).padStart(2, '0')}`;
    }

    function updateTimerDisplay(seconds) {
        if (!timerCountElement) return;
        timerCountElement.textContent = formatCountdown(seconds);
        if (seconds <= 10) {
            timerCountElement.classList.add('expiring');
        } else {
            timerCountElement.classList.remove('expiring');
        }
    }

    function timerExpired() {
        if (timerElement) timerElement.style.display = 'none';
        if (resendBtn) {
            resendBtn.disabled = false;
            resendBtn.classList.add('visible');
        }
        clearResendCooldown();
    }

    function startTimer(seconds) {
        let remaining = Math.max(0, seconds);

        if (remaining <= 0) {
            timerExpired();
            return;
        }

        if (resendBtn) {
            resendBtn.disabled = true;
            resendBtn.classList.remove('visible');
        }
        if (timerElement) timerElement.style.display = 'flex';
        updateTimerDisplay(remaining);

        if (timerInterval) clearInterval(timerInterval);

        timerInterval = setInterval(function () {
            remaining = getRemainingSeconds();
            if (remaining <= 0) {
                clearInterval(timerInterval);
                timerInterval = null;
                timerExpired();
            } else {
                updateTimerDisplay(remaining);
            }
        }, 1000);
    }

    function bootstrapTimer() {
        let remaining = getRemainingSeconds();

        if (remaining <= 0 && serverCooldown > 0) {
            remaining = serverCooldown;
            setResendCooldownExpiry(remaining);
        }

        if (remaining > 0) {
            if (!localStorage.getItem(cooldownKey)) {
                setResendCooldownExpiry(remaining);
            }
            startTimer(remaining);
        } else {
            timerExpired();
        }
    }

    function markConfirmedUI(message) {
        confirmationHandled = true;
        if (listeningBadge) {
            listeningBadge.innerHTML = '<span class="cp-listening-dot"></span> Password changed';
        }
        if (statusTitle) statusTitle.textContent = 'Password Changed!';
        if (statusSub) statusSub.textContent = message || 'Password updated. You can close this tab.';
        if (statusBadge) {
            statusBadge.textContent = 'Confirmed';
            statusBadge.style.background = '#dcfce7';
            statusBadge.style.color = '#166534';
            statusBadge.style.border = '1px solid #bbf7d0';
        }
    }

    function redirectAfterConfirm(message, redirectUrl) {
        clearResendCooldown();
        if (timerInterval) clearInterval(timerInterval);
        markConfirmedUI(message);
        showPageLoading('Done...');
        setTimeout(function () {
            window.location.replace(redirectUrl || '/login');
        }, 1200);
    }

    async function checkConfirmationStatus() {
        if (confirmationHandled || !statusUrl) return;

        try {
            const response = await fetch(statusUrl, {
                method: 'GET',
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (response.status === 401 || response.status === 419) {
                redirectAfterConfirm('Password changed successfully.', '/login');
                return;
            }

            if (!response.ok) {
                setTimeout(checkConfirmationStatus, POLL_INTERVAL_MS + 2000);
                return;
            }

            const payload = await response.json();

            if (payload.state === 'pending') {
                if (payload.resend_cooldown > 0 && getRemainingSeconds() <= 0) {
                    setResendCooldownExpiry(payload.resend_cooldown);
                    startTimer(payload.resend_cooldown);
                }
                setTimeout(checkConfirmationStatus, POLL_INTERVAL_MS);
                return;
            }

            if (payload.state === 'confirmed') {
                redirectAfterConfirm(
                    payload.message || 'Password changed successfully.',
                    payload.redirect || '/login',
                );
                return;
            }

            if (payload.state === 'cancelled') {
                clearResendCooldown();
                window.location.replace(payload.redirect || '/profile/change-password');
            }
        } catch (error) {
            setTimeout(checkConfirmationStatus, POLL_INTERVAL_MS + 2000);
        }
    }

    bootstrapTimer();
    checkConfirmationStatus();

    if (resendForm) {
        resendForm.addEventListener('submit', function () {
            if (resendBtn) {
                resendBtn.disabled = true;
                resendBtn.classList.remove('visible');
                resendBtn.textContent = 'Sending…';
            }
            setResendCooldownExpiry(COOLDOWN_SECONDS);
            if (timerElement) timerElement.style.display = 'flex';
            startTimer(COOLDOWN_SECONDS);
            showPageLoading('Sending reset link...');
        });
    }

    const cancelForm = document.getElementById('cpCancelForm');
    if (cancelForm) {
        cancelForm.addEventListener('submit', function () {
            clearResendCooldown();
            if (timerInterval) clearInterval(timerInterval);
            showPageLoading('Cancelling request...');
        });
    }
});
