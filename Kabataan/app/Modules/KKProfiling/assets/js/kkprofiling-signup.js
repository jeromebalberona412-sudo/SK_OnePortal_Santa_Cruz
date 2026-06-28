document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('barangaySearch');
    const barangayGrid = document.getElementById('barangayGrid');
    const noResults = document.getElementById('noResults');
    const filterButtons = Array.from(document.querySelectorAll('.kk-signup-filter'));

    let activeFilter = 'all';

    const statusConfig = {
        Ongoing:     { open: true  },
        Upcoming:    { open: false },
        Rescheduled: { open: false },
        Completed:   { open: false },
        Cancelled:   { open: false },
    };

    function fmtDate(str) {
        if (!str) return '';
        return new Date(str + 'T00:00:00').toLocaleDateString('en-PH', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
    }

    function fmtShortDate(str) {
        if (!str) return '';
        return new Date(str + 'T00:00:00').toLocaleDateString('en-PH', {
            month: 'short',
            day: 'numeric',
        });
    }

    function buildPeriodMarkup(sched) {
        const start = fmtDate(sched.date_start);
        const end = fmtDate(sched.date_expiry);
        const shortStart = fmtShortDate(sched.date_start);
        const shortEnd = fmtShortDate(sched.date_expiry);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const startDate = new Date(sched.date_start + 'T00:00:00');
        const endDate = new Date(sched.date_expiry + 'T00:00:00');

        let periodHtml = `<span class="kk-signup-period"><strong>Open:</strong> ${shortStart} – ${shortEnd}</span>`;

        if (sched.is_open) {
            const daysLeft = Math.ceil((endDate - today) / (1000 * 60 * 60 * 24));
            periodHtml += `<span class="kk-signup-open-hint">Sign up open${daysLeft > 0 ? ` · ${daysLeft} day${daysLeft === 1 ? '' : 's'} left` : ''}</span>`;
        } else if (startDate > today) {
            periodHtml += `<span class="kk-signup-period">Opens ${start}</span>`;
        } else if (endDate < today) {
            periodHtml += `<span class="kk-signup-period">Closed ${end}</span>`;
        } else {
            periodHtml += `<span class="kk-signup-period">Grace period: ${start} – ${end}</span>`;
        }

        return periodHtml;
    }

    function applyCardSchedule(btn, sched) {
        const cfg = statusConfig[sched.status];
        if (!cfg) return;

        const meta = btn.querySelector('.kk-signup-barangay-meta');
        const isOpen = Boolean(sched.is_open);

        btn.dataset.status = sched.status;
        btn.classList.add('has-schedule');

        if (meta) {
            meta.innerHTML = buildPeriodMarkup(sched);
        }

        if (isOpen) {
            btn.disabled = false;
            btn.classList.add('is-open');
            btn.onclick = () => {
                window.location.href = `/kkprofiling/${btn.dataset.slug}`;
            };
        }
    }

    function sortCards() {
        const cards = Array.from(barangayGrid.querySelectorAll('.kk-signup-barangay'));
        cards.sort((a, b) => {
            const aOpen = a.classList.contains('is-open') ? 0 : 1;
            const bOpen = b.classList.contains('is-open') ? 0 : 1;
            if (aOpen !== bOpen) return aOpen - bOpen;

            const aHas = a.classList.contains('has-schedule') ? 0 : 1;
            const bHas = b.classList.contains('has-schedule') ? 0 : 1;
            if (aHas !== bHas) return aHas - bHas;

            return (a.dataset.name || '').localeCompare(b.dataset.name || '');
        });

        cards.forEach((card) => barangayGrid.appendChild(card));
    }

    function applyFilters() {
        const query = (searchInput?.value || '').trim().toLowerCase();
        let visible = 0;

        barangayGrid.querySelectorAll('.kk-signup-barangay').forEach((btn) => {
            const nameMatch = (btn.dataset.name || '').toLowerCase().includes(query);
            const status = btn.dataset.status || 'none';
            const statusMatch = activeFilter === 'all'
                || (activeFilter === 'none' ? status === 'none' : status === activeFilter);

            const show = nameMatch && statusMatch;
            btn.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (noResults) {
            noResults.style.display = visible === 0 ? 'block' : 'none';
        }
    }

    filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            activeFilter = button.dataset.filter || 'all';
            filterButtons.forEach((item) => item.classList.toggle('is-active', item === button));
            applyFilters();
        });
    });

    searchInput?.addEventListener('input', applyFilters);

    fetch('/api/kkprofiling/open-barangays')
        .then((response) => response.json())
        .then(({ schedules }) => {
            const map = new Map(schedules.map((item) => [item.barangay_id, item]));

            barangayGrid.querySelectorAll('.kk-signup-barangay').forEach((btn) => {
                const sched = map.get(Number(btn.dataset.barangayId));
                if (sched) {
                    applyCardSchedule(btn, sched);
                }
            });

            sortCards();
            applyFilters();
        })
        .catch(() => {
            applyFilters();
        });
});
