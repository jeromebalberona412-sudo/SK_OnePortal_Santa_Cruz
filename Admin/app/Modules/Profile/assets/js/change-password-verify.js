document.addEventListener('DOMContentLoaded', function () {
    const COOLDOWN_SECONDS = 60;
    const POLL_INTERVAL_MS = 3000;

    const verifySection = document.getElementById('cpVerifySection');
    const statusUrl = verifySection?.dataset.statusUrl || '';
    const accountEmail = verifySection?.dataset.email || 'default';
    const cooldownKey = `op_admin_password_change_resend_${accountEmail}`;
    const serverCooldown = Number.parseInt(verifySection?.dataset.resendCooldown || '0', 10);

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
        localStorage.setItem(
            cooldownKey,
            String(Date.now() + Math.max(1, seconds || COOLDOWN_SECONDS) * 1000),
        );
    }

    function getRemainingSeconds() {
        const expiry = Number.parseInt(localStorage.getItem(cooldownKey) || '0', 10);
        if (expiry > Date.now()) {
            return Math.max(0, Math.ceil((expiry - Date.now()) / 1000));
        }

        return 0;
    }

    function formatCountdown(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${String(secs).padStart(2, '0')}`;
    }

    function updateTimerDisplay(seconds) {
        if (!timerCountElement) return;
        timerCountElement.textContent = formatCountdown(seconds);
        timerCountElement.classList.toggle('expiring', seconds <= 10);
    }

    function timerExpired() {
        if (timerElement) timerElement.style.display = 'none';
        if (resendBtn) {
            resendBtn.disabled = false;
            resendBtn.classList.add('visible');
            resendBtn.textContent = 'Resend Reset Link';
        }
        clearResendCooldown();
    }

    function startTimer(seconds) {
        const remaining = Math.max(0, seconds);
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
            const currentRemaining = getRemainingSeconds();
            if (currentRemaining <= 0) {
                clearInterval(timerInterval);
                timerInterval = null;
                timerExpired();
            } else {
                updateTimerDisplay(currentRemaining);
            }
        }, 1000);
    }

    function syncTimerFromServer(seconds) {
        const remaining = Math.max(0, Number.parseInt(String(seconds || 0), 10));
        if (remaining > 0) {
            setResendCooldownExpiry(remaining);
            startTimer(remaining);
            return;
        }

        if (!timerInterval) {
            timerExpired();
        }
    }

    function bootstrapTimer() {
        const localRemaining = getRemainingSeconds();

        if (serverCooldown > 0) {
            setResendCooldownExpiry(serverCooldown);
            startTimer(serverCooldown);
            return;
        }

        if (localRemaining > 0) {
            startTimer(localRemaining);
            return;
        }

        timerExpired();
    }

    function markConfirmedUI(message) {
        confirmationHandled = true;
        if (listeningBadge) {
            listeningBadge.innerHTML = '<span class="ev-listening-dot"></span> Password changed';
        }
        if (statusTitle) statusTitle.textContent = 'Password Changed!';
        if (statusSub) statusSub.textContent = message || 'Password updated. You can close this tab.';
        if (statusBadge) {
            statusBadge.textContent = 'Confirmed';
            statusBadge.classList.add('is-confirmed');
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
                if (payload.resend_cooldown > 0) {
                    const currentRemaining = getRemainingSeconds();
                    if (currentRemaining <= 0 || Math.abs(currentRemaining - payload.resend_cooldown) > 2) {
                        syncTimerFromServer(payload.resend_cooldown);
                    }
                } else if (!timerInterval) {
                    timerExpired();
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
