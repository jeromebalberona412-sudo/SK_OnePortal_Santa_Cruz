let schedulePrograms = [];
const PROGRAM_LETTER = 'I';
let programMeta = null;
let editingProgramId = null;
let pendingDeleteProgramId = null;

const KK_FIELD_LABELS = {
    last_name: 'Last Name',
    first_name: 'First Name',
    middle_name: 'Middle Name',
    suffix: 'Suffix',
    full_name: 'Full Name',
    birthday: 'Birthday',
    age: 'Age',
    sex: 'Sex',
    civil_status: 'Civil Status',
    contact_number: 'Contact Number',
    email: 'Email Address',
    region: 'Region',
    province: 'Province',
    city: 'City/Municipality',
    barangay: 'Barangay',
    purok_zone: 'Purok/Zone',
    youth_classification: 'Youth Classification',
    youth_age_group: 'Youth Age Group',
    education: 'Educational Attainment',
    current_school: 'Current School',
    course_strand: 'Course / Strand',
    work_status: 'Work Status',
    sk_voter: 'Registered SK Voter',
    sk_voted: 'Voted Last Election',
};

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

async function apiFetch(url, options = {}) {
    const response = await fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json',
            ...(options.headers || {}),
        },
        ...options,
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
        throw new Error(data.message || 'Request failed.');
    }

    return data;
}

function escapeHtml(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function showToast(msg, type) {
    const toastEl = document.getElementById('safToast');
    if (!toastEl) return;
    toastEl.textContent = msg;
    toastEl.style.display = 'flex';
    toastEl.style.background = type === 'error' ? '#ef4444' : '#22c55e';
    clearTimeout(showToast._t);
    showToast._t = setTimeout(() => { toastEl.style.display = 'none'; }, 2800);
}

function setSaveButtonLoading(isLoading) {
    const saveBtn = document.getElementById('btnSaveProgram');
    if (!saveBtn) return;

    if (isLoading) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="schol-save-btn-content"><span class="schol-save-spinner"></span> Saving...</span>';
        return;
    }

    saveBtn.disabled = false;
    saveBtn.innerHTML = `
        <span class="schol-save-btn-content">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Save Program
        </span>
    `;
}

function resolveProgramStatus(program) {
    return program?.status === 'closed' ? 'closed' : 'open';
}

function formatStatusLabel(status) {
    return status === 'closed' ? 'Closed' : 'Open';
}

function getTypeLabel(type) {
    const map = {
        text: 'Short Answer',
        paragraph: 'Paragraph',
        number: 'Number',
        checkbox: 'Checkboxes',
        radio: 'Multiple Choice',
        file: 'File Upload',
    };
    return map[type] || type || 'Short Answer';
}

async function loadProgramMeta() {
    const response = await apiFetch(`/api/schedule-programs/meta?letter=${PROGRAM_LETTER}`);
    programMeta = response.data || null;

    const typeEl = document.getElementById('programType');

    if (typeEl) {
        typeEl.value = programMeta?.program_type || 'Sports Development';
    }
}

async function loadPrograms() {
    const response = await apiFetch(`/api/schedule-programs?letter=${PROGRAM_LETTER}`);
    schedulePrograms = Array.isArray(response.data) ? response.data : [];
    renderFormsTable();
}

function resetModalForm() {
    editingProgramId = null;

    const participationQty = document.getElementById('participationQty');
    const startDate = document.getElementById('schedStartDate');
    const endDate = document.getElementById('schedEndDate');
    const status = document.getElementById('programStatus');
    const announcement = document.getElementById('spfbAnnouncement');
    const announcementCount = document.getElementById('spfbAnnouncementCount');

    if (participationQty) participationQty.value = '';
    if (startDate) startDate.value = '';
    if (endDate) endDate.value = '';
    if (status) status.value = 'open';
    if (announcement) announcement.value = '';
    if (announcementCount) announcementCount.textContent = '0';

    document.querySelectorAll('.kk-profiling-field').forEach((checkbox) => {
        checkbox.checked = false;
    });

    if (window.SpfbFormBuilder) {
        window.SpfbFormBuilder.reset();
    }

    if (programMeta) {
        const typeEl = document.getElementById('programType');
        const committeeEl = document.getElementById('programCommittee');
        if (typeEl) typeEl.value = programMeta.program_type || '';
    }
}

