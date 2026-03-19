// ── Sidebar Toggle ──
function toggleSidebar() {
    const isMobile = window.innerWidth <= 1024;
    if (isMobile) {
        document.body.classList.toggle('sidebar-open');
    } else {
        document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed'));
    }
}

// Close sidebar on overlay click (mobile)
document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.querySelector('.sidebar-overlay');
    if (overlay) {
        overlay.addEventListener('click', () => document.body.classList.remove('sidebar-open'));
    }

    // Restore sidebar state on desktop
    if (window.innerWidth > 1024 && localStorage.getItem('sidebarCollapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }
});

// ── Notification Popover ──
function toggleNotifPopover(e) {
    e.stopPropagation();
    const pop = document.getElementById('notifPopover');
    const profileDd = document.getElementById('profileDropdown');
    profileDd?.classList.remove('show');
    document.querySelector('.profile-btn')?.classList.remove('open');
    pop?.classList.toggle('show');
}

// ── Profile Dropdown ──
function toggleProfileDropdown(e) {
    e.stopPropagation();
    const dd = document.getElementById('profileDropdown');
    const notifPop = document.getElementById('notifPopover');
    const btn = document.querySelector('.profile-btn');
    notifPop?.classList.remove('show');
    dd?.classList.toggle('show');
    btn?.classList.toggle('open');
}

// Close popovers on outside click
document.addEventListener('click', function () {
    document.getElementById('notifPopover')?.classList.remove('show');
    document.getElementById('profileDropdown')?.classList.remove('show');
    document.querySelector('.profile-btn')?.classList.remove('open');
});

// ── Stat Card Scroll ──
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.stat-card-link').forEach(function (card) {
        card.addEventListener('click', function (e) {
            e.preventDefault();
            const sectionId = this.getAttribute('data-section');
            const target = document.getElementById(sectionId);
            if (target) {
                const offset = 80;
                const top = target.getBoundingClientRect().top + window.scrollY - offset;
                window.scrollTo({ top: top, behavior: 'smooth' });
            }
        });
    });
});

// ── Logout Modal ──
function showLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) modal.classList.add('show');
}

function closeLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) modal.classList.remove('show');
}

function confirmLogout() {
    if (typeof LoadingScreen !== 'undefined') {
        LoadingScreen.show('Logging Out', 'Please wait...');
    }

    const logoutUrl  = window.logoutRoute  || '/logout';
    const loginUrl   = window.loginRoute   || '/login';
    const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

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
            inp.type = 'hidden'; inp.name = '_token'; inp.value = csrfToken;
            form.appendChild(inp);
        }
        document.body.appendChild(form);
        form.submit();
    });
}
