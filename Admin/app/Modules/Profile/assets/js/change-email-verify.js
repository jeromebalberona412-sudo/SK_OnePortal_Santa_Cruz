document.addEventListener('DOMContentLoaded', function () {
    const COOLDOWN_SECONDS = 60;
    const TIMER_START_KEY = 'op_admin_email_change_timer_start';

    const timerElement = document.getElementById('ceTimer');
    const timerCountElement = document.getElementById('ceTimerCount');
    const resendBtn = document.getElementById('ceResendBtn');
    const resendForm = document.getElementById('ceResendForm');

    let timerInterval = null;
    const serverCooldown = Number(window.ceResendCooldown || 0);

    function formatCountdown(seconds) {
        const mins = Math.floor(Math.max(0, seconds) / 60);
        const secs = Math.max(0, seconds) % 60;
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
        localStorage.removeItem(TIMER_START_KEY);
    }

    function getRemainingSeconds() {
        const storedStart = localStorage.getItem(TIMER_START_KEY);
        if (storedStart) {
            const elapsed = Math.floor((Date.now() - parseInt(storedStart, 10)) / 1000);
            return Math.max(0, COOLDOWN_SECONDS - elapsed);
        }

        return serverCooldown > 0 ? serverCooldown : 0;
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

        if (remaining > 0) {
            if (!localStorage.getItem(TIMER_START_KEY)) {
                const startedAt = Date.now() - ((COOLDOWN_SECONDS - remaining) * 1000);
                localStorage.setItem(TIMER_START_KEY, startedAt.toString());
            }
            startTimer(remaining);
        } else {
            timerExpired();
        }
    }

    bootstrapTimer();

    if (resendForm) {
        resendForm.addEventListener('submit', function () {
            if (resendBtn) {
                resendBtn.disabled = true;
                resendBtn.classList.remove('visible');
                resendBtn.textContent = 'Sending…';
            }
            localStorage.setItem(TIMER_START_KEY, Date.now().toString());
            if (timerElement) timerElement.style.display = 'flex';
            startTimer(COOLDOWN_SECONDS);
        });
    }

    const cancelForm = document.getElementById('ceCancelForm');
    if (cancelForm) {
        cancelForm.addEventListener('submit', function () {
            localStorage.removeItem(TIMER_START_KEY);
            if (timerInterval) clearInterval(timerInterval);
        });
    }
});
