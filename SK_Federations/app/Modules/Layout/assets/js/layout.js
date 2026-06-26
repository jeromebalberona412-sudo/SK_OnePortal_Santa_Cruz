// ── Sidebar dropdown submenus ───────────────────────────────
function isSidebarCollapsed() {
    return document.body.classList.contains('sidebar-collapsed');
}

function isMobileSidebar() {
    return window.innerWidth <= 1024;
}

function getSubmenuToggleButton(submenuId) {
    return document.querySelector('[data-submenu-toggle="' + submenuId + '"]');
}

function setSubmenuOpen(submenuId, open) {
    var submenu = document.getElementById(submenuId);
    var toggle = getSubmenuToggleButton(submenuId);
    var chevron = toggle ? toggle.querySelector('.menu-dropdown-chevron') : null;

    if (!submenu) {
        return;
    }

    submenu.classList.toggle('is-open', open);
    if (toggle) {
        toggle.classList.toggle('active', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    if (chevron) {
        chevron.classList.toggle('is-open', open);
    }
}

function isSubmenuOpen(submenuId) {
    var submenu = document.getElementById(submenuId);
    return submenu ? submenu.classList.contains('is-open') : false;
}

function collapseSidebarExtras() {
    document.querySelectorAll('.sidebar-submenu.is-open').forEach(function (submenu) {
        setSubmenuOpen(submenu.id, false);
    });
}

function toggleSubmenu(submenuId) {
    if (!isMobileSidebar() && isSidebarCollapsed()) {
        document.body.classList.remove('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', 'false');

        window.setTimeout(function () {
            setSubmenuOpen(submenuId, true);
        }, 260);

        return;
    }

    var willOpen = !isSubmenuOpen(submenuId);

    document.querySelectorAll('.sidebar-submenu.is-open').forEach(function (submenu) {
        if (submenu.id !== submenuId) {
            setSubmenuOpen(submenu.id, false);
        }
    });

    setSubmenuOpen(submenuId, willOpen);
}

window.toggleSubmenuDropdown = function (btn, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    var submenuId = btn.getAttribute('data-submenu-toggle');
    if (submenuId) {
        toggleSubmenu(submenuId);
    }
};

window.toggleAccountsMenu = function (event) {
    if (event) {
        event.preventDefault();
    }
    toggleSubmenu('accountsSubmenu');
};

window.toggleArchiveManagementMenu = function (event) {
    if (event) {
        event.preventDefault();
    }
    toggleSubmenu('archiveManagementSubmenu');
};

window.toggleArchiveMenu = function (event) {
    if (event) {
        event.preventDefault();
    }
    toggleSubmenu('archiveSubmenu');
};

function initSidebarNavigation() {
    var overlay = document.querySelector('.sidebar-overlay');
    if (overlay && !overlay.dataset.bound) {
        overlay.dataset.bound = '1';
        overlay.addEventListener('click', function () {
            document.body.classList.remove('sidebar-open');
        });
    }

    if (window.innerWidth > 1024 && localStorage.getItem('sidebarCollapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
        collapseSidebarExtras();
    }
}

window.toggleSidebar = function () {
    var isMobile = isMobileSidebar();
    if (isMobile) {
        document.body.classList.toggle('sidebar-open');
    } else {
        document.body.classList.toggle('sidebar-collapsed');
        var collapsed = document.body.classList.contains('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', collapsed ? 'true' : 'false');
        if (collapsed) {
            collapseSidebarExtras();
        }
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSidebarNavigation);
} else {
    initSidebarNavigation();
}

// ── Notification Popover ──
function closeNotifPopover() {
    var pop = document.getElementById('notifPopover');
    var btn = document.getElementById('notifBtn');
    if (pop) {
        pop.classList.remove('show');
    }
    if (btn) {
        btn.setAttribute('aria-expanded', 'false');
    }
}

function updateNotifBadge() {
    var notifList = document.getElementById('notifList');
    var notifBadge = document.getElementById('notifBadge');
    var notifCountPill = document.getElementById('notifCountPill');
    var notifEmpty = document.getElementById('notifEmpty');
    var unread = notifList ? notifList.querySelectorAll('.notif-unread').length : 0;

    if (notifBadge) {
        notifBadge.textContent = unread > 0 ? String(unread) : '';
        notifBadge.style.display = unread > 0 ? 'inline-flex' : 'none';
    }
    if (notifCountPill) {
        notifCountPill.textContent = unread > 0 ? String(unread) : '';
        notifCountPill.style.display = unread > 0 ? 'inline' : 'none';
    }
    if (notifEmpty && notifList) {
        var hasItems = notifList.querySelectorAll('.notif-item').length > 0;
        notifEmpty.style.display = hasItems ? 'none' : 'flex';
        notifList.style.display = hasItems ? '' : 'none';
    }
}

window.toggleNotifPopover = function (e) {
    e.stopPropagation();
    var pop = document.getElementById('notifPopover');
    var btn = document.getElementById('notifBtn');
    var profileDd = document.getElementById('profileDropdown');
    if (profileDd) {
        profileDd.classList.remove('show');
    }
    var profileBtn = document.querySelector('.profile-btn');
    if (profileBtn) {
        profileBtn.classList.remove('open');
    }

    var isOpen = pop && pop.classList.contains('show');
    if (isOpen) {
        closeNotifPopover();
    } else if (pop) {
        pop.classList.add('show');
        if (btn) {
            btn.setAttribute('aria-expanded', 'true');
        }
    }
};

// ── Profile Dropdown ──
window.toggleProfileDropdown = function (e) {
    e.stopPropagation();
    var dd = document.getElementById('profileDropdown');
    var btn = document.querySelector('.profile-btn');
    closeNotifPopover();
    if (dd) {
        dd.classList.toggle('show');
    }
    if (btn) {
        btn.classList.toggle('open');
    }
};

document.addEventListener('click', function (e) {
    var notifMenu = document.getElementById('notifMenu');
    var profileWrapper = document.querySelector('.profile-dropdown-wrapper');

    if (notifMenu && !notifMenu.contains(e.target)) {
        closeNotifPopover();
    }
    if (profileWrapper && !profileWrapper.contains(e.target)) {
        var profileDropdown = document.getElementById('profileDropdown');
        if (profileDropdown) {
            profileDropdown.classList.remove('show');
        }
        var profileBtn = document.querySelector('.profile-btn');
        if (profileBtn) {
            profileBtn.classList.remove('open');
        }
    }
});

document.addEventListener('DOMContentLoaded', function () {
    var notifPopover = document.getElementById('notifPopover');
    var notifList = document.getElementById('notifList');
    var markAllBtn = document.getElementById('notifMarkAllBtn');

    if (notifPopover) {
        notifPopover.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    function getNotifCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    async function markNotifItemRead(item) {
        if (!item) return;
        var id = item.getAttribute('data-id');
        var actionUrl = item.getAttribute('data-action-url') || '';

        if (item.classList.contains('notif-unread') && id) {
            try {
                await fetch('/api/sk-federations/notifications/' + id + '/read', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getNotifCsrfToken(),
                    },
                    credentials: 'same-origin',
                });
            } catch (err) {
                // Continue with local UI update.
            }

            item.classList.remove('notif-unread');
            var dot = item.querySelector('.notif-unread-dot');
            if (dot) {
                dot.remove();
            }
            updateNotifBadge();
        }

        if (actionUrl) {
            window.location.href = actionUrl;
        }
    }

    if (notifList) {
        notifList.addEventListener('click', function (e) {
            var item = e.target.closest('.notif-item');
            if (!item) return;
            markNotifItemRead(item);
        });

        notifList.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            var item = e.target.closest('.notif-item');
            if (!item) return;
            e.preventDefault();
            markNotifItemRead(item);
        });
    }

    if (markAllBtn) {
        markAllBtn.addEventListener('click', async function (e) {
            e.stopPropagation();
            try {
                await fetch('/api/sk-federations/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getNotifCsrfToken(),
                    },
                    credentials: 'same-origin',
                });
            } catch (err) {
                // Continue with local UI update.
            }
            if (notifList) {
                notifList.querySelectorAll('.notif-unread').forEach(function (item) {
                    item.classList.remove('notif-unread');
                    var dot = item.querySelector('.notif-unread-dot');
                    if (dot) {
                        dot.remove();
                    }
                });
            }
            updateNotifBadge();
        });
    }

    updateNotifBadge();
});

