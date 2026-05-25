/**
 * Keep SKai header modal closed until initialized (runs before ai-modal.js)
 */
(function () {
    const root = document.documentElement;

    function forceCloseAiModal() {
        const modal = document.getElementById('aiAssistantModal');
        const btn = document.getElementById('aiAssistantBtn');
        if (modal) {
            modal.classList.remove('open');
            modal.setAttribute('hidden', '');
            modal.setAttribute('aria-hidden', 'true');
        }
        if (btn) btn.setAttribute('aria-expanded', 'false');
    }

    function markAiReady() {
        const modal = document.getElementById('aiAssistantModal');
        if (modal) modal.removeAttribute('hidden');
        root.classList.add('sk-ai-ready');
    }

    function onReady() {
        forceCloseAiModal();
        markAiReady();
    }

    forceCloseAiModal();
    root.classList.remove('sk-ai-ready');

    window.addEventListener('pageshow', forceCloseAiModal);

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') forceCloseAiModal();
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onReady);
    } else {
        onReady();
    }

    window.SkAiClose = {
        forceCloseAiModal,
        markAiReady,
    };
})();
