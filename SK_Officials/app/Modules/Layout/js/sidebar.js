// Sidebar JavaScript Functionality
// Single DOMContentLoaded — no duplicate listeners
// Sidebar responds ONLY to click actions. Hover has zero effect.

function isDesktopNav() {
    return window.matchMedia('(min-width: 769px)').matches;
}

function getSidebarEls() {
    return {
        sidebar: document.getElementById('mainSidebar'),
        overlay: document.getElementById('sidebarOverlay') || document.querySelector('.sidebar-overlay'),
        toggle: document.getElementById('sidebarToggle'),
        mainContent: document.querySelector('.main-content'),
        closeBtn: document.getElementById('sidebarClose'),
    };
}

function setMobileSidebarOpen(isOpen) {
    const { sidebar, overlay, toggle } = getSidebarEls();
    if (!sidebar) return;

    sidebar.classList.toggle('open', isOpen);
    document.body.classList.toggle('sidebar-open', isOpen);
    if (toggle) toggle.classList.toggle('active', isOpen);
    if (overlay) overlay.classList.toggle('show', isOpen);
}

function closeMobileSidebar() {
    setMobileSidebarOpen(false);
}

document.addEventListener('DOMContentLoaded', function () {
    initializeSidebar();
    isolateSidebarScroll();
    initArchivedDropdown();
    initYouthManagementDropdown();
    initPlanningDevDropdown();
    initNavClickExpand();
    initSidebarCloseControls();
});

function isolateSidebarScroll() {
    const sidebar = document.getElementById('mainSidebar');
    if (!sidebar) return;

    sidebar.addEventListener('wheel', function (e) {
        const atTop    = sidebar.scrollTop === 0 && e.deltaY < 0;
        const atBottom = sidebar.scrollTop + sidebar.clientHeight >= sidebar.scrollHeight && e.deltaY > 0;

        if (!atTop && !atBottom) e.stopPropagation();
        e.preventDefault();
        sidebar.scrollTop += e.deltaY;
    }, { passive: false });
}

function syncSidebarLayout() {
    const { sidebar, overlay, toggle, mainContent } = getSidebarEls();
    if (!sidebar || !mainContent) return;

    if (isDesktopNav()) {
        closeMobileSidebar();
        if (overlay) overlay.classList.remove('show');
        sidebar.classList.remove('open');
        document.body.classList.remove('sidebar-open');

        if (sidebar.classList.contains('collapsed')) {
            mainContent.classList.add('sidebar-collapsed');
            if (toggle) toggle.classList.remove('active');
        } else {
            mainContent.classList.remove('sidebar-collapsed');
            if (toggle) toggle.classList.add('active');
        }
    } else {
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('sidebar-collapsed');
        if (!sidebar.classList.contains('open')) {
            if (toggle) toggle.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }
    }
}

function initSidebarCloseControls() {
    const { closeBtn, overlay } = getSidebarEls();

    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            closeMobileSidebar();
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () {
            closeMobileSidebar();
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeMobileSidebar();
        }
    });
}

function initializeSidebar() {
    const { sidebar, mainContent, toggle } = getSidebarEls();

    if (!sidebar || !mainContent) return;

    if (isDesktopNav()) {
        const savedState = localStorage.getItem('sidebarState');
        if (savedState === 'open') {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('sidebar-collapsed');
            if (toggle) toggle.classList.add('active');
            localStorage.removeItem('sidebarState');
        } else {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('sidebar-collapsed');
            if (toggle) toggle.classList.remove('active');
        }
    } else {
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('sidebar-collapsed');
        closeMobileSidebar();
    }

    window.addEventListener('resize', syncSidebarLayout);
    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', syncSidebarLayout);
    }
}

function initNavClickExpand() {
    const sidebar = document.getElementById('mainSidebar');
    if (!sidebar) return;

    const navLinks = sidebar.querySelectorAll(
        '.nav-link:not(.nav-link-dropdown), .nav-sublink'
    );

    navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            if (isDesktopNav()) {
                localStorage.setItem('sidebarState', 'open');
            } else {
                closeMobileSidebar();
            }
        });
    });
}

function expandSidebarIfCollapsed() {
    const { sidebar, mainContent, toggle } = getSidebarEls();
    if (sidebar && isDesktopNav() && sidebar.classList.contains('collapsed')) {
        sidebar.classList.remove('collapsed');
        if (mainContent) mainContent.classList.remove('sidebar-collapsed');
        if (toggle) toggle.classList.add('active');
    }
}

function initPlanningDevDropdown() {
    const toggleLink = document.getElementById('planningDevToggleLink');
    const dropdown   = document.getElementById('planningDevDropdown');
    if (!toggleLink || !dropdown) return;

    toggleLink.addEventListener('click', function (e) {
        e.preventDefault();
        expandSidebarIfCollapsed();

        const isNowOpen = !dropdown.classList.contains('open');
        dropdown.classList.toggle('open', isNowOpen);
        sessionStorage.setItem('planningDevDropdownOpen', isNowOpen ? '1' : '0');
    });

    const isActive = dropdown.querySelector('.nav-sublink.active') !== null;
    const wasOpen  = sessionStorage.getItem('planningDevDropdownOpen') === '1';

    if (isActive || wasOpen) {
        dropdown.classList.add('open');
        sessionStorage.setItem('planningDevDropdownOpen', '1');
    }
}

function initYouthManagementDropdown() {
    const toggleLink = document.getElementById('youthManagementToggleLink');
    const dropdown   = document.getElementById('youthManagementDropdown');

    if (!toggleLink || !dropdown) return;

    toggleLink.addEventListener('click', function (e) {
        e.preventDefault();
        expandSidebarIfCollapsed();

        const isNowOpen = !dropdown.classList.contains('open');
        dropdown.classList.toggle('open', isNowOpen);
        sessionStorage.setItem('youthManagementDropdownOpen', isNowOpen ? '1' : '0');
    });

    const isActive = dropdown.querySelector('.nav-sublink.active') !== null;
    const wasOpen  = sessionStorage.getItem('youthManagementDropdownOpen') === '1';

    if (isActive || wasOpen) {
        dropdown.classList.add('open');
        sessionStorage.setItem('youthManagementDropdownOpen', '1');
    }
}

function initArchivedDropdown() {
    const toggleLink = document.getElementById('archivedToggleLink');
    const dropdown   = document.getElementById('archivedDropdown');

    if (!toggleLink || !dropdown) return;

    toggleLink.addEventListener('click', function (e) {
        e.preventDefault();
        expandSidebarIfCollapsed();

        const isNowOpen = !dropdown.classList.contains('open');
        dropdown.classList.toggle('open', isNowOpen);
        sessionStorage.setItem('archivedDropdownOpen', isNowOpen ? '1' : '0');
    });

    const isActive = dropdown.querySelector('.nav-sublink.active') !== null;
    const wasOpen  = sessionStorage.getItem('archivedDropdownOpen') === '1';

    if (isActive || wasOpen) {
        dropdown.classList.add('open');
        sessionStorage.setItem('archivedDropdownOpen', '1');
    }
}

window.toggleArchivedDropdown = function (event) {
    if (event) event.preventDefault();
    const dropdown = document.getElementById('archivedDropdown');
    if (!dropdown) return;
    const isNowOpen = !dropdown.classList.contains('open');
    dropdown.classList.toggle('open', isNowOpen);
    sessionStorage.setItem('archivedDropdownOpen', isNowOpen ? '1' : '0');
};

window.SidebarFunctions = {
    initializeSidebar: initializeSidebar,
    closeMobileSidebar: closeMobileSidebar,
    isDesktopNav: isDesktopNav,
};
