// Sidebar JavaScript Functionality
// Single DOMContentLoaded — no duplicate listeners

document.addEventListener('DOMContentLoaded', function () {
    initializeSidebar();
    isolateSidebarScroll();
    initArchivedDropdown();
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

// ── Sidebar collapse / hover (desktop) ──────────────────────────────────────
function initializeSidebar() {
    const sidebar     = document.getElementById('mainSidebar');
    const mainContent = document.querySelector('.main-content');
    const toggle      = document.getElementById('sidebarToggle');

    if (!sidebar || !mainContent) return;

    let hoverTimeout;
    let isHovering = false;

    function initState() {
        if (window.innerWidth > 768) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('sidebar-collapsed');
            if (toggle) toggle.classList.remove('active');
        } else {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('sidebar-collapsed');
        }
    }

    initState();

    sidebar.addEventListener('mouseenter', function () {
        if (window.innerWidth > 768 && sidebar.classList.contains('collapsed')) {
            clearTimeout(hoverTimeout);
            isHovering = true;
            sidebar.classList.add('hovering');
            mainContent.classList.add('sidebar-hover');
            if (toggle) toggle.classList.add('active');
        }
    });

    sidebar.addEventListener('mouseleave', function () {
        if (window.innerWidth > 768 && sidebar.classList.contains('collapsed')) {
            clearTimeout(hoverTimeout);
            isHovering = false;
            hoverTimeout = setTimeout(function () {
                if (!isHovering) {
                    sidebar.classList.remove('hovering');
                    mainContent.classList.remove('sidebar-hover');
                    if (toggle) toggle.classList.remove('active');
                }
            }, 100);
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('collapsed', 'hovering');
            mainContent.classList.remove('sidebar-collapsed', 'sidebar-hover');
        } else {
            initState();
        }
    });
}

// ── Archived dropdown — click-only, no inline onclick ───────────────────────
function initArchivedDropdown() {
    const toggleLink = document.getElementById('archivedToggleLink');
    const dropdown   = document.getElementById('archivedDropdown');

    if (!toggleLink || !dropdown) return;

    // Wire click listener once
    toggleLink.addEventListener('click', function (e) {
        e.preventDefault();
        const isNowOpen = !dropdown.classList.contains('open');
        dropdown.classList.toggle('open', isNowOpen);
        sessionStorage.setItem('archivedDropdownOpen', isNowOpen ? '1' : '0');
        nudgeSidebarScroll();
    });

    // Restore state from sessionStorage on page load
    const isActive = dropdown.querySelector('.nav-sublink.active') !== null;
    const wasOpen  = sessionStorage.getItem('archivedDropdownOpen') === '1';

    if (isActive || wasOpen) {
        dropdown.classList.add('open');
        sessionStorage.setItem('archivedDropdownOpen', '1');
        // Nudge after paint so scrollbar reflects expanded height
        requestAnimationFrame(nudgeSidebarScroll);
    }
}

// Force scrollbar thumb to recalculate after content height changes
function nudgeSidebarScroll() {
    const sidebar = document.getElementById('mainSidebar');
    if (!sidebar) return;
    requestAnimationFrame(function () {
        const prev = sidebar.scrollTop;
        sidebar.scrollTop = prev + 1;
        sidebar.scrollTop = prev;
    });
}

// ── Keep toggleArchivedDropdown on window for any legacy callers ─────────────
// (safe no-op if the new listener is already handling it)
window.toggleArchivedDropdown = function (event) {
    if (event) event.preventDefault();
    const dropdown = document.getElementById('archivedDropdown');
    if (!dropdown) return;
    const isNowOpen = !dropdown.classList.contains('open');
    dropdown.classList.toggle('open', isNowOpen);
    sessionStorage.setItem('archivedDropdownOpen', isNowOpen ? '1' : '0');
    nudgeSidebarScroll();
};

// ── Exports ──────────────────────────────────────────────────────────────────
window.SidebarFunctions = {
    initializeSidebar: initializeSidebar,
};
