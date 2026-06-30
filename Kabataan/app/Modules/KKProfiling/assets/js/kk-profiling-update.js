/**
 * KK Profiling yearly update modal (dashboard)
 */
(function () {
    'use strict';

    const modal = document.getElementById('kkProfilingUpdateModal');
    if (!modal) return;

    const panel = document.getElementById('kkpuModalPanel');
    const closeBtn = document.getElementById('kkpuCloseBtn');
    const fullscreenBtn = document.getElementById('kkpuFullscreenBtn');
    const isMandatory = window.__KK_PROFILING_UPDATE_REQUIRED === true;
    let isOpen = false;

    function resetModalMaximized() {
        modal.classList.remove('modal-maximized');
        panel?.classList.remove('modal-maximized');
        if (fullscreenBtn) {
            fullscreenBtn.textContent = '□';
            fullscreenBtn.setAttribute('aria-label', 'Maximize');
        }
    }

    function openModal() {
        if (isOpen) return;
        isOpen = true;
        modal.classList.add('is-open');
        if (isMandatory) {
            modal.classList.add('is-mandatory');
        }
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('kkpu-modal-open');
        if (isMandatory) {
            document.body.classList.add('is-mandatory-lock');
        }
    }

    function closeModal() {
        if (isMandatory) return;
        if (!isOpen) return;
        isOpen = false;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('kkpu-modal-open');
        resetModalMaximized();
    }

    function toggleFullscreen() {
        if (!panel) return;
        const isMax = !modal.classList.contains('modal-maximized');
        modal.classList.toggle('modal-maximized', isMax);
        panel.classList.toggle('modal-maximized', isMax);
        if (fullscreenBtn) {
            fullscreenBtn.textContent = isMax ? '⧉' : '□';
            fullscreenBtn.setAttribute('aria-label', isMax ? 'Restore down' : 'Maximize');
        }
    }

    function shouldAutoOpen() {
        return window.__SHOW_KK_UPDATE_MODAL === true;
    }

    function setCheckboxGroupValue(chkName, hiddenId, value) {
        const form = document.getElementById('kkProfilingUpdateForm');
        if (!form || !value) return;

        const hidden = document.getElementById(hiddenId);
        if (hidden) {
            hidden.value = value;
        }

        form.querySelectorAll(`input[name="${chkName}"]`).forEach((input) => {
            input.checked = input.value === value;
        });

        const matched = form.querySelector(`input[name="${chkName}"][value="${value}"]`);
        if (matched) {
            matched.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function populateUpdateForm(data) {
        const form = document.getElementById('kkProfilingUpdateForm');
        if (!form || !data || typeof data !== 'object') return;

        Object.entries(data).forEach(([key, value]) => {
            if (key === 'suffix' || key === 'email') return;
            if (value === null || value === undefined || value === '') return;

            const direct = form.querySelector(`[name="${key}"]`);
            if (direct && direct.type !== 'hidden' && direct.type !== 'checkbox' && direct.type !== 'radio') {
                direct.value = value;
                direct.dispatchEvent(new Event('input', { bubbles: true }));
                direct.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        const checkboxFields = [
            { name: 'sex', chk: 'sexChk', hiddenId: 'kkpSex' },
        ];

        checkboxFields.forEach(({ name, chk, hiddenId }) => {
            const raw = data[name];
            const value = Array.isArray(raw) ? raw[0] : raw;
            if (value) {
                setCheckboxGroupValue(chk, hiddenId, value);
            }
        });

        const suffixSelect = document.getElementById('kkpSuffix');
        if (suffixSelect) {
            const suffixOptions = Array.from(suffixSelect.options).map((option) => option.value);
            let suffixValue = (data.suffix || '').trim();
            if (!suffixValue || suffixValue.toLowerCase() === 'none') {
                suffixValue = 'None';
            }
            if (!suffixOptions.includes(suffixValue)) {
                suffixValue = 'None';
            }
            suffixSelect.value = suffixValue;
            suffixSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (data.age && typeof window.kkpSyncYouthAgeGroupFromAge === 'function') {
            window.kkpSyncYouthAgeGroupFromAge(data.age);
        }

        const emailInput = document.getElementById('kkpEmail');
        const lockedEmail = (window.__KK_PROFILING_ORIGINAL_EMAIL || data.email || '').trim().toLowerCase();
        if (emailInput && lockedEmail) {
            emailInput.value = lockedEmail;
            emailInput.readOnly = true;
            emailInput.classList.add('kkp-readonly');
            emailInput.dataset.originalEmail = lockedEmail;
        }
    }

    closeBtn?.addEventListener('click', () => closeModal());
    fullscreenBtn?.addEventListener('click', toggleFullscreen);

    if (!isMandatory) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
    }

    document.addEventListener('keydown', (e) => {
        if (!isOpen) return;
        if (e.key === 'Escape') {
            if (isMandatory) return;
            if (modal.classList.contains('modal-maximized')) {
                toggleFullscreen();
            } else {
                closeModal();
            }
        }
    });

    window.openKkProfilingUpdateModal = openModal;
    window.closeKkProfilingUpdateModal = closeModal;

    document.addEventListener('DOMContentLoaded', () => {
        populateUpdateForm(window.__KK_PROFILING_FORM_DATA || {});

        const updateForm = document.getElementById('kkProfilingUpdateForm');
        updateForm?.addEventListener('submit', (event) => {
            window.handleKkProfilingUpdateSubmit(event);
        });

        if (shouldAutoOpen()) {
            requestAnimationFrame(() => openModal());
        }
    });
})();

window.handleKkProfilingUpdateSubmit = async function (event) {
    event.preventDefault();

    const form = document.getElementById('kkProfilingUpdateForm');
    if (!form) return false;

    const submitBtn = document.getElementById('kkpSubmitBtn');
    const submitText = document.getElementById('kkpSubmitText');

    function setSubmitting(active, label) {
        if (submitBtn) {
            submitBtn.disabled = active;
            submitBtn.classList.toggle('is-submitting', active);
        }
        if (submitText && label) {
            submitText.textContent = label;
        }
    }

    function resetSubmit() {
        setSubmitting(false, 'Update KK Profiling');
        if (typeof window.hideLoading === 'function') {
            window.hideLoading();
        }
    }

    setSubmitting(true, 'Checking entries...');
    if (typeof window.showLoading === 'function') {
        window.showLoading('Checking your entries...');
    }

    const valid = typeof window.validateKkProfilingForm === 'function'
        ? await window.validateKkProfilingForm({ skipEmailExistenceCheck: true })
        : true;

    if (!valid) {
        resetSubmit();
        return false;
    }

    setSubmitting(true, 'Updating KK Profiling...');
    if (typeof window.showLoading === 'function') {
        window.showLoading('Updating KK Profiling...');
    }

    const formData = new FormData(form);

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            resetSubmit();
            if (data.errors && typeof data.errors === 'object') {
                Object.entries(data.errors).forEach(([field, messages]) => {
                    const message = Array.isArray(messages) ? messages[0] : messages;
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input && typeof window.showFieldError === 'function') {
                        window.showFieldError(input, message);
                    }
                });
            }
            alert(data.message || 'Unable to update KK Profiling. Please check your entries.');
            return false;
        }

        setSubmitting(true, 'Update complete. Refreshing...');
        if (typeof window.showLoading === 'function') {
            window.showLoading('Update complete. Refreshing...');
        }
        window.location.reload();
        return false;
    } catch (err) {
        resetSubmit();
        alert('Unable to update KK Profiling. Please check your connection and try again.');
        return false;
    }
};
