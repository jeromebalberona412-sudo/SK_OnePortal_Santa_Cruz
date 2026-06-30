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
        setFullscreen(false);
    }

    function setFullscreen(enabled) {
        if (!panel) return;
        panel.classList.toggle('is-fullscreen', enabled);
        fullscreenBtn?.setAttribute('aria-label', enabled ? 'Exit fullscreen' : 'Fullscreen');
        fullscreenBtn?.setAttribute('title', enabled ? 'Exit fullscreen' : 'Fullscreen');
    }

    function toggleFullscreen() {
        if (!panel) return;
        setFullscreen(!panel.classList.contains('is-fullscreen'));
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

        if (data.suffix) {
            const suffixSelect = document.getElementById('kkpSuffix');
            if (suffixSelect) {
                suffixSelect.value = data.suffix;
                suffixSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        if (data.age && typeof window.kkpSyncYouthAgeGroupFromAge === 'function') {
            window.kkpSyncYouthAgeGroupFromAge(data.age);
        }

        const emailInput = document.getElementById('kkpEmail');
        if (emailInput && data.email) {
            emailInput.value = data.email;
            emailInput.readOnly = true;
        }
    }

    if (isMandatory) {
        closeBtn?.remove();
    } else {
        closeBtn?.addEventListener('click', () => closeModal());
    }

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
            if (panel?.classList.contains('is-fullscreen')) {
                setFullscreen(false);
            } else {
                closeModal();
            }
        }
    });

    window.openKkProfilingUpdateModal = openModal;
    window.closeKkProfilingUpdateModal = closeModal;

    document.addEventListener('DOMContentLoaded', () => {
        populateUpdateForm(window.__KK_PROFILING_FORM_DATA || {});
        if (shouldAutoOpen()) {
            requestAnimationFrame(() => openModal());
        }
    });
})();

window.handleKkProfilingUpdateSubmit = function (event) {
    const form = document.getElementById('kkProfilingUpdateForm');
    if (!form) return false;

    const valid = typeof window.handleFormSubmit === 'function'
        ? window.handleFormSubmit(event)
        : true;

    if (!valid) return false;

    const submitText = document.getElementById('kkpSubmitText');
    if (submitText) submitText.textContent = 'Updating KK Profiling...';

    return true;
};
