/**
 * Shared Kabataan logout confirmation modal
 */
(function () {
    'use strict';

    const logoutBtn = document.querySelector('.kabataan-header .logout-btn');
    const logoutForm = logoutBtn?.closest('form');
    const modal = document.getElementById('kabataanLogoutModal');
    const confirmBtn = document.getElementById('kabataanConfirmLogoutBtn');

    window.closeKabataanLogoutModal = function () {
        if (modal) {
            modal.hidden = true;
        }
        document.body.style.overflow = '';
    };

    window.openKabataanLogoutModal = function () {
        if (modal) {
            modal.hidden = false;
            document.body.style.overflow = 'hidden';
        }
    };

    if (logoutBtn && logoutForm) {
        logoutBtn.addEventListener('click', function (e) {
            e.preventDefault();
            openKabataanLogoutModal();
        });
    }

    if (confirmBtn && logoutForm) {
        confirmBtn.addEventListener('click', function () {
            if (typeof showLoading === 'function') {
                showLoading('Logging out');
            }
            logoutForm.submit();
        });
    }

    modal?.querySelector('.kab-logout-modal__overlay')?.addEventListener('click', closeKabataanLogoutModal);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && !modal.hidden) {
            closeKabataanLogoutModal();
        }
    });
})();
