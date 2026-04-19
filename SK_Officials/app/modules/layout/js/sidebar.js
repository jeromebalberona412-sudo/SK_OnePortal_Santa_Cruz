// Sidebar JavaScript Functionality

document.addEventListener('DOMContentLoaded', function () {
    initializeSidebar();
    isolateSidebarScroll();
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
function toggleArchivedDropdown(event) {
    event.preventDefault();
    const dropdown = document.getElementById('archivedDropdown');
    const sidebar  = document.getElementById('mainSidebar');
    if (!dropdown) return;

    const isOpening = !dropdown.classList.contains('open');
    dropdown.classList.toggle('open');

    // When opening, scroll the sidebar so the submenu is visible
    if (isOpening && sidebar) {
        // Wait for the CSS max-height transition to start (one frame)
        requestAnimationFrame(() => {
            const submenu = document.getElementById('archivedSubmenu');
            if (!submenu) return;

            // Get position of the dropdown item relative to the sidebar
            const dropdownRect = dropdown.getBoundingClientRect();
            const sidebarRect  = sidebar.getBoundingClientRect();

            // How far the bottom of the submenu will be after it fully expands
            const submenuHeight = submenu.scrollHeight;
            const targetBottom  = dropdownRect.bottom - sidebarRect.top + submenuHeight + sidebar.scrollTop;
            const visibleBottom = sidebar.scrollTop + sidebar.clientHeight;

            if (targetBottom > visibleBottom) {
                sidebar.scrollTo({
                    top: targetBottom - sidebar.clientHeight + 16,
                    behavior: 'smooth'
                });
            }
        });
    }
}

window.toggleArchivedDropdown = toggleArchivedDropdown;
