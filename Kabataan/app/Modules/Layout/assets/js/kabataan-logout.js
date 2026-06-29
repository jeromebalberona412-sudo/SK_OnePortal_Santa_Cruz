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

    function getLoginUrl() {
        const loginLink = document.querySelector('a[href*="/login"]');
        if (loginLink?.href) {
            try {
                const url = new URL(loginLink.href, window.location.origin);
                return url.pathname;
            } catch (error) {
                // fall through
            }
        }

        return '/login';
    }

    async function performLogout() {
        closeKabataanLogoutModal();

        if (typeof showLoading === 'function') {
            showLoading('Logging out');
        }

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const logoutUrl = logoutForm?.getAttribute('action') || '/logout';

        try {
            await fetch(logoutUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
        } catch (error) {
            // Redirect to login even if the request fails
        }

        window.location.replace(getLoginUrl());
    }

    if (logoutBtn && logoutForm) {
        logoutBtn.addEventListener('click', function (e) {
            e.preventDefault();
            openKabataanLogoutModal();
        });
    }

    if (confirmBtn) {
        confirmBtn.addEventListener('click', performLogout);
    }

    modal?.querySelector('.kab-logout-modal__overlay')?.addEventListener('click', closeKabataanLogoutModal);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && !modal.hidden) {
            closeKabataanLogoutModal();
        }
    });
})();
