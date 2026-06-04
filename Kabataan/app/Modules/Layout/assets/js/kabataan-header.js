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

})();
