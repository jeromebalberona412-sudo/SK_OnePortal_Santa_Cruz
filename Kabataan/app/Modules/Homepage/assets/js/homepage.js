document.addEventListener('DOMContentLoaded', () => {

    // ── Feed filter tabs ──────────────────────────────────────────────────────
    const filterTabs = document.querySelectorAll('.filter-tab');
    const posts      = document.querySelectorAll('.post-card');
    const emptyState = document.getElementById('emptyState');
    const searchInput = document.getElementById('searchInput');

    let activeFilter = 'all';
    let searchQuery  = '';

    const applyFilters = () => {
        let visible = 0;

        posts.forEach((card) => {
            const type     = (card.dataset.postType     || '').toLowerCase();
            const category = (card.dataset.postCategory || '').toLowerCase();
            const text     = card.innerText.toLowerCase();

            const typeMatch   = activeFilter === 'all' || type === activeFilter;
            const searchMatch = searchQuery === '' || text.includes(searchQuery);

            const show = typeMatch && searchMatch;
            card.hidden = !show;
            if (show) visible++;
        });

        if (emptyState) emptyState.hidden = visible > 0;
    };

    filterTabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            filterTabs.forEach((t) => t.classList.remove('active'));
            tab.classList.add('active');
            activeFilter = tab.dataset.filter;
            applyFilters();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            searchQuery = searchInput.value.trim().toLowerCase();
            applyFilters();
        });
    }

    // ── Helper: open / close modal ────────────────────────────────────────────
    const openModal  = (id) => document.getElementById(id)?.classList.add('active');
    const closeModal = (id) => document.getElementById(id)?.classList.remove('active');

    const bindClose = (modalId, closeBtnId, overlayId) => {
        document.getElementById(closeBtnId)?.addEventListener('click', () => closeModal(modalId));
        document.getElementById(overlayId)?.addEventListener('click', () => closeModal(modalId));
    };

    bindClose('loginRequiredModal', 'loginRequiredClose', 'loginRequiredOverlay');
    bindClose('actionLoginModal',   'actionLoginClose',   'actionLoginOverlay');
    bindClose('programApplyModal',  'programApplyClose',  'programApplyOverlay');

    // ── Program category sidebar ──────────────────────────────────────────────
    document.querySelectorAll('.program-category').forEach((cat) => {
        cat.addEventListener('click', () => {
            const label    = cat.querySelector('.category-content h3')?.textContent || 'this';
            const titleEl  = document.getElementById('loginRequiredTitle');
            const catEl    = document.getElementById('loginRequiredCategory');

            if (titleEl) titleEl.textContent = label + ' Programs';
            if (catEl)   catEl.textContent   = label;

            openModal('loginRequiredModal');
        });
    });

    // ── Program apply buttons ─────────────────────────────────────────────────
    document.querySelectorAll('.program-apply-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const name = btn.dataset.programName || 'this program';
            const nameEl = document.getElementById('programApplyName');
            if (nameEl) nameEl.textContent = name;
            openModal('programApplyModal');
        });
    });

    // ── Like / Comment buttons ────────────────────────────────────────────────
    document.querySelectorAll('.like-btn, .comment-btn').forEach((btn) => {
        btn.addEventListener('click', () => openModal('actionLoginModal'));
    });

    // ── ESC key closes any open modal ─────────────────────────────────────────
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            ['loginRequiredModal', 'actionLoginModal', 'programApplyModal', 'barangayProfileModal']
                .forEach((id) => closeModal(id));
        }
    });

    // ── Barangay profile — login required on homepage ────────────────────────
    document.querySelectorAll('.brgy-profile-item').forEach((item) => {
        item.addEventListener('click', () => {
            const name   = item.dataset.brgyName;
            const titleEl = document.getElementById('loginRequiredTitle');
            const catEl   = document.getElementById('loginRequiredCategory');
            if (titleEl) titleEl.textContent = 'Barangay ' + name;
            if (catEl)   catEl.textContent   = name;
            openModal('loginRequiredModal');
        });
    });

    // ── Navbar Login / Sign Up → open join community modal ───────────────────
    const openLoginModal = () => {
        const catEl = document.getElementById('loginRequiredCategory');
        if (catEl) catEl.textContent = 'the community';
        openModal('loginRequiredModal');
    };

    document.getElementById('navLoginBtn')?.addEventListener('click', openLoginModal);
    document.getElementById('navDrawerLoginBtn')?.addEventListener('click', () => {
        document.getElementById('navDrawer')?.classList.remove('open');
        openLoginModal();
    });

    applyFilters();
});
