function isMobileViewport() {
    return window.matchMedia('(max-width: 767px)').matches;
}

function syncDesktopStretch() {
    const topNav = document.getElementById('topNav');
    if (!topNav) {
        return;
    }

    if (isMobileViewport()) {
        topNav.classList.remove('stretched');
        return;
    }

    if (document.body.classList.contains('sidebar-collapsed')) {
        topNav.classList.remove('stretched');
    } else {
        topNav.classList.add('stretched');
    }
}

function applyViewportMode() {
    const sidebar = document.querySelector('.sb-sidenav');
    const overlay = document.getElementById('sidebarOverlay');

    if (!sidebar || !overlay) {
        return;
    }

    if (isMobileViewport()) {
        document.body.classList.add('sidebar-collapsed');
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
    } else {
        const persisted = localStorage.getItem('adminSidebarCollapsed');
        if (persisted === '1') {
            document.body.classList.add('sidebar-collapsed');
        } else {
            document.body.classList.remove('sidebar-collapsed');
        }
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
    }

    syncDesktopStretch();
}

window.toggleSidebar = function () {
    const sidebar = document.querySelector('.sb-sidenav');
    const overlay = document.getElementById('sidebarOverlay');

    if (!sidebar || !overlay) {
        return;
    }

    if (isMobileViewport()) {
        const shouldOpen = !sidebar.classList.contains('open');
        sidebar.classList.toggle('open', shouldOpen);
        overlay.classList.toggle('show', shouldOpen);
        return;
    }

    const collapsed = document.body.classList.toggle('sidebar-collapsed');
    localStorage.setItem('adminSidebarCollapsed', collapsed ? '1' : '0');
    syncDesktopStretch();
};

function updateActiveNavigationByPath() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.sidebar-nav-list a.nav-link');

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
    const navLinks = document.querySelectorAll('.sidebar-nav-list a.nav-link');
    navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            navLinks.forEach(function (item) {
                item.classList.remove('active');
            });
            link.classList.add('active');
        });
    });
}

function bindTopbarUserMenu() {
    const userContainer = document.getElementById('topbarUser');
    const userToggle = document.getElementById('topbarUserToggle');
    const userMenu = document.getElementById('topbarUserMenu');

    if (!userContainer || !userToggle || !userMenu) {
        return;
    }

    function closeMenu() {
        userMenu.hidden = true;
        userToggle.setAttribute('aria-expanded', 'false');
    }

    userToggle.addEventListener('click', function (event) {
        event.stopPropagation();
        const isOpen = !userMenu.hidden;
        userMenu.hidden = isOpen;
        userToggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    });

    document.addEventListener('click', function (event) {
        if (!userContainer.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('sidebarOverlay');

    applyViewportMode();
    updateActiveNavigationByPath();
    bindNavClickState();
    bindTopbarUserMenu();

    if (overlay) {
        overlay.addEventListener('click', function () {
            const sidebar = document.querySelector('.sb-sidenav');
            if (!sidebar || !sidebar.classList.contains('open')) {
                return;
            }
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        const sidebar = document.querySelector('.sb-sidenav');
        if (sidebar && sidebar.classList.contains('open')) {
            sidebar.classList.remove('open');
            if (overlay) {
                overlay.classList.remove('show');
            }
        }
    });

    window.addEventListener('resize', applyViewportMode);
});
