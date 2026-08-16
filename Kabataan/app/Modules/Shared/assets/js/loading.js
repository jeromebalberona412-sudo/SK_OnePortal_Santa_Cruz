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

function getOverlay() {
    return document.getElementById('globalLoadingOverlay');
}

function lockPageInteraction() {
    document.body.classList.add('gl-loading-active');
    document.body.style.overflow = 'hidden';

    document.querySelectorAll('input, textarea, select, button, a[href]').forEach((el) => {
        if (el.closest('#globalLoadingOverlay') || el.closest('#globalLoadingScreen')) {
            return;
        }

        // Never disable hidden fields — CSRF token must stay in the POST body.
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
                // readOnly keeps values in form submission; blocks typing.
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
    const overlay = getOverlay();
    if (!overlay) return;

    const messageEl = overlay.querySelector('.gl-message');
    if (messageEl) {
        messageEl.textContent = message;
    }

    overlay.classList.add('gl-visible');
    overlay.setAttribute('aria-hidden', 'false');
    // Defer lock so form submit still includes CSRF + field values.
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
    show(message) {
        showLoading(message || 'Loading');
    },
    hide() {
        hideLoading();
    },
};

document.addEventListener('DOMContentLoaded', () => {
    hideLoading();

    const loginForm = document.querySelector('form[action*="login"]:not([action*="logout"])');
    if (loginForm && loginForm.id !== 'signInForm') {
        loginForm.addEventListener('submit', (e) => {
            if (e.defaultPrevented) return;

            // Skip if Turnstile is enabled - the login JS handles loading
            if (loginForm.dataset.turnstileEnabled === 'true') {
                return;
            }

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
            const overlay = getOverlay();
            if (!overlay?.classList.contains('gl-visible')) {
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
        if (anchor.target === '_blank') return;

        const currentPath = window.location.pathname;
        if (
            currentPath === '/login' || currentPath.endsWith('/login') ||
            currentPath === '/sign-in' || currentPath.endsWith('/sign-in') ||
            currentPath === '/register' || currentPath.endsWith('/register') ||
            currentPath.includes('forgot-password') || currentPath.includes('password/reset')
        ) {
            return;
        }

        try {
            const url = new URL(href, window.location.href);
            if (url.origin !== window.location.origin) return;
            if (url.pathname === window.location.pathname && url.search === window.location.search) return;
        } catch (_) {
            return;
        }

        showLoading(MESSAGES.redirect);
    });

    let navigatingAway = false;
    document.addEventListener('click', () => { navigatingAway = true; }, true);
    document.addEventListener('submit', () => { navigatingAway = true; }, true);

    window.addEventListener('beforeunload', () => {
        if (!navigatingAway) {
            showLoading(MESSAGES.loading);
        }
    });

    window.addEventListener('pageshow', () => {
        hideLoading();
    });

    window.addEventListener('load', hideLoading);
});

if (document.readyState !== 'loading') {
    hideLoading();
}
