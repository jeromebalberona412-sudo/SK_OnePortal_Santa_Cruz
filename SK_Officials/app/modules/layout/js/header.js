// Header JavaScript Functionality

document.addEventListener('DOMContentLoaded', function() {

    initializeHeader();

});



function initializeHeader() {

    // Get DOM elements

    const sidebarToggle = document.getElementById('sidebarToggle');

    const userMenuToggle = document.getElementById('userMenuToggle');

    const userDropdown = document.getElementById('userDropdown');

    const searchInput = document.querySelector('.search-input');

    const searchBtn = document.querySelector('.search-btn');

    const notificationBtn = document.querySelector('.notification-btn');

    const logoutBtn = document.getElementById('logoutBtn'); // Added logout button element



    // Sidebar toggle functionality

    if (sidebarToggle) {

        sidebarToggle.addEventListener('click', toggleSidebar);

    }



    // User menu dropdown functionality

    if (userMenuToggle && userDropdown) {

        userMenuToggle.addEventListener('click', toggleUserDropdown);

    }



    // Logout functionality

    initializeLogout();



    // Search functionality

    if (searchInput && searchBtn) {

        initializeSearch();

    }



    // Notification functionality

    if (notificationBtn) {

        initializeNotifications();

    }



    // Close dropdowns when clicking outside

    document.addEventListener('click', handleOutsideClick);

}



function toggleSidebar() {

    const sidebar = document.getElementById('mainSidebar');

    const sidebarToggle = document.getElementById('sidebarToggle');

    const overlay = document.querySelector('.sidebar-overlay');



    if (sidebar) {

        sidebar.classList.toggle('open');

        sidebarToggle.classList.toggle('active');

        

        // Handle overlay for mobile

        if (window.innerWidth <= 768) {

            if (!overlay) {

                createOverlay();

            } else {

                overlay.classList.toggle('show');

            }

        }

    }

}



function toggleUserDropdown(event) {

    event.stopPropagation();

    const userDropdown = document.getElementById('userDropdown');

    

    if (userDropdown) {

        userDropdown.classList.toggle('show');

    }

}



function initializeSearch() {

    const searchInput = document.querySelector('.search-input');

    const searchBtn = document.querySelector('.search-btn');



    // Search on enter key

    if (searchInput) {

        searchInput.addEventListener('keypress', function(e) {

            if (e.key === 'Enter') {

                performSearch();

            }

        });



        // Search on input with debounce

        let searchTimeout;

        searchInput.addEventListener('input', function() {

            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(() => {

                if (this.value.trim()) {

                    performSearch();

                }

            }, 300);

        });

    }



    // Search on button click

    if (searchBtn) {

        searchBtn.addEventListener('click', performSearch);

    }

}



function performSearch() {

    const searchInput = document.querySelector('.search-input');

    const query = searchInput ? searchInput.value.trim() : '';



    if (query) {

        console.log('Searching for:', query);

        // Implement actual search functionality here

        // For now, just log the query

        showSearchResults(query);

    }

}



function showSearchResults(query) {

    // This function would typically display search results

    // For now, we'll just show a simple alert

    console.log('Search results for:', query);

    

    // You can implement a dropdown with search results here

    // or redirect to a search results page

}



function initializeNotifications() {

    const notificationBtn = document.querySelector('.notification-btn');

    const notificationBadge = document.querySelector('.notification-badge');



    if (notificationBtn) {

        notificationBtn.addEventListener('click', function() {

            toggleNotifications();

        });

    }



    // Simulate real-time notifications

    setInterval(() => {

        updateNotificationBadge();

    }, 30000); // Check every 30 seconds

}



function toggleNotifications() {

    // This function would show/hide notifications dropdown

    console.log('Toggle notifications panel');

    

    // Implement notification panel toggle here

    // For now, just log the action

}



