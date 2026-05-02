document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.style.scrollBehavior = 'smooth';

    const navToggle = document.getElementById('kabataanNavToggle') || document.getElementById('navHamburger');
    const drawer = document.getElementById('kabataanDrawer') || document.getElementById('navDrawer');
    const navLinks = Array.from(document.querySelectorAll('.kabataan-nav-link, .kabataan-drawer-link'));
    const tabs = Array.from(document.querySelectorAll('.kabataan-tab'));
    const cards = Array.from(document.querySelectorAll('.kabataan-barangay-card'));
    const searchInput = document.getElementById('barangaySearch');
    const resultLabel = document.getElementById('barangayResultLabel');

    const state = {
        filter: 'all',
        query: '',
    };

    const setDrawerOpen = (open) => {
        if (!navToggle || !drawer) {
            return;
        }

        drawer.classList.toggle('open', open);
        drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
        navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    const setActiveLink = (sectionId) => {
        navLinks.forEach((link) => {
            const href = link.getAttribute('href') || '';
            if (!href.startsWith('#')) {
                return;
            }

            const targetId = href.slice(1);
            link.classList.toggle('active', targetId === sectionId);
        });
    };

    const applyFilters = () => {
        let visible = 0;

        cards.forEach((card) => {
            const barangay = (card.dataset.barangay || '').toLowerCase();
            const haystack = card.innerText.toLowerCase();
            const matchesFilter = state.filter === 'all' || barangay === state.filter;
            const matchesSearch = state.query === '' || haystack.includes(state.query);
            const show = matchesFilter && matchesSearch;

            card.hidden = !show;
            if (show) {
                visible += 1;
            }
        });

        if (resultLabel) {
            resultLabel.textContent = `${visible} highlight${visible === 1 ? '' : 's'} showing`;
        }
    };

    navToggle?.addEventListener('click', () => {
        const isOpen = drawer?.classList.contains('open');
        setDrawerOpen(!isOpen);
    });

    drawer?.addEventListener('click', (event) => {
        const target = event.target;
        if (target instanceof HTMLElement && target.closest('a')) {
            setDrawerOpen(false);
        }
    });

    document.addEventListener('click', (event) => {
        if (!drawer || !navToggle || !drawer.classList.contains('open')) {
            return;
        }

        const target = event.target;
        if (target instanceof HTMLElement && !drawer.contains(target) && !navToggle.contains(target)) {
            setDrawerOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setDrawerOpen(false);
        }
    });

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            tabs.forEach((item) => item.classList.remove('active'));
            tab.classList.add('active');
            state.filter = tab.dataset.filter || 'all';
            applyFilters();
        });
    });

    searchInput?.addEventListener('input', () => {
        state.query = searchInput.value.trim().toLowerCase();
        applyFilters();
    });

    const sections = ['about', 'programs', 'announcements', 'faq', 'contact']
        .map((id) => document.getElementById(id))
        .filter(Boolean);

    if ('IntersectionObserver' in window && sections.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            const visibleEntry = entries
                .filter((entry) => entry.isIntersecting)
                .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

            if (visibleEntry?.target.id) {
                setActiveLink(visibleEntry.target.id);
            }
        }, {
            root: null,
            threshold: [0.2, 0.35, 0.5, 0.65],
            rootMargin: '-18% 0px -58% 0px',
        });

        sections.forEach((section) => observer.observe(section));
    }

    navLinks.forEach((link) => {
        link.addEventListener('click', () => {
            setDrawerOpen(false);
        });
    });

    applyFilters();
});
