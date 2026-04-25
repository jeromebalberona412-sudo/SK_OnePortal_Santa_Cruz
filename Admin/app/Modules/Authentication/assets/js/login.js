document.addEventListener('DOMContentLoaded', () => {

    // ── Login form — show loading overlay on submit ─────────
    const loginForm = document.querySelector('form[action*="login"]:not([action*="two-factor"])');
    const overlay   = document.getElementById('signin-overlay');
    const overlaySub = document.getElementById('signin-overlay-sub');

    if (loginForm && overlay) {
        loginForm.addEventListener('submit', () => {
            // Show overlay
            overlay.hidden = false;
            overlay.classList.add('is-visible');

            // Animate the sub-text through stages
            const stages = [
                { text: 'Verifying credentials...', delay: 0 },
                { text: 'Checking authentication...', delay: 900 },
                { text: 'Redirecting to 2FA...', delay: 1800 },
            ];

            stages.forEach(({ text, delay }) => {
                setTimeout(() => {
                    if (overlaySub) overlaySub.textContent = text;
                }, delay);
            });
        });
    }

    // ── Password visibility toggle ──────────────────────────
    document.querySelectorAll('.login-toggle-pw').forEach((btn) => {
        const input   = btn.closest('.login-input-wrap')?.querySelector('input[type="password"], input[type="text"]');
        const iconShow = btn.querySelector('.pw-icon-show');
        const iconHide = btn.querySelector('.pw-icon-hide');
        if (!input) return;

        btn.addEventListener('click', () => {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            iconShow?.classList.toggle('d-none', !isPassword);
            iconHide?.classList.toggle('d-none', isPassword);
            btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        });
    });

    // ── OTP digit-group handler (two-factor pages) ──────────
    document.querySelectorAll('[data-otp-group]').forEach((group) => {
        const hiddenCode  = group.querySelector('input[type="hidden"][name="code"]');
        const digitInputs = Array.from(group.querySelectorAll('.otp-digit'));
        if (!hiddenCode || digitInputs.length === 0) return;

        const syncHiddenCode = () => {
            hiddenCode.value = digitInputs.map((i) => i.value).join('');
        };

        const fillFromRaw = (rawValue) => {
            const digits = rawValue.replace(/\D/g, '').slice(0, digitInputs.length);
            digitInputs.forEach((input, idx) => { input.value = digits[idx] || ''; });
            syncHiddenCode();
            return digits.length;
        };

        if (hiddenCode.value) fillFromRaw(hiddenCode.value);

        digitInputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                input.value = input.value.replace(/\D/g, '').slice(-1);
                if (input.value && index < digitInputs.length - 1) {
                    digitInputs[index + 1].focus();
                    digitInputs[index + 1].select();
                }
                syncHiddenCode();
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && index > 0) {
                    digitInputs[index - 1].focus();
                    digitInputs[index - 1].select();
                }
            });

            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text') || '';
                const count  = fillFromRaw(pasted);
                const target = Math.min(Math.max(count - 1, 0), digitInputs.length - 1);
                digitInputs[target].focus();
                digitInputs[target].select();
            });

            input.addEventListener('focus', () => input.select());
        });

        const otpForm = group.closest('form');
        if (otpForm) otpForm.addEventListener('submit', syncHiddenCode);
    });

    // ── Two-factor challenge countdown timer ────────────────
    const timerWrap  = document.querySelector('.challenge-timer-wrap');
    const timerValue = document.getElementById('challenge-timer');
    const expiredMsg = document.getElementById('challenge-expired-message');

    if (timerWrap && timerValue) {
        const totalSeconds = Number.parseInt(timerWrap.dataset.challengeExpirySeconds || '600', 10);

        if (Number.isFinite(totalSeconds) && totalSeconds > 0) {
            const challengeForms = Array.from(
                document.querySelectorAll('form[action$="/two-factor-challenge"]')
            );

            const fmt = (s) => {
                const m   = Math.floor(s / 60).toString().padStart(2, '0');
                const sec = (s % 60).toString().padStart(2, '0');
                return `${m}:${sec}`;
            };

            const setExpired = () => {
                timerValue.textContent = '00:00';
                timerValue.style.color = '#c62828';
                challengeForms.forEach((form) => {
                    form.classList.add('is-expired');
                    form.querySelectorAll('input, button').forEach((el) => { el.disabled = true; });
                });
                if (expiredMsg) expiredMsg.hidden = false;
            };

            let secondsLeft = totalSeconds;
            timerValue.textContent = fmt(secondsLeft);

            const intervalId = window.setInterval(() => {
                secondsLeft -= 1;
                if (secondsLeft <= 0) {
                    window.clearInterval(intervalId);
                    setExpired();
                    return;
                }
                timerValue.textContent = fmt(secondsLeft);
            }, 1000);
        }
    }
});
