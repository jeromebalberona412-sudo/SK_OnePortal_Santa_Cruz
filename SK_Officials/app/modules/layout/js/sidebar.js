// Sidebar JavaScript Functionality
// Single DOMContentLoaded — no duplicate listeners
// Sidebar responds ONLY to click actions. Hover has zero effect.

document.addEventListener('DOMContentLoaded', function () {
    initializeSidebar();
    isolateSidebarScroll();
    initArchivedDropdown();
    initNavClickExpand();
});

// ── Prevent page scroll when wheeling over sidebar ───────────────────────────
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

// ── Sidebar collapse (desktop) ───────────────────────────────────────────────
function initializeSidebar() {
    const sidebar     = document.getElementById('mainSidebar');
    const mainContent = document.querySelector('.main-content');
    const toggle      = document.getElementById('sidebarToggle');

    if (!sidebar || !mainContent) return;

    // On desktop, restore state from localStorage; default to collapsed
    if (window.innerWidth > 768) {
        const savedState = localStorage.getItem('sidebarState');
        if (savedState === 'open') {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('sidebar-collapsed');
            if (toggle) toggle.classList.add('active');
            // Clear the flag so next fresh load starts collapsed
            localStorage.removeItem('sidebarState');
        } else {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('sidebar-collapsed');
            if (toggle) toggle.classList.remove('active');
        }
    } else {
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('sidebar-collapsed');
    }

    window.addEventListener('resize', function () {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('sidebar-collapsed');
        } else {
            // Re-apply collapsed on resize to desktop if not already set
            if (!sidebar.classList.contains('collapsed')) {
                // Keep current state — don't force collapse on resize
            }
        }
    });
}

// ── Auto-expand sidebar when a nav item is clicked ───────────────────────────
function initNavClickExpand() {
    const sidebar     = document.getElementById('mainSidebar');
    const overlay     = document.getElementById('sidebarOverlay');

    if (!sidebar) return;

    // Collect all nav links (top-level + sub-links), excluding the Archived dropdown toggle
    const navLinks = sidebar.querySelectorAll(
        '.nav-link:not(.nav-link-dropdown), .nav-sublink'
    );

    navLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            const href = link.getAttribute('href');

            if (window.innerWidth > 768) {
                // Desktop: save "open" state to localStorage before navigation
                localStorage.setItem('sidebarState', 'open');
                // Navigation proceeds normally (no e.preventDefault())
            } else {
                // Mobile: open the sidebar (slide in)
                sidebar.classList.add('open');
                if (overlay) overlay.classList.add('show');
            }
        });
    });
}

// ── Archived dropdown — click-only, no inline onclick ───────────────────────
function initArchivedDropdown() {
    const toggleLink = document.getElementById('archivedToggleLink');
    const dropdown   = document.getElementById('archivedDropdown');

    if (!toggleLink || !dropdown) return;

    toggleLink.addEventListener('click', function (e) {
        e.preventDefault();

        // Also expand the sidebar if collapsed (desktop)
        const sidebar     = document.getElementById('mainSidebar');
        const mainContent = document.querySelector('.main-content');
        const toggle      = document.getElementById('sidebarToggle');
        if (sidebar && window.innerWidth > 768 && sidebar.classList.contains('collapsed')) {
            sidebar.classList.remove('collapsed');
            if (mainContent) mainContent.classList.remove('sidebar-collapsed');
            if (toggle) toggle.classList.add('active');
        }

        const isNowOpen = !dropdown.classList.contains('open');
        dropdown.classList.toggle('open', isNowOpen);
        sessionStorage.setItem('archivedDropdownOpen', isNowOpen ? '1' : '0');
    });

    // Restore dropdown open state on page load
    const isActive = dropdown.querySelector('.nav-sublink.active') !== null;
    const wasOpen  = sessionStorage.getItem('archivedDropdownOpen') === '1';

    if (isActive || wasOpen) {
        dropdown.classList.add('open');
        sessionStorage.setItem('archivedDropdownOpen', '1');
    }
}

// ── Keep toggleArchivedDropdown on window for any legacy callers ─────────────
window.toggleArchivedDropdown = function (event) {
    if (event) event.preventDefault();
    const dropdown = document.getElementById('archivedDropdown');
    if (!dropdown) return;
    const isNowOpen = !dropdown.classList.contains('open');
    dropdown.classList.toggle('open', isNowOpen);
    sessionStorage.setItem('archivedDropdownOpen', isNowOpen ? '1' : '0');
};

// ── Exports ──────────────────────────────────────────────────────────────────
window.SidebarFunctions = {
    initializeSidebar: initializeSidebar,
};
