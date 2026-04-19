// Sidebar JavaScript Functionality

document.addEventListener('DOMContentLoaded', function () {
    initializeSidebar();
    isolateSidebarScroll();
    restoreArchivedDropdown();
});

/**
 * Prevent the page from scrolling when the mouse wheel is used over the sidebar.
 * Works by intercepting the wheel event and manually scrolling the sidebar,
 * then stopping propagation so the main page never sees it.
 */
function isolateSidebarScroll() {
    const sidebar = document.getElementById('mainSidebar');
    if (!sidebar) return;

    sidebar.addEventListener('wheel', function (e) {
        const scrollTop    = sidebar.scrollTop;
        const scrollHeight = sidebar.scrollHeight;
        const clientHeight = sidebar.clientHeight;

        const atTop    = scrollTop === 0 && e.deltaY < 0;
        const atBottom = scrollTop + clientHeight >= scrollHeight && e.deltaY > 0;

        // Only block propagation when the sidebar itself can still scroll
        if (!atTop && !atBottom) {
            e.stopPropagation();
        }

        // Always prevent the default page scroll while cursor is over sidebar
        e.preventDefault();
        sidebar.scrollTop += e.deltaY;
    }, { passive: false });
}

function initializeSidebar() {
    const sidebar = document.getElementById('mainSidebar');
    const mainContent = document.querySelector('.main-content');
    const sidebarToggle = document.getElementById('sidebarToggle');

    if (!sidebar || !mainContent) return;

    let hoverTimeout;
    let isHovering = false;

    // Initialize sidebar state
    function initializeSidebarState() {
        if (window.innerWidth > 768) {
            // Desktop: start collapsed
            sidebar.classList.add('collapsed');
            mainContent.classList.add('sidebar-collapsed');

            // Update toggle button to show hamburger (collapsed state)
            if (sidebarToggle) {
                sidebarToggle.classList.remove('active');
            }
        } else {
            // Mobile: ensure normal state
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('sidebar-collapsed');
        }
    }

    // Initialize on load
    initializeSidebarState();

    // Handle mouse enter on sidebar
    sidebar.addEventListener('mouseenter', function () {
        if (window.innerWidth > 768 && sidebar.classList.contains('collapsed')) {
            clearTimeout(hoverTimeout);
            isHovering = true;

            // Add hover state class to sidebar
            sidebar.classList.add('hovering');

            // Add hover state class to main content
            mainContent.classList.add('sidebar-hover');

            // Update toggle button to show X during hover
            if (sidebarToggle) {
                sidebarToggle.classList.add('active');
            }
        }
    });

    // Handle mouse leave from sidebar
    sidebar.addEventListener('mouseleave', function () {
        if (window.innerWidth > 768 && sidebar.classList.contains('collapsed')) {
            clearTimeout(hoverTimeout);
            isHovering = false;

            // Add a small delay before collapsing to prevent flickering
            hoverTimeout = setTimeout(() => {
                if (!isHovering) {
                    sidebar.classList.remove('hovering');
                    mainContent.classList.remove('sidebar-hover');

                    // Update toggle button back to hamburger when hover ends
                    if (sidebarToggle) {
                        sidebarToggle.classList.remove('active');
                    }
                }
            }, 100);
        }
    });

    // Handle window resize
    window.addEventListener('resize', function () {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('collapsed', 'hovering');
            mainContent.classList.remove('sidebar-collapsed', 'sidebar-hover');
        } else {
            initializeSidebarState();
        }
    });
}

// Export functions for external use if needed
window.SidebarFunctions = {
    initializeSidebar: initializeSidebar
};

// Archived Dropdown Toggle
// State is persisted in sessionStorage so it survives page navigation.
function toggleArchivedDropdown(event) {
    event.preventDefault();
    const dropdown = document.getElementById('archivedDropdown');
    if (!dropdown) return;

    const isNowOpen = !dropdown.classList.contains('open');
    dropdown.classList.toggle('open');
    sessionStorage.setItem('archivedDropdownOpen', isNowOpen ? '1' : '0');
}

// Restore archived dropdown state on every page load
function restoreArchivedDropdown() {
    const dropdown = document.getElementById('archivedDropdown');
    if (!dropdown) return;

    // Default open if a sub-page is active, or if user had it open
    const isActive = dropdown.querySelector('.nav-sublink.active') !== null;
    const wasOpen  = sessionStorage.getItem('archivedDropdownOpen') === '1';

    if (isActive || wasOpen) {
        dropdown.classList.add('open');
        // Make sure storage reflects the open state
        sessionStorage.setItem('archivedDropdownOpen', '1');
    }
}

window.toggleArchivedDropdown = toggleArchivedDropdown;

// Deleted ABYIP — opens a modal view (stub, connect to ABYIP module modal)
function openDeletedABYIPModal(event) {
    event.preventDefault();
    // TODO: wire up to the actual Deleted ABYIP modal when the module is ready
    const modal = document.getElementById('deletedABYIPModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

window.openDeletedABYIPModal = openDeletedABYIPModal;
