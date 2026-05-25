/**
 * Global Loading Overlay
 * Usage: showLoading('Logging in') / hideLoading()
 * Auto-wires common actions via data attributes and form intercepts.
 */

const MESSAGES = {
    login:      'Logging in',
    register:   'Creating your account',
    logout:     'Logging out',
    redirect:   'Redirecting',
    loading:    'Loading',
};

function suppressHeaderPanelsDuringLoad() {
    document.documentElement.classList.remove('sk-ai-ready');
    if (window.SkAiClose?.forceCloseAiModal) {
        window.SkAiClose.forceCloseAiModal();
        return;
    }
    const aiModal = document.getElementById('aiAssistantModal');
    const aiBtn = document.getElementById('aiAssistantBtn');
    if (aiModal) {
        aiModal.classList.remove('open');
        aiModal.setAttribute('hidden', '');
        aiModal.setAttribute('aria-hidden', 'true');
    }
    if (aiBtn) aiBtn.setAttribute('aria-expanded', 'false');
    document.getElementById('notifDropdown')?.classList.remove('open');
    document.getElementById('userDropdown')?.classList.remove('open');
}

function restoreHeaderAfterLoad() {
    if (window.SkAiClose?.markAiReady) {
        window.SkAiClose.markAiReady();
    } else {
        document.documentElement.classList.add('sk-ai-ready');
        const aiModal = document.getElementById('aiAssistantModal');
        if (aiModal && !aiModal.classList.contains('open')) {
            aiModal.removeAttribute('hidden');
        }
    }
}

function showLoading(message = 'Loading') {
    suppressHeaderPanelsDuringLoad();
    const overlay = document.getElementById('globalLoadingOverlay');
    if (!overlay) return;
    const messageEl = overlay.querySelector('.gl-message');
    if (messageEl) messageEl.textContent = message;
    overlay.classList.add('gl-visible');
    document.body.classList.add('gl-loading-active');
    document.body.style.overflow = 'hidden';
}

function hideLoading() {
    const overlay = document.getElementById('globalLoadingOverlay');
    if (!overlay) return;
    overlay.classList.remove('gl-visible');
    document.body.classList.remove('gl-loading-active');
    document.body.style.overflow = '';
    restoreHeaderAfterLoad();
}

// Expose globally
window.showLoading = showLoading;
window.hideLoading = hideLoading;

// Close SKai immediately if script runs after header markup
suppressHeaderPanelsDuringLoad();

document.addEventListener('DOMContentLoaded', () => {
    suppressHeaderPanelsDuringLoad();
    hideLoading();

    // ── Login form ────────────────────────────────────────────────────────
    const loginForm = document.querySelector('form[action*="login"]:not([action*="logout"])');
    if (loginForm) {
        loginForm.addEventListener('submit', () => {
            const emailInput    = loginForm.querySelector('input[type="email"], input[name="email"]');
            const passwordInput = loginForm.querySelector('input[type="password"], input[name="password"]');
            const emailFilled    = emailInput    && emailInput.value.trim() !== '';
            const passwordFilled = passwordInput && passwordInput.value !== '';
            if (emailFilled && passwordFilled) {
                showLoading(MESSAGES.login);
            }
        });
    }

    // ── Register form ─────────────────────────────────────────────────────
    const registerForm = document.querySelector('form[action*="register"]');
    registerForm?.addEventListener('submit', () => showLoading(MESSAGES.register));

    // ── Logout form ───────────────────────────────────────────────────────
    const logoutForm = document.querySelector('form[action*="logout"]');
    if (logoutForm) {
        const origSubmit = HTMLFormElement.prototype.submit;
        logoutForm.submit = function () {
            showLoading(MESSAGES.logout);
            setTimeout(() => origSubmit.call(this), 80);
        };
        logoutForm.addEventListener('submit', () => {
            const overlay = document.getElementById('globalLoadingOverlay');
            if (!overlay?.classList.contains('gl-visible')) {
                showLoading(MESSAGES.logout);
            }
        });
    }

    // ── Internal navigation links (same-origin hrefs) ─────────────────────
    document.addEventListener('click', (e) => {
        const anchor = e.target.closest('a[href]');
        if (!anchor) return;

        const href = anchor.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript') || href.startsWith('mailto')) return;
        if (anchor.dataset.noLoading !== undefined) return;

        const currentPath = window.location.pathname;
        if (currentPath === '/login' || currentPath.endsWith('/login') ||
            currentPath === '/register' || currentPath.endsWith('/register') ||
            currentPath.includes('forgot-password') || currentPath.includes('password/reset')) return;

        if (href.startsWith('http') || href.startsWith('//')) {
            try {
                const url = new URL(href, window.location.href);
                if (url.origin !== window.location.origin) return;
            } catch (_) { return; }
        }

        showLoading(MESSAGES.redirect);
    });

    // ── Page reload (beforeunload) ────────────────────────────────────────
    let _navigatingAway = false;
    document.addEventListener('click', () => { _navigatingAway = true; }, true);
    document.addEventListener('submit', () => { _navigatingAway = true; }, true);

    window.addEventListener('beforeunload', () => {
        if (!_navigatingAway) {
            showLoading(MESSAGES.loading);
        }
    });

    window.addEventListener('pageshow', (e) => {
        suppressHeaderPanelsDuringLoad();
        if (e.persisted) hideLoading();
        else restoreHeaderAfterLoad();
    });
});
