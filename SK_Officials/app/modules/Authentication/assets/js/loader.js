/**
 * Centralized Loader System
 * Reusable loading animation for all pages
 */

class Loader {
    constructor() {
        this.loaderId = 'centralizedLoader';
        this.containerId = 'mainContainer';
        this.isActive = false;
        this.init();
    }

    /**
     * Initialize the loader by creating the HTML structure
     */
    init() {
        // Create loader HTML if it doesn't exist
        if (!document.getElementById(this.loaderId)) {
            const loaderHTML = `
                <div id="${this.loaderId}" class="loading-overlay" style="display: none;">
                    <div class="overlay-backdrop"></div>
                    <div class="loading-content">
                        <div class="main-spinner"></div>
                        <p class="loading-text">Loading...</p>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', loaderHTML);
        }

        // Create CSS if it doesn't exist
        this.injectStyles();
    }

    /**
     * Inject CSS styles for the loader
     */
    injectStyles() {
        if (document.getElementById('loader-styles')) return;

        const styles = `
            <style id="loader-styles">
                /* Full Page Loading Overlay */
                .loading-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: 9999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    opacity: 0;
                    visibility: hidden;
                    transition: opacity 0.3s ease, visibility 0.3s ease;
                }

                .loading-overlay.active {
                    opacity: 1;
                    visibility: visible;
                }

                .overlay-backdrop {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0, 0, 0, 0.7);
                    backdrop-filter: blur(5px);
                    -webkit-backdrop-filter: blur(5px);
                }

                .loading-content {
                    position: relative;
                    z-index: 10;
                    text-align: center;
                    animation: fadeInUp 0.5s ease;
                }

                .main-spinner {
                    width: 60px;
                    height: 60px;
                    border: 4px solid rgba(255, 255, 255, 0.3);
                    border-top: 4px solid #ffffff;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin: 0 auto 20px;
                }

                .loading-text {
                    color: white;
                    font-size: 18px;
                    font-weight: 500;
                    margin: 0;
                    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
                }

                /* Blur effect for page content during loading */
                .blurred {
                    filter: blur(3px) !important;
                    -webkit-filter: blur(3px) !important;
                    pointer-events: none !important;
                    user-select: none !important;
                }

                /* Animations */
                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }

                /* Responsive Design */
                @media (max-width: 480px) {
                    .main-spinner {
                        width: 50px;
                        height: 50px;
                        border-width: 3px;
                    }
                    
                    .loading-text {
                        font-size: 16px;
                    }
                }
            </style>
        `;
        document.head.insertAdjacentHTML('beforeend', styles);
    }

    /**
     * Show the loader with optional custom text
     * @param {string} text - Optional custom loading text
     * @param {string} containerSelector - Optional selector for container to blur
     */
    show(text = 'Loading...', containerSelector = null) {
        if (this.isActive) return;

        this.isActive = true;

        // Update loading text
        const loadingText = document.querySelector('.loading-text');
        if (loadingText) {
            loadingText.textContent = text;
        }

        // Determine which container to blur
        const container = containerSelector 
            ? document.querySelector(containerSelector)
            : document.body;

        // Add blur effect to container
        if (container) {
            container.classList.add('blurred');
        }

        // Show loader overlay
        const loader = document.getElementById(this.loaderId);
        if (loader) {
            loader.style.display = 'flex';
            setTimeout(() => {
                loader.classList.add('active');
            }, 10);
        }

        // Disable form elements in the container
        this.disableFormElements(container);
    }

    /**
     * Hide the loader
     * @param {string} containerSelector - Optional selector for container to unblur
     */
    hide(containerSelector = null) {
        if (!this.isActive) return;

        // Determine which container to unblur
        const container = containerSelector 
            ? document.querySelector(containerSelector)
            : document.body;

        // Hide loader overlay
        const loader = document.getElementById(this.loaderId);
        if (loader) {
            loader.classList.remove('active');
            setTimeout(() => {
                loader.style.display = 'none';
            }, 300);
        }

        // Remove blur effect
        if (container) {
            container.classList.remove('blurred');
        }

        // Enable form elements in the container
        this.enableFormElements(container);

        this.isActive = false;
    }

    /**
     * Disable all form elements in a container
     * @param {Element} container
     */
    disableFormElements(container) {
        if (!container) return;

        const formElements = container.querySelectorAll('input, button, select, textarea, a');
        formElements.forEach(element => {
            element.disabled = true;
            element.style.pointerEvents = 'none';
        });
    }

    /**
     * Enable all form elements in a container
     * @param {Element} container
     */
    enableFormElements(container) {
        if (!container) return;

        const formElements = container.querySelectorAll('input, button, select, textarea, a');
        formElements.forEach(element => {
            element.disabled = false;
            element.style.pointerEvents = '';
        });
    }

    /**
     * Check if loader is currently active
     * @returns {boolean}
     */
    isLoading() {
        return this.isActive;
    }

    /**
     * Update loading text dynamically
     * @param {string} text
     */
    updateText(text) {
        const loadingText = document.querySelector('.loading-text');
        if (loadingText) {
            loadingText.textContent = text;
        }
    }
}

// Create global loader instance
window.loader = new Loader();

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Loader;
}
