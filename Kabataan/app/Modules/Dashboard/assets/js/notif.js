/* =============================================
   SK OnePortal — Notification Popover JS
   ============================================= */

// ── Align arrow to button on mobile/tablet ────────────────────────────────────
function npAlignArrow() {
    const btn = document.getElementById('notifNavBtn');
    const popover = document.getElementById('notifPopover');
    if (!btn || !popover) return;

    const btnRect = btn.getBoundingClientRect();
    const btnCenter = btnRect.left + btnRect.width / 2;

    // On mobile (fixed positioning): popover right edge = viewport width - 8px (right: 8px)
    // On tablet (absolute, right: 0): popover right edge aligns with wrapper right edge
    // On desktop (absolute positioning): use popover's actual rect
    let popRight;
    if (window.innerWidth <= 480) {
        popRight = window.innerWidth - 8;
    } else {
        // For tablet and desktop, getBoundingClientRect is accurate since popover is visible
        popRight = popover.getBoundingClientRect().right;
    }

    const arrowRight = Math.max(10, Math.round(popRight - btnCenter - 8));
    popover.style.setProperty('--np-arrow-right', arrowRight + 'px');
}

window.toggleNotifPopover = function () {
    const popover = document.getElementById('notifPopover');
    if (!popover) return;
    if (popover.classList.contains('open')) {
        closeNotifPopover();
    } else {
        // Close chatbot if open
        document.getElementById('chatbotPopover')?.classList.remove('open');
        popover.classList.add('open');
        npAlignArrow();
    }
};

window.closeNotifPopover = function () {
    document.getElementById('notifPopover')?.classList.remove('open');
};

// Close on outside click
document.addEventListener('click', function (e) {
    const popover = document.getElementById('notifPopover');
    const btn = document.getElementById('notifNavBtn');
    if (!popover || !btn) return;
    if (popover.classList.contains('open') && !popover.contains(e.target) && !btn.contains(e.target)) {
        closeNotifPopover();
    }
});
