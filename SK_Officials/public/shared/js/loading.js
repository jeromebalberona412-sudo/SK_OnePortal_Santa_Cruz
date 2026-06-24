/**
 * Global Loading Overlay
 * Usage: showLoading('Logging in') / hideLoading()
 * Blocks all page interaction while visible.
 */

const MESSAGES = {
    login: 'Logging in',
    register: 'Creating your account',
    logout: 'Logging out',
    redirect: 'Redirecting',
    loading: 'Loading',
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

function getOverlay() {
    return document.getElementById('globalLoadingOverlay');
}

function lockPageInteraction() {
    document.body.classList.add('gl-loading-active');
    document.body.style.overflow = 'hidden';

    document.querySelectorAll('input, textarea, select, button, a[href]').forEach((el) => {
        if (el.closest('#globalLoadingOverlay')) return;

        if (el.tagName === 'INPUT' && el.type === 'hidden') {
            return;
        }

        if (!el.hasAttribute('data-gl-locked')) {
            el.setAttribute('data-gl-locked', '1');
            el.setAttribute('data-gl-was-disabled', el.disabled ? '1' : '0');
            el.setAttribute('data-gl-was-readonly', el.readOnly ? '1' : '0');

            if (el.tagName === 'A') {
                el.setAttribute('aria-disabled', 'true');
                el.style.pointerEvents = 'none';
                el.style.cursor = 'not-allowed';
            } else if (
                el.tagName === 'TEXTAREA' ||
                (el.tagName === 'INPUT' &&
                    ['text', 'email', 'password', 'search', 'tel', 'url', 'number'].includes(el.type))
            ) {
                el.readOnly = true;
            } else {
                el.disabled = true;
            }
        }
    });
}

function unlockPageInteraction() {
    document.body.classList.remove('gl-loading-active');
    document.body.style.overflow = '';
    restoreHeaderAfterLoad();

    document.querySelectorAll('[data-gl-locked]').forEach((el) => {
        if (el.getAttribute('data-gl-was-disabled') === '0') {
            el.disabled = false;
        }

        if (el.getAttribute('data-gl-was-readonly') === '0') {
            el.readOnly = false;
        }

        if (el.tagName === 'A') {
            el.removeAttribute('aria-disabled');
            el.style.pointerEvents = '';
            el.style.cursor = '';
        }

        el.removeAttribute('data-gl-locked');
        el.removeAttribute('data-gl-was-disabled');
        el.removeAttribute('data-gl-was-readonly');
    });
}

function showLoading(message = 'Loading') {
    suppressHeaderPanelsDuringLoad();
    const overlay = getOverlay();
    if (!overlay) return;

    const messageEl = overlay.querySelector('.gl-message');
    if (messageEl) messageEl.textContent = message;

    overlay.classList.add('gl-visible');
    overlay.setAttribute('aria-hidden', 'false');
    setTimeout(lockPageInteraction, 0);
}

function hideLoading() {
    const overlay = getOverlay();
    if (!overlay) return;

    overlay.classList.remove('gl-visible');
    overlay.setAttribute('aria-hidden', 'true');
    unlockPageInteraction();
}

window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.LoadingScreen = {
    show(message) { showLoading(message || 'Loading'); },
    hide() { hideLoading(); },
};

suppressHeaderPanelsDuringLoad();

document.addEventListener('DOMContentLoaded', () => {
    suppressHeaderPanelsDuringLoad();
    hideLoading();

    const loginForm = document.querySelector('form[action*="login"]:not([action*="logout"])');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            if (e.defaultPrevented) return;

            const emailInput = loginForm.querySelector('input[type="email"], input[name="email"]');
            const passwordInput = loginForm.querySelector('input[type="password"], input[name="password"]');
            const emailFilled = emailInput && emailInput.value.trim() !== '';
            const passwordFilled = passwordInput && passwordInput.value !== '';
            if (emailFilled && passwordFilled) {
                showLoading(MESSAGES.login);
            }
        });
    }

    const registerForm = document.querySelector('form[action*="register"]');
    registerForm?.addEventListener('submit', () => showLoading(MESSAGES.register));

    const logoutForm = document.querySelector('form[action*="logout"]');
    if (logoutForm) {
        const origSubmit = HTMLFormElement.prototype.submit;
        logoutForm.submit = function () {
            showLoading(MESSAGES.logout);
            setTimeout(() => origSubmit.call(this), 80);
        };
        logoutForm.addEventListener('submit', () => {
            if (!getOverlay()?.classList.contains('gl-visible')) {
                showLoading(MESSAGES.logout);
            }
        });
    }

    document.addEventListener('click', (e) => {
        const anchor = e.target.closest('a[href]');
        if (!anchor) return;

        const href = anchor.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript') || href.startsWith('mailto')) return;
        if (anchor.dataset.noLoading !== undefined) return;

        const currentPath = window.location.pathname;
        if (
            currentPath === '/login' || currentPath.endsWith('/login') ||
            currentPath === '/register' || currentPath.endsWith('/register') ||
            currentPath.includes('forgot-password') ||
            currentPath.includes('password/reset') ||
            currentPath.includes('reset-password')
        ) {
            return;
        }

        if (href.startsWith('http') || href.startsWith('//')) {
            try {
                const url = new URL(href, window.location.href);
                if (url.origin !== window.location.origin) return;
            } catch (_) { return; }
        }

        showLoading(MESSAGES.redirect);
    });

    let navigatingAway = false;
    document.addEventListener('click', () => { navigatingAway = true; }, true);
    document.addEventListener('submit', () => { navigatingAway = true; }, true);

    window.addEventListener('beforeunload', () => {
        if (!navigatingAway) showLoading(MESSAGES.loading);
    });

    window.addEventListener('pageshow', (e) => {
        suppressHeaderPanelsDuringLoad();
        if (e.persisted) hideLoading();
        else restoreHeaderAfterLoad();
    });

    window.addEventListener('load', hideLoading);
});

if (document.readyState !== 'loading') {
    hideLoading();
}
