(function () {
    const ICON_ELLIPSIS = `
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <circle cx="5" cy="12" r="2"></circle>
            <circle cx="12" cy="12" r="2"></circle>
            <circle cx="19" cy="12" r="2"></circle>
        </svg>
    `;

    function resetRowActionsDropdown(menu) {
        const dropdown = menu?.querySelector('.row-actions-dropdown');
        if (!dropdown) {
            return;
        }

        dropdown.classList.remove('is-floating', 'row-actions-dropdown-up');
        dropdown.style.position = '';
        dropdown.style.top = '';
        dropdown.style.left = '';
        dropdown.style.right = '';
        dropdown.style.bottom = '';
        dropdown.style.zIndex = '';
    }

    function positionRowActionsDropdown(menu) {
        const trigger = menu.querySelector('.row-actions-trigger');
        const dropdown = menu.querySelector('.row-actions-dropdown');
        if (!trigger || !dropdown) {
            return;
        }

        dropdown.classList.add('is-floating');
        dropdown.style.position = 'fixed';
        dropdown.style.zIndex = '1300';

        const rect = trigger.getBoundingClientRect();
        const gap = 6;
        const dropdownHeight = dropdown.offsetHeight || 150;
        const dropdownWidth = dropdown.offsetWidth || 188;

        let top = rect.bottom + gap;
        dropdown.classList.remove('row-actions-dropdown-up');

        if (top + dropdownHeight > window.innerHeight - 8 && rect.top - dropdownHeight - gap > 8) {
            top = rect.top - dropdownHeight - gap;
            dropdown.classList.add('row-actions-dropdown-up');
        }

        let right = window.innerWidth - rect.right;
        right = Math.max(8, Math.min(right, window.innerWidth - dropdownWidth - 8));

        dropdown.style.top = `${Math.max(8, top)}px`;
        dropdown.style.right = `${right}px`;
        dropdown.style.left = 'auto';
        dropdown.style.bottom = 'auto';
    }

    function closeAllRowActionMenus(exceptMenu = null) {
        document.querySelectorAll('.row-actions-menu.is-open').forEach((menu) => {
            if (menu === exceptMenu) {
                return;
            }

            menu.classList.remove('is-open');
            const trigger = menu.querySelector('.row-actions-trigger');
            if (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
            }
            resetRowActionsDropdown(menu);
        });
    }

    function toggleRowActionsMenu(trigger) {
        const menu = trigger.closest('.row-actions-menu');
        if (!menu) {
            return;
        }

        const willOpen = !menu.classList.contains('is-open');
        closeAllRowActionMenus();

        if (willOpen) {
            menu.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
            requestAnimationFrame(() => positionRowActionsDropdown(menu));
        }
    }

    function bindRowActionsTable(tbody) {
        if (!tbody || tbody.dataset.rowActionsBound === '1') {
            return;
        }

        tbody.dataset.rowActionsBound = '1';
        tbody.addEventListener('click', (event) => {
            const trigger = event.target.closest('.row-actions-trigger');
            if (trigger) {
                event.stopPropagation();
                toggleRowActionsMenu(trigger);
            }
        });
    }

    document.addEventListener('click', (event) => {
        if (event.target.closest('.row-actions-item')) {
            closeAllRowActionMenus();
            return;
        }

        if (!event.target.closest('.row-actions-menu')) {
            closeAllRowActionMenus();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAllRowActionMenus();
        }
    });

    window.addEventListener('resize', () => {
        const openMenu = document.querySelector('.row-actions-menu.is-open');
        if (openMenu) {
            positionRowActionsDropdown(openMenu);
        }
    });

    window.addEventListener('scroll', () => {
        closeAllRowActionMenus();
    }, true);

    window.ROW_ACTIONS_ELLIPSIS = ICON_ELLIPSIS;
    window.bindRowActionsTable = bindRowActionsTable;
    window.closeAllRowActionMenus = closeAllRowActionMenus;
})();
