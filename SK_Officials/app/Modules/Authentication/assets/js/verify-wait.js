(() => {
    const COOLDOWN_SECONDS = 60;
    const POLL_INTERVAL_MS = 1000;

    const statusUrl = document.querySelector('[data-status-url]')?.getAttribute('data-status-url') || '';
    const expiresAt = new Date(document.querySelector('[data-expires-at]')?.getAttribute('data-expires-at') || '');
    const stateElement = document.getElementById('verification-state');
    const countdownElement = document.getElementById('countdown-timer');
    const resendBtn = document.getElementById('resend-btn');
    const resendCooldownElement = document.getElementById('resend-cooldown');
    const resendCooldownCount = document.getElementById('resend-cooldown-count');
    const resendForm = document.getElementById('resend-form');
    const email = document.querySelector('[data-email]')?.getAttribute('data-email') || '';

    const COOLDOWN_KEY = `sk_official_resend_cooldown_${email}`;
    const serverCooldown = Number(window.skVerifyResendCooldown || 0);

    let resendTimerInterval = null;

    function clearResendCooldown() {
        localStorage.removeItem(COOLDOWN_KEY);
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

        return serverCooldown > 0 ? serverCooldown : 0;
    }

    function formatCountdown(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${String(secs).padStart(2, '0')}`;
    }

    function updateResendButton() {
        const remaining = getRemainingCooldown();

        if (remaining > 0) {
            if (resendBtn) {
                resendBtn.disabled = true;
            }
            if (resendCooldownElement) {
                resendCooldownElement.style.display = 'block';
            }
            if (resendCooldownCount) {
                resendCooldownCount.textContent = formatCountdown(remaining);
            }
            return;
        }

        if (resendBtn) {
            resendBtn.disabled = false;
        }
        if (resendCooldownElement) {
            resendCooldownElement.style.display = 'none';
        }
        clearResendCooldown();
    }

    function bootstrapResendCooldown() {
        let remaining = getRemainingCooldown();

        if (remaining <= 0 && serverCooldown > 0) {
            remaining = serverCooldown;
            setResendCooldownExpiry(remaining);
        }

        if (remaining > 0 && !localStorage.getItem(COOLDOWN_KEY)) {
            setResendCooldownExpiry(remaining);
        }

        updateResendButton();

        if (resendTimerInterval) {
            clearInterval(resendTimerInterval);
        }

        resendTimerInterval = setInterval(updateResendButton, 1000);
    }

    if (resendForm) {
        resendForm.addEventListener('submit', function (e) {
            const remaining = getRemainingCooldown();
            if (remaining > 0) {
                e.preventDefault();
                return false;
            }

            if (resendBtn) {
                resendBtn.disabled = true;
                resendBtn.textContent = 'Sending…';
            }

            setResendCooldownExpiry(COOLDOWN_SECONDS);
        });
    }

    function renderCountdown() {
        const seconds = Math.max(0, Math.floor((expiresAt.getTime() - Date.now()) / 1000));
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        if (countdownElement) {
            countdownElement.textContent = `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
        }
    }

    async function checkStatus() {
        try {
            const response = await fetch(statusUrl, {
                method: 'GET',
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            const payload = await response.json();

            if (payload.state === 'verified' && payload.redirect) {
                if (stateElement) {
                    stateElement.className = 'verification-state success';
                    stateElement.textContent = 'Email verified successfully!';
                }
                clearResendCooldown();

                const successModal = document.getElementById('success-modal');
                if (successModal) {
                    successModal.classList.add('show');
                }

                setTimeout(() => {
                    if (successModal) {
                        successModal.classList.add('fade-out');
                    }
                    setTimeout(() => {
                        if (window.showLoading) {
                            window.showLoading('Redirecting');
                        }
                        setTimeout(() => {
                            window.location.replace(payload.redirect);
                        }, 500);
                    }, 300);
                }, 3000);
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

            if (payload.state === 'missing') {
                if (stateElement) {
                    stateElement.className = 'verification-state warning';
                    stateElement.textContent = 'Verification session not found. Please sign in again.';
                }
                clearResendCooldown();
                return;
            }

            if (payload.state === 'pending' && payload.resend_cooldown > 0) {
                const localRemaining = getRemainingCooldown();
                if (localRemaining <= 0) {
                    setResendCooldownExpiry(payload.resend_cooldown);
                    updateResendButton();
                }
            }
        } catch (error) {
            if (stateElement) {
                stateElement.className = 'verification-state error';
                stateElement.textContent = 'Unable to check verification status. Retrying...';
            }
        }

        renderCountdown();
        updateResendButton();
        setTimeout(checkStatus, POLL_INTERVAL_MS);
    }

    bootstrapResendCooldown();
    renderCountdown();
    checkStatus();

    document.querySelector('.form-footer a')?.addEventListener('click', function (e) {
        e.preventDefault();
        if (window.showLoading) {
            window.showLoading('Redirecting');
        }
        setTimeout(() => {
            window.location.href = this.href;
        }, 300);
    });
})();
