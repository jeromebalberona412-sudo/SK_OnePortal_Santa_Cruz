document.addEventListener('DOMContentLoaded', function () {
    initTabs();
});

function initTabs() {
    const tabs = [
        { btn: document.getElementById('tabBtnInfo'), panel: document.getElementById('tabInfo') },
        { btn: document.getElementById('tabBtnSettings'), panel: document.getElementById('tabSettings') },
    ];

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

    tabs.forEach(function (t, idx) {
        if (!t.btn) {
            return;
        }
        t.btn.addEventListener('click', function () {
            activateTab(idx);
        });
    });

    if (window.location.hash === '#settings') {
        activateTab(1);
    }
}
