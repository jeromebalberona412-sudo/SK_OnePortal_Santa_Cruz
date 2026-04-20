// Header JavaScript Functionality
// Single DOMContentLoaded — no duplicate listeners

document.addEventListener('DOMContentLoaded', function () {
    initializeHeader();
});

function initializeHeader() {
    const sidebarToggle    = document.getElementById('sidebarToggle');
    const userMenuToggle   = document.getElementById('userMenuToggle');
    const userDropdown     = document.getElementById('userDropdown');
    const searchInput      = document.querySelector('.search-input');
    const searchBtn        = document.querySelector('.search-btn');
    const notificationBtn  = document.querySelector('.notification-btn');

    // ── Sidebar toggle ──────────────────────────────────────────────────────
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', responsiveToggleSidebar);
    }
    syncToggleState();

    // ── Profile dropdown — click-only, no hover ─────────────────────────────
    if (userMenuToggle && userDropdown) {
        // Toggle open/close on button click
        userMenuToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = userDropdown.classList.contains('open');
            closeProfileDropdown(); // close first (handles any stale state)
            if (!isOpen) {
                userDropdown.classList.add('open');
                userMenuToggle.setAttribute('aria-expanded', 'true');
            }
        });
    }

    // ── Change Password trigger ─────────────────────────────────────────────
    const changePasswordTrigger = document.getElementById('changePasswordTrigger');
    if (changePasswordTrigger) {
        changePasswordTrigger.addEventListener('click', function (e) {
            e.preventDefault();
            closeProfileDropdown();
            // TODO: wire to change-password route or modal when ready
        });
    }

    // ── Logout ──────────────────────────────────────────────────────────────
    initializeLogout();

    // ── Search ──────────────────────────────────────────────────────────────
    if (searchInput && searchBtn) {
        initializeSearch();
    }

    // ── Notifications ───────────────────────────────────────────────────────
    if (notificationBtn) {
        initializeNotifications();
    }

    // ── Global outside-click — single listener on document ──────────────────
    document.addEventListener('click', function (e) {
        // Close profile dropdown when clicking outside
        if (userDropdown && userMenuToggle) {
            if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
                closeProfileDropdown();
            }
        }
    });

    // ── Escape key closes dropdown ──────────────────────────────────────────
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeProfileDropdown();
            const logoutModal = document.getElementById('logoutModal');
            if (logoutModal && logoutModal.style.display === 'flex') {
                logoutModal.style.display = 'none';
            }
        }
        // Ctrl/Cmd + K → focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const si = document.querySelector('.search-input');
            if (si) si.focus();
        }
    });
}

// ── Profile dropdown helpers ────────────────────────────────────────────────
function closeProfileDropdown() {
    const userDropdown   = document.getElementById('userDropdown');
    const userMenuToggle = document.getElementById('userMenuToggle');
    if (userDropdown)   userDropdown.classList.remove('open');
    if (userMenuToggle) userMenuToggle.setAttribute('aria-expanded', 'false');
}

// ── Sidebar toggle ──────────────────────────────────────────────────────────
function responsiveToggleSidebar() {
    const sidebar      = document.getElementById('mainSidebar');
    const toggle       = document.getElementById('sidebarToggle');
    let   overlay      = document.querySelector('.sidebar-overlay');

    if (!sidebar || !toggle) return;

    if (window.innerWidth <= 768) {
        // Mobile: slide in/out
        const isOpen = sidebar.classList.contains('open');
        if (isOpen) {
            sidebar.classList.remove('open');
            toggle.classList.remove('active');
            if (overlay) overlay.classList.remove('show');
        } else {
            sidebar.classList.add('open');
            toggle.classList.add('active');
            if (!overlay) overlay = createOverlay();
            overlay.classList.add('show');
        }
    } else {
        // Desktop: collapse / expand permanently
        const isCollapsed = sidebar.classList.contains('collapsed');
        const mainContent = document.querySelector('.main-content');

        if (isCollapsed) {
            sidebar.classList.remove('collapsed', 'hovering');
            if (mainContent) {
                mainContent.classList.remove('sidebar-collapsed', 'sidebar-hover');
            }
            toggle.classList.add('active');
        } else {
            sidebar.classList.add('collapsed');
            if (mainContent) {
                mainContent.classList.add('sidebar-collapsed');
                mainContent.classList.remove('sidebar-hover');
            }
            toggle.classList.remove('active');
        }
        if (overlay) overlay.classList.remove('show');
    }
}

