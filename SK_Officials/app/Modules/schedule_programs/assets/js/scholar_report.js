/**
 * Scholarship reports: modal editor + Scholarship Reports tab list
 */
document.addEventListener('DOMContentLoaded', () => {
    const STORAGE_KEY = 'sk_official_reports';
    const SCHOLARSHIP_SOURCE = 'scholarship';

    const openBtn = document.getElementById('safOpenReportBtn');
    const modal = document.getElementById('scholarReportModal');
    const editor = document.getElementById('scholarReportEditor');
    const pageEl = document.getElementById('scholarReportPage');
    const toast = document.getElementById('safToast');

    if (!modal || !editor) return;

    const closeBtn = document.getElementById('scholarReportClose');
    const cancelBtn = document.getElementById('scholarReportCancel');
    const saveBtn = document.getElementById('scholarReportSave');
    const generateBtn = document.getElementById('scholarReportGenerate');
    const printBtn = document.getElementById('scholarReportPrint');
    const titleInput = document.getElementById('scholarReportTitle');
    const typeSelect = document.getElementById('scholarReportType');
    const paperSelect = document.getElementById('scholarReportPaper');
    const modalTitleEl = document.getElementById('scholarReportModalTitle');
    const reportsTableBody = document.getElementById('safReportsTableBody');
    const subTabs = document.querySelectorAll('[data-saf-subtab]');
    const panelForms = document.getElementById('safPanelForms');
    const panelReports = document.getElementById('safPanelReports');

    let editingId = null;
    let editorApi = null;

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
        const m = window.SkReportEditor?.PAPER_SIZES?.[key];
        return m ? m.label : (key || 'A4').toUpperCase();
    }

    function buildTemplate(type, title) {
        const today = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        const t = title || 'SK Report';
        const templates = {
            scholarship: `
                <h1 style="text-align:center;">SANGGUNIANG KABATAAN</h1>
                <h1 style="text-align:center;">SCHOLARSHIP PROGRAM REPORT</h1>
                <p style="text-align:center;"><strong>${escapeHtml(t)}</strong></p>
                <p><strong>Date:</strong> ${today}</p>
                <h2>I. Program Overview</h2>
                <p>Summary of the SK scholarship program, beneficiaries, and application window.</p>
                <h2>II. Applicants & Scholars</h2>
                <p>Total applicants, approved scholars, and status breakdown.</p>
                <h2>III. Activities</h2>
                <p>Orientation, processing, and award activities.</p>
                <h2>IV. Budget</h2>
                <p>Allocated budget, expenses, and balance.</p>
                <h2>V. Recommendations</h2>
                <p>Recommendations for the next cycle.</p>
                <p><br></p>
                <p>Prepared by: _________________________</p>`,
            activity: `<h1 style="text-align:center;">ACTIVITY REPORT</h1><p><strong>${escapeHtml(t)}</strong> — ${today}</p><h2>I. Background</h2><p></p><h2>II. Narrative</h2><p></p>`,
            resolution: `<h1 style="text-align:center;">RESOLUTION</h1><p><strong>${escapeHtml(t)}</strong></p><p>${today}</p><p><strong>WHEREAS,</strong> …</p><p><strong>NOW, THEREFORE,</strong> be it resolved…</p>`,
            minutes: `<h1 style="text-align:center;">MINUTES OF MEETING</h1><p><strong>${escapeHtml(t)}</strong> — ${today}</p><h2>Attendance</h2><p></p>`,
            custom: `<h1 style="text-align:center;">${escapeHtml(t)}</h1><p>${today}</p><p></p>`,
        };
        return templates[type] || templates.custom;
    }

    function resetModal() {
        editingId = null;
        if (titleInput) titleInput.value = '';
        if (typeSelect) typeSelect.value = 'scholarship';
        if (paperSelect) paperSelect.value = 'a4';
        editor.innerHTML = '';
        editor.setAttribute('contenteditable', 'true');
        if (modalTitleEl) modalTitleEl.textContent = 'Make Report';
        editorApi?.setPaperSize?.('a4');
    }

    function openModal(reportId) {
        resetModal();
        if (reportId) {
            const r = loadAll().find(x => x.id === reportId);
            if (r) {
                editingId = r.id;
                if (titleInput) titleInput.value = r.title || '';
                if (typeSelect) typeSelect.value = r.type || 'scholarship';
                if (paperSelect) paperSelect.value = r.paperSize || 'a4';
                editor.innerHTML = r.html || '';
                window.SkReportEditor?.hydrateImages?.(editor);
                editorApi?.setPaperSize?.(r.paperSize || 'a4');
                if (modalTitleEl) modalTitleEl.textContent = 'Edit Report';
            }
        }
        modal.style.display = 'flex';
        editor.focus();
    }

    function closeModal() {
        modal.style.display = 'none';
        resetModal();
        renderReportsTable();
    }

    if (window.SkReportEditor) {
        editorApi = window.SkReportEditor.init({
            editor,
            pageEl,
            root: document.getElementById('scholarReportWordShell'),
            paperSelect,
            imageInput: document.getElementById('scholarReportImageInput'),
            cropBtn: document.getElementById('scholarReportCropBtn'),
            deleteImgBtn: document.getElementById('scholarReportDeleteImgBtn'),
            onToast: showToast,
        });
    }

    if (openBtn) openBtn.addEventListener('click', () => openModal());
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    if (generateBtn) {
        generateBtn.addEventListener('click', () => {
            const type = typeSelect?.value || 'scholarship';
            const title = titleInput?.value?.trim() || 'Scholarship Program Report';
            if (titleInput && !titleInput.value.trim()) titleInput.value = title;
            editor.innerHTML = buildTemplate(type, title);
            showToast('Template generated.');
        });
    }

    if (printBtn) printBtn.addEventListener('click', () => window.print());

    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            const title = titleInput?.value?.trim();
            const html = editor.innerHTML.trim();
            const paperSize = editorApi?.getPaperSize?.() || paperSelect?.value || 'a4';
            if (!title) { showToast('Enter a report title.', true); return; }
            if (!html || html === '<br>') { showToast('Report is empty.', true); return; }

            const list = loadAll();
            const payload = {
                id: editingId || ('rpt_' + Date.now()),
                title,
                type: typeSelect?.value || 'scholarship',
                paperSize,
                html,
                source: SCHOLARSHIP_SOURCE,
                createdAt: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
                updatedAt: new Date().toISOString(),
            };

            const idx = list.findIndex(r => r.id === payload.id);
            if (idx >= 0) {
                payload.createdAt = list[idx].createdAt || payload.createdAt;
                list[idx] = { ...list[idx], ...payload };
            } else {
                list.unshift(payload);
            }

            saveAll(list);
            showToast(editingId ? 'Report updated.' : 'Report saved.');
            closeModal();
            switchSubTab('reports');
        });
    }

    function renderReportsTable() {
        if (!reportsTableBody) return;
        const reports = getScholarshipReports();
        if (!reports.length) {
            reportsTableBody.innerHTML = `<tr><td colspan="4" class="saf-table-empty">No scholarship reports yet. Click <strong>Make Report</strong> to create one.</td></tr>`;
            return;
        }

        reportsTableBody.innerHTML = reports.map(r => `
            <tr>
                <td class="saf-form-title-cell">${escapeHtml(r.title)}</td>
                <td>${escapeHtml(getPaperLabel(r.paperSize))}</td>
                <td class="saf-date-cell">${escapeHtml(r.createdAt || '—')}</td>
                <td class="col-actions">
                    <div class="prog-tbl-actions">
                        <button type="button" class="prog-btn prog-btn-view" data-rpt-view="${r.id}">View</button>
                        <button type="button" class="prog-btn prog-btn-edit" data-rpt-edit="${r.id}">Edit</button>
                        <button type="button" class="prog-btn prog-btn-delete" data-rpt-delete="${r.id}">Delete</button>
                    </div>
                </td>
            </tr>
        `).join('');

        reportsTableBody.querySelectorAll('[data-rpt-view]').forEach(btn => {
            btn.addEventListener('click', () => {
                openModal(btn.getAttribute('data-rpt-view'));
                editor.setAttribute('contenteditable', 'false');
            });
        });
        reportsTableBody.querySelectorAll('[data-rpt-edit]').forEach(btn => {
            btn.addEventListener('click', () => openModal(btn.getAttribute('data-rpt-edit')));
        });
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
