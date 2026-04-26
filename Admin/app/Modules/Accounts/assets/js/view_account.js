// View Account Modal Functions
document.addEventListener('DOMContentLoaded', function() {
    console.log('View account modal initialized');
});

// Close view modal function
function closeViewModal() {
    const modal = document.getElementById('viewAccountModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('modal-fullscreen', 'modal-minimized');
        _setViewModalBtns(modal, 'normal');
    }
}

// Maximize / restore-down toggle
function toggleFullscreenViewModal() {
    const modal = document.getElementById('viewAccountModal');
    if (!modal) return;
    const isFullscreen = modal.classList.contains('modal-fullscreen');
    if (isFullscreen) {
        // restore down
        modal.classList.remove('modal-fullscreen');
        _setViewModalBtns(modal, 'normal');
    } else {
        // maximize
        modal.classList.remove('modal-minimized');
        modal.classList.add('modal-fullscreen');
        _setViewModalBtns(modal, 'fullscreen');
    }
}

// Restore-down button (shown only when fullscreen)
function toggleRestoreViewModal() {
    const modal = document.getElementById('viewAccountModal');
    if (!modal) return;
    modal.classList.remove('modal-fullscreen');
    _setViewModalBtns(modal, 'normal');
}

function _setViewModalBtns(modal, state) {
    const fullscreenBtn = modal.querySelector('.modal-fullscreen-btn');
    const restoreBtn = modal.querySelector('.modal-restore-btn');
    if (state === 'fullscreen') {
        if (fullscreenBtn) { fullscreenBtn.title = 'Restore Down'; fullscreenBtn.style.display = 'none'; }
        if (restoreBtn)    { restoreBtn.style.display = 'inline-flex'; }
    } else {
        if (fullscreenBtn) { fullscreenBtn.title = 'Maximize'; fullscreenBtn.style.display = 'inline-flex'; }
        if (restoreBtn)    { restoreBtn.style.display = 'none'; }
    }
}

// Legacy alias kept for any existing references
function restoreViewModal() { toggleRestoreViewModal(); }

window.toggleFullscreenViewModal = toggleFullscreenViewModal;
window.toggleRestoreViewModal = toggleRestoreViewModal;
window.restoreViewModal = restoreViewModal;
window.closeViewModal = closeViewModal;