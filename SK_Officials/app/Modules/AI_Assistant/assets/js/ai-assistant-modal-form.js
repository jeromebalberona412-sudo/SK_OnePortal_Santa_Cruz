/**
 * SKai — header exclusivity + attachment type picker
 */
(function () {
    const ACCEPT = {
        pdf: '.pdf,application/pdf',
        word: '.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        photos: 'image/*',
    };

    function closeOtherHeaderPanels() {
        if (typeof closeProfileDropdown === 'function') closeProfileDropdown();
        if (typeof closeNotifDropdown === 'function') closeNotifDropdown();
        if (typeof window.closeLogoutModal === 'function') window.closeLogoutModal();
    }

    function initHeaderExclusivity() {
        const aiBtn = document.getElementById('aiAssistantBtn');
        const notifBtn = document.getElementById('notificationBtn');
        const userMenuToggle = document.getElementById('userMenuToggle');
        const logoutTrigger = document.getElementById('logoutTrigger');

        if (notifBtn) notifBtn.addEventListener('click', closeOtherHeaderPanels, true);
        if (userMenuToggle) {
            userMenuToggle.addEventListener('click', function () {
                if (typeof window.closeAIAssistant === 'function') window.closeAIAssistant();
            }, true);
        }
        if (logoutTrigger) {
            logoutTrigger.addEventListener('click', function () {
                if (typeof window.closeAIAssistant === 'function') window.closeAIAssistant();
                if (typeof closeNotifDropdown === 'function') closeNotifDropdown();
            }, true);
        }
        if (aiBtn) aiBtn.addEventListener('click', closeOtherHeaderPanels, true);
    }

    function closeAllAttachMenus(except) {
        document.querySelectorAll('.ai-attach-type-menu.is-open').forEach(menu => {
            if (menu !== except) menu.classList.remove('is-open');
        });
    }

    function initAttachTypePickers() {
        document.querySelectorAll('[data-ai-attach-picker]').forEach(wrap => {
            if (wrap.dataset.aiAttachPickerInit) return;
            wrap.dataset.aiAttachPickerInit = '1';

            const trigger = wrap.querySelector('[data-ai-attach-trigger]');
            const menu = wrap.querySelector('.ai-attach-type-menu');
            const fileInput = wrap.querySelector('input[type="file"]');
            if (!trigger || !menu || !fileInput) return;

            trigger.addEventListener('click', function (e) {
                e.stopPropagation();
                const willOpen = !menu.classList.contains('is-open');
                closeAllAttachMenus(menu);
                menu.classList.toggle('is-open', willOpen);
            });

            menu.querySelectorAll('[data-attach-type]').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    fileInput.accept = ACCEPT[this.getAttribute('data-attach-type')] || ACCEPT.photos;
                    menu.classList.remove('is-open');
                    fileInput.click();
                });
            });
        });

        document.addEventListener('click', () => closeAllAttachMenus());
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeAllAttachMenus();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initHeaderExclusivity();
        initAttachTypePickers();
    });

    window.SkAiModalForm = {
        closeOtherHeaderPanels,
        initAttachTypePickers,
    };
})();
