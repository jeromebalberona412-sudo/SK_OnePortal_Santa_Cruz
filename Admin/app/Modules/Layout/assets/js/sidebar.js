function isMobileViewport() {
    return window.matchMedia('(max-width: 1024px)').matches;
}

function getLayoutElements() {
    return {
        sidebar: document.querySelector('.sb-sidenav'),
        overlay: document.getElementById('sidebarOverlay'),
        topNav: document.getElementById('topNav'),
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
    const { profileMenu, profileToggle } = getLayoutElements();

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
    // Notification removed — no-op kept for backward compatibility
    event.stopPropagation();
};

window.toggleProfileDropdown = function (event) {
    event.stopPropagation();
    const { profileMenu, profileToggle } = getLayoutElements();
    if (!profileMenu || !profileToggle) {
        return;
    }

    const shouldOpen = profileMenu.hidden;

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
    const { overlay, profileMenu, profileToggle } = getLayoutElements();

    if (profileMenu) {
        profileMenu.hidden = true;
    }

    if (profileToggle) {
        profileToggle.setAttribute('aria-expanded', 'false');
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

        const clickedUserArea = topbarUser ? topbarUser.contains(event.target) : false;

        if (!clickedUserArea) {
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

window.toggleAccountDropdown = function (btn) {
    // If sidebar is collapsed on desktop, expand it first then open dropdown
    if (!isMobileViewport() && document.body.classList.contains('sidebar-collapsed')) {
        document.body.classList.remove('sidebar-collapsed');
        localStorage.setItem('adminSidebarCollapsed', '0');
        syncDesktopStretch();
        // Open the dropdown after the transition
        setTimeout(function () {
            const submenu = document.getElementById('accountDropdown');
            const chevron = btn.querySelector('.dropdown-chevron');
            if (!submenu) return;
            submenu.classList.add('open');
            if (chevron) chevron.classList.add('open');
            btn.setAttribute('aria-expanded', 'true');
        }, 260);
        return;
    }

    const submenu = document.getElementById('accountDropdown');
    const chevron = btn.querySelector('.dropdown-chevron');
    if (!submenu) return;

    const isOpen = submenu.classList.toggle('open');
    if (chevron) chevron.classList.toggle('open', isOpen);
    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
};

window.toggleArchivedDropdown = function (btn) {
    // If sidebar is collapsed on desktop, expand it first then open dropdown
    if (!isMobileViewport() && document.body.classList.contains('sidebar-collapsed')) {
        document.body.classList.remove('sidebar-collapsed');
        localStorage.setItem('adminSidebarCollapsed', '0');
        syncDesktopStretch();
        // Open the dropdown after the transition
        setTimeout(function () {
            const submenu = document.getElementById('archivedDropdown');
            const chevron = btn.querySelector('.dropdown-chevron');
            if (!submenu) return;
            submenu.classList.add('open');
            if (chevron) chevron.classList.add('open');
            btn.setAttribute('aria-expanded', 'true');
        }, 260);
        return;
    }

    const submenu = document.getElementById('archivedDropdown');
    const chevron = btn.querySelector('.dropdown-chevron');
    if (!submenu) return;

    const isOpen = submenu.classList.toggle('open');
    if (chevron) chevron.classList.toggle('open', isOpen);
    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
};
