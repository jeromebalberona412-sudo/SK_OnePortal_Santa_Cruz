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
            closeProfileDropdown();
            // Navigate to change-password page (let the href handle it)
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
            closeNotifDropdown();
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
            sidebar.classList.remove('collapsed');
            if (mainContent) {
                mainContent.classList.remove('sidebar-collapsed');
            }
            toggle.classList.add('active');
        } else {
            sidebar.classList.add('collapsed');
            if (mainContent) {
                mainContent.classList.add('sidebar-collapsed');
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

    if (sidebar.classList.contains('collapsed')) {
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
    const notifBtn      = document.getElementById('notificationBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    const notifMenu     = document.getElementById('notifMenu');
    const markAllBtn    = document.getElementById('notifMarkAllBtn');
    const notifList     = document.getElementById('notifList');
    const notifBadge    = document.getElementById('notifBadge');
    const notifCountPill = document.getElementById('notifCountPill');
    const notifEmpty    = document.getElementById('notifEmpty');

    if (!notifBtn || !notifDropdown) return;

    /* ── Toggle open/close ── */
    notifBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = notifDropdown.classList.contains('open');

        // Close profile dropdown if open
        closeProfileDropdown();

        if (isOpen) {
            closeNotifDropdown();
        } else {
            notifDropdown.classList.add('open');
            notifBtn.setAttribute('aria-expanded', 'true');
        }
    });

    /* ── Close on outside click ── */
    document.addEventListener('click', function (e) {
        if (notifMenu && !notifMenu.contains(e.target)) {
            closeNotifDropdown();
        }
    });

    /* ── Escape closes it ── */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeNotifDropdown();
    });

    /* ── Mark individual item as read on click ── */
    if (notifList) {
        notifList.addEventListener('click', function (e) {
            const item = e.target.closest('.notif-item');
            if (!item) return;
            if (item.classList.contains('notif-unread')) {
                item.classList.remove('notif-unread');
                const dot = item.querySelector('.notif-unread-dot');
                if (dot) dot.remove();
                updateUnreadCount();
            }
        });
    }

    /* ── Mark all as read ── */
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const unreadItems = notifList ? notifList.querySelectorAll('.notif-unread') : [];
            unreadItems.forEach(function (item) {
                item.classList.remove('notif-unread');
                const dot = item.querySelector('.notif-unread-dot');
                if (dot) dot.remove();
            });
            updateUnreadCount();
        });
    }

    /* ── Count helpers ── */
    function updateUnreadCount() {
        const unread = notifList ? notifList.querySelectorAll('.notif-unread').length : 0;

        if (notifBadge) {
            notifBadge.textContent = unread;
            notifBadge.style.display = unread > 0 ? 'flex' : 'none';
        }
        if (notifCountPill) {
            notifCountPill.textContent = unread;
            notifCountPill.style.display = unread > 0 ? 'inline' : 'none';
        }
        if (notifEmpty && notifList) {
            const hasItems = notifList.querySelectorAll('.notif-item').length > 0;
            notifEmpty.style.display = hasItems ? 'none' : 'flex';
        }
    }
}

function closeNotifDropdown() {
    const notifDropdown = document.getElementById('notifDropdown');
    const notifBtn      = document.getElementById('notificationBtn');
    if (notifDropdown) notifDropdown.classList.remove('open');
    if (notifBtn)      notifBtn.setAttribute('aria-expanded', 'false');
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
