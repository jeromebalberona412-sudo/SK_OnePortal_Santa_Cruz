document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('ceForm');
    const submitBtn = document.getElementById('ceSubmitBtn');
    const btnText = document.getElementById('ceBtnText');
    const overlay = document.getElementById('signin-overlay');

    if (!form) return;

    function setFieldError(inputId, errorId, msg) {
        const input = document.getElementById(inputId);
        const err = document.getElementById(errorId);
        if (input) input.classList.add('is-invalid');
        if (err) {
            err.textContent = msg;
            err.style.display = 'block';
        }
    }

    function clearFieldError(inputId, errorId) {
        const input = document.getElementById(inputId);
        const err = document.getElementById(errorId);
        if (input) input.classList.remove('is-invalid');
        if (err) {
            err.textContent = '';
            err.style.display = 'none';
        }
    }

    form.addEventListener('submit', function (e) {
        const currentEmail = document.getElementById('ceCurrentEmail')?.value.trim() || '';
        const newEmail = document.getElementById('ceNewEmail')?.value.trim() || '';
        const password = document.getElementById('cePassword')?.value || '';

        let valid = true;

        clearFieldError('ceNewEmail', 'ceNewEmailError');
        clearFieldError('cePassword', 'cePasswordError');

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
        if (btnText) btnText.textContent = 'Sending…';
        if (overlay) {
            overlay.removeAttribute('hidden');
            overlay.classList.add('is-visible');
        }
    });
});
