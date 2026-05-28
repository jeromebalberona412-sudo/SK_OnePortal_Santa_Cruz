const SAF_STORAGE_KEY = 'scholar_application_forms';

document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('safFormsTableBody');
    if (!tableBody) return;

    const modal = document.getElementById('scholarProgramModal');
    const modalBox = document.getElementById('scholarProgramBox');
    const openBtn = document.getElementById('safOpenFormBtn');
    const closeBtn = document.getElementById('scholarProgramClose');
    const cancelBtn = document.getElementById('btnCancelProgram');
    const saveBtn = document.getElementById('btnSaveProgram');
    const maximizeBtn = document.getElementById('scholarProgramMaximize');
    const previewModal = document.getElementById('safPreviewModal');
    const previewBody = document.getElementById('safPreviewBody');
    const previewClose = document.getElementById('safPreviewClose');
    const toastEl = document.getElementById('safToast');

    const builder = window.SpfbFormBuilder;

    function showToast(msg, type) {
        if (!toastEl) return;
        toastEl.textContent = msg;
        toastEl.style.display = 'flex';
        toastEl.style.background = type === 'error' ? '#ef4444' : '#22c55e';
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => { toastEl.style.display = 'none'; }, 2800);
    }

    function loadForms() {
        try {
            return JSON.parse(localStorage.getItem(SAF_STORAGE_KEY) || '[]');
        } catch {
            return [];
        }
    }

    function saveForms(forms) {
        localStorage.setItem(SAF_STORAGE_KEY, JSON.stringify(forms));
    }

    function uid() {
        return 'saf_' + Date.now() + '_' + Math.random().toString(36).slice(2, 7);
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function resetModalForm() {
        const fields = [
            'programName', 'programCommittee', 'participationQty',
            'programVenue', 'programDescription', 'programTerms',
            'schedStartDate', 'schedEndDate', 'programStatus',
            'schedStartTimeHour', 'schedStartTimeMinute',
            'schedEndTimeHour', 'schedEndTimeMinute'
        ];

        fields.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                if (el.tagName === 'SELECT') {
                    el.value = id === 'programStatus' ? 'auto' : '';
                } else if (el.type === 'number') {
                    el.value = '';
                } else if (id === 'schedStartTimeHour' || id === 'schedEndTimeHour') {
                    el.value = id === 'schedStartTimeHour' ? '08' : '17';
                } else if (id === 'schedStartTimeMinute' || id === 'schedEndTimeMinute') {
                    el.value = '00';
                } else {
                    el.value = '';
                }
            }
        });
    }

    function openModal(forEditId) {
        console.log('openModal called', { modal, forEditId });
        if (modal) {
            modal.style.display = 'flex';
            console.log('Modal opened successfully');
            try {
                resetModalForm();
                // Reset form builder
                if (window.SpfbFormBuilder) {
                    window.SpfbFormBuilder.reset();
                }
                // Setup counters
                setupCounters();
                // Setup standard form toggle
                setupStandardFormToggle();
            } catch (e) {
                console.error('Error in openModal:', e);
            }
        } else {
            console.error('Modal element not found!');
        }
    }

    function setupCounters() {
        // Setup announcement counter
        const announcementEl = document.getElementById('spfbAnnouncement');
        const countEl = document.getElementById('spfbAnnouncementCount');
        if (announcementEl && countEl) {
            announcementEl.addEventListener('input', () => {
                countEl.textContent = String(announcementEl.value.length);
            });
        }

        // Setup description counter
        const descriptionEl = document.getElementById('programDescription');
        const descCountEl = document.getElementById('descriptionCount');
        if (descriptionEl && descCountEl) {
            descriptionEl.addEventListener('input', () => {
                descCountEl.textContent = String(descriptionEl.value.length);
            });
        }

        // Setup terms counter
        const termsEl = document.getElementById('programTerms');
        const termsCountEl = document.getElementById('termsCount');
        if (termsEl && termsCountEl) {
            termsEl.addEventListener('input', () => {
                termsCountEl.textContent = String(termsEl.value.length);
            });
        }
    }

    function setupStandardFormToggle() {
        const toggle = document.getElementById('includeStandardFormToggle');
        const staticPreview = document.getElementById('staticFormPreview');

        if (toggle && staticPreview) {
            // Set initial state
            staticPreview.style.display = toggle.checked ? 'block' : 'none';

            toggle.addEventListener('change', function () {
                staticPreview.style.display = this.checked ? 'block' : 'none';
            });
        }
    }

    function closeModal() {
        if (modal) modal.style.display = 'none';
        if (modalBox) modalBox.classList.remove('schol-modal-maximized');
        resetModalForm();
    }

    function initDateMin() {
        const today = new Date().toISOString().split('T')[0];
        ['safStartDate', 'safEndDate'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.setAttribute('min', today);
        });
    }

    function filterFormsByDate(forms, filterValue) {
        if (filterValue === 'all') return forms;
        
        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        
        return forms.filter(f => {
            if (!f.createdAt) return false;
            const createdDate = new Date(f.createdAt);
            
            switch (filterValue) {
                case 'recent': {
                    const sevenDaysAgo = new Date(today);
                    sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
                    return createdDate >= sevenDaysAgo;
                }
                case 'monthly': {
                    return createdDate.getMonth() === now.getMonth() && 
                           createdDate.getFullYear() === now.getFullYear();
                }
                case 'yearly': {
                    return createdDate.getFullYear() === now.getFullYear();
                }
                default:
                    return true;
            }
        });
    }

    function renderFormsTable(filterValue = 'all') {
        const allForms = loadForms();
        const forms = filterFormsByDate(allForms, filterValue);
        
        // Update program count
        const countEl = document.getElementById('programCount');
        if (countEl) {
            countEl.textContent = forms.length;
        }
        
        if (!forms.length) {
            const message = filterValue === 'all' 
                ? 'No programs yet. Click <strong>Create Scholarship Program</strong> to create one.'
                : 'No programs found for the selected filter.';
            tableBody.innerHTML = `<tr><td colspan="8" class="saf-table-empty">${message}</td></tr>`;
            return;
        }

        tableBody.innerHTML = forms.map(f => {
            const statusClass = f.status === 'open' ? 'schol-pill-approved' : 
                               f.status === 'closed' ? 'schol-pill-rejected' : 
                               'schol-pill-pending';
            const statusText = f.status === 'auto' ? 'Auto' : 
                              f.status === 'open' ? 'Open' : 
                              f.status === 'closed' ? 'Closed' : 'Upcoming';
            
            return `
            <tr>
                <td>${escapeHtml(f.programName)}</td>
                <td>${escapeHtml(f.programType)}</td>
                <td>${escapeHtml(f.committee)}</td>
                <td>${escapeHtml(f.participationQty || 'N/A')}</td>
                <td>${escapeHtml(f.startDate)}</td>
                <td>${escapeHtml(f.endDate)}</td>
                <td><span class="schol-pill ${statusClass}">${statusText}</span></td>
                <td class="col-actions">
                    <div class="prog-tbl-actions">
                        <button type="button" class="prog-btn prog-btn-view" data-form-view="${f.id}">View</button>
                        <button type="button" class="prog-btn prog-btn-delete" data-form-delete="${f.id}">Delete</button>
                    </div>
                </td>
            </tr>
        `;
        }).join('');

        tableBody.querySelectorAll('[data-form-view]').forEach(btn => {
            btn.addEventListener('click', () => openFormPreview(btn.getAttribute('data-form-view')));
        });
        tableBody.querySelectorAll('[data-form-delete]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-form-delete');
                if (!confirm('Delete this program?')) return;
                saveForms(loadForms().filter(f => f.id !== id));
                const currentFilter = document.getElementById('programFilter')?.value || 'all';
                renderFormsTable(currentFilter);
                showToast('Program deleted.', 'success');
            });
        });
    }

    function openFormPreview(formId) {
        const f = loadForms().find(x => x.id === formId);
        if (!f || !previewBody || !previewModal) return;

        previewBody.innerHTML = `
            <div style="padding:20px;">
                <h2 style="font-size:18px;font-weight:800;margin:0 0 16px;color:#111827;">${escapeHtml(f.programName)}</h2>
                
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:16px;">
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Program Type</div>
                        <div style="font-size:14px;color:#111827;">${escapeHtml(f.programType)}</div>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Committee</div>
                        <div style="font-size:14px;color:#111827;">${escapeHtml(f.committee)}</div>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Schedule</div>
                        <div style="font-size:14px;color:#111827;">${escapeHtml(f.startDate)} ${escapeHtml(f.startTime)} - ${escapeHtml(f.endDate)} ${escapeHtml(f.endTime)}</div>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Participation Quantity</div>
                        <div style="font-size:14px;color:#111827;">${escapeHtml(f.participationQty || 'N/A')}</div>
                    </div>
                </div>
                
                ${f.venue ? `<div style="margin-bottom:12px;">
                    <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Venue</div>
                    <div style="font-size:14px;color:#111827;">${escapeHtml(f.venue)}</div>
                </div>` : ''}
                
                ${f.description ? `<div style="margin-bottom:12px;">
                    <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Description</div>
                    <div style="font-size:14px;color:#111827;line-height:1.6;">${escapeHtml(f.description)}</div>
                </div>` : ''}
                
                ${f.terms ? `<div style="margin-bottom:12px;">
                    <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Terms and Conditions</div>
                    <div style="font-size:14px;color:#111827;line-height:1.6;white-space:pre-wrap;">${escapeHtml(f.terms)}</div>
                </div>` : ''}
            </div>`;
        previewModal.style.display = 'flex';
    }

    function handleSave() {
        const programName = document.getElementById('programName')?.value?.trim();
        const programCommittee = document.getElementById('programCommittee')?.value?.trim();
        const participationQty = document.getElementById('participationQty')?.value?.trim();
        const programVenue = document.getElementById('programVenue')?.value?.trim();
        const programDescription = document.getElementById('programDescription')?.value?.trim();
        const programTerms = document.getElementById('programTerms')?.value?.trim();
        const startDate = document.getElementById('schedStartDate')?.value?.trim();
        const endDate = document.getElementById('schedEndDate')?.value?.trim();
        const status = document.getElementById('programStatus')?.value || 'auto';

        const startHour = document.getElementById('schedStartTimeHour')?.value || '08';
        const startMin = document.getElementById('schedStartTimeMinute')?.value || '00';
        const endHour = document.getElementById('schedEndTimeHour')?.value || '17';
        const endMin = document.getElementById('schedEndTimeMinute')?.value || '00';

        const startTime = `${startHour}:${startMin}`;
        const endTime = `${endHour}:${endMin}`;

        if (!programName) { showToast('Please enter a program name.', 'error'); return; }
        if (!programCommittee) { showToast('Please select a committee.', 'error'); return; }
        if (!startDate || !endDate) { showToast('Please select start and end dates.', 'error'); return; }

        const forms = loadForms();
        const payload = {
            id: uid(),
            programName,
            programType: 'Equitable Access to Quality Education',
            committee: programCommittee,
            participationQty: participationQty || '',
            venue: programVenue || '',
            description: programDescription || '',
            terms: programTerms || '',
            startDate,
            endDate,
            startTime,
            endTime,
            status,
            createdAt: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
        };

        forms.unshift(payload);
        saveForms(forms);
        closeModal();
        renderFormsTable();
        showToast('Program created successfully!', 'success');
    }

    if (openBtn) openBtn.addEventListener('click', () => openModal());
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (saveBtn) saveBtn.addEventListener('click', handleSave);
    if (modal) {
        modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    }
    if (previewClose && previewModal) {
        previewClose.addEventListener('click', () => { previewModal.style.display = 'none'; });
        previewModal.addEventListener('click', e => { if (e.target === previewModal) previewModal.style.display = 'none'; });
    }
    if (maximizeBtn && modalBox && modal) {
        maximizeBtn.addEventListener('click', e => {
            e.stopPropagation();
            modalBox.classList.toggle('schol-modal-maximized');
            maximizeBtn.textContent = modalBox.classList.contains('schol-modal-maximized') ? '⧉' : '□';
        });
    }

    // Setup filter
    const filterSelect = document.getElementById('programFilter');
    if (filterSelect) {
        filterSelect.addEventListener('change', () => {
            renderFormsTable(filterSelect.value);
        });
    }

    renderFormsTable();
});
