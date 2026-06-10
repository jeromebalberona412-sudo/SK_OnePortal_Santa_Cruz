document.addEventListener('DOMContentLoaded', function () {
    const COOLDOWN_SECONDS = 60;
    const POLL_INTERVAL_MS = 3000;

    const verifySection = document.getElementById('evVerifySection');
    const statusUrl = verifySection?.dataset.statusUrl || '';
    const accountEmail = verifySection?.dataset.email || 'default';
    const cooldownKey = `op_admin_login_verify_resend_${accountEmail}`;

    const timerElement = document.getElementById('evTimer');
    const timerCountElement = document.getElementById('evTimerCount');
    const resendBtn = document.getElementById('evResendBtn');
    const resendForm = document.getElementById('evResendForm');
    const statusTitle = document.getElementById('evStatusTitle');
    const statusSub = document.getElementById('evStatusSub');
    const listeningBadge = document.getElementById('evListeningBadge');
    const overlay = document.getElementById('signin-overlay');

    let timerInterval = null;
    let verificationHandled = false;
    const serverCooldown = Number(window.evResendCooldown || 0);

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
        localStorage.setItem(cooldownKey, String(Date.now() + Math.max(1, seconds || COOLDOWN_SECONDS) * 1000));
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
        timerCountElement.classList.toggle('expiring', seconds <= 10);
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

    function markVerifiedUI(message) {
        verificationHandled = true;
        if (statusTitle) statusTitle.textContent = 'Email Verified!';
        if (statusSub) statusSub.textContent = message || 'Redirecting to dashboard...';
        if (listeningBadge) {
            listeningBadge.innerHTML = '<span class="cp-listening-dot"></span> Email verified';
        }
    }

    function redirectToDashboard(message, redirectUrl) {
        clearResendCooldown();
        if (timerInterval) clearInterval(timerInterval);
        markVerifiedUI(message);
        showPageLoading('Redirecting...');
        setTimeout(function () {
            window.location.replace(redirectUrl || '/dashboard');
        }, 900);
    }

    async function checkVerificationStatus() {
        if (verificationHandled || !statusUrl) return;

        try {
            const response = await fetch(statusUrl, {
                method: 'GET',
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (response.status === 401 || response.status === 419) {
                window.location.replace('/login');
                return;
            }

            if (!response.ok) {
                setTimeout(checkVerificationStatus, POLL_INTERVAL_MS + 2000);
                return;
            }

            const payload = await response.json();

            if (payload.state === 'pending') {
                if (payload.resend_cooldown > 0 && getRemainingSeconds() <= 0) {
                    setResendCooldownExpiry(payload.resend_cooldown);
                    startTimer(payload.resend_cooldown);
                }
                setTimeout(checkVerificationStatus, POLL_INTERVAL_MS);
                return;
            }

            if (payload.state === 'verified') {
                redirectToDashboard(
                    payload.message || 'Email verified. Redirecting to dashboard...',
                    payload.redirect || '/dashboard',
                );
            }
        } catch (error) {
            setTimeout(checkVerificationStatus, POLL_INTERVAL_MS + 2000);
        }
    }

    bootstrapTimer();
    checkVerificationStatus();

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
            showPageLoading('Sending verification email...');
        });
    }
});