// ── Logout Modal ──
window.showLogoutModal = function () {
    var modal = document.getElementById('logoutModal');
    if (!modal) {
        return;
    }
    var profileDropdown = document.getElementById('profileDropdown');
    if (profileDropdown) {
        profileDropdown.classList.remove('show');
    }
    var profileBtn = document.querySelector('.profile-btn');
    if (profileBtn) {
        profileBtn.classList.remove('open');
    }
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
};

window.closeLogoutModal = function () {
    var modal = document.getElementById('logoutModal');
    if (!modal) {
        return;
    }
    modal.classList.remove('show');
    document.body.style.overflow = '';
};

window.confirmLogout = function () {
    if (typeof LoadingScreen !== 'undefined') {
        LoadingScreen.show('Logging Out', 'Please wait...');
    }

    var logoutUrl = window.logoutRoute || '/logout';
    var loginUrl = window.loginRoute || '/login';
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    fetch(logoutUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken || '',
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
    }).then(function () {
        window.location.replace(loginUrl);
    }).catch(function () {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = logoutUrl;
        if (csrfToken) {
            var inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = '_token';
            inp.value = csrfToken;
            form.appendChild(inp);
        }
        document.body.appendChild(form);
        form.submit();
    });
};

document.addEventListener('DOMContentLoaded', function () {
    var logoutModal = document.getElementById('logoutModal');
    if (logoutModal) {
        logoutModal.addEventListener('click', function (event) {
            if (event.target === logoutModal) {
                window.closeLogoutModal();
            }
        });
    }
});
