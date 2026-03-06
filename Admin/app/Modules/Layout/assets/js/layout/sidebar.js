// Make toggleSidebar available globally
window.toggleSidebar = function() {
    console.log('toggleSidebar called');
    const sidebar = document.querySelector('.sb-sidenav');
    const overlay = document.getElementById('sidebarOverlay');
    const topNav = document.getElementById('topNav');
    const toggleBtn = document.getElementById('sidebarToggle');
    
    console.log('sidebar:', sidebar);
    console.log('overlay:', overlay);
    console.log('topNav:', topNav);
    console.log('toggleBtn:', toggleBtn);
    
    if (sidebar.classList.contains('open')) {
        // Close sidebar
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
        topNav.classList.remove('stretched');
        // Show toggle button when sidebar is closed
        if (toggleBtn) {
            toggleBtn.style.display = 'flex';
        }
        console.log('Sidebar closed');
    } else {
        // Open sidebar
        sidebar.classList.add('open');
        overlay.classList.add('show');
        topNav.classList.add('stretched');
        // Hide toggle button when sidebar is open
        if (toggleBtn) {
            toggleBtn.style.display = 'none';
        }
        console.log('Sidebar opened');
    }
}

// Close sidebar when clicking overlay
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('sidebarOverlay');
    if (overlay) {
        overlay.addEventListener('click', function() {
            toggleSidebar();
        });
    }

    // Close sidebar with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const sidebar = document.querySelector('.sb-sidenav');
            if (sidebar.classList.contains('open')) {
                toggleSidebar();
            }
        }
    });

    // Set active navigation
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.sb-sidenav-menu .nav-link');
    
    navLinks.forEach(link => {
        let linkPath = '';
        try {
            linkPath = new URL(link.href).pathname;
        } catch (e) {
            linkPath = link.getAttribute('href') || '';
        }

        if (linkPath === currentPath ||
            (currentPath === '/dashboard' && link.textContent.includes('Dashboard'))) {
            link.classList.add('active');
        }
    });
});