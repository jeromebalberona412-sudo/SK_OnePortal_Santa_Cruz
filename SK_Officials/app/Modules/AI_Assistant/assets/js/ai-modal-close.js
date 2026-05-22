/**
 * Keep SKai header modal closed on navigation / bfcache (runs before other AI scripts)
 */
(function () {
    function forceCloseAiModal() {
        const modal = document.getElementById('aiAssistantModal');
        const btn = document.getElementById('aiAssistantBtn');
        if (modal) modal.classList.remove('open');
        if (btn) btn.setAttribute('aria-expanded', 'false');
    }

    window.addEventListener('pageshow', forceCloseAiModal);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', forceCloseAiModal);
    } else {
        forceCloseAiModal();
    }

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') forceCloseAiModal();
    });
})();
