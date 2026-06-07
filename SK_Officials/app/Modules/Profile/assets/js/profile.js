/**
 * SK Officials — Profile Page JS
 * Handles: tab switching
 */

document.addEventListener('DOMContentLoaded', function () {
    initTabs();
});

/* ── Tab switching (URL stays /profile) ─────────────────── */
function initTabs() {
    const tabs = [
        { btn: document.getElementById('tabBtnInfo'), panel: document.getElementById('tabInfo') },
        { btn: document.getElementById('tabBtnSettings'), panel: document.getElementById('tabSettings') },
    ];

    tabs.forEach(function (t, idx) {
        if (!t.btn) return;
        t.btn.addEventListener('click', function () {
            activateTab(idx);
        });
    });

    function activateTab(activeIdx) {
        tabs.forEach(function (t, i) {
            const isActive = i === activeIdx;
            if (t.btn) {
                t.btn.classList.toggle('active', isActive);
                t.btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
            }
            if (t.panel) {
                t.panel.classList.toggle('active', isActive);
            }
        });
    }
}
