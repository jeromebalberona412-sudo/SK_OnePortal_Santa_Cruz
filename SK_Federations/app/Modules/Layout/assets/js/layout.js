// ── Sidebar Toggle ──
function collapseSidebarExtras() {
    const submenu = document.getElementById('archiveSubmenu');
    const chevron = document.getElementById('archiveChevron');
    if (submenu) {
        submenu.style.display = 'none';
    }
    if (chevron) {
        chevron.style.transform = 'rotate(0deg)';
    }
}

function toggleSidebar() {
    const isMobile = window.innerWidth <= 1024;
    if (isMobile) {
        document.body.classList.toggle('sidebar-open');
    } else {
        document.body.classList.toggle('sidebar-collapsed');
        const collapsed = document.body.classList.contains('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', collapsed);
        if (collapsed) {
            collapseSidebarExtras();
        }
    }
}

function toggleArchiveMenu(event) {
    event?.preventDefault();
    const submenu = document.getElementById('archiveSubmenu');
    const chevron = document.getElementById('archiveChevron');
    if (!submenu || !chevron) {
        return;
    }
    const isOpen = submenu.style.display === 'block';
    submenu.style.display = isOpen ? 'none' : 'block';
    chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
}

document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.querySelector('.sidebar-overlay');
    overlay?.addEventListener('click', () => document.body.classList.remove('sidebar-open'));

    if (window.innerWidth > 1024 && localStorage.getItem('sidebarCollapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
        collapseSidebarExtras();
    }
});

// ── Notification Popover ──
function closeNotifPopover() {
    const pop = document.getElementById('notifPopover');
    const btn = document.getElementById('notifBtn');
    pop?.classList.remove('show');
    btn?.setAttribute('aria-expanded', 'false');
}

function updateNotifBadge() {
    const notifList = document.getElementById('notifList');
    const notifBadge = document.getElementById('notifBadge');
    const notifCountPill = document.getElementById('notifCountPill');
    const notifEmpty = document.getElementById('notifEmpty');
    const unread = notifList ? notifList.querySelectorAll('.notif-unread').length : 0;

    if (notifBadge) {
        notifBadge.textContent = unread > 0 ? String(unread) : '';
        notifBadge.style.display = unread > 0 ? 'inline-flex' : 'none';
    }
    if (notifCountPill) {
        notifCountPill.textContent = unread > 0 ? String(unread) : '';
        notifCountPill.style.display = unread > 0 ? 'inline' : 'none';
    }
    if (notifEmpty && notifList) {
        const hasItems = notifList.querySelectorAll('.notif-item').length > 0;
        notifEmpty.style.display = hasItems ? 'none' : 'flex';
        notifList.style.display = hasItems ? '' : 'none';
    }
}

function toggleNotifPopover(e) {
    e.stopPropagation();
    const pop = document.getElementById('notifPopover');
    const btn = document.getElementById('notifBtn');
    const profileDd = document.getElementById('profileDropdown');
    profileDd?.classList.remove('show');
    document.querySelector('.profile-btn')?.classList.remove('open');

    const isOpen = pop?.classList.contains('show');
    if (isOpen) {
        closeNotifPopover();
    } else {
        pop?.classList.add('show');
        btn?.setAttribute('aria-expanded', 'true');
    }
}

// ── Profile Dropdown ──
function toggleProfileDropdown(e) {
    e.stopPropagation();
    const dd = document.getElementById('profileDropdown');
    const btn = document.querySelector('.profile-btn');
    closeNotifPopover();
    dd?.classList.toggle('show');
    btn?.classList.toggle('open');
}

document.addEventListener('click', function (e) {
    const notifMenu = document.getElementById('notifMenu');
    const profileWrapper = document.querySelector('.profile-dropdown-wrapper');

    if (notifMenu && !notifMenu.contains(e.target)) {
        closeNotifPopover();
    }
    if (profileWrapper && !profileWrapper.contains(e.target)) {
        document.getElementById('profileDropdown')?.classList.remove('show');
        document.querySelector('.profile-btn')?.classList.remove('open');
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const notifPopover = document.getElementById('notifPopover');
    const notifList = document.getElementById('notifList');
    const markAllBtn = document.getElementById('notifMarkAllBtn');

    notifPopover?.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    notifList?.addEventListener('click', function (e) {
        const item = e.target.closest('.notif-item');
        if (!item || !item.classList.contains('notif-unread')) {
            return;
        }
        item.classList.remove('notif-unread');
        item.querySelector('.notif-unread-dot')?.remove();
        updateNotifBadge();
    });

    markAllBtn?.addEventListener('click', function (e) {
        e.stopPropagation();
        notifList?.querySelectorAll('.notif-unread').forEach(function (item) {
            item.classList.remove('notif-unread');
            item.querySelector('.notif-unread-dot')?.remove();
        });
        updateNotifBadge();
    });

    updateNotifBadge();
});

// ── Logout Modal ──
function showLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (!modal) {
        return;
    }
    document.getElementById('profileDropdown')?.classList.remove('show');
    document.querySelector('.profile-btn')?.classList.remove('open');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (!modal) {
        return;
    }
    modal.classList.remove('show');
    document.body.style.overflow = '';
}

function confirmLogout() {
    if (typeof LoadingScreen !== 'undefined') {
        LoadingScreen.show('Logging Out', 'Please wait...');
    }

    const logoutUrl = window.logoutRoute || '/logout';
    const loginUrl = window.loginRoute || '/login';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch(logoutUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken || '',
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
    }).then(() => {
        window.location.replace(loginUrl);
    }).catch(() => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = logoutUrl;
        if (csrfToken) {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = '_token';
            inp.value = csrfToken;
            form.appendChild(inp);
        }
        document.body.appendChild(form);
        form.submit();
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const logoutModal = document.getElementById('logoutModal');
    logoutModal?.addEventListener('click', function (event) {
        if (event.target === logoutModal) {
            closeLogoutModal();
        }
    });
});
