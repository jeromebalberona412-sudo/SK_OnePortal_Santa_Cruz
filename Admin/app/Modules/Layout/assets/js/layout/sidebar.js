// Make toggleSidebar available globally
window.toggleSidebar = function() {
    console.log('toggleSidebar called');
    const sidebar = document.querySelector('.sb-sidenav');
    const overlay = document.getElementById('sidebarOverlay');
    const topNav = document.getElementById('topNav');
    
    console.log('sidebar:', sidebar);
    console.log('overlay:', overlay);
    console.log('topNav:', topNav);
    
    if (sidebar.classList.contains('open')) {
        // Close sidebar
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
        topNav.classList.remove('stretched');
        console.log('Sidebar closed');
    } else {
        // Open sidebar
        sidebar.classList.add('open');
        overlay.classList.add('show');
        topNav.classList.add('stretched');
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
        if (link.getAttribute('href') === currentPath || 
            (currentPath === '/dashboard' && link.textContent.includes('Dashboard'))) {
            link.classList.add('active');
        }
    });
});