(() => {
    const statusUrl = document.querySelector('[data-status-url]')?.getAttribute('data-status-url') || '';
    const expiresAt = new Date(document.querySelector('[data-expires-at]')?.getAttribute('data-expires-at') || '');
    const stateElement = document.getElementById('verification-state');
    const countdownElement = document.getElementById('countdown-timer');
    const resendBtn = document.getElementById('resend-btn');
    const resendCooldownElement = document.getElementById('resend-cooldown');
    const resendForm = document.getElementById('resend-form');
    const email = document.querySelector('[data-email]')?.getAttribute('data-email') || '';

    const COOLDOWN_KEY = 'sk_official_resend_cooldown_' + email;
    const COOLDOWN_DURATION = 600;

    function getResendCooldownExpiry() {
        const stored = localStorage.getItem(COOLDOWN_KEY);
        return stored ? parseInt(stored, 10) : 0;
    }

    function setResendCooldownExpiry() {
        const expiryTime = Date.now() + (COOLDOWN_DURATION * 1000);
        localStorage.setItem(COOLDOWN_KEY, expiryTime.toString());
    }

    function clearResendCooldown() {
        localStorage.removeItem(COOLDOWN_KEY);
    }

    function getRemainingCooldown() {
        const expiry = getResendCooldownExpiry();
        if (expiry === 0) return 0;
        const remaining = Math.max(0, Math.ceil((expiry - Date.now()) / 1000));
        if (remaining === 0) {
            clearResendCooldown();
        }
        return remaining;
    }

    function updateResendButton() {
        const remaining = getRemainingCooldown();
        if (remaining > 0) {
            resendBtn.disabled = true;
            resendCooldownElement.style.display = 'block';
            resendCooldownElement.textContent = `Please try again in ${remaining} seconds`;
        } else {
            resendBtn.disabled = false;
            resendCooldownElement.style.display = 'none';
        }
    }

    resendForm.addEventListener('submit', function(e) {
        const remaining = getRemainingCooldown();
        if (remaining > 0) {
            e.preventDefault();
            return false;
        }
        setResendCooldownExpiry();
    });

    function renderCountdown() {
        const seconds = Math.max(0, Math.floor((expiresAt.getTime() - Date.now()) / 1000));
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        const formattedMinutes = String(minutes).padStart(2, '0');
        const formattedSeconds = String(remainingSeconds).padStart(2, '0');
        countdownElement.textContent = `${formattedMinutes}:${formattedSeconds}`;
    }

    async function checkStatus() {
        try {
            const response = await fetch(statusUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            const payload = await response.json();

            if (payload.state === 'verified' && payload.redirect) {
                stateElement.className = 'verification-state success';
                stateElement.textContent = 'Email verified successfully!';
                clearResendCooldown();

                const successModal = document.getElementById('success-modal');
                successModal.classList.add('show');

                setTimeout(() => {
                    successModal.classList.add('fade-out');
                    setTimeout(() => {
                        LoadingScreen.show('Redirecting', 'Taking you to dashboard...');
                        setTimeout(() => {
                            window.location.replace(payload.redirect);
                        }, 500);
                    }, 300);
                }, 3000);
                return;
            }

            if (payload.state === 'expired') {
                stateElement.className = 'verification-state warning';
                stateElement.textContent = 'Verification window expired. Please sign in again.';
                clearResendCooldown();
                return;
            }

            if (payload.state === 'missing') {
                stateElement.className = 'verification-state warning';
                stateElement.textContent = 'Verification session not found. Please sign in again.';
                clearResendCooldown();
                return;
            }
        } catch (error) {
            stateElement.className = 'verification-state error';
            stateElement.textContent = 'Unable to check verification status. Retrying...';
        }

        renderCountdown();
        updateResendButton();
        setTimeout(checkStatus, 1000);
    }

    renderCountdown();
    updateResendButton();
    checkStatus();

    setInterval(updateResendButton, 1000);

    document.querySelector('.form-footer a')?.addEventListener('click', function(e) {
        e.preventDefault();
        LoadingScreen.show('Redirecting', 'Taking you to login...');
        setTimeout(() => {
            window.location.href = this.href;
        }, 300);
    });
})();
