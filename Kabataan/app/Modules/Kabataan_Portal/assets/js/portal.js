// SK OnePortal - Kabataan Portal JS

/* ── Sticky header shadow ── */
const header = document.getElementById('portalHeader');
if (header) {
    window.addEventListener('scroll', () => {
        header.classList.toggle('scrolled', window.scrollY > 10);
    }, { passive: true });
}

/* ── Mobile hamburger ── */
const hamburger = document.getElementById('hamburger');
const headerNav = document.getElementById('headerNav');
if (hamburger && headerNav) {
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('open');
        headerNav.classList.toggle('open');
    });
    // Close on nav link click
    headerNav.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
            hamburger.classList.remove('open');
            headerNav.classList.remove('open');
        });
    });
}

/* ── Active nav link on scroll ── */
const sections = document.querySelectorAll('section[id], header[id]');
const navLinks  = document.querySelectorAll('.nav-link');

const observerNav = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            navLinks.forEach(l => l.classList.remove('active'));
            const active = document.querySelector(`.nav-link[href="#${entry.target.id}"]`);
            if (active) active.classList.add('active');
        }
    });
}, { threshold: 0.4 });

sections.forEach(s => observerNav.observe(s));

/* ── Scroll fade-in animations ── */

// Card-level stagger: each .fade-in-up inside a grid/list
const fadeEls = document.querySelectorAll('.fade-in-up');
const observerFade = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (!entry.isIntersecting) return;

        // Find siblings in same parent to stagger them
        const parent = entry.target.parentElement;
        const siblings = parent
            ? [...parent.querySelectorAll('.fade-in-up:not(.visible)')]
            : [entry.target];

        siblings.forEach((el, idx) => {
            setTimeout(() => el.classList.add('visible'), idx * 90);
        });

        // Unobserve all siblings once triggered
        siblings.forEach(el => observerFade.unobserve(el));
    });
}, { threshold: 0.10, rootMargin: '0px 0px -40px 0px' });

fadeEls.forEach(el => observerFade.observe(el));

// Section-level reveal
const sectionEls = document.querySelectorAll('.section-reveal');
const observerSection = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observerSection.unobserve(entry.target);
        }
    });
}, { threshold: 0.08, rootMargin: '0px 0px -60px 0px' });

sectionEls.forEach(el => observerSection.observe(el));

/* ── Barangay search ── */
const brgySearch = document.getElementById('brgySearch');
const brgyGrid   = document.getElementById('brgyGrid');
const noResults  = document.getElementById('brgyNoResults');

if (brgySearch && brgyGrid) {
    brgySearch.addEventListener('input', () => {
        const q = brgySearch.value.trim().toLowerCase();
        let visible = 0;
        brgyGrid.querySelectorAll('.brgy-card').forEach(card => {
            const name = card.dataset.brgy.toLowerCase();
            const show = name.includes(q);
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
    });
}

/* ── Barangay selection ── */
window.selectBarangay = function(btn) {
    // Deactivate all
    document.querySelectorAll('.brgy-card').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');

    const name  = btn.dataset.brgy;
    const color = btn.dataset.color;
    const initials = name.substring(0, 2).toUpperCase();

    // Update details header
    const avatar = document.getElementById('brgyDetailsAvatar');
    const nameEl = document.getElementById('brgyDetailsName');
    const descEl = document.getElementById('brgyDetailsDesc');

    if (avatar) { avatar.textContent = initials; avatar.style.background = color; }
    if (nameEl)  nameEl.textContent = 'Barangay ' + name;
    if (descEl)  descEl.textContent =
        'A barangay in Santa Cruz, Laguna under the jurisdiction of the Sangguniang Kabataan. Data will be available once SK officials submit their records.';

    // Reset stats
    ['statKabataan','statPrograms','statCompleted','statBudget'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.textContent = '—';
    });

    // Show details section
    const detailsSection = document.getElementById('brgyDetails');
    if (detailsSection) {
        detailsSection.style.display = 'block';
        // Reset to first tab
        switchTab('accomplishments');
        // Smooth scroll
        setTimeout(() => {
            detailsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }
};

/* ── Close barangay details ── */
window.closeBarangayDetails = function() {
    const detailsSection = document.getElementById('brgyDetails');
    if (detailsSection) detailsSection.style.display = 'none';
    document.querySelectorAll('.brgy-card').forEach(c => c.classList.remove('active'));
};

/* ── Tab switching ── */
function switchTab(tabName) {
    document.querySelectorAll('.details-tab').forEach(t => {
        t.classList.toggle('active', t.dataset.tab === tabName);
    });
    document.querySelectorAll('.tab-panel').forEach(p => {
        p.classList.toggle('active', p.id === 'tab-' + tabName);
    });
}

document.querySelectorAll('.details-tab').forEach(tab => {
    tab.addEventListener('click', () => switchTab(tab.dataset.tab));
});

/* ── Smooth scroll for anchor links ── */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', e => {
        const target = document.querySelector(anchor.getAttribute('href'));
        if (target) {
            e.preventDefault();
            const offset = 80; // header height
            const top = target.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top, behavior: 'smooth' });
        }
    });
});
