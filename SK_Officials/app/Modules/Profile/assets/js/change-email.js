document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('ceForm');
    const submitBtn = document.getElementById('ceSubmitBtn');
    const btnText = document.getElementById('ceBtnText');

    if (!form) return;

    document.querySelectorAll('.toggle-password').forEach((btn) => {
        btn.addEventListener('click', () => {
            const target = document.getElementById(btn.dataset.target || '');
            if (!target) return;

            const eyeOpen = btn.querySelector('.eye-open');
            const eyeClosed = btn.querySelector('.eye-closed');
            const isPassword = target.type === 'password';

            target.type = isPassword ? 'text' : 'password';

            if (eyeOpen && eyeClosed) {
                eyeOpen.style.display = isPassword ? 'none' : 'flex';
                eyeClosed.style.display = isPassword ? 'flex' : 'none';
            }
        });
    });

    function setFieldError(inputId, errorId, msg) {
        const input = document.getElementById(inputId);
        const err = document.getElementById(errorId);
        if (input) input.classList.add('cp-input-error');
        if (err) {
            err.textContent = msg;
            err.hidden = false;
        }
    }

    function clearFieldError(inputId, errorId) {
        const input = document.getElementById(inputId);
        const err = document.getElementById(errorId);
        if (input) input.classList.remove('cp-input-error');
        if (err) {
            err.textContent = '';
            err.hidden = true;
        }
    }

    form.addEventListener('submit', (e) => {
        const currentEmail = document.getElementById('ceCurrentEmail')?.value.trim() || '';
        const newEmail = document.getElementById('ceNewEmail')?.value.trim() || '';
        const password = document.getElementById('cePassword')?.value || '';

        let valid = true;

        clearFieldError('ceCurrentEmail', 'ceCurrentEmailError');
        clearFieldError('ceNewEmail', 'ceNewEmailError');
        clearFieldError('cePassword', 'cePasswordError');

        if (!currentEmail) {
            setFieldError('ceCurrentEmail', 'ceCurrentEmailError', 'Current email is required.');
            valid = false;
        }

        if (!newEmail) {
            setFieldError('ceNewEmail', 'ceNewEmailError', 'New email address is required.');
            valid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(newEmail)) {
            setFieldError('ceNewEmail', 'ceNewEmailError', 'Please enter a valid email address.');
            valid = false;
        } else if (newEmail.toLowerCase() === currentEmail.toLowerCase()) {
            setFieldError('ceNewEmail', 'ceNewEmailError', 'New email must be different from current email.');
            valid = false;
        }

        if (!password) {
            setFieldError('cePassword', 'cePasswordError', 'Current password is required.');
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
            return;
        }

        if (submitBtn) submitBtn.disabled = true;
        if (btnText) btnText.textContent = 'Sending email verification...';
    });
});
