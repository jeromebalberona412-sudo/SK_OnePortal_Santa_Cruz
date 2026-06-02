/**
 * Scholarship reports module (disabled - reports removed).
 */
document.addEventListener('DOMContentLoaded', () => {
    const STORAGE_KEY = 'sk_official_reports';
    const SCHOLARSHIP_SOURCE = 'scholarship';

    const openBtn = document.getElementById('safOpenReportBtn');
    const reportsTableBody = document.getElementById('safReportsTableBody');
    const subTabs = document.querySelectorAll('[data-saf-subtab]');
    const panelForms = document.getElementById('safPanelForms');
    const panelReports = document.getElementById('safPanelReports');
    const toast = document.getElementById('safToast');

    function showToast(msg, isError) {
        if (!toast) return;
        toast.textContent = msg;
        toast.style.display = 'flex';
        toast.style.background = isError ? '#ef4444' : '#22c55e';
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => { toast.style.display = 'none'; }, 2600);
    }

    function loadAll() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        } catch {
            return [];
        }
    }

    function saveAll(list) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
    }

    function getScholarshipReports() {
        return loadAll().filter(r => r.source === SCHOLARSHIP_SOURCE);
    }

    function escapeHtml(s) {
        return String(s || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function getPaperLabel(key) {
        const labels = { a4: 'A4', letter: 'Letter', legal: 'Legal' };
        return labels[key] || (key || 'A4').toUpperCase();
    }

    if (openBtn) {
        openBtn.addEventListener('click', () => {
            showToast('Reports module has been removed.', true);
        });
    }

    function renderReportsTable() {
        if (!reportsTableBody) return;
        const reports = getScholarshipReports();
        if (!reports.length) {
            reportsTableBody.innerHTML = `<tr><td colspan="4" class="saf-table-empty">No data available.</td></tr>`;
            return;
        }

        reportsTableBody.innerHTML = reports.map(r => `
            <tr>
                <td class="saf-form-title-cell">${escapeHtml(r.title)}</td>
                <td>${escapeHtml(getPaperLabel(r.paperSize))}</td>
                <td class="saf-date-cell">${escapeHtml(r.createdAt || '—')}</td>
                <td class="col-actions">
                    <div class="prog-tbl-actions">
                        <button type="button" class="prog-btn prog-btn-edit" disabled aria-disabled="true" title="Reports removed">Edit</button>
                        <button type="button" class="prog-btn prog-btn-delete" data-rpt-delete="${r.id}">Delete</button>
                    </div>
                </td>
            </tr>
        `).join('');

        reportsTableBody.querySelectorAll('[data-rpt-delete]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-rpt-delete');
                if (!confirm('Delete this report?')) return;
                saveAll(loadAll().filter(r => r.id !== id));
                renderReportsTable();
                showToast('Report deleted.');
            });
        });
    }

    function switchSubTab(name) {
        subTabs.forEach(t => t.classList.toggle('active', t.getAttribute('data-saf-subtab') === name));
        if (panelForms) panelForms.classList.toggle('saf-panel-hidden', name !== 'forms');
        if (panelReports) panelReports.classList.toggle('saf-panel-hidden', name !== 'reports');
        if (name === 'reports') renderReportsTable();
    }

    subTabs.forEach(tab => {
        tab.addEventListener('click', () => switchSubTab(tab.getAttribute('data-saf-subtab')));
    });

    renderReportsTable();
});
