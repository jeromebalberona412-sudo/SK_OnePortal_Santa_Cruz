function showLoading(message = 'Loading') {
    const overlay = document.getElementById('globalLoadingOverlay');
    if (!overlay) return;
    const messageEl = overlay.querySelector('.gl-message');
    if (messageEl) messageEl.textContent = message;
    overlay.classList.add('gl-visible');
    document.body.classList.add('gl-loading-active');
    document.body.style.overflow = 'hidden';
}

function hideLoading() {
    const overlay = document.getElementById('globalLoadingOverlay');
    if (!overlay) return;
    overlay.classList.remove('gl-visible');
    document.body.classList.remove('gl-loading-active');
    document.body.style.overflow = '';
}

window.showLoading = showLoading;
window.hideLoading = hideLoading;

window.LoadingScreen = {
    show(message) { showLoading(message || 'Loading'); },
    hide() { hideLoading(); },
};
