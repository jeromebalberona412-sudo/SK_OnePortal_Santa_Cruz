/**
 * Reusable Kabataan header — user menu, logout modal & mobile helpers
 */
(function () {
    'use strict';

    const userWrap = document.getElementById('kabataanHeaderUser');
    const avatarBtn = userWrap?.querySelector('.kabataan-header__avatar-btn');

    if (avatarBtn && userWrap) {
        avatarBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const open = userWrap.classList.toggle('is-open');
            avatarBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        document.addEventListener('click', function (e) {
            if (!userWrap.contains(e.target)) {
                userWrap.classList.remove('is-open');
                avatarBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    const menuToggle = document.getElementById('kabataanHeaderMenuToggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', function () {
            const expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            document.body.classList.toggle('kabataan-mobile-nav-open');
        });
    }

    // ── Logout confirmation (all pages with kabataan-header) ──
    const logoutBtn = document.querySelector('.kabataan-header .logout-btn');
    const logoutForm = logoutBtn?.closest('form');
    const modal = document.getElementById('kabataanLogoutModal');
    const confirmBtn = document.getElementById('kabataanConfirmLogoutBtn');

    window.closeKabataanLogoutModal = function () {
        if (modal) modal.hidden = true;
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
            if (typeof showLoading === 'function') showLoading('Logging out');
            logoutForm.submit();
        });
    }

    modal?.querySelector('.kab-logout-modal__overlay')?.addEventListener('click', closeKabataanLogoutModal);
})();
