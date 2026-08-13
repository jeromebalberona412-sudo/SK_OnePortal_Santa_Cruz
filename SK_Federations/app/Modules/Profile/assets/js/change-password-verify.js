document.addEventListener('DOMContentLoaded', function () {
    const COOLDOWN_SECONDS = 60;
    const POLL_INTERVAL_MS = 3000;

    const verifySection = document.getElementById('cpVerifySection');
    const statusUrl = verifySection?.dataset.statusUrl || '';
    const accountEmail = verifySection?.dataset.email || 'default';
    const cooldownKey = `sk_password_change_resend_${accountEmail}`;

    const timerElement = document.getElementById('cpTimer');
    const timerCountElement = document.getElementById('cpTimerCount');
    const resendBtn = document.getElementById('cpResendBtn');
    const resendForm = document.getElementById('cpResendForm');
    const listeningBadge = document.getElementById('cpListeningBadge');
    const statusTitle = document.getElementById('cpStatusTitle');
    const statusSub = document.getElementById('cpStatusSub');
    const statusBadge = document.getElementById('cpStatusBadge');
    const infoBox = document.getElementById('cpInfoBox');

    let timerInterval = null;
    let confirmationHandled = false;
    const serverCooldown = Number(window.cpResendCooldown || 0);

    function showPageLoading() {}

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

        if (serverCooldown > 0) {
            return serverCooldown;
        }

        return 0;
    }

    function formatCountdown(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${String(secs).padStart(2, '0')}`;
    }

    function updateTimerDisplay(seconds) {
        if (timerCountElement) {
            timerCountElement.textContent = formatCountdown(seconds);
        }
    }

    function timerExpired() {
        if (timerElement) {
            timerElement.style.display = 'none';
        }
        if (resendBtn) {
            resendBtn.disabled = false;
            resendBtn.textContent = 'Resend Verification';
        }
        clearResendCooldown();
    }

    function startTimer(seconds) {
        let remaining = seconds;

        if (remaining <= 0) {
            timerExpired();
            return;
        }

        if (resendBtn) {
            resendBtn.disabled = true;
            resendBtn.textContent = 'Resend Verification';
        }
        if (timerElement) {
            timerElement.style.display = 'block';
        }
        updateTimerDisplay(remaining);

        if (timerInterval) {
            clearInterval(timerInterval);
        }

        timerInterval = setInterval(function () {
            remaining = getRemainingSeconds();
            if (remaining <= 0) {
                clearInterval(timerInterval);
                timerExpired();
            } else {
                updateTimerDisplay(remaining);
            }
        }, 1000);
    }

    function bootstrapTimer() {
        let remaining = getRemainingSeconds();

        if (window.cpFreshVerification === true || window.cpFreshVerification === 'true') {
            setResendCooldownExpiry(COOLDOWN_SECONDS);
            remaining = COOLDOWN_SECONDS;
        } else if (remaining <= 0 && serverCooldown > 0) {
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

        if (verifySection) {
            verifySection.classList.add('is-confirmed');
        }
        if (listeningBadge) {
            listeningBadge.classList.add('is-confirmed');
            listeningBadge.innerHTML = '<span class="cp-listening-dot"></span> Password confirmed';
        }
        if (statusTitle) {
            statusTitle.textContent = 'Password Confirmed!';
        }
        if (statusSub) {
            statusSub.textContent = message || 'Signing you out so you can log in with your new password.';
        }
        if (statusBadge) {
            statusBadge.textContent = 'Confirmed';
            statusBadge.style.background = '#dcfce7';
            statusBadge.style.color = '#166534';
        }
        if (infoBox) {
            infoBox.textContent = message || 'Password change confirmed. Redirecting to login...';
        }
    }

    function redirectToLogin(message, redirectUrl) {
        clearResendCooldown();
        if (timerInterval) {
            clearInterval(timerInterval);
        }
        markConfirmedUI(message);
        showPageLoading('Signing out...');
        setTimeout(function () {
            window.location.replace(redirectUrl || '/login');
        }, 900);
    }

    async function checkConfirmationStatus() {
        if (confirmationHandled || !statusUrl) {
            return;
        }

        try {
            const response = await fetch(statusUrl, {
                method: 'GET',
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (response.status === 401 || response.status === 419) {
                redirectToLogin('Password changed successfully. Please sign in with your new password.', '/login');
                return;
            }

            if (!response.ok) {
                setTimeout(checkConfirmationStatus, POLL_INTERVAL_MS + 2000);
                return;
            }

            const payload = await response.json();

            if (payload.state === 'pending') {
                if (payload.resend_cooldown > 0) {
                    const localRemaining = getRemainingSeconds();
                    if (localRemaining <= 0) {
                        setResendCooldownExpiry(payload.resend_cooldown);
                        startTimer(payload.resend_cooldown);
                    }
                }
                setTimeout(checkConfirmationStatus, POLL_INTERVAL_MS);
                return;
            }

            if (payload.state === 'confirmed') {
                redirectToLogin(
                    payload.message || 'Password changed successfully. Please sign in with your new password.',
                    payload.redirect || '/login',
                );
                return;
            }

            if (payload.state === 'cancelled') {
                clearResendCooldown();
                window.location.replace(payload.redirect || '/change-password');
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
                resendBtn.textContent = 'Sending…';
            }
            setResendCooldownExpiry(COOLDOWN_SECONDS);
        });
    }

    const cancelForm = document.getElementById('cpCancelForm');
    if (cancelForm) {
        cancelForm.addEventListener('submit', function () {
            clearResendCooldown();
            if (timerInterval) {
                clearInterval(timerInterval);
            }
            showPageLoading('Cancelling request...');
        });
    }
});
