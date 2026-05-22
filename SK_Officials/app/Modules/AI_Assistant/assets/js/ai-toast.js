/**
 * SK AI Assistant — lightweight toast notifications
 */
(function (global) {
    let container = null;

    function ensureContainer() {
        if (container) return container;
        container = document.getElementById('aiToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'aiToastContainer';
            container.className = 'ai-toast-container';
            container.setAttribute('aria-live', 'polite');
            document.body.appendChild(container);
        }
        return container;
    }

    function show(message, type) {
        const root = ensureContainer();
        const el = document.createElement('div');
        el.className = 'ai-toast ai-toast--' + (type || 'success');
        el.textContent = message;
        root.appendChild(el);
        requestAnimationFrame(() => el.classList.add('is-visible'));
        setTimeout(() => {
            el.classList.remove('is-visible');
            setTimeout(() => el.remove(), 300);
        }, 2800);
    }

    global.SkAiToast = { show };
})(window);
