// Sidebar JavaScript Functionality

document.addEventListener('DOMContentLoaded', function() {
    initializeSidebar();
});

function initializeSidebar() {
    // Get DOM elements
    const sidebar = document.getElementById('mainSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const overlay = document.querySelector('.sidebar-overlay');
    
    // Sidebar toggle is handled by header.js responsiveToggleSidebar() only
    
    // Close sidebar when clicking overlay
    if (overlay) {
        overlay.addEventListener('click', function() {
            closeSidebar();
        });
    }
    
    // Close sidebar with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });
}

function openSidebar() {
    const sidebar = document.getElementById('mainSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (sidebar) {
        sidebar.classList.add('open');
    }
    
    if (sidebarToggle) {
        sidebarToggle.classList.add('active');
    }
    
    // Show overlay for mobile
    if (window.innerWidth <= 768) {
        if (overlay) {
            overlay.classList.add('show');
        }
    }
}

function closeSidebar() {
    const sidebar = document.getElementById('mainSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (sidebar) {
        sidebar.classList.remove('open');
    }
    
    if (sidebarToggle) {
        sidebarToggle.classList.remove('active');
    }
    
    // Hide overlay
    if (overlay) {
        overlay.classList.remove('show');
    }
}

// Handle window resize
window.addEventListener('resize', function() {
    handleResize();
});

function handleResize() {
    const sidebar = document.getElementById('mainSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (window.innerWidth > 768) {
        // Desktop view - remove mobile overlay
        if (overlay) {
            overlay.classList.remove('show');
        }
        
        // Reset sidebar state for desktop
        if (sidebar) {
            sidebar.classList.remove('open');
            if (sidebarToggle) {
                sidebarToggle.classList.remove('active');
            }
        }
    }
}

// Export functions for external use if needed
window.SidebarFunctions = {
    open: openSidebar,
    close: closeSidebar
};