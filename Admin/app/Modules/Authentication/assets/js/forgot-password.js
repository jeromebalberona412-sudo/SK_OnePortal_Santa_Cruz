document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('fpSubmitBtn');
    const input = document.getElementById('email');
    const form = document.getElementById('fpForm');
    const resendForm = document.getElementById('fpResendForm');
    const overlay = document.getElementById('signin-overlay');

    if (btn && input) {
        const toggle = () => {
            btn.disabled = !input.value.trim();
        };
        toggle();
        input.addEventListener('input', toggle);
    }

    const showOverlay = (title, sub) => {
        if (!overlay) return;
        const titleEl = overlay.querySelector('.signin-overlay-title');
        const subEl = overlay.querySelector('.signin-overlay-sub');
        if (titleEl) titleEl.textContent = title;
        if (subEl) subEl.textContent = sub;
        overlay.removeAttribute('hidden');
        overlay.classList.add('is-visible');
    };

    if (form && overlay) {
        form.addEventListener('submit', () => {
            showOverlay('Sending Reset Link', 'Please wait...');
        });
    }

    if (resendForm && overlay) {
        resendForm.addEventListener('submit', () => {
            showOverlay('Resending Reset Link', 'Please wait...');
        });
    }

    const timerRow = document.getElementById('fpTimerRow');
    const countdown = document.getElementById('fpCountdown');
    const resendBtn = document.getElementById('fpResendBtn');

    if (!timerRow || !countdown || !resendBtn || !resendForm) {
        return;
    }

    let seconds = 60;
    let tick;

    const runTick = () => {
        tick = setInterval(() => {
            seconds -= 1;
            const minutes = Math.floor(seconds / 60);
            const secs = seconds % 60;
            countdown.textContent = `${minutes}:${secs < 10 ? '0' : ''}${secs}`;

            if (seconds <= 10) {
                countdown.classList.add('expiring');
            } else {
                countdown.classList.remove('expiring');
            }

            if (seconds <= 0) {
                clearInterval(tick);
                timerRow.style.display = 'none';
                resendBtn.classList.add('visible');
            }
        }, 1000);
    };

    runTick();

    resendBtn.addEventListener('click', () => {
        resendForm.submit();
    });
});
