function isMobileViewport() {
    return window.matchMedia('(max-width: 1024px)').matches;
}

function getLayoutElements() {
    return {
        sidebar: document.querySelector('.sb-sidenav'),
        overlay: document.getElementById('sidebarOverlay'),
        topNav: document.getElementById('topNav'),
        notifPopover: document.getElementById('notifPopover'),
        notifToggle: document.getElementById('notifToggle'),
        profileMenu: document.getElementById('topbarUserMenu'),
        profileToggle: document.getElementById('topbarUserToggle'),
    };
}

function closeSidebarOverlay() {
    const { sidebar, overlay } = getLayoutElements();
    document.body.classList.remove('sidebar-open');

    if (sidebar) {
        sidebar.classList.remove('open');
    }

    if (overlay) {
        overlay.classList.remove('show');
    }
}

function syncDesktopStretch() {
    const { topNav } = getLayoutElements();
    if (!topNav) {
        return;
    }

    if (isMobileViewport()) {
        topNav.classList.remove('stretched');
        return;
    }

    topNav.classList.toggle('stretched', !document.body.classList.contains('sidebar-collapsed'));
}

function closeTopbarMenus() {
    const { notifPopover, notifToggle, profileMenu, profileToggle } = getLayoutElements();

    if (notifPopover) {
        notifPopover.classList.remove('show');
        notifPopover.hidden = true;
    }

    if (notifToggle) {
        notifToggle.setAttribute('aria-expanded', 'false');
    }

    if (profileMenu) {
        profileMenu.classList.remove('show');
        profileMenu.hidden = true;
    }

    if (profileToggle) {
        profileToggle.classList.remove('open');
        profileToggle.setAttribute('aria-expanded', 'false');
    }
}

function applyViewportMode() {
    if (isMobileViewport()) {
        document.body.classList.remove('sidebar-collapsed');
        closeSidebarOverlay();
        syncDesktopStretch();
        return;
    }

    document.body.classList.remove('sidebar-open');
    const persisted = localStorage.getItem('adminSidebarCollapsed');
    document.body.classList.toggle('sidebar-collapsed', persisted === '1');
    closeSidebarOverlay();
    syncDesktopStretch();
}

window.toggleSidebar = function () {
    const { sidebar, overlay } = getLayoutElements();
    if (!sidebar || !overlay) {
        return;
    }

    if (isMobileViewport()) {
        const shouldOpen = !document.body.classList.contains('sidebar-open');
        document.body.classList.toggle('sidebar-open', shouldOpen);
        sidebar.classList.toggle('open', shouldOpen);
        overlay.classList.toggle('show', shouldOpen);
        return;
    }

    const collapsed = document.body.classList.toggle('sidebar-collapsed');
    localStorage.setItem('adminSidebarCollapsed', collapsed ? '1' : '0');
    syncDesktopStretch();
};

window.toggleNotifPopover = function (event) {
    event.stopPropagation();
    const { notifPopover, notifToggle, profileMenu, profileToggle } = getLayoutElements();
    if (!notifPopover || !notifToggle) {
        return;
    }

    const shouldOpen = notifPopover.hidden;

    if (profileMenu) {
        profileMenu.classList.remove('show');
        profileMenu.hidden = true;
    }

    if (profileToggle) {
        profileToggle.classList.remove('open');
        profileToggle.setAttribute('aria-expanded', 'false');
    }

    notifPopover.hidden = !shouldOpen;
    notifPopover.classList.toggle('show', shouldOpen);
    notifToggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
};

window.toggleProfileDropdown = function (event) {
    event.stopPropagation();
    const { notifPopover, notifToggle, profileMenu, profileToggle } = getLayoutElements();
    if (!profileMenu || !profileToggle) {
        return;
    }

    const shouldOpen = profileMenu.hidden;

    if (notifPopover) {
        notifPopover.classList.remove('show');
        notifPopover.hidden = true;
    }

    if (notifToggle) {
        notifToggle.setAttribute('aria-expanded', 'false');
    }

    profileMenu.hidden = !shouldOpen;
    profileMenu.classList.toggle('show', shouldOpen);
    profileToggle.classList.toggle('open', shouldOpen);
    profileToggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
};

function updateActiveNavigationByPath() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.sidebar-nav a.menu-item[href]');

    navLinks.forEach(function (link) {
        link.classList.remove('active');

        let linkPath = '';
        try {
            linkPath = new URL(link.href).pathname;
        } catch (_error) {
            linkPath = link.getAttribute('href') || '';
        }

        if (linkPath === currentPath || (currentPath === '/dashboard' && link.classList.contains('dashboard-btn'))) {
            link.classList.add('active');
        }
    });
}

function bindNavClickState() {
    const navLinks = document.querySelectorAll('.sidebar-nav a.menu-item[href]');
    navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            navLinks.forEach(function (item) {
                item.classList.remove('active');
            });
            link.classList.add('active');
        });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const { overlay, profileMenu, profileToggle, notifPopover, notifToggle } = getLayoutElements();

    if (profileMenu) {
        profileMenu.hidden = true;
    }

    if (notifPopover) {
        notifPopover.hidden = true;
    }

    if (profileToggle) {
        profileToggle.setAttribute('aria-expanded', 'false');
    }

    if (notifToggle) {
        notifToggle.setAttribute('aria-expanded', 'false');
    }

    applyViewportMode();
    updateActiveNavigationByPath();
    bindNavClickState();

    if (overlay) {
        overlay.addEventListener('click', function () {
            closeSidebarOverlay();
        });
    }

    document.addEventListener('click', function (event) {
        const topbarUser = document.getElementById('topbarUser');
        const notifButton = document.getElementById('notifToggle');
        const notifPopoverEl = document.getElementById('notifPopover');

        const clickedUserArea = topbarUser ? topbarUser.contains(event.target) : false;
        const clickedNotifArea = notifButton ? notifButton.contains(event.target) : false;
        const clickedNotifPopover = notifPopoverEl ? notifPopoverEl.contains(event.target) : false;

        if (!clickedUserArea && !clickedNotifArea && !clickedNotifPopover) {
            closeTopbarMenus();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        closeTopbarMenus();

        if (document.body.classList.contains('sidebar-open')) {
            closeSidebarOverlay();
        }
    });

    window.addEventListener('resize', applyViewportMode);
});