function openModal(forEditId) {
    const modal = document.getElementById('scholarProgramModal');
    const modalBox = document.getElementById('scholarProgramBox');
    const maximizeBtn = document.getElementById('scholarProgramMaximize');
    const modalTitle = document.getElementById('scholarProgramModalTitle');

    if (!modal) return;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    modal.classList.remove('schol-modal-maximized');
    if (modalBox) modalBox.classList.remove('schol-modal-maximized');
    if (maximizeBtn) {
        maximizeBtn.textContent = '□';
        maximizeBtn.title = 'Maximize';
    }

    if (!forEditId) {
        resetModalForm();
        if (modalTitle) {
            modalTitle.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                Create Sports Program
            `;
        }
    }
}

function closeModal() {
    const modal = document.getElementById('scholarProgramModal');
    const modalBox = document.getElementById('scholarProgramBox');
    const maximizeBtn = document.getElementById('scholarProgramMaximize');

    document.body.style.overflow = '';
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('schol-modal-maximized');
    }
    if (modalBox) modalBox.classList.remove('schol-modal-maximized');
    if (maximizeBtn) {
        maximizeBtn.textContent = '□';
        maximizeBtn.title = 'Maximize';
    }
    resetModalForm();
}

function renderFormsTable() {
    const tableBody = document.getElementById('safFormsTableBody');
    const countEl = document.getElementById('programCount');
    if (!tableBody) return;

    const forms = [...schedulePrograms].sort((a, b) => {
        const nameA = (a.program_name || '').toLowerCase();
        const nameB = (b.program_name || '').toLowerCase();
        return nameA.localeCompare(nameB);
    });

    if (countEl) countEl.textContent = String(forms.length);

    if (!forms.length) {
        tableBody.innerHTML = '<tr><td colspan="6" class="saf-table-empty">No sports programs yet. Click Create Program to add one.</td></tr>';
        return;
    }

    tableBody.innerHTML = forms.map((program) => {
        const status = resolveProgramStatus(program);
        const statusClass = status === 'open' ? 'schol-pill-approved' : 'schol-pill-rejected';

        return `
            <tr>
                <td>${escapeHtml(program.program_type)}</td>
                <td>${escapeHtml(program.participation_quantity ?? 'N/A')}</td>
                <td>${escapeHtml(program.start_date)}</td>
                <td>${escapeHtml(program.end_date)}</td>
                <td><span class="schol-pill ${statusClass}">${formatStatusLabel(status)}</span></td>
                <td class="col-actions">
                    <div class="prog-tbl-actions">
                        <button type="button" class="prog-btn prog-btn-view" data-form-view="${program.id}">View</button>
                        <button type="button" class="prog-btn prog-btn-edit" data-form-edit="${program.id}">Edit</button>
                        <button type="button" class="prog-btn prog-btn-delete" data-form-delete="${program.id}">Delete</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    tableBody.querySelectorAll('[data-form-view]').forEach((btn) => {
        btn.addEventListener('click', () => openFormPreview(btn.getAttribute('data-form-view')));
    });
    tableBody.querySelectorAll('[data-form-edit]').forEach((btn) => {
        btn.addEventListener('click', () => editProgram(btn.getAttribute('data-form-edit')));
    });
    tableBody.querySelectorAll('[data-form-delete]').forEach((btn) => {
        btn.addEventListener('click', () => openDeleteProgramModal(btn.getAttribute('data-form-delete')));
    });
}

function editProgram(programId) {
    const program = schedulePrograms.find((item) => String(item.id) === String(programId));
    if (!program) {
        showToast('Program not found.', 'error');
        return;
    }

    editingProgramId = program.id;
    openModal(program.id);

    const participationQty = document.getElementById('participationQty');
    const startDate = document.getElementById('schedStartDate');
    const endDate = document.getElementById('schedEndDate');
    const status = document.getElementById('programStatus');
    const typeEl = document.getElementById('programType');
    const announcementEl = document.getElementById('spfbAnnouncement');
    const announcementCountEl = document.getElementById('spfbAnnouncementCount');
    const modalTitle = document.getElementById('scholarProgramModalTitle');

    if (participationQty) participationQty.value = program.participation_quantity ?? '';
    if (startDate) startDate.value = program.start_date || '';
    if (endDate) endDate.value = program.end_date || '';
    if (status) status.value = resolveProgramStatus(program);
    if (typeEl) typeEl.value = program.program_type || '';
    if (announcementEl) {
        announcementEl.value = program.announcement || '';
        if (announcementCountEl) announcementCountEl.textContent = String(announcementEl.value.length);
    }

    const kkFields = program.kk_profiling_fields || [];
    document.querySelectorAll('.kk-profiling-field').forEach((checkbox) => {
        checkbox.checked = kkFields.includes(checkbox.value);
    });

    if (window.SpfbFormBuilder && typeof window.SpfbFormBuilder.setQuestions === 'function') {
        window.SpfbFormBuilder.setQuestions(program.custom_questions || []);
    }

    if (modalTitle) {
        modalTitle.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit Sports Program
        `;
    }
}

function openFormPreview(programId) {
    const program = schedulePrograms.find((item) => String(item.id) === String(programId));
    const viewProgramBody = document.getElementById('viewProgramBody');
    const viewProgramModal = document.getElementById('viewProgramModal');
    if (!program || !viewProgramBody || !viewProgramModal) return;

    const status = resolveProgramStatus(program);
    const statusColors = {
        open: { bg: '#dcfce7', text: '#166534', label: 'Open' },
        closed: { bg: '#fee2e2', text: '#991b1b', label: 'Closed' },
    };
    const statusStyle = statusColors[status] || statusColors.open;
    const kkFields = program.kk_profiling_fields || [];
    const customQuestions = program.custom_questions || [];

    viewProgramBody.innerHTML = `
        <div style="padding:24px;background:#f0f1f5;">
            <div class="schol-schedule-card" style="margin-bottom:20px;">
                <h4 class="schol-schedule-title">Program Information</h4>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                    <div style="grid-column:1/-1;">
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Program</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;">${escapeHtml(program.program_name)}</div>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Program Type</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;">${escapeHtml(program.program_type)}</div>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Committee</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;">${escapeHtml(program.committee)}</div>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Participation Quantity</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;">${escapeHtml(program.participation_quantity ?? 'N/A')}</div>
                    </div>
                </div>
            </div>

            <div class="schol-schedule-card" style="margin-bottom:20px;">
                <h4 class="schol-schedule-title">Application Window Schedule</h4>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Start Date</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;">${escapeHtml(program.start_date)}</div>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">End Date</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;">${escapeHtml(program.end_date)}</div>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Status</label>
                        <span style="display:inline-flex;align-items:center;padding:8px 20px;border-radius:999px;font-size:13px;font-weight:700;text-transform:uppercase;background:${statusStyle.bg};color:${statusStyle.text};">${statusStyle.label}</span>
                    </div>
                </div>
            </div>

            <div class="schol-schedule-card">
                <h4 class="schol-schedule-title">Application Form Builder</h4>
                <div style="background:#fff;border-radius:8px;padding:20px;margin-bottom:20px;border:2px solid #e5e7eb;">
                    <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Announcement</label>
                    <div style="font-size:15px;color:#374151;padding:16px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;min-height:80px;white-space:pre-wrap;">${escapeHtml(program.announcement || 'No announcement set')}</div>
                </div>

                <div style="background:#f0f9ff;border:2px solid #0ea5e9;border-radius:12px;padding:20px;margin-bottom:20px;">
                    <h5 style="margin:0 0 16px;font-size:16px;font-weight:700;color:#0369a1;">Include KK Profiling Data</h5>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
                        ${Object.entries(KK_FIELD_LABELS).map(([value, label]) => `
                            <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid ${kkFields.includes(value) ? '#0ea5e9' : '#e2e8f0'};border-radius:6px;">
                                <input type="checkbox" ${kkFields.includes(value) ? 'checked' : ''} disabled style="width:18px;height:18px;">
                                <span>${label}</span>
                            </label>
                        `).join('')}
                    </div>
                </div>

                ${customQuestions.length ? `
                    <div style="background:#f8f9fa;border-radius:12px;padding:24px;border:2px solid #e5e7eb;">
                        ${customQuestions.map((question, index) => `
                            <div style="background:white;border-radius:8px;padding:24px;margin-bottom:16px;border:1px solid #e5e7eb;">
                                <div style="font-size:15px;color:#202124;font-weight:500;margin-bottom:10px;">
                                    ${index + 1}. ${escapeHtml(question.label)}
                                    ${question.required ? '<span style="color:#d93025;">*</span>' : ''}
                                </div>
                                <div style="font-size:13px;color:#5f6368;font-style:italic;">Type: ${escapeHtml(getTypeLabel(question.type))}</div>
                            </div>
                        `).join('')}
                    </div>
                ` : `
                    <div style="background:#fff3cd;border:2px solid #ffc107;border-radius:12px;padding:24px;text-align:center;">
                        <div style="font-size:16px;color:#856404;font-weight:600;">No custom questions added</div>
                    </div>
                `}
            </div>
        </div>
    `;

    viewProgramModal.style.display = 'flex';
}

function openDeleteProgramModal(programId) {
    const program = schedulePrograms.find((item) => String(item.id) === String(programId));
    pendingDeleteProgramId = programId;
    const deleteModal = document.getElementById('deleteProgramModal');
    const nameEl = document.getElementById('deleteProgramName');
    if (nameEl) {
        nameEl.textContent = program ? `"${program.program_name}"` : '';
    }
    if (deleteModal) deleteModal.style.display = 'flex';
}

function closeDeleteProgramModal() {
    pendingDeleteProgramId = null;
    const deleteModal = document.getElementById('deleteProgramModal');
    if (deleteModal) deleteModal.style.display = 'none';
}

async function confirmDeleteProgram() {
    if (!pendingDeleteProgramId) return;

    const confirmBtn = document.getElementById('deleteProgramConfirm');
    const defaultHtml = confirmBtn ? confirmBtn.innerHTML : 'Delete Program';

    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="schol-save-spinner"></span> Deleting...';
    }

    try {
        await apiFetch(`/api/schedule-programs/${pendingDeleteProgramId}`, { method: 'DELETE' });
        closeDeleteProgramModal();
        await loadPrograms();
        showToast('Program deleted successfully.', 'success');
    } catch (error) {
        showToast(error.message || 'Failed to delete program.', 'error');
    } finally {
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = defaultHtml;
        }
    }
}

async function handleSave() {
    const startDate = document.getElementById('schedStartDate')?.value?.trim();
    const endDate = document.getElementById('schedEndDate')?.value?.trim();
    const status = document.getElementById('programStatus')?.value || 'open';
    const participationQtyRaw = document.getElementById('participationQty')?.value?.trim();
    const announcement = document.getElementById('spfbAnnouncement')?.value?.trim() || '';

    if (!startDate || !endDate) {
        showToast('Please select start and end dates.', 'error');
        return;
    }

    let participationQuantity = null;
    if (participationQtyRaw !== '') {
        const qtyNum = parseInt(participationQtyRaw, 10);
        if (Number.isNaN(qtyNum) || qtyNum < 0) {
            showToast('Participation quantity cannot be negative.', 'error');
            return;
        }
        participationQuantity = qtyNum;
    }

    let customQuestions = [];
    if (window.SpfbFormBuilder && typeof window.SpfbFormBuilder.getQuestions === 'function') {
        customQuestions = window.SpfbFormBuilder.getQuestions();
    }

    const kkProfilingFields = [];
    document.querySelectorAll('.kk-profiling-field:checked').forEach((checkbox) => {
        kkProfilingFields.push(checkbox.value);
    });

    const payload = {
        start_date: startDate,
        end_date: endDate,
        status,
        participation_quantity: participationQuantity,
        announcement,
        kk_profiling_fields: kkProfilingFields,
        custom_questions: customQuestions,
    };

    setSaveButtonLoading(true);

    try {
        if (editingProgramId) {
            await apiFetch(`/api/schedule-programs/${editingProgramId}?letter=${PROGRAM_LETTER}`, {
                method: 'PUT',
                body: JSON.stringify({ ...payload, program_letter: PROGRAM_LETTER }),
            });
            showToast('Program updated successfully!', 'success');
        } else {
            await apiFetch(`/api/schedule-programs?letter=${PROGRAM_LETTER}`, {
                method: 'POST',
                body: JSON.stringify({ ...payload, program_letter: PROGRAM_LETTER }),
            });
            showToast('Program saved successfully!', 'success');
        }

        closeModal();
        await loadPrograms();
    } catch (error) {
        showToast(error.message || 'Failed to save program.', 'error');
    } finally {
        setSaveButtonLoading(false);
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const tableBody = document.getElementById('safFormsTableBody');
    if (!tableBody) return;

    const modal = document.getElementById('scholarProgramModal');
    const modalBox = document.getElementById('scholarProgramBox');
    const openBtn = document.getElementById('safOpenFormBtn');
    const closeBtn = document.getElementById('scholarProgramClose');
    const cancelBtn = document.getElementById('btnCancelProgram');
    const saveBtn = document.getElementById('btnSaveProgram');
    const maximizeBtn = document.getElementById('scholarProgramMaximize');

    if (openBtn) openBtn.addEventListener('click', () => openModal());
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (saveBtn) saveBtn.addEventListener('click', handleSave);

    if (modal) {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal();
        });
    }

    if (maximizeBtn && modalBox && modal) {
        maximizeBtn.addEventListener('click', (event) => {
            event.stopPropagation();
            modalBox.classList.toggle('schol-modal-maximized');
            modal.classList.toggle('schol-modal-maximized', modalBox.classList.contains('schol-modal-maximized'));
            const isMax = modalBox.classList.contains('schol-modal-maximized');
            maximizeBtn.textContent = isMax ? '⧉' : '□';
            maximizeBtn.title = isMax ? 'Restore Down' : 'Maximize';
        });
    }

    const selectAllKKBtn = document.getElementById('selectAllKKFields');
    const clearAllKKBtn = document.getElementById('clearAllKKFields');
    if (selectAllKKBtn) {
        selectAllKKBtn.addEventListener('click', () => {
            document.querySelectorAll('.kk-profiling-field').forEach((checkbox) => {
                checkbox.checked = true;
            });
        });
    }
    if (clearAllKKBtn) {
        clearAllKKBtn.addEventListener('click', () => {
            document.querySelectorAll('.kk-profiling-field').forEach((checkbox) => {
                checkbox.checked = false;
            });
        });
    }

    const deleteClose = document.getElementById('deleteProgramClose');
    const deleteCancel = document.getElementById('deleteProgramCancel');
    const deleteConfirm = document.getElementById('deleteProgramConfirm');
    const deleteModal = document.getElementById('deleteProgramModal');
    if (deleteClose) deleteClose.addEventListener('click', closeDeleteProgramModal);
    if (deleteCancel) deleteCancel.addEventListener('click', closeDeleteProgramModal);
    if (deleteConfirm) deleteConfirm.addEventListener('click', confirmDeleteProgram);
    if (deleteModal) {
        deleteModal.addEventListener('click', (event) => {
            if (event.target === deleteModal) closeDeleteProgramModal();
        });
    }

    const viewProgramClose = document.getElementById('viewProgramClose');
    const viewProgramModal = document.getElementById('viewProgramModal');
    if (viewProgramClose && viewProgramModal) {
        viewProgramClose.addEventListener('click', () => {
            viewProgramModal.style.display = 'none';
        });
        viewProgramModal.addEventListener('click', (event) => {
            if (event.target === viewProgramModal) {
                viewProgramModal.style.display = 'none';
            }
        });
    }

    try {
        if (typeof window.showLoading === 'function') window.showLoading();
        await loadProgramMeta();
        await loadPrograms();
    } catch (error) {
        showToast(error.message || 'Failed to load schedule programs.', 'error');
        tableBody.innerHTML = '<tr><td colspan="6" class="saf-table-empty">Unable to load schedule programs.</td></tr>';
    } finally {
        if (typeof window.hideLoading === 'function') window.hideLoading();
    }
});

window.editSportsProgram = editProgram;
