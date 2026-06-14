/**
 * Global Loading Screen Manager
 * Handles loading states across the application
 */

const LoadingScreen = {
    element: null,
    textElement: null,
    subtextElement: null,

    init() {
        if (!this.element) {
            this.create();
        }
    },

    create() {
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
        this.textElement.innerHTML = message + '<span class="loading-dots"></span>';
        this.subtextElement.textContent = subtext;
        this.element.classList.add('active');
        document.body.style.overflow = 'hidden';
    },

    hide() {
        if (this.element) {
            this.element.classList.remove('active');
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
        }
        this.hide();
    }
};

const NetworkNotification = {
    element: null,
    hideTimeout: null,

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
        this.textElement.textContent = message;

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
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        LoadingScreen.init();
    });
} else {
    LoadingScreen.init();
}

window.addEventListener('load', () => {
    setTimeout(() => {
        LoadingScreen.hide();
    }, 300);
});

document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        LoadingScreen.hide();
    }
});

window.LoadingScreen = LoadingScreen;
window.NetworkNotification = NetworkNotification;

window.addEventListener('beforeunload', function () {
    LoadingScreen.show('Loading', 'Please wait...');
});

window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        LoadingScreen.hide();
    }
});

if (document.readyState === 'loading') {
    LoadingScreen.show('Loading', 'Please wait...');
}
