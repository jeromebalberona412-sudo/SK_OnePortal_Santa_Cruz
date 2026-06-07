/**
 * SK Officials — Profile Page JS
 * Handles: tab switching, inline account settings panels
 */

document.addEventListener('DOMContentLoaded', function () {
    initTabs();
    initAccountSettingsPanels();
    initChangeEmailForm();
    initChangePasswordForm();

    const openSettings = sessionStorage.getItem('profileOpenSettings');
    if (openSettings) {
        sessionStorage.removeItem('profileOpenSettings');
        if (window.ProfileFunctions.activateTab) {
            window.ProfileFunctions.activateTab(1);
        }
        if (openSettings === 'password' && window.ProfileFunctions.openSettingsPanel) {
            window.ProfileFunctions.openSettingsPanel('password');
        }
    }
});

/* ── Tab switching (URL stays /profile) ─────────────────── */
function initTabs() {
    const tabs = [
        { btn: document.getElementById('tabBtnInfo'), panel: document.getElementById('tabInfo') },
        { btn: document.getElementById('tabBtnSettings'), panel: document.getElementById('tabSettings') },
    ];

    tabs.forEach(function (t, idx) {
        if (!t.btn) return;
        t.btn.addEventListener('click', function () {
            activateTab(idx);
        });
    });

    function activateTab(activeIdx) {
        tabs.forEach(function (t, i) {
            const isActive = i === activeIdx;
            if (t.btn) {
                t.btn.classList.toggle('active', isActive);
                t.btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
            }
            if (t.panel) {
                t.panel.classList.toggle('active', isActive);
            }
        });
    }

    window.ProfileFunctions = window.ProfileFunctions || {};
    window.ProfileFunctions.activateTab = activateTab;
}

/* ── Account Settings panel toggles ──────────────────────── */
function initAccountSettingsPanels() {
    const panels = [
        {
            btn: document.getElementById('btnToggleChangeEmail'),
            panel: document.getElementById('panelChangeEmail'),
            block: document.getElementById('settingsBlockEmail'),
            cancel: document.getElementById('ceInlineCancel'),
            form: document.getElementById('ceInlineForm'),
        },
        {
            btn: document.getElementById('btnToggleChangePassword'),
            panel: document.getElementById('panelChangePassword'),
            block: document.getElementById('settingsBlockPassword'),
            cancel: document.getElementById('cpInlineCancel'),
            form: document.getElementById('cpInlineForm'),
        },
    ];

    function closePanel(entry) {
        if (!entry.panel || !entry.btn) return;
        entry.panel.hidden = true;
        entry.btn.classList.remove('account-settings-btn--active');
        entry.btn.setAttribute('aria-expanded', 'false');
        if (entry.block) entry.block.classList.remove('account-settings-block--open');
    }

    function openPanel(entry) {
        panels.forEach(function (other) {
            if (other !== entry) closePanel(other);
        });
        entry.panel.hidden = false;
        entry.btn.classList.add('account-settings-btn--active');
        entry.btn.setAttribute('aria-expanded', 'true');
        if (entry.block) entry.block.classList.add('account-settings-block--open');
    }

    panels.forEach(function (entry) {
        if (!entry.btn || !entry.panel) return;

        entry.btn.addEventListener('click', function () {
            const isOpen = !entry.panel.hidden;
            if (isOpen) {
                closePanel(entry);
            } else {
                openPanel(entry);
            }
        });

        if (entry.cancel) {
            entry.cancel.addEventListener('click', function () {
                if (entry.form) entry.form.reset();
                closePanel(entry);
            });
        }
    });

    window.ProfileFunctions = window.ProfileFunctions || {};
    window.ProfileFunctions.openSettingsPanel = function (type) {
        const idx = type === 'password' ? 1 : 0;
        if (window.ProfileFunctions.activateTab) {
            window.ProfileFunctions.activateTab(1);
        }
        openPanel(panels[idx]);
    };
}

