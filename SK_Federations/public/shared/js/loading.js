/**
 * Global Loading Screen Manager
 * Supports both #globalLoadingOverlay (module pages) and #globalLoadingScreen (dynamic overlay).
 */

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
            <div id="globalLoadingScreen" class="global-loading-screen">
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
        }
        document.body.style.overflow = 'hidden';
    },

    hide() {
        if (this.element) {
            this.element.classList.remove('active');
        }
        if (!document.body.classList.contains('gl-loading-active')) {
            document.body.style.overflow = '';
        }
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

function showLoading(message = 'Loading', subtext = 'Please wait') {
    const overlay = document.getElementById('globalLoadingOverlay');
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
        document.body.classList.add('gl-loading-active');
        document.body.style.overflow = 'hidden';
        return;
    }

    LoadingScreen.show(message, subtext);
}

function hideLoading() {
    const overlay = document.getElementById('globalLoadingOverlay');
    if (overlay) {
        overlay.classList.remove('gl-visible');
        overlay.setAttribute('aria-hidden', 'true');
    }

    document.body.classList.remove('gl-loading-active');
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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ensureLoadingHidden);
} else {
    ensureLoadingHidden();
}

window.addEventListener('load', ensureLoadingHidden);

window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        ensureLoadingHidden();
    }
});

document.addEventListener('visibilitychange', function () {
    if (!document.hidden) {
        ensureLoadingHidden();
    }
});
