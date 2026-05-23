const REPORTS_STORAGE_KEY = 'sk_official_reports';

document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('reportsTableBody');
    const searchInput = document.getElementById('reportsSearchInput');
    const toast = document.getElementById('reportsToast');

    if (!tableBody) return;

    const makeUrl = document.body.dataset.reportsMakeUrl || '/reports/make';

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
            accomplishment: 'Accomplishment Report',
            custom: 'Custom Document',
        };
        return map[v] || v;
    }

    function getPaperLabel(key) {
        const labels = { a4: 'A4', letter: 'Letter', legal: 'Legal', short: 'Short', long: 'Long' };
        return labels[key] || (key || 'A4').toUpperCase();
    }

    function renderTable() {
        const q = (searchInput?.value || '').trim().toLowerCase();
        let list = loadReports();
        if (q) {
            list = list.filter(r =>
                (r.title || '').toLowerCase().includes(q) ||
                getTypeLabel(r.type).toLowerCase().includes(q)
            );
        }

        if (!list.length) {
            tableBody.innerHTML = `<tr><td colspan="5" class="reports-table-empty">No reports yet. <a href="${escapeHtml(makeUrl)}">Make a Report</a> to create one.</td></tr>`;
            return;
        }

        tableBody.innerHTML = list.map(r => `
            <tr>
                <td style="font-weight:600;">${escapeHtml(r.title)}</td>
                <td>${escapeHtml(r.category || getTypeLabel(r.type))}</td>
                <td>${escapeHtml(getPaperLabel(r.paperSize))}</td>
                <td>${escapeHtml(r.createdAt || '—')}</td>
                <td>
                    <div class="prog-tbl-actions">
                        <a href="${makeUrl}?id=${encodeURIComponent(r.id)}" class="prog-btn prog-btn-edit">Edit</a>
                        <button type="button" class="prog-btn prog-btn-delete" data-rpt-delete="${r.id}">Delete</button>
                    </div>
                </td>
            </tr>
        `).join('');

        tableBody.querySelectorAll('[data-rpt-delete]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-rpt-delete');
                if (!confirm('Delete this report?')) return;
                saveReports(loadReports().filter(r => r.id !== id));
                renderTable();
                showToast('Report deleted.');
            });
        });
    }

    if (searchInput) searchInput.addEventListener('input', renderTable);
    renderTable();
});
