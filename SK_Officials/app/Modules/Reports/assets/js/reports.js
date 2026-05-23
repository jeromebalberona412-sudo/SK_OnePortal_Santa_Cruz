const REPORTS_STORAGE_KEY = 'sk_official_reports';

document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('[data-reports-tab]');
    const panelList = document.getElementById('reportsPanelList');
    const panelMake = document.getElementById('reportsPanelMake');
    const editor = document.getElementById('reportEditor');
    const pageEl = document.getElementById('reportPage');
    const titleInput = document.getElementById('reportTitleInput');
    const typeSelect = document.getElementById('reportTypeSelect');
    const paperSelect = document.getElementById('reportPaperSelect');
    const generateBtn = document.getElementById('reportGenerateBtn');
    const saveBtn = document.getElementById('reportSaveBtn');
    const printBtn = document.getElementById('reportPrintBtn');
    const tableBody = document.getElementById('reportsTableBody');
    const searchInput = document.getElementById('reportsSearchInput');
    const toast = document.getElementById('reportsToast');

    if (!editor) return;

    let editingId = null;
    let editorApi = null;

    function getPaperLabel(key) {
        const m = window.SkReportEditor?.PAPER_SIZES?.[key];
        return m ? m.label : (key || 'A4').toUpperCase();
    }

    if (window.SkReportEditor) {
        editorApi = window.SkReportEditor.init({
            editor,
            pageEl,
            root: document.getElementById('reportWordShell'),
            paperSelect,
            imageInput: document.getElementById('reportImageInput'),
            cropBtn: document.getElementById('reportCropBtn'),
            deleteImgBtn: document.getElementById('reportDeleteImgBtn'),
            onToast: (msg) => showToast(msg),
        });
    }

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

    function uid() {
        return 'rpt_' + Date.now() + '_' + Math.random().toString(36).slice(2, 7);
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
            custom: 'Custom Document',
        };
        return map[v] || v;
    }

    function switchTab(name) {
        tabs.forEach(t => t.classList.toggle('active', t.getAttribute('data-reports-tab') === name));
        if (panelList) panelList.classList.toggle('reports-panel--hidden', name !== 'list');
        if (panelMake) panelMake.classList.toggle('reports-panel--hidden', name !== 'make');
        if (name === 'list') renderTable();
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => switchTab(tab.getAttribute('data-reports-tab')));
    });

    function buildGeneratedHtml(type, title) {
        const today = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        const t = title || 'Untitled SK Report';

        const templates = {
            activity: `
                <h1 style="text-align:center;">SANGGUNIANG KABATAAN</h1>
                <h1 style="text-align:center;">BARANGAY ACTIVITY REPORT</h1>
                <p style="text-align:center;"><strong>${escapeHtml(t)}</strong></p>
                <p><strong>Date:</strong> ${today}</p>
                <h2>I. Background</h2>
                <p>Describe the purpose and context of the youth activity/program.</p>
                <h2>II. Objectives</h2>
                <p>List the specific objectives of the activity.</p>
                <h2>III. Participants</h2>
                <p>Number of participants, demographics, and barangay coverage.</p>
                <h2>IV. Activity Narrative</h2>
                <p>Detailed narrative of what transpired during the activity.</p>
                <h2>V. Outcomes & Recommendations</h2>
                <p>Summarize results, learnings, and recommendations for future programs.</p>
                <p><br></p>
                <p>Prepared by: _________________________</p>
                <p>SK Chairperson / SK Official</p>`,
            resolution: `
                <h1 style="text-align:center;">RESOLUTION NO. _____</h1>
                <h1 style="text-align:center;">${escapeHtml(t)}</h1>
                <p style="text-align:center;">${today}</p>
                <p><strong>WHEREAS,</strong> the Sangguniang Kabataan recognizes the need to address matters affecting the youth of the barangay;</p>
                <p><strong>WHEREAS,</strong> relevant provisions of the SK Reform Act and local ordinances support youth development initiatives;</p>
                <p><strong>NOW, THEREFORE,</strong> be it resolved, as it is hereby resolved by the SK Council in session assembled:</p>
                <p><strong>Section 1.</strong> [State the main resolution clause here.]</p>
                <p><strong>Section 2.</strong> This resolution shall take effect immediately upon approval.</p>
                <p><br></p>
                <p>APPROVED this ___ day of ____________ 20__.</p>
                <p><br><br></p>
                <p>_________________________<br>SK Chairperson</p>`,
            minutes: `
                <h1 style="text-align:center;">MINUTES OF MEETING</h1>
                <p style="text-align:center;"><strong>${escapeHtml(t)}</strong></p>
                <p><strong>Date:</strong> ${today}</p>
                <p><strong>Time:</strong> _____</p>
                <p><strong>Venue:</strong> Barangay Hall / SK Office</p>
                <h2>Attendance</h2>
                <p>List SK officials and guests present.</p>
                <h2>Agenda</h2>
                <ol><li>Call to Order</li><li>Approval of Previous Minutes</li><li>Matters Discussed</li><li>Other Matters</li><li>Adjournment</li></ol>
                <h2>Discussion</h2>
                <p>Record the discussion per agenda item.</p>
                <h2>Action Items</h2>
                <p>List decisions, assignments, and deadlines.</p>
                <p><br></p>
                <p>Prepared by: _________________________</p>
                <p>SK Secretary / Documentation Officer</p>`,
            financial: `
                <h1 style="text-align:center;">FINANCIAL REPORT</h1>
                <p style="text-align:center;"><strong>${escapeHtml(t)}</strong></p>
                <p><strong>Reporting Period:</strong> ${today}</p>
                <h2>I. Summary</h2>
                <p>Total allocated budget, total expenses, and remaining balance.</p>
                <h2>II. Program Breakdown</h2>
                <p>Itemize expenses per SK program or project.</p>
                <h2>III. Supporting Documents</h2>
                <p>List receipts, vouchers, and liquidation attachments.</p>
                <h2>IV. Certification</h2>
                <p>I hereby certify that the foregoing financial report is true and correct to the best of my knowledge.</p>
                <p><br></p>
                <p>_________________________<br>SK Treasurer</p>`,
            custom: `
                <h1 style="text-align:center;">${escapeHtml(t)}</h1>
                <p style="text-align:center;">Republic of the Philippines<br>Sangguniang Kabataan — Barangay</p>
                <p><strong>Date:</strong> ${today}</p>
                <p>Start composing your custom SK document below.</p>`,
        };

        return templates[type] || templates.custom;
    }

    if (generateBtn) {
        generateBtn.addEventListener('click', () => {
            const type = typeSelect?.value || 'activity';
            const title = titleInput?.value?.trim() || getTypeLabel(type);
            if (!titleInput?.value?.trim() && titleInput) titleInput.value = title;
            editor.innerHTML = buildGeneratedHtml(type, title);
            showToast('Report template generated. Edit the content as needed.');
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            const title = titleInput?.value?.trim();
            const html = editor.innerHTML.trim();
            if (!title) {
                showToast('Please enter a report title.');
                return;
            }
            if (!html || html === '<br>') {
                showToast('Report content is empty.');
                return;
            }

            const list = loadReports();
            const payload = {
                id: editingId || uid(),
                title,
                type: typeSelect?.value || 'custom',
                paperSize: editorApi?.getPaperSize?.() || paperSelect?.value || 'a4',
                html,
                source: typeSelect?.value === 'scholarship' ? 'scholarship' : 'general',
                updatedAt: new Date().toISOString(),
                createdAt: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
            };

            const idx = list.findIndex(r => r.id === payload.id);
            if (idx >= 0) {
                payload.createdAt = list[idx].createdAt || payload.createdAt;
                list[idx] = payload;
            } else {
                list.unshift(payload);
            }

            saveReports(list);
            editingId = null;
            showToast('Report saved.');
            switchTab('list');
        });
    }

    if (printBtn) {
        printBtn.addEventListener('click', () => window.print());
    }

    function renderTable() {
        if (!tableBody) return;
        const q = (searchInput?.value || '').trim().toLowerCase();
        let list = loadReports();
        if (q) {
            list = list.filter(r =>
                (r.title || '').toLowerCase().includes(q) ||
                getTypeLabel(r.type).toLowerCase().includes(q)
            );
        }

        if (!list.length) {
            tableBody.innerHTML = `<tr><td colspan="5" class="reports-table-empty">No reports yet. Go to <strong>Make Reports</strong> to create one.</td></tr>`;
            return;
        }

        tableBody.innerHTML = list.map(r => `
            <tr>
                <td style="font-weight:600;">${escapeHtml(r.title)}</td>
                <td>${escapeHtml(getTypeLabel(r.type))}</td>
                <td>${escapeHtml(getPaperLabel(r.paperSize))}</td>
                <td>${escapeHtml(r.createdAt || '—')}</td>
                <td>
                    <div class="prog-tbl-actions">
                        <button type="button" class="prog-btn prog-btn-view" data-rpt-open="${r.id}">View</button>
                        <button type="button" class="prog-btn prog-btn-edit" data-rpt-edit="${r.id}">Edit</button>
                        <button type="button" class="prog-btn prog-btn-delete" data-rpt-delete="${r.id}">Delete</button>
                    </div>
                </td>
            </tr>
        `).join('');

        tableBody.querySelectorAll('[data-rpt-open]').forEach(btn => {
            btn.addEventListener('click', () => openReport(btn.getAttribute('data-rpt-open'), true));
        });
        tableBody.querySelectorAll('[data-rpt-edit]').forEach(btn => {
            btn.addEventListener('click', () => openReport(btn.getAttribute('data-rpt-edit'), false));
        });
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

    function openReport(id, viewOnly) {
        const r = loadReports().find(x => x.id === id);
        if (!r) return;
        editingId = viewOnly ? null : r.id;
        if (titleInput) titleInput.value = r.title || '';
        if (typeSelect) typeSelect.value = r.type || 'custom';
        if (paperSelect) paperSelect.value = r.paperSize || 'a4';
        editor.innerHTML = r.html || '';
        window.SkReportEditor?.hydrateImages?.(editor);
        editorApi?.setPaperSize?.(r.paperSize || 'a4');
        editor.setAttribute('contenteditable', viewOnly ? 'false' : 'true');
        switchTab('make');
        if (viewOnly) {
            showToast('View mode — use Edit from My Reports to modify.');
        }
    }

    if (searchInput) searchInput.addEventListener('input', renderTable);

    renderTable();
});
