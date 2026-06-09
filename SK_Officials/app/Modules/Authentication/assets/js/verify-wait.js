document.addEventListener('DOMContentLoaded', () => {
    const COOLDOWN_SECONDS = 60;
    const POLL_INTERVAL_MS = 500;
    const BTN_LABEL = 'Resend Verification Email';

    if (typeof window.hideLoading === 'function') {
        window.hideLoading();
    }

    const verifyContent = document.querySelector('.verify-content');
    if (!verifyContent) {
        return;
    }

    const resendUrl = verifyContent.dataset.resendUrl || '';
    const dashboardUrl = verifyContent.dataset.dashboardUrl || '/dashboard';
    const email = verifyContent.dataset.email || '';
    const userId = verifyContent.dataset.userId || '';
    const sessionKey = verifyContent.dataset.sessionKey || email;
    const statusUrl = (() => {
        const base = verifyContent.dataset.statusUrl || '';

        if (!base) {
            return base;
        }

        const params = new URLSearchParams();

        if (sessionKey) {
            params.set('session_key', sessionKey);
        }

        if (userId) {
            params.set('user_id', userId);
        }

        const query = params.toString();

        if (!query) {
            return base;
        }

        const separator = base.includes('?') ? '&' : '?';

        return `${base}${separator}${query}`;
    })();
    const freshSession = verifyContent.dataset.freshSession === '1';
    const resendJustSent = verifyContent.dataset.resendJustSent === '1';
    const serverCooldown = resendJustSent
        ? Number.parseInt(verifyContent.dataset.resendCooldown || '0', 10)
        : 0;
    const shouldShowNotification = verifyContent.dataset.showNotification === '1';
    const notifyTitle = verifyContent.dataset.notifyTitle || 'SK Officials';
    const notifyBody = verifyContent.dataset.notifyBody || 'Check your email for the verification link.';

    const stateElement = document.getElementById('verification-state');
    const refreshHintElement = document.getElementById('refresh-hint');
    const verifyWaitMessage = document.getElementById('verify-wait-message');
    const resendBtn = document.getElementById('resend-btn');
    const resendBtnLabel = document.getElementById('resend-btn-label');
    const resendBtnSpinner = document.getElementById('resend-btn-spinner');
    const resendCooldownElement = document.getElementById('resend-cooldown');
    const resendCooldownCount = document.getElementById('resend-cooldown-count');
    const resendStatusElement = document.getElementById('resend-status');

    const COOLDOWN_KEY = `sk_official_resend_cooldown_${sessionKey}`;
    const LEGACY_COOLDOWN_KEY = `sk_official_resend_cooldown_${email}`;
    const resendSection = document.querySelector('.resend-section');
    let resendTimerInterval = null;
    let resendInFlight = false;
    let verificationComplete = false;
    let redirectStarted = false;

    function clearResendCooldown() {
        localStorage.removeItem(COOLDOWN_KEY);
        localStorage.removeItem(LEGACY_COOLDOWN_KEY);
    }

    function clearAllCooldownStorage() {
        clearResendCooldown();

        for (let index = localStorage.length - 1; index >= 0; index -= 1) {
            const key = localStorage.key(index);
            if (key && key.startsWith('sk_official_resend_cooldown_')) {
                localStorage.removeItem(key);
            }
        }
    }

    function setResendCooldownExpiry(seconds) {
        const duration = Math.max(1, seconds || COOLDOWN_SECONDS);
        localStorage.setItem(COOLDOWN_KEY, String(Date.now() + duration * 1000));
    }

    function getRemainingCooldown() {
        const expiry = Number.parseInt(localStorage.getItem(COOLDOWN_KEY) || '0', 10);

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

    function setButtonLabel(text) {
        if (resendBtnLabel) {
            resendBtnLabel.textContent = text;
        } else if (resendBtn) {
            resendBtn.textContent = text;
        }
    }

    function setButtonLoading(isLoading) {
        if (resendBtn) {
            resendBtn.classList.toggle('is-loading', isLoading);
            resendBtn.disabled = isLoading;
        }

        if (resendBtnSpinner) {
            resendBtnSpinner.hidden = !isLoading;
        }

        if (isLoading) {
            setButtonLabel('Sending…');
        } else {
            setButtonLabel(BTN_LABEL);
        }
    }

    function setResendStatus(message, type = 'success') {
        if (!resendStatusElement) {
            return;
        }

        if (!message) {
            resendStatusElement.hidden = true;
            resendStatusElement.textContent = '';
            resendStatusElement.className = 'resend-status';
            return;
        }

        resendStatusElement.hidden = false;
        resendStatusElement.textContent = message;
        resendStatusElement.className = `resend-status resend-status-${type}`;
    }

    function updateResendButton() {
        const remaining = getRemainingCooldown();

        if (remaining > 0) {
            if (resendBtn) {
                resendBtn.disabled = true;
                resendBtn.classList.remove('is-loading');
            }
            if (resendBtnSpinner) {
                resendBtnSpinner.hidden = true;
            }
            setButtonLabel(BTN_LABEL);

            if (resendCooldownElement) {
                resendCooldownElement.style.display = 'block';
            }
            if (resendCooldownCount) {
                resendCooldownCount.textContent = formatCountdown(remaining);
            }
            return;
        }

        if (resendCooldownElement) {
            resendCooldownElement.style.display = 'none';
        }

        if (!resendInFlight && resendBtn) {
            resendBtn.disabled = false;
            resendBtn.classList.remove('is-loading');
        }
        if (resendBtnSpinner) {
            resendBtnSpinner.hidden = true;
        }
        if (!resendInFlight) {
            setButtonLabel(BTN_LABEL);
        }

        clearResendCooldown();
    }

    function bootstrapResendCooldown() {
        let storedRemaining = getRemainingCooldown();

        if (freshSession && storedRemaining <= 0) {
            setResendCooldownExpiry(COOLDOWN_SECONDS);
            storedRemaining = COOLDOWN_SECONDS;
        } else if (resendJustSent && storedRemaining <= 0 && serverCooldown > 0) {
            setResendCooldownExpiry(serverCooldown);
        }

        updateResendButton();

        if (resendTimerInterval) {
            clearInterval(resendTimerInterval);
        }

        resendTimerInterval = setInterval(updateResendButton, 1000);
    }

    async function requestDeviceNotificationPermission() {
        if (!('Notification' in window)) {
            return;
        }

        if (Notification.permission === 'default') {
            try {
                await Notification.requestPermission();
            } catch (error) {
                // Ignore permission errors.
            }
        }
    }

    function showDeviceNotification(title, body) {
        if (!('Notification' in window) || Notification.permission !== 'granted') {
            return;
        }

        const iconUrl = `${window.location.origin}/images/logo.png`;

        try {
            const notification = new Notification(title, {
                body,
                icon: iconUrl,
                badge: iconUrl,
                tag: `sk-official-email-verify-${sessionKey}`,
                renotify: true,
            });

            notification.onclick = () => {
                window.focus();
                notification.close();
            };
        } catch (error) {
            // Ignore unsupported notification environments.
        }
    }

    function disableResendSection(message = '') {
        verificationComplete = true;

        if (resendBtn) {
            resendBtn.disabled = true;
            resendBtn.classList.remove('is-loading');
        }

        if (resendBtnSpinner) {
            resendBtnSpinner.hidden = true;
        }

        if (resendCooldownElement) {
            resendCooldownElement.style.display = 'none';
        }

        if (resendSection) {
            resendSection.style.display = 'none';
        }

        if (message) {
            setResendStatus(message, 'success');
        }
    }

    function showRefreshHint(message = 'Email verified! Refresh your browser to continue to the dashboard.') {
        if (verifyWaitMessage) {
            verifyWaitMessage.hidden = true;
        }

        if (refreshHintElement) {
            refreshHintElement.hidden = false;
            refreshHintElement.textContent = message;
        }
    }

    function redirectToDashboard(targetUrl) {
        const destination = targetUrl || dashboardUrl;

        if (!destination || redirectStarted) {
            return;
        }

        redirectStarted = true;
        verificationComplete = true;
        disableResendSection();

        if (stateElement) {
            stateElement.className = 'verification-state success';
            stateElement.textContent = 'Email verified successfully!';
        }

        const successModal = document.getElementById('success-modal');
        if (successModal) {
            successModal.classList.add('show');
        }

        showDeviceNotification('SK Officials', 'Email verified successfully!');

        if (typeof window.showLoading === 'function') {
            window.showLoading('Redirecting', 'Taking you to the dashboard...');
        }

        setTimeout(() => {
            window.location.replace(destination);
        }, 350);
    }

    async function resendVerificationEmail() {
        if (verificationComplete || resendInFlight) {
            return;
        }

        const remaining = getRemainingCooldown();
        if (remaining > 0) {
            setResendStatus(`Please wait ${formatCountdown(remaining)} before resending.`, 'error');
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        resendInFlight = true;
        setResendStatus('', 'success');
        setButtonLoading(true);

        try {
            const response = await fetch(resendUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ email, session_key: sessionKey, user_id: userId }),
            });

            const payload = await response.json().catch(() => ({}));

            if (payload.state === 'verified' && payload.redirect) {
                redirectToDashboard(payload.redirect);
                return;
            }

            if (!response.ok || !payload.ok) {
                const message = payload.message || 'Unable to resend verification email. Please try again.';
                setResendStatus(message, 'error');
                setButtonLoading(false);

                if (Number(payload.resend_cooldown) > 0) {
                    setResendCooldownExpiry(Number(payload.resend_cooldown));
                }

                updateResendButton();
                return;
            }

            setResendStatus(payload.message || 'Verification email resent.', 'success');
            setResendCooldownExpiry(Number(payload.resend_cooldown) || COOLDOWN_SECONDS);
            setButtonLoading(false);
            updateResendButton();
            showDeviceNotification(notifyTitle, 'Verification email resent. Check your inbox.');
        } catch (error) {
            setResendStatus('Unable to resend verification email. Please try again.', 'error');
            setButtonLoading(false);
            updateResendButton();
        } finally {
            resendInFlight = false;
        }
    }

    if (resendBtn) {
        resendBtn.addEventListener('click', (event) => {
            event.preventDefault();
            resendVerificationEmail();
        });
    }

    async function checkStatus() {
        if (verificationComplete) {
            return;
        }

        try {
            const response = await fetch(statusUrl, {
                method: 'GET',
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
                cache: 'no-store',
            });

            const payload = await response.json().catch(() => ({}));

            if (payload.state === 'verified' && payload.redirect) {
                showRefreshHint('Email verified! Redirecting you to the dashboard...');
                clearResendCooldown();
                redirectToDashboard(payload.redirect);
                return;
            }

            if (payload.state === 'expired') {
                if (stateElement) {
                    stateElement.className = 'verification-state warning';
                    stateElement.textContent = 'Verification window expired. Please sign in again.';
                }
                clearResendCooldown();
                return;
            }
        } catch (error) {
            if (stateElement) {
                stateElement.className = 'verification-state error';
                stateElement.textContent = 'Unable to check verification status. Retrying...';
            }
        }

        setTimeout(checkStatus, POLL_INTERVAL_MS);
    }

    if (shouldShowNotification) {
        requestDeviceNotificationPermission().then(() => {
            showDeviceNotification(notifyTitle, notifyBody);
        });
    }

    bootstrapResendCooldown();
    checkStatus();

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && !verificationComplete) {
            checkStatus();
        }
    });

    window.addEventListener('pageshow', () => {
        if (typeof window.hideLoading === 'function') {
            window.hideLoading();
        }

        updateResendButton();

        if (!verificationComplete) {
            checkStatus();
        }
    });

});
