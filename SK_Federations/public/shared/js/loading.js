/**
 * Global Loading Screen Manager
 * Supports #globalLoadingOverlay (blade) and #globalLoadingScreen (dynamic fallback).
 * Blocks all page interaction while visible.
 */

const MESSAGES = {
    login: 'Signing In',
    register: 'Creating your account',
    logout: 'Logging out',
    redirect: 'Redirecting',
    loading: 'Loading',
};

const LoadingScreen = {
    element: null,
    textElement: null,
    subtextElement: null,
    delayTimeout: null,

    init() {
        if (!this.element) {
            this.create();
        }
    },

    create() {
        if (document.getElementById('globalLoadingScreen')) {
            this.element = document.getElementById('globalLoadingScreen');
            this.textElement = document.getElementById('loadingText');
            this.subtextElement = document.getElementById('loadingSubtext');
            return;
        }

        const loadingHTML = `
            <div id="globalLoadingScreen" class="global-loading-screen" aria-hidden="true">
                <div class="loading-content">
                    <div class="loading-spinner">
                        <div class="spinner-circle"></div>
                    </div>
                    <div class="loading-text" id="loadingText">Loading<span class="loading-dots"></span></div>
                    <div class="loading-subtext" id="loadingSubtext">Please wait</div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', loadingHTML);
        this.element = document.getElementById('globalLoadingScreen');
        this.textElement = document.getElementById('loadingText');
        this.subtextElement = document.getElementById('loadingSubtext');
    },

    show(message = 'Loading', subtext = 'Please wait') {
        this.init();
        if (this.textElement) {
            this.textElement.innerHTML = message + '<span class="loading-dots"></span>';
        }
        if (this.subtextElement) {
            this.subtextElement.textContent = subtext;
        }
        if (this.element) {
            this.element.classList.add('active');
            this.element.setAttribute('aria-hidden', 'false');
        }
        setTimeout(lockPageInteraction, 0);
    },

    hide() {
        if (this.element) {
            this.element.classList.remove('active');
            this.element.setAttribute('aria-hidden', 'true');
        }
        unlockPageInteraction();
    },

    showWithDelay(message, subtext, delay = 200) {
        this.delayTimeout = setTimeout(() => {
            this.show(message, subtext);
        }, delay);
    },

    hideImmediate() {
        if (this.delayTimeout) {
            clearTimeout(this.delayTimeout);
            this.delayTimeout = null;
        }
        this.hide();
    },
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

function showLoading(message = 'Loading', subtext = 'Please wait') {
    const overlay = getOverlay();
    if (overlay) {
        const messageEl = overlay.querySelector('.gl-message');
        const subtextEl = overlay.querySelector('.gl-sub');
        if (messageEl) {
            messageEl.textContent = message;
        }
        if (subtextEl) {
            subtextEl.textContent = subtext;
        }
        overlay.classList.add('gl-visible');
        overlay.setAttribute('aria-hidden', 'false');
        setTimeout(lockPageInteraction, 0);
        return;
    }

    LoadingScreen.show(message, subtext);
}

function hideLoading() {
    const overlay = getOverlay();
    if (overlay) {
        overlay.classList.remove('gl-visible');
        overlay.setAttribute('aria-hidden', 'true');
    }

    LoadingScreen.hideImmediate();
}

function ensureLoadingHidden() {
    hideLoading();
}

const NetworkNotification = {
    element: null,
    hideTimeout: null,
    textElement: null,

    init() {
        if (!this.element) {
            this.create();
        }
    },

    create() {
        if (document.getElementById('networkNotification')) {
            this.element = document.getElementById('networkNotification');
            this.textElement = document.getElementById('networkNotificationText');
            return;
        }

        const notifHTML = `
            <div id="networkNotification" class="network-notification">
                <div class="network-notification-content">
                    <i class="fas fa-wifi-slash"></i>
                    <span id="networkNotificationText">Unable to connect. Please try again.</span>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', notifHTML);
        this.element = document.getElementById('networkNotification');
        this.textElement = document.getElementById('networkNotificationText');
    },

    show(message = 'Unable to connect. Please try again.', duration = 5000, isOnline = false) {
        this.init();
        if (this.textElement) {
            this.textElement.textContent = message;
        }

        if (isOnline) {
            this.element.classList.add('online');
        } else {
            this.element.classList.remove('online');
        }

        this.element.classList.add('show');

        if (this.hideTimeout) {
            clearTimeout(this.hideTimeout);
        }

        if (duration > 0) {
            this.hideTimeout = setTimeout(() => {
                this.hide();
            }, duration);
        }
    },

    hide() {
        if (this.element) {
            this.element.classList.remove('show');
        }
    },

    showOffline() {
        this.show('You are offline. Please try again.', 0);
    },

    showOnline() {
        this.show('Connection restored!', 3000, true);
    },

    showSlowConnection() {
        this.show('Slow connection detected. Loading may take longer.', 5000);
    },

    showLoadError() {
        this.show('Unable to load data. Please try again.', 5000);
    },
};

window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.LoadingScreen = {
    show(message, subtext) {
        showLoading(message || 'Loading', subtext || 'Please wait');
    },
    hide() {
        hideLoading();
    },
    hideImmediate() {
        hideLoading();
    },
    init() {
        LoadingScreen.init();
    },
};
window.NetworkNotification = NetworkNotification;

document.addEventListener('DOMContentLoaded', () => {
    ensureLoadingHidden();

    const loginForm = document.querySelector('form[action*="login"]:not([action*="logout"])');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            if (e.defaultPrevented) return;

            const emailInput = loginForm.querySelector('input[type="email"], input[name="email"]');
            const passwordInput = loginForm.querySelector('input[type="password"], input[name="password"]');
            const emailFilled = emailInput && emailInput.value.trim() !== '';
            const passwordFilled = passwordInput && passwordInput.value !== '';

            if (emailFilled && passwordFilled) {
                showLoading(MESSAGES.login, 'Verifying your credentials...');
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
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ensureLoadingHidden);
} else {
    ensureLoadingHidden();
}

window.addEventListener('load', ensureLoadingHidden);

window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        ensureLoadingHidden();
    }
});

document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        ensureLoadingHidden();
    }
});
