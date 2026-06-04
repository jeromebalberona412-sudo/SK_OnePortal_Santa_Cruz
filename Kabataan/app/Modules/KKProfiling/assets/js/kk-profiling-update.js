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
    let isOpen = false;

    function openModal() {
        if (isOpen) return;
        isOpen = true;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('kkpu-modal-open');
    }

    function closeModal() {
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

    closeBtn?.addEventListener('click', () => closeModal());
    fullscreenBtn?.addEventListener('click', toggleFullscreen);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', (e) => {
        if (!isOpen) return;
        if (e.key === 'Escape') {
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
