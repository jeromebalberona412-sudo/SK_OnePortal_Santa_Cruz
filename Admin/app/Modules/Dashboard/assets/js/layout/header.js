document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const userInfo = document.getElementById('userInfo');
    const userDropdown = document.getElementById('userDropdown');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const logoutForm = document.getElementById('logoutForm');
    const adminHeader = document.querySelector('.admin-header');
    
    // Initialize dropdown state
    let isDropdownOpen = false;
    
    // Toggle user dropdown
    function toggleDropdown() {
        isDropdownOpen = !isDropdownOpen;
        
        if (isDropdownOpen) {
            userInfo.classList.add('active');
            userDropdown.classList.add('show');
        } else {
            userInfo.classList.remove('active');
            userDropdown.classList.remove('show');
        }
    }
    
    // Close dropdown
    function closeDropdown() {
        isDropdownOpen = false;
        userInfo.classList.remove('active');
        userDropdown.classList.remove('show');
    }
    
    // User info click event
    if (userInfo) {
        userInfo.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleDropdown();
        });
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!userInfo.contains(e.target) && !userDropdown.contains(e.target)) {
            closeDropdown();
        }
    });
    
    // Mobile menu toggle
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            // Toggle sidebar if it exists
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('show');
            }
        });
    }
    
    // Logout form submission
    if (logoutForm) {
        logoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Add loading state
            const submitBtn = this.querySelector('.logout-btn');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Logging out...</span>';
                submitBtn.disabled = true;
            }
            
            // Simulate logout process (remove in production)
            setTimeout(() => {
                this.submit();
            }, 1000);
        });
    }
    
    // Header scroll effect
    let lastScrollTop = 0;
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (adminHeader) {
            if (scrollTop > 50) {
                adminHeader.classList.add('scrolled');
            } else {
                adminHeader.classList.remove('scrolled');
            }
        }
        
        lastScrollTop = scrollTop;
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        // ESC key to close dropdown
        if (e.key === 'Escape' && isDropdownOpen) {
            closeDropdown();
        }
        
        // Enter key on user info to toggle dropdown
        if (e.key === 'Enter' && document.activeElement === userInfo) {
            e.preventDefault();
            toggleDropdown();
        }
        
        // Arrow key navigation in dropdown
        if (isDropdownOpen) {
            const items = userDropdown.querySelectorAll('.dropdown-item');
            let currentIndex = Array.from(items).findIndex(item => item === document.activeElement);
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                currentIndex = (currentIndex + 1) % items.length;
                items[currentIndex].focus();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                currentIndex = currentIndex <= 0 ? items.length - 1 : currentIndex - 1;
                items[currentIndex].focus();
            }
        }
    });
    
    // Make dropdown items focusable
    const dropdownItems = userDropdown.querySelectorAll('.dropdown-item');
    dropdownItems.forEach(item => {
        item.setAttribute('tabindex', '0');
        
        item.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
    
    // Mobile touch support
    let touchStartY = 0;
    let touchEndY = 0;
    
    document.addEventListener('touchstart', function(e) {
        touchStartY = e.changedTouches[0].screenY;
    });
    
    document.addEventListener('touchend', function(e) {
        touchEndY = e.changedTouches[0].screenY;
        handleSwipe();
    });
    
    function handleSwipe() {
        const swipeThreshold = 50;
        const diff = touchStartY - touchEndY;
        
        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                // Swipe up - could be used to hide header
                console.log('Swipe up detected');
            } else {
                // Swipe down - could be used to show header
                console.log('Swipe down detected');
            }
        }
    }
    
    // User profile image error handling
    const userAvatar = document.querySelector('.user-avatar img');
    if (userAvatar) {
        userAvatar.addEventListener('error', function() {
            this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM2NjdlZWEiLz4KPGNpcmNsZSBjeD0iMjAiIGN5PSIxNSIgcj0iNiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTggMzJDOCAyNi41IDEyLjUgMjIgMjAgMjJDMjcuNSAyMiAzMiAyNi41IDMyIDMyVjM2SDhWMzJaIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4K';
        });
    }
    
    // Initialize tooltips if needed
    function initTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', function() {
                const tooltip = document.createElement('div');
                tooltip.className = 'header-tooltip';
                tooltip.textContent = this.getAttribute('data-tooltip');
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
                
                this.addEventListener('mouseleave', function() {
                    tooltip.remove();
                }, { once: true });
            });
        });
    }
    
    // Initialize
    initTooltips();
    
    // Console log for debugging
    console.log('Header initialized successfully');
});

// Header manager for external use
window.HeaderManager = {
    toggleDropdown: function() {
        const userInfo = document.getElementById('userInfo');
        if (userInfo) {
            userInfo.click();
        }
    },
    
    closeDropdown: function() {
        const userInfo = document.getElementById('userInfo');
        const userDropdown = document.getElementById('userDropdown');
        if (userInfo && userDropdown) {
            userInfo.classList.remove('active');
            userDropdown.classList.remove('show');
        }
    },
    
    setUser: function(userData) {
        const userName = document.querySelector('.user-name');
        const userEmail = document.querySelector('.user-email');
        const userAvatar = document.querySelector('.user-avatar img');
        
        if (userName && userData.name) {
            userName.textContent = userData.name;
        }
        
        if (userEmail && userData.email) {
            userEmail.textContent = userData.email;
        }
        
        if (userAvatar && userData.avatar) {
            userAvatar.src = userData.avatar;
        }
    },
    
    showNotification: function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `header-notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
};
