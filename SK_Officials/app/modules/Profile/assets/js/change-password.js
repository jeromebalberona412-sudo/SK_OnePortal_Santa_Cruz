/**
 * SK Officials — Change Password Page JS
 * Handles email validation + simulated reset link send
 */

document.addEventListener('DOMContentLoaded', function () {

    const form        = document.getElementById('cpForm');
    const emailInput  = document.getElementById('cpEmail');
    const emailError  = document.getElementById('cpEmailError');
    const submitBtn   = document.getElementById('cpSubmitBtn');
    const btnText     = document.getElementById('cpBtnText');
    const successBox  = document.getElementById('cpSuccess');
    const errorBox    = document.getElementById('cpError');
    const errorText   = document.getElementById('cpErrorText');

    if (!form) return;

    /* Demo registered emails */
    const REGISTERED_EMAILS = [
        'kianpaula4@gmail.com',
        'maria.santos@email.com',
        'robert.tan@email.com',
        'jerome.balberona@email.com',
        'gabriel.garcia@email.com',
        'frankien.belangoy@email.com',
        'juan.delacruz@email.com',
        'jane.doe@email.com',
        'mark.reyes@email.com',
    ];

    function isValidEmail(val) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val.trim());
    }

    function showFieldError(msg) {
        emailError.textContent = msg;
        emailError.hidden = false;
        emailInput.classList.add('is-invalid');
    }

    function clearFieldError() {
        emailError.textContent = '';
        emailError.hidden = true;
        emailInput.classList.remove('is-invalid');
    }

    function hideAlerts() {
        successBox.style.display = 'none';
        errorBox.style.display   = 'none';
    }

    function showSuccess(msg) {
        document.getElementById('cpSuccessText').textContent = msg;
        successBox.style.display = 'flex';
        errorBox.style.display   = 'none';
    }

    function showError(msg) {
        errorText.textContent  = msg;
        errorBox.style.display = 'flex';
        successBox.style.display = 'none';
    }

    function setLoading(loading) {
        submitBtn.disabled = loading;
        btnText.textContent = loading ? 'Sending...' : 'Send Reset Password Link';
    }

    /* Live clear on input */
    emailInput.addEventListener('input', function () {
        clearFieldError();
        hideAlerts();
    });

    emailInput.addEventListener('blur', function () {
        const val = emailInput.value.trim();
        if (!val) {
            showFieldError('Email address is required.');
        } else if (!isValidEmail(val)) {
            showFieldError('Please enter a valid email address.');
        }
    });

    /* Submit */
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideAlerts();

        const val = emailInput.value.trim();

        if (!val) {
            showFieldError('Email address is required.');
            emailInput.focus();
            return;
        }

        if (!isValidEmail(val)) {
            showFieldError('Please enter a valid email address.');
            emailInput.focus();
            return;
        }

        clearFieldError();
        setLoading(true);

        setTimeout(function () {
            setLoading(false);
            const found = REGISTERED_EMAILS.includes(val.toLowerCase());
            if (found) {
                showSuccess('Reset password link has been sent to your email.');
                emailInput.value = '';
            } else {
                showError('No account found with that email address. Please try again.');
            }
        }, 600);
    });

});
