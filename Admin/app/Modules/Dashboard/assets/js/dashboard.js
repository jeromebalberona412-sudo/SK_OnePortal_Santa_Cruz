// Modern SK OnePortal Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeModernDashboard();
});

function initializeModernDashboard() {
    initializeLogoutModal();
    initializeFlashMessages();
    addInteractiveEffects();
    initializeKeyboardNavigation();
}

// Logout Modal
function initializeLogoutModal() {
    let modalOverlay = null;
    let logoutForm = null;

    function showLogoutModal(form) {
        logoutForm = form;
        
        if (!modalOverlay) {
            createModal();
        }
        
        modalOverlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        document.getElementById('modal-btn-no').focus();
    }

    function createModal() {
        modalOverlay = document.createElement('div');
        modalOverlay.className = 'logout-modal-overlay';
        modalOverlay.setAttribute('role', 'dialog');
        modalOverlay.setAttribute('aria-modal', 'true');
        modalOverlay.setAttribute('aria-labelledby', 'modal-title');
        modalOverlay.setAttribute('aria-describedby', 'modal-message');
        
        modalOverlay.innerHTML = `
            <div class="logout-modal-content">
                <h3 class="modal-title" id="modal-title">Confirm Logout</h3>
                <p class="modal-message" id="modal-message">
                    Are you sure you want to logout? Any unsaved changes will be lost.
                </p>
                <div class="modal-buttons">
                    <button type="button" class="modal-btn modal-btn-no" id="modal-btn-no">
                        No
                    </button>
                    <button type="button" class="modal-btn modal-btn-yes" id="modal-btn-yes">
                        Yes
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modalOverlay);
        
        const noBtn = document.getElementById('modal-btn-no');
        const yesBtn = document.getElementById('modal-btn-yes');
        
        noBtn.addEventListener('click', closeModal);
        yesBtn.addEventListener('click', confirmLogout);
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === modalOverlay) closeModal();
        });
        
        modalOverlay.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    }
    
    function closeModal() {
        if (modalOverlay) {
            modalOverlay.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
    
    function confirmLogout() {
        const yesBtn = document.getElementById('modal-btn-yes');
        yesBtn.innerHTML = 'Logging out...';
        yesBtn.disabled = true;
        yesBtn.style.opacity = '0.7';
        
        if (logoutForm) {
            logoutForm.submit();
        } else {
            window.location.href = '/logout';
        }
    }

    // Find logout buttons
    const logoutForms = document.querySelectorAll('form[action*="logout"]');
    logoutForms.forEach(form => {
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton && !submitButton.hasAttribute('data-modal-processed')) {
            submitButton.setAttribute('data-modal-processed', 'true');
            submitButton.addEventListener('click', function(e) {
                e.preventDefault();
                showLogoutModal(form);
            });
        }
    });
}

// Flash Messages
function initializeFlashMessages() {
    const flashMessages = document.querySelectorAll('[role="alert"]');
    flashMessages.forEach(message => {
        setTimeout(() => fadeOutElement(message), 5000);
        
        if (!message.querySelector('.close-flash')) {
            const closeButton = document.createElement('button');
            closeButton.className = 'close-flash';
            closeButton.innerHTML = '&times;';
            closeButton.setAttribute('aria-label', 'Close message');
            closeButton.style.cssText = `
                position: absolute;
                top: 12px;
                right: 12px;
                background: none;
                border: none;
                font-size: 24px;
                cursor: pointer;
                opacity: 0.7;
                color: inherit;
                padding: 4px;
                line-height: 1;
                transition: all 0.3s ease;
            `;
            closeButton.addEventListener('click', () => fadeOutElement(message));
            message.style.position = 'relative';
            message.appendChild(closeButton);
        }
    });
}

function fadeOutElement(element) {
    element.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
    element.style.opacity = '0';
    element.style.transform = 'translateX(20px)';
    setTimeout(() => element.remove(), 500);
}

// Interactive Effects
function addInteractiveEffects() {
    // Button ripple effects
    const buttons = document.querySelectorAll('.btn-primary-modern, .btn-logout-modern, .modal-btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!this.disabled) createRipple(e, this);
        });
    });
    
    // Card hover effects
    const cards = document.querySelectorAll('.dashboard-card-modern, .quick-action-card-modern');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}

function createRipple(e, button) {
    const ripple = document.createElement('span');
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = e.clientX - rect.left - size / 2;
    const y = e.clientY - rect.top - size / 2;
    
    ripple.style.cssText = `
        position: absolute;
        width: ${size}px;
        height: ${size}px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        left: ${x}px;
        top: ${y}px;
        pointer-events: none;
        transform: scale(0);
        animation: ripple 0.6s ease-out;
    `;
    
    button.style.position = 'relative';
    button.style.overflow = 'hidden';
    button.appendChild(ripple);
    
    setTimeout(() => ripple.remove(), 600);
}

// Keyboard Navigation
function initializeKeyboardNavigation() {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.querySelector('.logout-modal-overlay');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
        
        // Alt + L for logout
        if (e.altKey && e.key === 'l') {
            e.preventDefault();
            const logoutForm = document.querySelector('form[action*="logout"]');
            if (logoutForm) {
                const logoutButton = logoutForm.querySelector('button[type="submit"]');
                if (logoutButton) logoutButton.click();
            }
        }
    });
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to { transform: scale(4); opacity: 0; }
    }
`;
document.head.appendChild(style);
