// Sidebar JavaScript Functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeSidebar();
});

function initializeSidebar() {
    // Get DOM elements
    const sidebar = document.getElementById('mainSidebar');
    const sidebarClose = document.getElementById('sidebarClose');
    const navLinks = document.querySelectorAll('.nav-link');
    const sidebarToggle = document.getElementById('sidebarToggle');

    // Sidebar toggle button functionality
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    // Sidebar close button functionality
    if (sidebarClose) {
        sidebarClose.addEventListener('click', closeSidebar);
    }

    // Navigation link functionality
    initializeNavigation(navLinks);

    // Handle active state based on current page
    setActiveNavigation();

    // Initialize sidebar state
    initializeSidebarState();

    // Handle sidebar collapse on desktop
    initializeCollapseFeature();
}

function closeSidebar() {
    const sidebar = document.getElementById('mainSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const overlay = document.querySelector('.sidebar-overlay');

    if (sidebar) {
        sidebar.classList.remove('open');
        sidebarToggle.classList.remove('active');
        
        // Hide overlay
        if (overlay) {
            overlay.classList.remove('show');
        }
    }
}

function initializeNavigation(navLinks) {
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Remove active class from all links
            navLinks.forEach(l => l.classList.remove('active'));
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Handle mobile navigation
            if (window.innerWidth <= 768) {
                closeSidebar();
            }
            
            // Store active navigation
            localStorage.setItem('activeNav', this.getAttribute('href'));
        });
    });
}

function setActiveNavigation() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    const storedActiveNav = localStorage.getItem('activeNav');
    
    // Try to match current path with navigation links
    let activeFound = false;
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        
        // Check if href matches current path
        if (href === currentPath || (href !== '#' && currentPath.includes(href))) {
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            activeFound = true;
        }
    });
    
    // If no match found, use stored active navigation
    if (!activeFound && storedActiveNav) {
        navLinks.forEach(link => {
            if (link.getAttribute('href') === storedActiveNav) {
                navLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            }
        });
    }
    
    // Default to dashboard if still no active found
    if (!activeFound) {
        const dashboardLink = document.querySelector('.nav-link[href="#"]');
        if (dashboardLink) {
            dashboardLink.classList.add('active');
        }
    }
}

function initializeSidebarState() {
    const sidebar = document.getElementById('mainSidebar');
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    
    if (sidebar && window.innerWidth > 768) {
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
        }
    }
}

function initializeCollapseFeature() {
    // Add double-click to collapse/expand on desktop
    const sidebar = document.getElementById('mainSidebar');
    const sidebarHeader = document.querySelector('.sidebar-header');
    
    if (sidebar && sidebarHeader && window.innerWidth > 768) {
        sidebarHeader.addEventListener('dblclick', function(e) {
            e.preventDefault();
            toggleSidebarCollapse();
        });
    }
}

function toggleSidebarCollapse() {
    const sidebar = document.getElementById('mainSidebar');
    
    if (sidebar) {
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }
}

// Handle sidebar navigation with smooth scroll
function smoothScrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Mobile sidebar touch gestures
let touchStartX = 0;
let touchEndX = 0;

function initializeTouchGestures() {
    const sidebar = document.getElementById('mainSidebar');
    
    if (sidebar && window.innerWidth <= 768) {
        sidebar.addEventListener('touchstart', handleTouchStart, { passive: true });
        sidebar.addEventListener('touchend', handleTouchEnd, { passive: true });
    }
}

function handleTouchStart(e) {
    touchStartX = e.changedTouches[0].screenX;
}

function handleTouchEnd(e) {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipeGesture();
}

function handleSwipeGesture() {
    const swipeThreshold = 50;
    const diff = touchStartX - touchEndX;
    
    if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
            // Swipe left - close sidebar
            closeSidebar();
        } else {
            // Swipe right - open sidebar (if starting from edge)
            if (touchStartX < 20) {
                const sidebar = document.getElementById('mainSidebar');
                const sidebarToggle = document.getElementById('sidebarToggle');
                const overlay = document.querySelector('.sidebar-overlay');
                
                if (sidebar) {
                    sidebar.classList.add('open');
                    sidebarToggle.classList.add('active');
                    
                    if (!overlay) {
                        createOverlay();
                    } else {
                        overlay.classList.add('show');
                    }
                }
            }
        }
    }
}

// Create overlay for mobile sidebar
function createOverlay() {
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    overlay.addEventListener('click', closeSidebar);
    document.body.appendChild(overlay);
}

// Handle window resize
window.addEventListener('resize', function() {
    handleSidebarResize();
});

function handleSidebarResize() {
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (window.innerWidth > 768) {
        // Desktop view
        if (overlay) {
            overlay.classList.remove('show');
        }
        
        // Reset mobile sidebar state
        if (sidebar) {
            sidebar.classList.remove('open');
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.classList.remove('active');
            }
        }
        
        // Remove touch gesture listeners on desktop
        if (sidebar) {
            sidebar.removeEventListener('touchstart', handleTouchStart);
            sidebar.removeEventListener('touchend', handleTouchEnd);
        }
    } else {
        // Mobile view - initialize touch gestures
        initializeTouchGestures();
        
        // Ensure collapsed state is removed on mobile
        if (sidebar) {
            sidebar.classList.remove('collapsed');
        }
    }
}

// Keyboard navigation for sidebar
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + B to toggle sidebar
    if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
        e.preventDefault();
        toggleSidebar();
    }
    
    // Arrow key navigation in sidebar
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        const focusedElement = document.activeElement;
        const navLinks = Array.from(document.querySelectorAll('.nav-link'));
        
        if (navLinks.includes(focusedElement)) {
            e.preventDefault();
            const currentIndex = navLinks.indexOf(focusedElement);
            let nextIndex;
            
            if (e.key === 'ArrowDown') {
                nextIndex = (currentIndex + 1) % navLinks.length;
            } else {
                nextIndex = (currentIndex - 1 + navLinks.length) % navLinks.length;
            }
            
            navLinks[nextIndex].focus();
        }
    }
});

function toggleSidebar() {
    const sidebar = document.getElementById('mainSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const overlay = document.querySelector('.sidebar-overlay');

    if (sidebar) {
        if (window.innerWidth <= 768) {
            // Mobile - toggle open state
            sidebar.classList.toggle('open');
            sidebarToggle.classList.toggle('active');
            
            if (!overlay) {
                createOverlay();
            } else {
                overlay.classList.toggle('show');
            }
        } else {
            // Desktop - toggle collapsed state
            toggleSidebarCollapse();
        }
    }
}

// Sidebar animation helpers
function animateSidebarEntry() {
    const sidebar = document.getElementById('mainSidebar');
    if (sidebar) {
        sidebar.style.animation = 'slideInLeft 0.3s ease-out';
    }
}

function animateSidebarExit() {
    const sidebar = document.getElementById('mainSidebar');
    if (sidebar) {
        sidebar.style.animation = 'slideOutLeft 0.3s ease-out';
    }
}

// Initialize touch gestures on load
initializeTouchGestures();

// Export functions for external use if needed
window.SidebarFunctions = {
    closeSidebar: closeSidebar,
    toggleSidebar: toggleSidebar,
    toggleSidebarCollapse: toggleSidebarCollapse,
    setActiveNavigation: setActiveNavigation,
    smoothScrollToElement: smoothScrollToElement
};