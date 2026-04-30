/* ============================================================
   SK Officials — Change Email JS
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

    /* ── Elements ── */
    const step1     = document.getElementById('ceStep1');
    const step2     = document.getElementById('ceStep2');
    const form      = document.getElementById('ceForm');
    const submitBtn = document.getElementById('ceSubmitBtn');
    const btnText   = document.getElementById('ceBtnText');
    const errAlert  = document.getElementById('ceError');
    const errText   = document.getElementById('ceErrorText');

    /* ── Step-2 elements ── */
    const pendingEmailSpan  = document.getElementById('cePendingEmail');
    const currentEmailSpan  = document.getElementById('ceCurrentEmailVal');
    const pendingEmailVal   = document.getElementById('cePendingEmailVal');
    const resendBtn         = document.getElementById('ceResendBtn');
    const cancelBtn         = document.getElementById('ceCancelBtn');
    const timerEl           = document.getElementById('ceTimer');
    const timerCountEl      = document.getElementById('ceTimerCount');

    let resendInterval = null;
    let resendSeconds  = 60;

    /* ── Password toggle ── */
    document.querySelectorAll('.cp-pw-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.getElementById(btn.dataset.target);
            if (!target) return;
            const isPassword = target.type === 'password';
            target.type = isPassword ? 'text' : 'password';
            btn.classList.toggle('pw-visible', isPassword);
        });
    });

    /* ── Show error ── */
    function showError(msg) {
        errText.textContent = msg;
        errAlert.style.display = 'flex';
    }

    function hideError() {
        errAlert.style.display = 'none';
    }

    /* ── Field error helpers ── */
    function setFieldError(inputId, errorId, msg) {
        const input = document.getElementById(inputId);
        const err   = document.getElementById(errorId);
        if (input) input.classList.add('cp-input-error');
        if (err)   { err.textContent = msg; err.hidden = false; }
    }

    function clearFieldError(inputId, errorId) {
        const input = document.getElementById(inputId);
        const err   = document.getElementById(errorId);
        if (input) input.classList.remove('cp-input-error');
        if (err)   { err.textContent = ''; err.hidden = true; }
    }

    /* ── Start resend countdown ── */
    function startResendTimer() {
        resendSeconds = 60;
        resendBtn.disabled = true;
        timerEl.style.display = 'block';
        timerCountEl.textContent = resendSeconds;

        resendInterval = setInterval(() => {
            resendSeconds--;
            timerCountEl.textContent = resendSeconds;
            if (resendSeconds <= 0) {
                clearInterval(resendInterval);
                resendBtn.disabled = false;
                timerEl.style.display = 'none';
            }
        }, 1000);
    }

    /* ── Form submit ── */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideError();

        const currentEmail = document.getElementById('ceCurrentEmail').value.trim();
        const newEmail     = document.getElementById('ceNewEmail').value.trim();
        const password     = document.getElementById('cePassword').value;

        let valid = true;

        clearFieldError('ceCurrentEmail', 'ceCurrentEmailError');
        clearFieldError('ceNewEmail',     'ceNewEmailError');
        clearFieldError('cePassword',     'cePasswordError');

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

        if (!valid) return;

        /* Loading state */
        submitBtn.disabled = true;
        btnText.textContent = 'Sending…';

        /* Simulate API call — replace with real fetch when backend is ready */
        await new Promise(r => setTimeout(r, 1000));

        /* Populate step 2 */
        if (pendingEmailSpan)  pendingEmailSpan.textContent  = newEmail;
        if (currentEmailSpan)  currentEmailSpan.textContent  = currentEmail;
        if (pendingEmailVal)   pendingEmailVal.textContent   = newEmail;

        /* Switch to step 2 */
        step1.style.display = 'none';
        step2.style.display = 'block';

        startResendTimer();

        submitBtn.disabled  = false;
        btnText.textContent = 'Send Verification Link';
    });

    /* ── Resend button ── */
    if (resendBtn) {
        resendBtn.addEventListener('click', async () => {
            resendBtn.disabled = true;
            resendBtn.textContent = 'Sending…';

            await new Promise(r => setTimeout(r, 800));

            resendBtn.textContent = 'Resend Verification';
            startResendTimer();
        });
    }

    /* ── Cancel button ── */
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            clearInterval(resendInterval);
            step2.style.display = 'none';
            step1.style.display = 'block';
            form.reset();
            hideError();
        });
    }

});
