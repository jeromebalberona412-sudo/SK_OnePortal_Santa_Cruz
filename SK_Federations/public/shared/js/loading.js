/**
 * Global Loading Screen Manager
 * Handles loading states across the application
 */

const LoadingScreen = {
    element: null,
    textElement: null,
    subtextElement: null,
    slowLoadingTimeout: null,
    slowLoadingThreshold: 5000, // 5 seconds

    // Initialize the loading screen
    init() {
        if (!this.element) {
            this.create();
        }
    },

    // Create the loading screen HTML
    create() {
        const loadingHTML = `
            <div id="globalLoadingScreen" class="global-loading-screen">
                <div class="loading-content">
                    <div class="loading-spinner">
                        <div class="spinner-circle"></div>
                    </div>
                    <div class="loading-text" id="loadingText">Loading<span class="loading-dots"></span></div>
                    <div class="loading-subtext" id="loadingSubtext">Please wait</div>
                    <div class="loading-warning" id="loadingWarning" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>This is taking longer than usual...</span>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', loadingHTML);
        this.element = document.getElementById('globalLoadingScreen');
        this.textElement = document.getElementById('loadingText');
        this.subtextElement = document.getElementById('loadingSubtext');
        this.warningElement = document.getElementById('loadingWarning');
    },

    // Show loading screen with custom message
    show(message = 'Loading', subtext = 'Please wait') {
        this.init();
        this.textElement.innerHTML = message + '<span class="loading-dots"></span>';
        this.subtextElement.textContent = subtext;
        this.element.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Hide warning initially
        if (this.warningElement) {
            this.warningElement.style.display = 'none';
        }
        
        // Clear any existing timeout
        if (this.slowLoadingTimeout) {
            clearTimeout(this.slowLoadingTimeout);
        }
        
        // Show slow loading warning after threshold
        this.slowLoadingTimeout = setTimeout(() => {
            this.showSlowLoadingWarning();
        }, this.slowLoadingThreshold);
    },

    // Show slow loading warning
    showSlowLoadingWarning() {
        if (this.warningElement && this.element.classList.contains('active')) {
            this.warningElement.style.display = 'flex';
            this.subtextElement.textContent = 'Please check your internet connection';
        }
    },

    // Hide loading screen
    hide() {
        if (this.slowLoadingTimeout) {
            clearTimeout(this.slowLoadingTimeout);
        }
        
        if (this.element) {
            this.element.classList.remove('active');
            document.body.style.overflow = '';
            
            if (this.warningElement) {
                this.warningElement.style.display = 'none';
            }
        }
    },

    // Show with delay (useful for quick operations)
    showWithDelay(message, subtext, delay = 200) {
        this.delayTimeout = setTimeout(() => {
            this.show(message, subtext);
        }, delay);
    },

    // Hide and clear any pending delays
    hideImmediate() {
        if (this.delayTimeout) {
            clearTimeout(this.delayTimeout);
        }
        this.hide();
    }
};

// Network Error Notification System
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
                    <span id="networkNotificationText">Network error. Please check your internet connection.</span>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', notifHTML);
        this.element = document.getElementById('networkNotification');
        this.textElement = document.getElementById('networkNotificationText');
    },

    show(message = 'Network error. Please check your internet connection.', duration = 5000, isOnline = false) {
        this.init();
        this.textElement.textContent = message;
        
        // Add or remove online class
        if (isOnline) {
            this.element.classList.add('online');
        } else {
            this.element.classList.remove('online');
        }
        
        this.element.classList.add('show');

        // Clear existing timeout
        if (this.hideTimeout) {
            clearTimeout(this.hideTimeout);
        }

        // Auto-hide after duration
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
        this.show('You are offline. Please check your internet connection.', 0);
    },

    showOnline() {
        this.show('Connection restored!', 3000, true);
    },

    showSlowConnection() {
        this.show('Slow internet connection detected. Loading may take longer.', 5000);
    },

    showLoadError() {
        this.show('Unable to load data. Please try again.', 5000);
    }
};

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        LoadingScreen.init();
        NetworkNotification.init();
    });
} else {
    LoadingScreen.init();
    NetworkNotification.init();
}

// Hide loading when page is fully loaded
window.addEventListener('load', () => {
    setTimeout(() => {
        LoadingScreen.hide();
    }, 300);
});

// Handle page visibility changes
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        LoadingScreen.hide();
    }
});

// Monitor online/offline status
window.addEventListener('online', () => {
    NetworkNotification.showOnline();
});

window.addEventListener('offline', () => {
    LoadingScreen.hide();
    NetworkNotification.showOffline();
});

// Monitor slow connection using Navigation Timing API
window.addEventListener('load', () => {
    if (window.performance && window.performance.timing) {
        const perfData = window.performance.timing;
        const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
        
        // If page took more than 10 seconds to load, show slow connection warning
        if (pageLoadTime > 10000) {
            NetworkNotification.showSlowConnection();
        }
    }
});

// Export for global use
window.LoadingScreen = LoadingScreen;
window.NetworkNotification = NetworkNotification;

// Show loading screen on page reload/refresh
window.addEventListener('beforeunload', function(e) {
    LoadingScreen.show('Loading', 'Please wait...');
});

// Show loading screen on page navigation (back/forward buttons)
window.addEventListener('pageshow', function(event) {
    // If page is loaded from cache (back/forward navigation)
    if (event.persisted) {
        LoadingScreen.hide();
    }
});

// Show loading screen immediately when page starts loading
if (document.readyState === 'loading') {
    LoadingScreen.show('Loading', 'Please wait...');
}
