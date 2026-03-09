document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const isAuthPage = body.classList.contains('gov-auth-page');

    document.querySelectorAll('input[type="password"]').forEach((input) => {
        const wrapper = input.closest('.field-wrap');
        if (!wrapper) {
            return;
        }

        const syncState = () => {
            wrapper.classList.toggle('is-focused', document.activeElement === input);
        };

        input.addEventListener('focus', syncState);
        input.addEventListener('blur', syncState);
        syncState();
    });

    if (!isAuthPage) {
        return;
    }

    let loader = document.querySelector('.auth-loader-screen');
    if (!loader) {
        loader = document.createElement('div');
        loader.className = 'auth-loader-screen';
        loader.setAttribute('aria-hidden', 'true');
        loader.innerHTML = `
        <div class="auth-loader-stack">
            <div class="auth-loader-core">
                <div class="auth-loader-sweep"></div>
                <div class="auth-loader-dot"></div>
            </div>
            <div class="auth-loader-label">Authenticating</div>
            <div class="auth-loader-subtext">Securing session access...</div>
        </div>
    `;

        body.appendChild(loader);
    }

    const loaderSubtext = loader.querySelector('.auth-loader-subtext');
    let loaderVisible = false;

    const showLoader = (subtext = 'Securing session access...') => {
        if (loaderSubtext) {
            loaderSubtext.textContent = subtext;
        }
        if (!loaderVisible) {
            loader.classList.remove('is-hidden');
            body.classList.add('gov-auth-loading');
            loaderVisible = true;
        }
    };

    const hideLoader = () => {
        if (loaderVisible) {
            loader.classList.add('is-hidden');
            body.classList.remove('gov-auth-loading');
            loaderVisible = false;
        }
    };

    const showLoaderForSubmit = () => {
        showLoader('Verifying credentials...');
    };

    showLoader();

    const revealDelay = 430;
    if (document.readyState === 'complete') {
        window.setTimeout(hideLoader, revealDelay);
    } else {
        window.addEventListener(
            'load',
            () => {
                window.setTimeout(hideLoader, revealDelay);
            },
            { once: true }
        );
    }

    window.addEventListener('pageshow', () => {
        hideLoader();
    });

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => {
            showLoaderForSubmit();
        });
    });

    document.querySelectorAll('[data-otp-group]').forEach((group) => {
        const hiddenCode = group.querySelector('input[type="hidden"][name="code"]');
        const digitInputs = Array.from(group.querySelectorAll('.otp-digit'));
        if (!hiddenCode || digitInputs.length === 0) {
            return;
        }

        const syncHiddenCode = () => {
            hiddenCode.value = digitInputs.map((input) => input.value).join('');
        };

        const fillFromRaw = (rawValue) => {
            const digitsOnly = rawValue.replace(/\D/g, '').slice(0, digitInputs.length);
            digitInputs.forEach((input, index) => {
                input.value = digitsOnly[index] || '';
            });
            syncHiddenCode();
            return digitsOnly.length;
        };

        if (hiddenCode.value) {
            fillFromRaw(hiddenCode.value);
        }

        digitInputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                const numeric = input.value.replace(/\D/g, '');
                input.value = numeric.slice(-1);
                if (input.value && index < digitInputs.length - 1) {
                    digitInputs[index + 1].focus();
                    digitInputs[index + 1].select();
                }
                syncHiddenCode();
            });

            input.addEventListener('keydown', (event) => {
                if (event.key === 'Backspace' && !input.value && index > 0) {
                    digitInputs[index - 1].focus();
                    digitInputs[index - 1].select();
                }
            });

            input.addEventListener('paste', (event) => {
                event.preventDefault();
                const pasted = (event.clipboardData || window.clipboardData).getData('text') || '';
                const count = fillFromRaw(pasted);
                const targetIndex = Math.min(Math.max(count - 1, 0), digitInputs.length - 1);
                digitInputs[targetIndex].focus();
                digitInputs[targetIndex].select();
            });

            input.addEventListener('focus', () => {
                input.select();
            });
        });

        const otpForm = group.closest('form');
        if (otpForm) {
            otpForm.addEventListener('submit', () => {
                syncHiddenCode();
            });
        }
    });

    const isChallengePage = body.classList.contains('gov-auth-challenge');
    if (!isChallengePage) {
        return;
    }

    const timerWrap = document.querySelector('.challenge-timer-wrap');
    const timerValue = document.getElementById('challenge-timer');
    const expiredMessage = document.getElementById('challenge-expired-message');
    if (!timerWrap || !timerValue) {
        return;
    }

    const totalSeconds = Number.parseInt(timerWrap.dataset.challengeExpirySeconds || '600', 10);
    if (!Number.isFinite(totalSeconds) || totalSeconds <= 0) {
        return;
    }

    const challengeForms = Array.from(document.querySelectorAll('form[action$="/two-factor-challenge"]'));

    const formatTime = (secondsLeft) => {
        const mins = Math.floor(secondsLeft / 60)
            .toString()
            .padStart(2, '0');
        const secs = (secondsLeft % 60)
            .toString()
            .padStart(2, '0');
        return `${mins}:${secs}`;
    };

    const setExpiredState = () => {
        timerValue.textContent = '00:00';
        timerValue.style.color = 'rgba(255, 177, 177, 0.95)';

        challengeForms.forEach((form) => {
            form.classList.add('is-expired');
            form.querySelectorAll('input, button').forEach((el) => {
                el.disabled = true;
            });
        });

        if (expiredMessage) {
            expiredMessage.hidden = false;
        }
    };

    let secondsLeft = totalSeconds;
    timerValue.textContent = formatTime(secondsLeft);

    const intervalId = window.setInterval(() => {
        secondsLeft -= 1;
        if (secondsLeft <= 0) {
            window.clearInterval(intervalId);
            setExpiredState();
            return;
        }

        timerValue.textContent = formatTime(secondsLeft);
    }, 1000);
});
