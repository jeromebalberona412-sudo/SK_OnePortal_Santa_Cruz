import { populateKkProfilingView, mapRegistrationToKkView } from './kk-profiling-view-populate.js';

const POLL_MS = 20000;

document.addEventListener('DOMContentLoaded', () => {
    const shell = document.getElementById('manageKabataanApp');
    if (!shell) {
        return;
    }

    let routes = {};
    try {
        routes = JSON.parse(shell.dataset.mkRoutes || '{}');
    } catch {
        routes = {};
    }

    const state = {
        rows: [],
        debounceTimer: null,
        pollTimer: null,
    };

    const els = {
        barangay: document.getElementById('mkBarangayFilter'),
        search: document.getElementById('mkSearch'),
        body: document.getElementById('mkTableBody'),
        subtitle: document.getElementById('mkTableSubtitle'),
        modal: document.getElementById('mkViewModal'),
        modalSubtitle: document.getElementById('mkViewModalSubtitle'),
        statTotal: document.getElementById('mkStatTotal'),
        statApproved: document.getElementById('mkStatApproved'),
        statPending: document.getElementById('mkStatPending'),
        statRejected: document.getElementById('mkStatRejected'),
    };

    const navBtn = document.querySelector('.nav-link.manage-kabataan-btn');
    if (navBtn) {
        navBtn.classList.add('active');
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function statusLabel(row) {
        if (row.status === 'rejected') {
            return 'Rejected';
        }
        if (['active', 'Auto Approved'].includes(row.evaluation_status)) {
            return 'Approved';
        }
        return row.evaluation_status || row.status || 'Pending';
    }

    function renderRows(rows) {
        if (!els.body) {
            return;
        }

        if (!rows.length) {
            els.body.innerHTML = '<tr><td colspan="7" class="mk-empty-cell">No KK profiling records found.</td></tr>';
            if (els.subtitle) {
                els.subtitle.textContent = 'No records for the selected filters';
            }
            return;
        }

        els.body.innerHTML = rows.map((row, index) => `
            <tr>
                <td>${escapeHtml(row.respondent_number)}</td>
                <td class="mk-cell-name">${escapeHtml(row.full_name)}</td>
                <td>${escapeHtml(row.barangay)}</td>
                <td class="mk-cell-email">${escapeHtml(row.email)}</td>
                <td>${escapeHtml(statusLabel(row))}</td>
                <td>${escapeHtml(row.submitted_at)}</td>
                <td>
                    <button type="button" class="mk-view-btn" data-row-index="${index}">View</button>
                </td>
            </tr>
        `).join('');

        if (els.subtitle) {
            els.subtitle.textContent = `Showing ${rows.length} KK profiling record${rows.length === 1 ? '' : 's'}`;
        }
    }

    function renderStats(stats = {}) {
        if (els.statTotal) els.statTotal.textContent = stats.total ?? 0;
        if (els.statApproved) els.statApproved.textContent = stats.approved ?? 0;
        if (els.statPending) els.statPending.textContent = stats.pending ?? 0;
        if (els.statRejected) els.statRejected.textContent = stats.rejected ?? 0;
    }

    async function fetchRows() {
        if (!routes.data) {
            return;
        }

        const params = new URLSearchParams();
        if (els.barangay?.value && els.barangay.value !== 'all') {
            params.set('barangay_id', els.barangay.value);
        }
        if (els.search?.value.trim()) {
            params.set('search', els.search.value.trim());
        }

        try {
            const response = await fetch(`${routes.data}?${params.toString()}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            state.rows = Array.isArray(payload.data) ? payload.data : [];
            renderRows(state.rows);
            renderStats(payload.stats || {});
        } catch {
            // silent refresh
        }
    }

    function scheduleFetch() {
        window.clearTimeout(state.debounceTimer);
        state.debounceTimer = window.setTimeout(fetchRows, 250);
    }

    async function openViewModal(rowIndex) {
        const row = state.rows[rowIndex];
        if (!row || !routes.show) {
            return;
        }

        const showUrl = routes.show.replace('__ID__', String(row.id));

        try {
            const response = await fetch(showUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const detail = payload.data || row;
            const viewData = mapRegistrationToKkView(detail);

            populateKkProfilingView(viewData, {
                showRejection: detail.status === 'rejected',
                rejectionReason: detail.rejection_reason || detail.review_notes || '',
            });

            if (els.modalSubtitle) {
                els.modalSubtitle.textContent = `${detail.full_name || row.full_name} — ${detail.barangay || row.barangay}`;
            }

            if (els.modal) {
                els.modal.hidden = false;
                document.body.style.overflow = 'hidden';
            }
        } catch {
            // ignore
        }
    }

    function closeModal() {
        if (!els.modal) {
            return;
        }
        els.modal.hidden = true;
        document.body.style.removeProperty('overflow');
    }

    els.barangay?.addEventListener('change', scheduleFetch);
    els.search?.addEventListener('input', scheduleFetch);

    els.body?.addEventListener('click', (event) => {
        const button = event.target.closest('.mk-view-btn');
        if (!button) {
            return;
        }
        openViewModal(Number(button.dataset.rowIndex));
    });

    document.querySelectorAll('[data-close-mk-modal]').forEach((el) => {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && els.modal && !els.modal.hidden) {
            closeModal();
        }
    });

    fetchRows();
    state.pollTimer = window.setInterval(fetchRows, POLL_MS);
});
