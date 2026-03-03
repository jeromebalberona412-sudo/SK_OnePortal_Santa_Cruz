// View Account Modal Functions
document.addEventListener('DOMContentLoaded', function() {
    console.log('View account modal initialized');
});

// Close view modal function
function closeViewModal() {
    console.log('closeViewModal called');
    const modal = document.getElementById('viewAccountModal');
    if (modal) {
        modal.classList.remove('modal-fullscreen', 'modal-minimized');
        console.log('View modal closed');
    }
}

// Fullscreen toggle function for view modal
function toggleFullscreenViewModal() {
    console.log('toggleFullscreenViewModal called');
    const modal = document.getElementById('viewAccountModal');
    console.log('Modal found:', modal);
    
    if (modal) {
        const isFullscreen = modal.classList.contains('modal-fullscreen');
        const isMinimized = modal.classList.contains('modal-minimized');
        console.log('Current state - fullscreen:', isFullscreen, 'minimized:', isMinimized);
        
        if (isFullscreen) {
            // Exit fullscreen - show minimize state
            modal.classList.remove('modal-fullscreen');
            modal.classList.add('modal-minimized');
            console.log('Switched to minimized state');
            
            // Update icon to maximize (restore)
            const fullscreenBtn = modal.querySelector('.modal-fullscreen-btn svg');
            if (fullscreenBtn) {
                fullscreenBtn.innerHTML = `
                    <path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path>
                `;
            }
            
            // Update tooltip
            const btnElement = modal.querySelector('.modal-fullscreen-btn');
            if (btnElement) {
                btnElement.setAttribute('title', 'Maximize');
            }
            
            // Add click to restore functionality
            const header = modal.querySelector('.modal-header');
            if (header) {
                header.addEventListener('click', function() {
                    console.log('Header clicked, restoring modal');
                    restoreViewModal();
                }, { once: true });
            }
        } else if (isMinimized) {
            // Restore from minimized
            console.log('Restoring from minimized state');
            restoreViewModal();
        } else {
            // Enter fullscreen
            modal.classList.add('modal-fullscreen');
            console.log('Switched to fullscreen state');
            
            // Update icon to minus (minimize)
            const fullscreenBtn = modal.querySelector('.modal-fullscreen-btn svg');
            if (fullscreenBtn) {
                fullscreenBtn.innerHTML = `
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                `;
            }
            
            // Update tooltip
            const btnElement = modal.querySelector('.modal-fullscreen-btn');
            if (btnElement) {
                btnElement.setAttribute('title', 'Minimize');
            }
        }
    }
}

// Restore function for view modal
function restoreViewModal() {
    console.log('restoreViewModal called');
    const modal = document.getElementById('viewAccountModal');
    console.log('Modal found:', modal);
    
    if (modal) {
        modal.classList.remove('modal-minimized');
        modal.classList.remove('modal-fullscreen');
        console.log('Modal restored to normal state');
        
        // Update icon to fullscreen
        const fullscreenBtn = modal.querySelector('.modal-fullscreen-btn svg');
        if (fullscreenBtn) {
            fullscreenBtn.innerHTML = `
                <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
            `;
        }
        
        // Update tooltip
        const btnElement = modal.querySelector('.modal-fullscreen-btn');
        if (btnElement) {
            btnElement.setAttribute('title', 'Fullscreen');
        }
    }
}

// Make functions globally accessible for onclick handlers
window.toggleFullscreenViewModal = toggleFullscreenViewModal;
window.restoreViewModal = restoreViewModal;
window.closeViewModal = closeViewModal;