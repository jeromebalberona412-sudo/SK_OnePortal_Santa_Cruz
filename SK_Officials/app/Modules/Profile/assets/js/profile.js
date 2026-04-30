/**
 * SK Officials — Profile Page JS
 * Handles: tab switching, inline change password form
 */

document.addEventListener('DOMContentLoaded', function () {
    initTabs();
    initChangePasswordForm();
});

/* ── Tab switching ───────────────────────────────────────── */
function initTabs() {
    const tabs    = document.querySelectorAll('.profile-tab');
    const panels  = document.querySelectorAll('.profile-tab-content');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            const target = tab.getAttribute('data-tab');

            // Deactivate all
            tabs.forEach(function (t) { t.classList.remove('active'); });
            panels.forEach(function (p) { p.classList.remove('active'); });

            // Activate selected
            tab.classList.add('active');
            const panel = document.getElementById(target);
            if (panel) panel.classList.add('active');
        });
    });
}

/* ── Inline Change Password Form ─────────────────────────── */
function initChangePasswordForm() {
    const form        = document.getElementById('cpInlineForm');
    if (!form) return;

    const currentInput  = document.getElementById('cpCurrent');
    const newInput      = document.getElementById('cpNew');
    const confirmInput  = document.getElementById('cpConfirm');
    const submitBtn     = document.getElementById('cpInlineSubmit');
    const cancelBtn     = document.getElementById('cpInlineCancel');
    const successBox    = document.getElementById('cpInlineSuccess');
    const errorBox      = document.getElementById('cpInlineError');
    const successText   = document.getElementById('cpInlineSuccessText');
    const errorText     = document.getElementById('cpInlineErrorText');

    /* Demo: accepted current password */
    const DEMO_CURRENT_PASSWORD = 'Admin@1234';

    /* ── Eye toggle ──────────────────────────────────────── */
    document.querySelectorAll('.cp-eye-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = btn.getAttribute('data-target');
            const input    = document.getElementById(targetId);
            const icon     = btn.querySelector('i');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    /* ── Helpers ─────────────────────────────────────────── */
    function setFieldError(input, errorEl, msg) {
        errorEl.textContent = msg;
        input.classList.add('is-error');
    }

    function clearFieldError(input, errorEl) {
        errorEl.textContent = '';
        input.classList.remove('is-error');
    }

    function hideAlerts() {
        successBox.style.display = 'none';
        errorBox.style.display   = 'none';
    }

    function showSuccess(msg) {
        successText.textContent  = msg;
        successBox.style.display = 'flex';
        errorBox.style.display   = 'none';
    }

    function showFormError(msg) {
        errorText.textContent  = msg;
        errorBox.style.display = 'flex';
        successBox.style.display = 'none';
    }

    /* ── Live clear on input ─────────────────────────────── */
    [
        [currentInput,  document.getElementById('cpCurrentError')],
        [newInput,      document.getElementById('cpNewError')],
        [confirmInput,  document.getElementById('cpConfirmError')],
    ].forEach(function ([input, errEl]) {
        if (input) {
            input.addEventListener('input', function () {
                clearFieldError(input, errEl);
                hideAlerts();
            });
        }
    });

    /* ── Cancel ──────────────────────────────────────────── */
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            form.reset();
            hideAlerts();
            [currentInput, newInput, confirmInput].forEach(function (inp) {
                if (inp) inp.classList.remove('is-error');
            });
            document.querySelectorAll('.cp-inline-field-error').forEach(function (el) {
                el.textContent = '';
            });
        });
    }

    /* ── Submit ──────────────────────────────────────────── */
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideAlerts();

        const currentVal  = currentInput.value.trim();
        const newVal      = newInput.value.trim();
        const confirmVal  = confirmInput.value.trim();
        const currentErr  = document.getElementById('cpCurrentError');
        const newErr      = document.getElementById('cpNewError');
        const confirmErr  = document.getElementById('cpConfirmError');

        let valid = true;

        /* Validate current password */
        if (!currentVal) {
            setFieldError(currentInput, currentErr, 'Current password is required.');
            valid = false;
        } else if (currentVal !== DEMO_CURRENT_PASSWORD) {
            setFieldError(currentInput, currentErr, 'Current password is incorrect.');
            valid = false;
        } else {
            clearFieldError(currentInput, currentErr);
        }

        /* Validate new password */
        if (!newVal) {
            setFieldError(newInput, newErr, 'New password is required.');
            valid = false;
        } else if (newVal.length < 8) {
            setFieldError(newInput, newErr, 'Password must be at least 8 characters.');
            valid = false;
        } else if (newVal === currentVal) {
            setFieldError(newInput, newErr, 'New password must differ from current password.');
            valid = false;
        } else {
            clearFieldError(newInput, newErr);
        }

        /* Validate confirm password */
        if (!confirmVal) {
            setFieldError(confirmInput, confirmErr, 'Please confirm your new password.');
            valid = false;
        } else if (confirmVal !== newVal) {
            setFieldError(confirmInput, confirmErr, 'Passwords do not match.');
            valid = false;
        } else {
            clearFieldError(confirmInput, confirmErr);
        }

        if (!valid) {
            showFormError('Please fix the errors below.');
            return;
        }

        /* Simulate save */
        submitBtn.disabled = true;
        submitBtn.querySelector('span').textContent = 'Updating...';

        setTimeout(function () {
            submitBtn.disabled = false;
            submitBtn.querySelector('span').textContent = 'Update Password';
            form.reset();
            showSuccess('Password updated successfully.');
        }, 700);
    });
}

/* ── Exports ─────────────────────────────────────────────── */
window.ProfileFunctions = {
    initTabs: initTabs,
    initChangePasswordForm: initChangePasswordForm,
};
