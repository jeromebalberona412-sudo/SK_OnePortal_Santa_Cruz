document.addEventListener('DOMContentLoaded', function () {
    const COOLDOWN_SECONDS = 60;
    const POLL_INTERVAL_MS = 3000;
    const BTN_LABEL = 'Resend Verification';

    const verifySection = document.getElementById('cpVerifySection');
    const statusUrl = verifySection?.dataset.statusUrl || '';
    const resendUrl = verifySection?.dataset.resendUrl || '';
    const accountEmail = verifySection?.dataset.email || 'default';
    const cooldownKey = `sk_officials_password_change_resend_${accountEmail}`;
    const resendBtn = document.getElementById('cpResendBtn');
    const resendBtnText = document.getElementById('cpResendBtnText');
    const resendForm = document.getElementById('cpResendForm');
    const feedback = document.getElementById('cpFeedback');

    let timerInterval = null;
    let remainingSeconds = 0;
    let resendInFlight = false;
    let confirmationHandled = false;
    const serverCooldown = Number(window.cpResendCooldown || 0);

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || resendForm?.querySelector('input[name="_token"]')?.value
            || '';
    }

    function setFeedback(message, type) {
        if (!feedback || !message) {
            return;
        }

        feedback.hidden = false;
        feedback.textContent = message;
        feedback.classList.remove('sk-alert-success', 'sk-alert-error');
        feedback.classList.add(type === 'error' ? 'sk-alert-error' : 'sk-alert-success');
    }

    function setButtonLabel(text) {
        if (resendBtnText) {
            resendBtnText.textContent = text;
        } else if (resendBtn) {
            resendBtn.textContent = text;
        }
    }

    function setButtonLoading(isLoading) {
        if (!resendBtn) {
            return;
        }

        resendBtn.classList.toggle('is-loading', isLoading);
        resendBtn.disabled = isLoading || remainingSeconds > 0 || resendInFlight;

        if (isLoading) {
            setButtonLabel('Sending resend verification...');
        }
    }

    function storedRemaining() {
        const expiry = Number.parseInt(localStorage.getItem(cooldownKey) || '0', 10);
        return expiry > Date.now() ? Math.max(0, Math.ceil((expiry - Date.now()) / 1000)) : 0;
    }

    function setResendCooldownExpiry(seconds) {
        localStorage.setItem(cooldownKey, String(Date.now() + Math.max(1, seconds || COOLDOWN_SECONDS) * 1000));
    }

    function clearResendCooldown() {
        localStorage.removeItem(cooldownKey);
    }

    function clearCountdown() {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        remainingSeconds = 0;
    }

    function restoreResendButton() {
        resendInFlight = false;
        setButtonLoading(false);
        if (remainingSeconds <= 0 && resendBtn) {
            resendBtn.disabled = false;
            setButtonLabel(BTN_LABEL);
        }
    }

    function tickCountdown() {
        remainingSeconds -= 1;

        if (remainingSeconds <= 0) {
            clearCountdown();
            clearResendCooldown();
            restoreResendButton();
            return;
        }

        if (resendBtn) {
            resendBtn.disabled = true;
        }
        setButtonLabel(`Resend available in ${remainingSeconds}s`);
    }

    function startCountdown(seconds) {
        clearCountdown();
        remainingSeconds = Math.max(0, Number(seconds) || 0);

        if (remainingSeconds <= 0) {
            clearResendCooldown();
            restoreResendButton();
            return;
        }

        setResendCooldownExpiry(remainingSeconds);

        if (resendBtn) {
            resendBtn.disabled = true;
        }
        resendBtn?.classList.remove('is-loading');
        setButtonLabel(`Resend available in ${remainingSeconds}s`);

        timerInterval = setInterval(tickCountdown, 1000);
    }

    function bootstrapTimer() {
        let remaining = storedRemaining();
        if (remaining <= 0 && serverCooldown > 0) {
            remaining = serverCooldown;
        }
        if (remaining > 0) {
            startCountdown(remaining);
        }
    }

    function markConfirmedUI() {
        confirmationHandled = true;
        if (verifySection) {
            verifySection.classList.add('is-confirmed');
        }
    }

    function redirectToLogin(message, redirectUrl) {
        clearCountdown();
        clearResendCooldown();
        markConfirmedUI();
        if (feedback && message) {
            setFeedback(message, 'success');
        }
        window.location.replace(redirectUrl || '/login');
    }

    async function checkConfirmationStatus() {
        if (confirmationHandled || !statusUrl) {
            return;
        }

        try {
            const response = await fetch(statusUrl, {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            const contentType = response.headers.get('content-type') || '';
            if (response.status === 401 || response.status === 419 || response.redirected || !contentType.includes('application/json')) {
                redirectToLogin('Password changed successfully. Please sign in with your new password.', '/login');
                return;
            }

            if (!response.ok) {
                setTimeout(checkConfirmationStatus, POLL_INTERVAL_MS + 2000);
                return;
            }

            const payload = await response.json();

            if (payload.state === 'pending') {
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
                window.location.replace(payload.redirect || '/change-password');
            }
        } catch (error) {
            setTimeout(checkConfirmationStatus, POLL_INTERVAL_MS + 2000);
        }
    }

    async function submitResend(event) {
        event.preventDefault();

        if (resendInFlight || remainingSeconds > 0 || storedRemaining() > 0 || !(resendUrl || resendForm)) {
            return;
        }

        resendInFlight = true;
        setButtonLoading(true);

        try {
            const response = await fetch(resendUrl || resendForm.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ _token: csrfToken() }),
            });

            let payload = {};
            try {
                payload = await response.json();
            } catch (parseError) {
                payload = {};
            }

            if (response.status === 419) {
                setFeedback('Your session expired. Please refresh the page and try again.', 'error');
                restoreResendButton();
                return;
            }

            if (response.ok && payload.ok) {
                setButtonLoading(false);
                setFeedback(payload.message || 'Verification email resent.', 'success');
                startCountdown(payload.cooldown || COOLDOWN_SECONDS);
                resendInFlight = false;
                return;
            }

            setFeedback(payload.message || 'Unable to resend verification email. Please try again.', 'error');
            restoreResendButton();

            if (Number(payload.cooldown) > 0) {
                startCountdown(Number(payload.cooldown));
            }
        } catch (error) {
            setFeedback('Unable to resend verification. Please check your connection and try again.', 'error');
            restoreResendButton();
        }
    }

    bootstrapTimer();
    checkConfirmationStatus();

    if (resendBtn) {
        resendBtn.addEventListener('click', function (event) {
            event.preventDefault();
            submitResend(event);
        });
    }

    if (resendForm) {
        resendForm.addEventListener('submit', function (event) {
            event.preventDefault();
        });
    }

    const cancelForm = document.getElementById('cpCancelForm');
    if (cancelForm) {
        cancelForm.addEventListener('submit', function () {
            clearCountdown();
            clearResendCooldown();
        });
    }
});