/* ── Inline Change Email Form ────────────────────────────── */
function initChangeEmailForm() {
    const form = document.getElementById('ceInlineForm');
    if (!form) return;

    const currentInput = document.getElementById('ceInlineCurrentEmail');
    const newInput = document.getElementById('ceInlineNewEmail');
    const passwordInput = document.getElementById('ceInlinePassword');
    const submitBtn = document.getElementById('ceInlineSubmit');
    const successBox = document.getElementById('ceInlineSuccess');
    const errorBox = document.getElementById('ceInlineError');
    const successText = document.getElementById('ceInlineSuccessText');
    const errorText = document.getElementById('ceInlineErrorText');

    const DEMO_CURRENT_EMAIL = 'example@gmail.com';
    const DEMO_PASSWORD = 'Admin@1234';

    initPasswordToggles();

    function setFieldError(input, errorEl, msg) {
        if (errorEl) errorEl.textContent = msg;
        if (input) input.classList.add('is-error');
    }

    function clearFieldError(input, errorEl) {
        if (errorEl) errorEl.textContent = '';
        if (input) input.classList.remove('is-error');
    }

    function hideAlerts() {
        if (successBox) successBox.style.display = 'none';
        if (errorBox) errorBox.style.display = 'none';
    }

    [
        [currentInput, document.getElementById('ceInlineCurrentEmailError')],
        [newInput, document.getElementById('ceInlineNewEmailError')],
        [passwordInput, document.getElementById('ceInlinePasswordError')],
    ].forEach(function (pair) {
        const input = pair[0];
        const errEl = pair[1];
        if (!input) return;
        input.addEventListener('input', function () {
            clearFieldError(input, errEl);
            hideAlerts();
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideAlerts();

        const currentVal = currentInput.value.trim();
        const newVal = newInput.value.trim();
        const passwordVal = passwordInput.value.trim();
        const currentErr = document.getElementById('ceInlineCurrentEmailError');
        const newErr = document.getElementById('ceInlineNewEmailError');
        const passwordErr = document.getElementById('ceInlinePasswordError');
        let valid = true;

        if (!currentVal) {
            setFieldError(currentInput, currentErr, 'Current email is required.');
            valid = false;
        } else if (currentVal.toLowerCase() !== DEMO_CURRENT_EMAIL) {
            setFieldError(currentInput, currentErr, 'Current email does not match your account.');
            valid = false;
        } else {
            clearFieldError(currentInput, currentErr);
        }

        if (!newVal) {
            setFieldError(newInput, newErr, 'New email is required.');
            valid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(newVal)) {
            setFieldError(newInput, newErr, 'Enter a valid email address.');
            valid = false;
        } else if (newVal.toLowerCase() === currentVal.toLowerCase()) {
            setFieldError(newInput, newErr, 'New email must differ from current email.');
            valid = false;
        } else {
            clearFieldError(newInput, newErr);
        }

        if (!passwordVal) {
            setFieldError(passwordInput, passwordErr, 'Current password is required.');
            valid = false;
        } else if (passwordVal !== DEMO_PASSWORD) {
            setFieldError(passwordInput, passwordErr, 'Current password is incorrect.');
            valid = false;
        } else {
            clearFieldError(passwordInput, passwordErr);
        }

        if (!valid) {
            errorText.textContent = 'Please fix the errors below.';
            errorBox.style.display = 'flex';
            return;
        }

        submitBtn.disabled = true;
        submitBtn.querySelector('span').textContent = 'Sending...';

        setTimeout(function () {
            submitBtn.disabled = false;
            submitBtn.querySelector('span').textContent = 'Send Verification Link';
            form.reset();
            successText.textContent = 'Verification link sent to ' + newVal + '.';
            successBox.style.display = 'flex';
        }, 700);
    });
}

/* ── Inline Change Password Form ─────────────────────────── */
function initChangePasswordForm() {
    const form = document.getElementById('cpInlineForm');
    if (!form) return;

    const currentInput = document.getElementById('cpCurrent');
    const newInput = document.getElementById('cpNew');
    const confirmInput = document.getElementById('cpConfirm');
    const submitBtn = document.getElementById('cpInlineSubmit');
    const successBox = document.getElementById('cpInlineSuccess');
    const errorBox = document.getElementById('cpInlineError');
    const successText = document.getElementById('cpInlineSuccessText');
    const errorText = document.getElementById('cpInlineErrorText');

    const DEMO_CURRENT_PASSWORD = 'Admin@1234';

    initPasswordToggles();

    function setFieldError(input, errorEl, msg) {
        if (errorEl) errorEl.textContent = msg;
        if (input) input.classList.add('is-error');
    }

    function clearFieldError(input, errorEl) {
        if (errorEl) errorEl.textContent = '';
        if (input) input.classList.remove('is-error');
    }

    function hideAlerts() {
        if (successBox) successBox.style.display = 'none';
        if (errorBox) errorBox.style.display = 'none';
    }

    [
        [currentInput, document.getElementById('cpCurrentError')],
        [newInput, document.getElementById('cpNewError')],
        [confirmInput, document.getElementById('cpConfirmError')],
    ].forEach(function (pair) {
        const input = pair[0];
        const errEl = pair[1];
        if (!input) return;
        input.addEventListener('input', function () {
            clearFieldError(input, errEl);
            hideAlerts();
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideAlerts();

        const currentVal = currentInput.value.trim();
        const newVal = newInput.value.trim();
        const confirmVal = confirmInput.value.trim();
        const currentErr = document.getElementById('cpCurrentError');
        const newErr = document.getElementById('cpNewError');
        const confirmErr = document.getElementById('cpConfirmError');
        let valid = true;

        if (!currentVal) {
            setFieldError(currentInput, currentErr, 'Current password is required.');
            valid = false;
        } else if (currentVal !== DEMO_CURRENT_PASSWORD) {
            setFieldError(currentInput, currentErr, 'Current password is incorrect.');
            valid = false;
        } else {
            clearFieldError(currentInput, currentErr);
        }

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
            errorText.textContent = 'Please fix the errors below.';
            errorBox.style.display = 'flex';
            return;
        }

        submitBtn.disabled = true;
        submitBtn.querySelector('span').textContent = 'Updating...';

        setTimeout(function () {
            submitBtn.disabled = false;
            submitBtn.querySelector('span').textContent = 'Update Password';
            form.reset();
            successText.textContent = 'Password updated successfully.';
            successBox.style.display = 'flex';
        }, 700);
    });
}

function initPasswordToggles() {
    document.querySelectorAll('.cp-eye-btn').forEach(function (btn) {
        if (btn.dataset.bound === 'true') return;
        btn.dataset.bound = 'true';
        btn.addEventListener('click', function () {
            const targetId = btn.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = btn.querySelector('i');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                if (icon) icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
}
