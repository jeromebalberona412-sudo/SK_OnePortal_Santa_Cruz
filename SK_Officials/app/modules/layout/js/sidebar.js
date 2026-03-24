// Sidebar JavaScript Functionality

document.addEventListener('DOMContentLoaded', function () {
    initializeSidebar();
});

function initializeSidebar() {
    const sidebar = document.getElementById('mainSidebar');
    const mainContent = document.querySelector('.main-content');

    if (!sidebar || !mainContent) return;

    let hoverTimeout;
    let isHovering = false;

    // Initialize sidebar state
    function initializeSidebarState() {
        if (window.innerWidth > 768) {
            // Desktop: start collapsed
            sidebar.classList.add('collapsed');
            mainContent.classList.add('sidebar-collapsed');
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