function updateNotificationBadge() {

    const notificationBadge = document.querySelector('.notification-badge');

    

    // Simulate getting notification count

    const count = Math.floor(Math.random() * 10);

    

    if (notificationBadge) {

        if (count > 0) {

            notificationBadge.textContent = count;

            notificationBadge.style.display = 'block';

        } else {

            notificationBadge.style.display = 'none';

        }

    }

}



function handleOutsideClick(event) {

    const userMenuToggle = document.getElementById('userMenuToggle');

    const userDropdown = document.getElementById('userDropdown');

    

    // Close user dropdown if clicking outside

    if (userDropdown && userMenuToggle) {

        if (!userMenuToggle.contains(event.target) && !userDropdown.contains(event.target)) {

            userDropdown.classList.remove('show');

        }

    }

}



function createOverlay() {

    const overlay = document.createElement('div');

    overlay.className = 'sidebar-overlay';

    overlay.addEventListener('click', function() {

        const sidebar = document.getElementById('mainSidebar');

        const sidebarToggle = document.getElementById('sidebarToggle');

        

        if (sidebar) {

            sidebar.classList.remove('open');

            sidebarToggle.classList.remove('active');

            overlay.classList.remove('show');

        }

    });

    

    document.body.appendChild(overlay);

}



// Logout functionality

function initializeLogout() {

    const logoutTrigger = document.getElementById('logoutTrigger');

    const logoutModal = document.getElementById('logoutModal');

    const cancelLogout = document.getElementById('cancelLogout');

    const confirmLogout = document.getElementById('confirmLogout');

    const logoutForm = document.getElementById('logoutForm');



    // Open logout modal

    if (logoutTrigger) {

        logoutTrigger.addEventListener('click', function (e) {

            e.preventDefault();

            if (logoutModal) {

                logoutModal.style.display = 'flex';

            }

        });

    }



    // Cancel logout

    if (cancelLogout) {

        cancelLogout.addEventListener('click', function () {

            if (logoutModal) {

                logoutModal.style.display = 'none';

            }

        });

    }



    // Confirm logout

    if (confirmLogout) {

        confirmLogout.addEventListener('click', function () {

            // Use the global logout function from login.js

            if (typeof window.logout === 'function') {

                window.logout();

            } else {

                // Fallback: clear session and redirect

                sessionStorage.removeItem('isLoggedIn');

                sessionStorage.removeItem('userEmail');

                window.location.href = '/login';

            }

        });

    }



    // Close modal when clicking overlay

    if (logoutModal) {

        logoutModal.addEventListener('click', function (e) {

            if (e.target === logoutModal) {

                logoutModal.style.display = 'none';

            }

        });

    }



    // Close modal with Escape key

    document.addEventListener('keydown', function (e) {

        if (e.key === 'Escape' && logoutModal) {

            if (logoutModal.style.display === 'flex') {

                logoutModal.style.display = 'none';

            }

        }

    });

}



function handleLogout() {

    const logoutForm = document.getElementById('logoutForm');

    if (logoutForm) {

        logoutForm.submit();

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

            sidebarToggle.classList.remove('active');

        }

    }

}



// Keyboard shortcuts

document.addEventListener('keydown', function(e) {

    // Ctrl/Cmd + K for search focus

    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {

        e.preventDefault();

        const searchInput = document.querySelector('.search-input');

        if (searchInput) {

            searchInput.focus();

        }

    }

    

    // Escape to close dropdowns

    if (e.key === 'Escape') {

        const userDropdown = document.getElementById('userDropdown');

        if (userDropdown) {

            userDropdown.classList.remove('show');

        }

    }

});



// Export functions for external use if needed

window.HeaderFunctions = {

    toggleSidebar: toggleSidebar,

    toggleUserDropdown: toggleUserDropdown,

    performSearch: performSearch,

    updateNotificationBadge: updateNotificationBadge,

    initializeLogout: initializeLogout,

    handleLogout: handleLogout

};