const REPORTS_STORAGE_KEY = 'sk_official_reports';

document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('reportsTableBody');
    const searchInput = document.getElementById('reportsSearchInput');
    const toast = document.getElementById('reportsToast');
    const viewModal = document.getElementById('reportsViewModal');
    const deleteModal = document.getElementById('reportsDeleteModal');
    const viewBody = document.getElementById('reportsViewBody');
    const viewTitle = document.getElementById('reportsViewTitle');
    const viewMeta = document.getElementById('reportsViewMeta');
    const viewEditBtn = document.getElementById('reportsViewEditBtn');
    const deleteReportName = document.getElementById('reportsDeleteReportName');
    const confirmDeleteBtn = document.getElementById('reportsConfirmDelete');

    if (!tableBody) return;

    const makeUrl = document.body.dataset.reportsMakeUrl || '/reports/ckeditor';
    let deleteTargetId = null;

    function showToast(msg) {
        if (!toast) return;
        toast.textContent = msg;
        toast.hidden = false;
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => { toast.hidden = true; }, 2600);
    }

    function loadReports() {
        try {
            return JSON.parse(localStorage.getItem(REPORTS_STORAGE_KEY) || '[]');
        } catch {
            return [];
        }
    }

    function saveReports(list) {
        localStorage.setItem(REPORTS_STORAGE_KEY, JSON.stringify(list));
    }

    function escapeHtml(s) {
        return String(s || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function getTypeLabel(v) {
        const map = {
            activity: 'Activity Report',
            resolution: 'SK Resolution',
            minutes: 'Meeting Minutes',
            financial: 'Financial Report',
            scholarship: 'Scholarship Program Report',
            sports: 'Sports Program Report',
            accomplishment: 'Accomplishment Report',
            custom: 'Custom Document',
        };
        return map[v] || v || '—';
    }

    function getPaperLabel(key) {
        const labels = { a4: 'A4', letter: 'Letter', legal: 'Legal', short: 'Short', long: 'Long' };
        return labels[key] || (key || 'A4').toUpperCase();
    }

    function parseReportDate(r, preferUpdated = false) {
        const raw = preferUpdated ? (r.updatedAt || r.createdAt) : (r.createdAt || r.updatedAt);
        if (!raw) return null;
        const d = new Date(raw);
        return Number.isNaN(d.getTime()) ? null : d;
    }

    function formatReportDate(r) {
        if (r.createdAt && !String(r.createdAt).includes('T')) {
            return r.createdAt;
        }
        const d = parseReportDate(r);
        return d
            ? d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
            : '—';
    }

    function formatReportTime(r) {
        const d = parseReportDate(r, true);
        return d
            ? d.toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit' })
            : '—';
    }

    function normalizePages(r) {
        if (Array.isArray(r.pages) && r.pages.length) {
            return r.pages.map(p => (typeof p === 'string' ? p : (p.html || '')));
        }
        return r.html ? [r.html] : [''];
    }

    function isPageEmpty(html) {
        const text = (html || '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
        return !text;
    }

    function openModal(el) {
        if (!el) return;
        el.hidden = false;
        document.body.classList.add('reports-modal-open');
    }

    function closeModals() {
        document.querySelectorAll('.reports-modal-backdrop').forEach(m => { m.hidden = true; });
        document.body.classList.remove('reports-modal-open');
        deleteTargetId = null;
    }

    function openViewModal(report) {
        if (!viewBody || !report) return;

        const pages = normalizePages(report);
        const title = report.title || 'Untitled Report';

        if (viewTitle) viewTitle.textContent = title;
        if (viewMeta) {
            viewMeta.innerHTML = `
                <span><strong>Type:</strong> ${escapeHtml(report.category || getTypeLabel(report.type))}</span>
                <span><strong>Paper:</strong> ${escapeHtml(getPaperLabel(report.paperSize))}</span>
                <span><strong>Date:</strong> ${escapeHtml(formatReportDate(report))}</span>
                <span><strong>Time:</strong> ${escapeHtml(formatReportTime(report))}</span>
            `;
        }

        if (viewEditBtn) {
            viewEditBtn.href = `${makeUrl}?id=${encodeURIComponent(report.id)}`;
        }

        const pagesHtml = pages.map((html, i) => {
            const body = isPageEmpty(html)
                ? '<p class="reports-view-empty">This page is empty.</p>'
                : html;
            return `
                <article class="reports-view-page">
                    <span class="reports-view-page-label">Page ${i + 1}</span>
                    <div class="reports-view-page-content">${body}</div>
                </article>`;
        }).join('');

        viewBody.innerHTML = pagesHtml || '<p class="reports-view-empty">No content in this report.</p>';
        openModal(viewModal);
    }

    function openDeleteModal(report) {
        if (!report) return;
        deleteTargetId = report.id;
        if (deleteReportName) {
            deleteReportName.textContent = report.title ? `"${report.title}"` : 'This report';
        }
        openModal(deleteModal);
    }

    function confirmDelete() {
        if (!deleteTargetId) return;
        saveReports(loadReports().filter(r => r.id !== deleteTargetId));
        closeModals();
        renderTable();
        showToast('Report deleted.');
    }

    document.querySelectorAll('[data-reports-close]').forEach(btn => {
        btn.addEventListener('click', closeModals);
    });

    document.querySelectorAll('.reports-modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', e => {
            if (e.target === backdrop) closeModals();
        });
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeModals();
    });

    confirmDeleteBtn?.addEventListener('click', confirmDelete);

    function renderTable() {
        const q = (searchInput?.value || '').trim().toLowerCase();
        let list = loadReports();
        if (q) {
            list = list.filter(r =>
                (r.title || '').toLowerCase().includes(q) ||
                getTypeLabel(r.type).toLowerCase().includes(q) ||
                (r.category || '').toLowerCase().includes(q)
            );
        }

        if (!list.length) {
            tableBody.innerHTML = `<tr><td colspan="6" class="reports-table-empty">No reports yet. Click <strong>Make Report</strong> to create one.</td></tr>`;
            return;
        }

        tableBody.innerHTML = list.map(r => `
            <tr>
                <td class="col-title">${escapeHtml(r.title)}</td>
                <td>${escapeHtml(r.category || getTypeLabel(r.type))}</td>
                <td>${escapeHtml(getPaperLabel(r.paperSize))}</td>
                <td>${escapeHtml(formatReportDate(r))}</td>
                <td>${escapeHtml(formatReportTime(r))}</td>
                <td>
                    <div class="prog-tbl-actions">
                        <button type="button" class="prog-btn prog-btn-view" data-rpt-view="${encodeURIComponent(r.id)}">View</button>
                        <a href="${makeUrl}?id=${encodeURIComponent(r.id)}" class="prog-btn prog-btn-edit">Edit</a>
                        <button type="button" class="prog-btn prog-btn-delete" data-rpt-delete="${encodeURIComponent(r.id)}">Delete</button>
                    </div>
                </td>
            </tr>
        `).join('');

        tableBody.querySelectorAll('[data-rpt-view]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = decodeURIComponent(btn.getAttribute('data-rpt-view') || '');
                const report = loadReports().find(x => x.id === id);
                if (report) openViewModal(report);
            });
        });

        tableBody.querySelectorAll('[data-rpt-delete]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = decodeURIComponent(btn.getAttribute('data-rpt-delete') || '');
                const report = loadReports().find(x => x.id === id);
                if (report) openDeleteModal(report);
            });
        });
    }

    if (searchInput) searchInput.addEventListener('input', renderTable);
    renderTable();
});
