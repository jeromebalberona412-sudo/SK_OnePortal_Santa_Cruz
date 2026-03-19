// Dashboard route protection is handled server-side in Laravel.
document.addEventListener('DOMContentLoaded', function () {
    initializeDashboard();
});

// Initialize dashboard features
function initializeDashboard() {
    // Add dynamic content loading
    loadDashboardContent();
    
    // Add keyboard shortcuts
    setupKeyboardShortcuts();
}

// Load dynamic dashboard content
function loadDashboardContent() {
    // Simulate loading dashboard data
    const dashboardContent = document.querySelector('.dashboard-content');
    
    if (dashboardContent) {
        // Add loading state
        dashboardContent.style.opacity = '0.5';
        
        // Simulate API call
        setTimeout(() => {
            // Add more content cards dynamically
            addContentCards();
            dashboardContent.style.opacity = '1';
        }, 1000);
    }
}

// Add content cards to dashboard
function addContentCards() {
    const dashboardContent = document.querySelector('.dashboard-content');
    
    if (!dashboardContent) return;
    
    const additionalCards = [
        {
            title: 'Quick Stats',
            content: 'View your statistics and performance metrics here.'
        },
        {
            title: 'Recent Activity',
            content: 'Check your recent activities and updates.'
        },
        {
            title: 'Notifications',
            content: 'Stay updated with the latest notifications and alerts.'
        }
    ];
    
    additionalCards.forEach((card, index) => {
        const cardElement = document.createElement('div');
        cardElement.className = 'content-card';
        cardElement.style.animationDelay = `${0.4 + (index * 0.1)}s`;
        
        cardElement.innerHTML = `
            <h3>${card.title}</h3>
            <p>${card.content}</p>
        `;
        
        dashboardContent.appendChild(cardElement);
    });
}

// Setup keyboard shortcuts
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Add other keyboard shortcuts here (non-logout related)
        if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
            e.preventDefault();
            // Add dashboard-specific action
            console.log('Dashboard shortcut pressed');
        }
    });
}

// Add hover effects to content cards
document.addEventListener('DOMContentLoaded', function() {
    const contentCards = document.querySelectorAll('.content-card');
    
    contentCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.cursor = 'pointer';
        });
        
        card.addEventListener('click', function() {
            // Simulate card click action
            const title = this.querySelector('h3').textContent;
            showCardDetails(title);
        });
    });
});

// Show card details (placeholder function)
function showCardDetails(cardTitle) {
    alert(`You clicked on: ${cardTitle}\n\nThis would typically open a detailed view or perform an action related to this card.`);
}

// Add responsive menu toggle for mobile
function setupMobileMenu() {
    const headerContent = document.querySelector('.header-content');
    const userSection = document.querySelector('.user-section');
    
    if (window.innerWidth <= 768) {
        // Add mobile menu toggle button if needed
        if (!document.querySelector('.mobile-menu-toggle')) {
            const menuToggle = document.createElement('button');
            menuToggle.className = 'mobile-menu-toggle';
            menuToggle.innerHTML = '☰';
            menuToggle.style.cssText = `
                background: none;
                border: none;
                font-size: 20px;
                cursor: pointer;
                display: none;
            `;
            
            headerContent.insertBefore(menuToggle, userSection);
            
            menuToggle.addEventListener('click', function() {
                userSection.style.display = userSection.style.display === 'none' ? 'flex' : 'none';
            });
        }
    }
}

// Handle window resize
window.addEventListener('resize', function() {
    setupMobileMenu();
});

// Initialize mobile menu on load
setupMobileMenu();

// Add smooth scroll behavior
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// Performance optimization: Debounce resize events
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Debounced resize handler
const debouncedResize = debounce(function() {
    setupMobileMenu();
}, 250);

window.addEventListener('resize', debouncedResize);