/**
 * SK Officials — Notifications Page JS
 * Handles: filter tabs, mark as read, mark all as read, stats update
 */

document.addEventListener('DOMContentLoaded', function () {
    initNotificationPage();
});

function initNotificationPage() {
    const list        = document.getElementById('notifPageList');
    const markAllBtn  = document.getElementById('pageMarkAllBtn');
    const emptyState  = document.getElementById('notifPageEmpty');
    const filterBtns  = document.querySelectorAll('.notif-filter-btn');
    const totalEl     = document.getElementById('totalCount');
    const unreadEl    = document.getElementById('unreadCount');
    const readEl      = document.getElementById('readCount');

    let currentFilter = 'all';

    /* ── Update stats ────────────────────────────────────── */
    function updateStats() {
        if (!list) return;
        const all    = list.querySelectorAll('.notif-page-item');
        const unread = list.querySelectorAll('.notif-page-unread');
        const read   = all.length - unread.length;

        if (totalEl)  totalEl.textContent  = all.length;
        if (unreadEl) unreadEl.textContent = unread.length;
        if (readEl)   readEl.textContent   = read;

        /* Also sync header badge */
        const headerBadge    = document.getElementById('notifBadge');
        const headerCountPill = document.getElementById('notifCountPill');
        if (headerBadge) {
            headerBadge.textContent = unread.length;
            headerBadge.style.display = unread.length > 0 ? 'flex' : 'none';
        }
        if (headerCountPill) {
            headerCountPill.textContent = unread.length;
            headerCountPill.style.display = unread.length > 0 ? 'inline' : 'none';
        }
    }

    /* ── Apply filter ────────────────────────────────────── */
    function applyFilter(filter) {
        if (!list) return;
        const items = list.querySelectorAll('.notif-page-item');
        let visible = 0;

        items.forEach(function (item) {
            const isUnread = item.classList.contains('notif-page-unread');
            let show = false;

            if (filter === 'all')    show = true;
            if (filter === 'unread') show = isUnread;
            if (filter === 'read')   show = !isUnread;

            item.style.display = show ? 'flex' : 'none';
            if (show) visible++;
        });

        if (emptyState) {
            emptyState.style.display = visible === 0 ? 'flex' : 'none';
        }
    }

    /* ── Mark single item as read ────────────────────────── */
    function markAsRead(item) {
        if (!item.classList.contains('notif-page-unread')) return;
        item.classList.remove('notif-page-unread');

        /* Remove dot */
        const dot = item.querySelector('.notif-page-dot');
        if (dot) dot.remove();

        /* Update read button */
        const btn = item.querySelector('.notif-page-read-btn');
        if (btn) {
            btn.classList.add('notif-read-done');
            btn.disabled = true;
            btn.title = 'Already read';
        }

        updateStats();
        applyFilter(currentFilter);
    }

    /* ── Filter tab clicks ───────────────────────────────── */
    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            filterBtns.forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            currentFilter = btn.getAttribute('data-filter');
            applyFilter(currentFilter);
        });
    });

    /* ── Mark all as read ────────────────────────────────── */
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function () {
            if (!list) return;
            const unreadItems = list.querySelectorAll('.notif-page-unread');
            unreadItems.forEach(function (item) { markAsRead(item); });
        });
    }

    /* ── Individual read button clicks ──────────────────── */
    if (list) {
        list.addEventListener('click', function (e) {
            const readBtn = e.target.closest('.notif-page-read-btn');
            if (readBtn && !readBtn.disabled) {
                const item = readBtn.closest('.notif-page-item');
                if (item) markAsRead(item);
                e.stopPropagation();
                return;
            }

            /* Click anywhere on item also marks it read */
            const item = e.target.closest('.notif-page-item');
            if (item) markAsRead(item);
        });
    }

    /* ── Init ────────────────────────────────────────────── */
    updateStats();
    applyFilter('all');
}