function syncToggleState() {
    const sidebar = document.getElementById('mainSidebar');
    const toggle  = document.getElementById('sidebarToggle');
    if (!sidebar || !toggle || window.innerWidth <= 768) return;

    const isCollapsed = sidebar.classList.contains('collapsed');
    const isHovering  = sidebar.classList.contains('hovering');

    if (isCollapsed && !isHovering) {
        toggle.classList.remove('active');
    } else {
        toggle.classList.add('active');
    }
}

function createOverlay() {
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    overlay.addEventListener('click', function () {
        const sidebar = document.getElementById('mainSidebar');
        const toggle  = document.getElementById('sidebarToggle');
        if (sidebar) sidebar.classList.remove('open');
        if (toggle)  toggle.classList.remove('active');
        overlay.classList.remove('show');
    });
    document.body.appendChild(overlay);
    return overlay;
}

// ── Search ──────────────────────────────────────────────────────────────────
function initializeSearch() {
    const searchInput = document.querySelector('.search-input');
    const searchBtn   = document.querySelector('.search-btn');

    if (searchInput) {
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') performSearch();
        });
        let searchTimeout;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.trim()) performSearch();
            }, 300);
        });
    }
    if (searchBtn) {
        searchBtn.addEventListener('click', performSearch);
    }
}

function performSearch() {
    const searchInput = document.querySelector('.search-input');
    const query = searchInput ? searchInput.value.trim() : '';
    if (query) console.log('Searching for:', query);
}

// ── Notifications ────────────────────────────────────────────────────────────
function initializeNotifications() {
    const notificationBtn = document.querySelector('.notification-btn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function () {
            console.log('Toggle notifications panel');
        });
    }
    setInterval(updateNotificationBadge, 30000);
}

function updateNotificationBadge() {
    const badge = document.querySelector('.notification-badge');
    const count = Math.floor(Math.random() * 10);
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'block' : 'none';
    }
}

// ── Logout ───────────────────────────────────────────────────────────────────
function initializeLogout() {
    const logoutTrigger  = document.getElementById('logoutTrigger');
    const logoutModal    = document.getElementById('logoutModal');
    const cancelLogout   = document.getElementById('cancelLogout');
    const confirmLogout  = document.getElementById('confirmLogout');

    if (logoutTrigger) {
        logoutTrigger.addEventListener('click', function (e) {
            e.preventDefault();
            closeProfileDropdown();
            if (logoutModal) logoutModal.style.display = 'flex';
        });
    }

    if (cancelLogout) {
        cancelLogout.addEventListener('click', function () {
            if (logoutModal) logoutModal.style.display = 'none';
        });
    }

    if (confirmLogout) {
        confirmLogout.addEventListener('click', function () {
            if (logoutModal) logoutModal.style.display = 'none';
            handleLogout();
        });
    }

    if (logoutModal) {
        logoutModal.addEventListener('click', function (e) {
            if (e.target === logoutModal) logoutModal.style.display = 'none';
        });
    }
}

function handleLogout() {
    const logoutForm = document.getElementById('logoutForm');
    sessionStorage.removeItem('isLoggedIn');
    sessionStorage.removeItem('userEmail');
    if (logoutForm) logoutForm.submit();
}

// ── Resize ───────────────────────────────────────────────────────────────────
window.addEventListener('resize', function () {
    const sidebar  = document.getElementById('mainSidebar');
    const overlay  = document.querySelector('.sidebar-overlay');
    if (window.innerWidth > 768) {
        if (overlay)  overlay.classList.remove('show');
        if (sidebar)  sidebar.classList.remove('open');
        syncToggleState();
    }
});

// ── Exports ──────────────────────────────────────────────────────────────────
window.HeaderFunctions = {
    toggleSidebar:          responsiveToggleSidebar,
    closeProfileDropdown:   closeProfileDropdown,
    performSearch:          performSearch,
    updateNotificationBadge: updateNotificationBadge,
    initializeLogout:       initializeLogout,
    handleLogout:           handleLogout,
};
