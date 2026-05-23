const SAF_STORAGE_KEY = 'scholar_application_forms';

document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('safFormsTableBody');
    if (!tableBody) return;

    const modal = document.getElementById('safCreateFormModal');
    const modalBox = document.getElementById('safCreateFormModalBox');
    const openBtn = document.getElementById('safOpenFormBtn');
    const closeBtn = document.getElementById('safFormClose');
    const cancelBtn = document.getElementById('safFormCancelBtn');
    const saveBtn = document.getElementById('safFormSaveBtn');
    const editIdInput = document.getElementById('safEditFormId');
    const titleInput = document.getElementById('safFormTitleInput');
    const announcementEl = document.getElementById('spfbAnnouncement');
    const countEl = document.getElementById('spfbAnnouncementCount');
    const modalTitleText = document.getElementById('safModalTitleText');
    const saveBtnText = document.getElementById('safFormSaveBtnText');
    const previewModal = document.getElementById('safPreviewModal');
    const previewBody = document.getElementById('safPreviewBody');
    const previewClose = document.getElementById('safPreviewClose');
    const toastEl = document.getElementById('safToast');
    const maximizeBtn = document.getElementById('safFormMaximize');

    const builder = window.SpfbFormBuilder;
    if (!builder) return;

    let editingId = (editIdInput?.value || '').trim();

    builder.init({
        showToast: (msg, type) => showToast(msg, type),
    });

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

    function getTimeFromDropdowns(prefix) {
        const h = document.getElementById(prefix + 'Hour')?.value || '';
        const m = document.getElementById(prefix + 'Min')?.value || '';
        const p = document.getElementById(prefix + 'Period')?.value || '';
        if (!h || !m || !p) return '';
        return `${h}:${m} ${p}`;
    }

    function resetTimeDropdowns(prefix) {
        ['Hour', 'Min', 'Period'].forEach(part => {
            const el = document.getElementById(prefix + part);
            if (el) el.value = '';
        });
    }

    function setTimeDropdowns(prefix, timeStr) {
        resetTimeDropdowns(prefix);
        if (!timeStr) return;
        const m = timeStr.trim().match(/^(1[0-2]|0?[1-9]):([0-5][0-9])\s*(AM|PM)$/i);
        if (!m) return;
        const hEl = document.getElementById(prefix + 'Hour');
        const minEl = document.getElementById(prefix + 'Min');
        const pEl = document.getElementById(prefix + 'Period');
        if (hEl) {
            const hourNum = parseInt(m[1], 10);
            hEl.value = String(hourNum <= 12 && hourNum >= 1 ? hourNum : m[1]);
        }
        if (minEl) minEl.value = m[2];
        if (pEl) pEl.value = m[3].toUpperCase();
    }

    function resetModalForm() {
        editingId = '';
        if (editIdInput) editIdInput.value = '';
        if (titleInput) titleInput.value = '';
        if (announcementEl) announcementEl.value = '';
        if (countEl) countEl.textContent = '0';
        ['safStartDate', 'safEndDate'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        resetTimeDropdowns('safStartTime');
        resetTimeDropdowns('safEndTime');
        builder.reset();
        if (modalTitleText) modalTitleText.textContent = 'Create Scholar Application Form';
        if (saveBtnText) saveBtnText.textContent = 'Save Form';
    }

    function openModal(forEditId) {
        resetModalForm();
        if (forEditId) {
            const form = loadForms().find(f => f.id === forEditId);
            if (form) {
                editingId = form.id;
                if (editIdInput) editIdInput.value = form.id;
                if (titleInput) titleInput.value = form.title || '';
                if (announcementEl) announcementEl.value = form.announcement || '';
                if (countEl) countEl.textContent = String((form.announcement || '').length);
                if (document.getElementById('safStartDate')) document.getElementById('safStartDate').value = form.startDateRaw || '';
                if (document.getElementById('safEndDate')) document.getElementById('safEndDate').value = form.endDateRaw || '';
                setTimeDropdowns('safStartTime', form.startTime || '');
                setTimeDropdowns('safEndTime', form.endTime || '');
                builder.setQuestions(form.questions || []);
                if (modalTitleText) modalTitleText.textContent = 'Edit Scholar Application Form';
                if (saveBtnText) saveBtnText.textContent = 'Update Form';
            }
        }
        if (modal) modal.style.display = 'flex';
        initDateMin();
    }

    function closeModal() {
        if (modal) modal.style.display = 'none';
        if (modalBox) modalBox.classList.remove('sports-modal-maximized');
        if (modal) modal.classList.remove('sports-overlay-maximized');
        resetModalForm();
    }

    function initDateMin() {
        const today = new Date().toISOString().split('T')[0];
        ['safStartDate', 'safEndDate'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.setAttribute('min', today);
        });
    }

    function renderFormsTable() {
        const forms = loadForms();
        if (!forms.length) {
            tableBody.innerHTML = `<tr><td colspan="3" class="saf-table-empty">No forms yet. Click <strong>Make Scholar Application Form</strong> to create one.</td></tr>`;
            return;
        }

        tableBody.innerHTML = forms.map(f => `
            <tr>
                <td>
                    <div class="saf-form-title-cell">${escapeHtml(f.title)}</div>
                    <div class="saf-form-meta">${(f.questions || []).length} question(s)</div>
                </td>
                <td class="saf-date-cell">${escapeHtml(f.createdAt || '—')}</td>
                <td class="col-actions">
                    <div class="prog-tbl-actions">
                        <button type="button" class="prog-btn prog-btn-view" data-form-view="${f.id}">View</button>
                        <button type="button" class="prog-btn prog-btn-edit" data-form-edit="${f.id}">Edit</button>
                        <button type="button" class="prog-btn prog-btn-delete" data-form-delete="${f.id}">Delete</button>
                    </div>
                </td>
            </tr>
        `).join('');

        tableBody.querySelectorAll('[data-form-view]').forEach(btn => {
            btn.addEventListener('click', () => openFormPreview(btn.getAttribute('data-form-view')));
        });
        tableBody.querySelectorAll('[data-form-edit]').forEach(btn => {
            btn.addEventListener('click', () => openModal(btn.getAttribute('data-form-edit')));
        });
        tableBody.querySelectorAll('[data-form-delete]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-form-delete');
                if (!confirm('Delete this application form?')) return;
                saveForms(loadForms().filter(f => f.id !== id));
                renderFormsTable();
                showToast('Form deleted.', 'success');
            });
        });
    }

    function openFormPreview(formId) {
        const f = loadForms().find(x => x.id === formId);
        if (!f || !previewBody || !previewModal) return;

        const questionsHtml = (f.questions || []).map((q, i) => {
            const type = q.type === 'text' ? 'Short Answer' : q.type === 'paragraph' ? 'Paragraph' : q.type;
            return `<div style="margin-bottom:10px;padding:12px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;">
                <div style="font-weight:700;color:#111827;">${i + 1}. ${escapeHtml(q.label || 'Untitled')}${q.required ? ' <span style="color:#ef4444;">*</span>' : ''}</div>
                <div style="font-size:12px;color:#6b7280;margin-top:4px;">${escapeHtml(type)}</div>
            </div>`;
        }).join('');

        previewBody.innerHTML = `
            <h2 style="font-size:18px;font-weight:800;margin:0 0 8px;">${escapeHtml(f.title)}</h2>
            <p style="font-size:13px;color:#4b5563;line-height:1.6;white-space:pre-wrap;margin-bottom:14px;">${escapeHtml(f.announcement)}</p>
            <h3 style="font-size:14px;font-weight:700;margin-bottom:8px;">Questions</h3>
            ${questionsHtml || '<p style="color:#9ca3af;">No questions.</p>'}`;
        previewModal.style.display = 'flex';
    }

    function handleSave() {
        const title = titleInput?.value?.trim();
        const announcement = announcementEl?.value?.trim() || '';
        const startDateRaw = document.getElementById('safStartDate')?.value?.trim() || '';
        const endDateRaw = document.getElementById('safEndDate')?.value?.trim() || '';
        const startTime = getTimeFromDropdowns('safStartTime');
        const endTime = getTimeFromDropdowns('safEndTime');
        const questions = builder.getQuestions();

        if (!title) { showToast('Please enter a form title.', 'error'); return; }
        if (!announcement) { showToast('Please enter an announcement.', 'error'); return; }
        if (!startDateRaw || !endDateRaw) { showToast('Please select start and end dates.', 'error'); return; }
        if (!startTime || !endTime) { showToast('Please complete start and end times.', 'error'); return; }
        if (!questions.length) { showToast('Add at least one question.', 'error'); return; }

        const forms = loadForms();
        const payload = {
            id: editingId || uid(),
            title,
            announcement,
            startDateRaw,
            endDateRaw,
            startTime,
            endTime,
            questions,
            status: 'Published',
            createdAt: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
        };

        const idx = forms.findIndex(f => f.id === payload.id);
        if (idx >= 0) {
            payload.createdAt = forms[idx].createdAt || payload.createdAt;
            forms[idx] = { ...forms[idx], ...payload };
        } else {
            forms.unshift(payload);
        }

        saveForms(forms);
        closeModal();
        renderFormsTable();
        showToast(editingId ? 'Form updated.' : 'Form published.', 'success');

        if (window.history.replaceState && /\/scholar-application-form\//.test(window.location.pathname)) {
            window.history.replaceState({}, '', '/scholar-application-form');
        }
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
    if (announcementEl && countEl) {
        announcementEl.addEventListener('input', () => { countEl.textContent = String(announcementEl.value.length); });
    }
    if (maximizeBtn && modalBox && modal) {
        maximizeBtn.addEventListener('click', e => {
            e.stopPropagation();
            const isMax = !modalBox.classList.contains('sports-modal-maximized');
            modalBox.classList.toggle('sports-modal-maximized', isMax);
            modal.classList.toggle('sports-overlay-maximized', isMax);
            maximizeBtn.textContent = isMax ? '⧉' : '□';
        });
    }

    renderFormsTable();

    if (editingId) {
        openModal(editingId);
    }
});
