// ══════════════════════════════════════════════════════════════
// Sports Tabs Functionality
// ══════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function() {
    initSportsTabs();
});

function initSportsTabs() {
    const tabs = document.querySelectorAll('.sports-tab');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            // Allow default navigation (href)
            // The active class is set server-side via Blade
        });
    });
    
    // Handle tab switching if needed for SPA-like behavior
    const tabBar = document.querySelector('.sports-tab-bar');
    if (tabBar) {
        const currentPath = window.location.pathname;
        const currentTab = getCurrentTabFromPath(currentPath);
        
        // Update active class based on current path
        tabs.forEach(tab => {
            tab.classList.remove('active');
            const tabName = tab.getAttribute('data-tab');
            if (tabName === currentTab) {
                tab.classList.add('active');
            }
        });
    }
}

function getCurrentTabFromPath(path) {
    if (path.includes('sports-application-form')) return 'form';
    if (path.includes('sports-requests')) return 'requests';
    if (path.includes('sport.list')) return 'list';
    if (path.includes('sports.evaluation')) return 'evaluation';
    return 'form';
}

// Export for use in other files
window.initSportsTabs = initSportsTabs;
