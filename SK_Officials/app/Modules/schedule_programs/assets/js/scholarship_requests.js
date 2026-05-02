document.addEventListener('DOMContentLoaded', () => {
    initScholarshipRequests();
});

// ── Sample Data ────────────────────────────────────────────────────────────
const SAMPLE_DATA = [
    {
        id: 1001,
        last_name: 'Reyes', first_name: 'Maria', middle_name: 'Santos',
        date_of_birth: '2005-03-14', gender: 'Female', age: 20,
        contact_no: '09171234567',
        address: '123 Sampaguita St., Brgy. Calios, Santa Cruz, Laguna',
        email: 'maria.reyes@email.com',
        school_name: 'Laguna State Polytechnic University',
        school_address: 'Siniloan, Laguna',
        year_level: '2nd Year', program_strand: 'BSED',
        purpose: 'Tuition Fees, Books / Equipments',
        purpose_list: ['Tuition Fees', 'Books / Equipments'],
        purpose_others: '',
        cor_certified: true, photo_id: true,
        status: 'Approved',
        submitted_at: 'Jan 10, 2025',
        approved_at: 'Jan 15, 2025',
        result: 'Passed',
    },
    {
        id: 1002,
        last_name: 'Cruz', first_name: 'Juan', middle_name: 'Dela',
        date_of_birth: '2004-07-22', gender: 'Male', age: 21,
        contact_no: '09281234567',
        address: '45 Rizal Ave., Brgy. Calios, Santa Cruz, Laguna',
        email: 'juan.cruz@email.com',
        school_name: 'University of the Philippines Los Baños',
        school_address: 'College, Los Baños, Laguna',
        year_level: '3rd Year', program_strand: 'BS Agriculture',
        purpose: 'Tuition Fees, Living Expenses',
        purpose_list: ['Tuition Fees', 'Living Expenses'],
        purpose_others: '',
        cor_certified: true, photo_id: false,
        status: 'Pending',
        submitted_at: 'Feb 3, 2025',
    },
    {
        id: 1003,
        last_name: 'Garcia', first_name: 'Ana', middle_name: 'Lim',
        date_of_birth: '2006-11-05', gender: 'Female', age: 18,
        contact_no: '09391234567',
        address: '78 Mabini St., Brgy. Calios, Santa Cruz, Laguna',
        email: 'ana.garcia@email.com',
        school_name: 'Santa Cruz National High School',
        school_address: 'Santa Cruz, Laguna',
        year_level: 'Grade 12', program_strand: 'STEM',
        purpose: 'Books / Equipments',
        purpose_list: ['Books / Equipments'],
        purpose_others: '',
        cor_certified: false, photo_id: true,
        status: 'Pending',
        submitted_at: 'Feb 15, 2025',
    },
    {
        id: 1004,
        last_name: 'Mendoza', first_name: 'Carlo', middle_name: 'Bautista',
        date_of_birth: '2003-05-18', gender: 'Male', age: 22,
        contact_no: '09501234567',
        address: '12 Bonifacio Rd., Brgy. Calios, Santa Cruz, Laguna',
        email: 'carlo.mendoza@email.com',
        school_name: 'Laguna College of Business and Arts',
        school_address: 'Calamba, Laguna',
        year_level: '4th Year', program_strand: 'BSBA',
        purpose: 'Tuition Fees, Living Expenses, Others (Transportation)',
        purpose_list: ['Tuition Fees', 'Living Expenses', 'Others'],
        purpose_others: 'Transportation',
        cor_certified: true, photo_id: true,
        status: 'Rejected',
        submitted_at: 'Mar 1, 2025',
    },
    {
        id: 1005,
        last_name: 'Torres', first_name: 'Liza', middle_name: 'Villanueva',
        date_of_birth: '2007-09-30', gender: 'Female', age: 17,
        contact_no: '09611234567',
        address: '56 Aguinaldo St., Brgy. Calios, Santa Cruz, Laguna',
        email: 'liza.torres@email.com',
        school_name: 'Calios Elementary School',
        school_address: 'Brgy. Calios, Santa Cruz, Laguna',
        year_level: 'Grade 10', program_strand: '',
        purpose: 'Books / Equipments, Living Expenses',
        purpose_list: ['Books / Equipments', 'Living Expenses'],
        purpose_others: '',
        cor_certified: false, photo_id: false,
        status: 'Pending',
        submitted_at: 'Apr 5, 2025',
    },
    // ── 2 additional Passed scholars ──────────────────────────────────────
    {
        id: 1006,
        last_name: 'Dela Cruz', first_name: 'Jose', middle_name: 'Ramos',
        date_of_birth: '2004-11-20', gender: 'Male', age: 21,
        contact_no: '09721234567',
        address: '88 Magsaysay St., Brgy. Calios, Santa Cruz, Laguna',
        email: 'jose.delacruz@email.com',
        school_name: 'Laguna State Polytechnic University',
        school_address: 'Siniloan, Laguna',
        year_level: '3rd Year', program_strand: 'BSIT',
        purpose: 'Tuition Fees, Living Expenses',
        purpose_list: ['Tuition Fees', 'Living Expenses'],
        purpose_others: '',
        cor_certified: true, photo_id: true,
        status: 'Approved',
        submitted_at: 'Jan 20, 2025',
        approved_at: 'Jan 25, 2025',
        result: 'Passed',
    },
    {
        id: 1007,
        last_name: 'Bautista', first_name: 'Kristine', middle_name: 'Flores',
        date_of_birth: '2005-06-08', gender: 'Female', age: 20,
        contact_no: '09831234567',
        address: '14 Quezon Blvd., Brgy. Calios, Santa Cruz, Laguna',
        email: 'kristine.bautista@email.com',
        school_name: 'De La Salle University – Dasmariñas',
        school_address: 'Dasmariñas, Cavite',
        year_level: '2nd Year', program_strand: 'BSN',
        purpose: 'Tuition Fees, Books / Equipments',
        purpose_list: ['Tuition Fees', 'Books / Equipments'],
        purpose_others: '',
        cor_certified: true, photo_id: true,
        status: 'Approved',
        submitted_at: 'Feb 5, 2025',
        approved_at: 'Feb 10, 2025',
        result: 'Passed',
    },
];

function initScholarshipRequests() {
    // Seed sample data if localStorage is empty
    if (!localStorage.getItem('scholarship_requests_seeded_v3')) {
        localStorage.setItem('scholarship_requests', JSON.stringify(SAMPLE_DATA));
        localStorage.setItem('scholarship_requests_seeded_v3', '1');
    }

    let records = JSON.parse(localStorage.getItem('scholarship_requests') || '[]');
    let deleteTargetId = null;
    let viewTargetId   = null;

    const tbody         = document.getElementById('scholTableBody');
    const searchInput   = document.getElementById('scholSearch');
    const statusFilter  = document.getElementById('scholStatusFilter');
    const viewModal     = document.getElementById('scholViewModal');
    const viewBody      = document.getElementById('scholViewBody');
    const viewClose     = document.getElementById('scholViewClose');
    const viewCloseFooter = document.getElementById('scholViewCloseFooter');
    const approveBtn    = document.getElementById('scholApproveBtn');
    const rejectBtn     = document.getElementById('scholRejectBtn');
    const deleteModal   = document.getElementById('scholDeleteModal');
    const deleteClose   = document.getElementById('scholDeleteClose');
    const deleteCancel  = document.getElementById('scholDeleteCancel');
    const deleteConfirm = document.getElementById('scholDeleteConfirm');
    const makeFormBtn   = document.getElementById('btnMakeForm');
    const makeFormModal = document.getElementById('makeFormModal');
    const makeFormClose = document.getElementById('makeFormClose');
    const makeFormCloseFooter = document.getElementById('makeFormCloseFooter');

    let filterSearch = '';
    let filterStatus = '';

    // ── Make Form modal ─────────────────────────────────────────────────────
    if (makeFormBtn) makeFormBtn.addEventListener('click', () => {
        makeFormModal.style.display = 'flex';
        initUploadFields();
    });
    [makeFormClose, makeFormCloseFooter].forEach(btn => {
        if (btn) btn.addEventListener('click', () => { makeFormModal.style.display = 'none'; });
    });
    if (makeFormModal) makeFormModal.addEventListener('click', e => { if (e.target === makeFormModal) makeFormModal.style.display = 'none'; });

    // ── Picture Here uploader ────────────────────────────────────────────────
    let uploadFieldsInited = false;
    function initUploadFields() {
        if (uploadFieldsInited) return;
        uploadFieldsInited = true;

        // Picture Here
        const picBox   = document.getElementById('pictureUploadBox');
        const picInput = document.getElementById('pictureUploadInput');
        const picImg   = document.getElementById('picturePreviewImg');
        const picPlaceholder = document.getElementById('pictureUploadPlaceholder');

        if (picBox && picInput) {
            picBox.addEventListener('click', () => picInput.click());
            picInput.addEventListener('change', () => {
                const file = picInput.files[0];
                if (!file) return;
                if (!file.type.startsWith('image/')) {
                    showScholToast('Please select an image file.', 'error'); return;
                }
                const reader = new FileReader();
                reader.onload = e => {
                    picImg.src = e.target.result;
                    picImg.style.display = 'block';
                    picPlaceholder.style.display = 'none';
                    picBox.style.border = '2px solid #2c2c3e';
                };
                reader.readAsDataURL(file);
            });
        }

        // File upload helper
        function initFileUpload(dropId, inputId, previewId, innerDivId) {
            const drop    = document.getElementById(dropId);
            const input   = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            const inner   = document.getElementById(innerDivId);
            if (!drop || !input) return;

            const MAX_MB = 10;

            drop.addEventListener('click', () => input.click());

            drop.addEventListener('dragover', e => { e.preventDefault(); drop.classList.add('drag-over'); });
            drop.addEventListener('dragleave', () => drop.classList.remove('drag-over'));
            drop.addEventListener('drop', e => {
                e.preventDefault(); drop.classList.remove('drag-over');
                const file = e.dataTransfer.files[0];
                if (file) handleFile(file);
            });

            input.addEventListener('change', () => {
                if (input.files[0]) handleFile(input.files[0]);
            });

            function handleFile(file) {
                const allowed = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
                if (!allowed.includes(file.type)) {
                    showScholToast('Only PDF or image files are allowed.', 'error'); return;
                }
                if (file.size > MAX_MB * 1024 * 1024) {
                    showScholToast(`File must be under ${MAX_MB}MB.`, 'error'); return;
                }

                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                const isPdf  = file.type === 'application/pdf';

                inner.style.display = 'none';
                preview.style.display = 'flex';
                preview.innerHTML = `
                    <div class="schol-upload-preview-icon">
                        ${isPdf
                            ? `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>`
                            : `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>`
                        }
                    </div>
                    <div class="schol-upload-preview-info">
                        <div class="schol-upload-preview-name">${file.name}</div>
                        <div class="schol-upload-preview-size">${sizeMB} MB</div>
                    </div>
                    <button type="button" class="schol-upload-preview-remove" title="Remove">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                `;
                preview.querySelector('.schol-upload-preview-remove').addEventListener('click', e => {
                    e.stopPropagation();
                    input.value = '';
                    preview.style.display = 'none';
                    preview.innerHTML = '';
                    inner.style.display = 'flex';
                });
            }
        }

        initFileUpload('corUploadDrop',     'corUploadInput',     'corPreview',     'corDropInner');
        initFileUpload('photoIdUploadDrop', 'photoIdUploadInput', 'photoIdPreview', 'photoIdDropInner');
    }

    // ── Save Schedule ────────────────────────────────────────────────────────
    const btnSaveSchedule = document.getElementById('btnSaveSchedule');
    if (btnSaveSchedule) {
        btnSaveSchedule.addEventListener('click', () => {
            const openDate  = document.getElementById('schedOpenDate').value;
            const openTime  = document.getElementById('schedOpenTime').value;
            const closeDate = document.getElementById('schedCloseDate').value;
            const closeTime = document.getElementById('schedCloseTime').value;

            if (!openDate || !closeDate) {
                showScholToast('Please set both open and close dates.', 'error');
                return;
            }
            if (closeDate < openDate || (closeDate === openDate && closeTime <= openTime)) {
                showScholToast('Close date/time must be after open date/time.', 'error');
                return;
            }

            const schedule = { openDate, openTime, closeDate, closeTime };
            localStorage.setItem('scholarship_schedule', JSON.stringify(schedule));

            // Show status badge
            const badge = document.getElementById('schedStatusBadge');
            if (badge) {
                const fmt = d => new Date(d + 'T00:00:00').toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
                badge.innerHTML = `<span class="schol-sched-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Scheduled: ${fmt(openDate)} ${openTime} → ${fmt(closeDate)} ${closeTime}
                </span>`;
                badge.style.display = 'block';
            }

            showScholToast('Schedule saved successfully!');
        });
    }

    // ── Render ──────────────────────────────────────────────────────────────
    function render() {
        const filtered = records.filter(r => {
            const name   = `${r.last_name} ${r.first_name}`.toLowerCase();
            const school = (r.school_name || '').toLowerCase();
            const q      = filterSearch.toLowerCase();
            return (!filterSearch || name.includes(q) || school.includes(q))
                && (!filterStatus || r.status === filterStatus);
        });

        tbody.innerHTML = '';

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr class="schol-empty-row"><td colspan="9">No applications found.</td></tr>`;
        } else {
            filtered.forEach((r, i) => {
                const statusCls = r.status === 'Approved' ? 'schol-pill-approved'
                                : r.status === 'Rejected' ? 'schol-pill-rejected'
                                : 'schol-pill-pending';
                const corIcon   = r.cor_certified ? '✅' : '❌';
                const photoIcon = r.photo_id      ? '✅' : '❌';
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${i + 1}</td>
                    <td style="text-align:left;font-weight:600;">${r.last_name}, ${r.first_name}${r.middle_name ? ' ' + r.middle_name.charAt(0) + '.' : ''}</td>
                    <td style="text-align:left;font-size:12px;">${r.school_name || '—'}</td>
                    <td>${r.year_level || '—'}</td>
                    <td style="text-align:left;font-size:12px;">${r.purpose || '—'}</td>
                    <td style="font-size:12px;">
                        <div style="display:flex;flex-direction:column;gap:2px;align-items:center;">
                            <span title="COR">${corIcon} COR</span>
                            <span title="Photo ID">${photoIcon} Photo ID</span>
                        </div>
                    </td>
                    <td><span class="schol-pill ${statusCls}">${r.status}</span></td>
                    <td>${r.submitted_at || '—'}</td>
                    <td>
                        <div class="schol-tbl-actions">
                            <button class="schol-tbl-btn schol-tbl-btn-view" data-action="view" data-id="${r.id}">View</button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        updateStats();
    }

    function updateStats() {
        document.getElementById('statTotal').textContent    = records.length;
        document.getElementById('statPending').textContent  = records.filter(r => r.status === 'Pending').length;
        document.getElementById('statApproved').textContent = records.filter(r => r.status === 'Approved').length;
        document.getElementById('statRejected').textContent = records.filter(r => r.status === 'Rejected').length;
    }

    function save() { localStorage.setItem('scholarship_requests', JSON.stringify(records)); }

    // ── Table click ─────────────────────────────────────────────────────────
    tbody.addEventListener('click', e => {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const action = btn.getAttribute('data-action');
        const id     = parseInt(btn.getAttribute('data-id'), 10);
        const record = records.find(r => r.id === id);
        if (!record) return;

        if (action === 'view') { viewTargetId = id; openViewModal(record); }
        else if (action === 'delete') { deleteTargetId = id; deleteModal.style.display = 'flex'; }
    });

    // ── View modal — exact PDF inline layout with filled data ──────────────
    function openViewModal(r) {
        const allPurposes = ['Tuition Fees', 'Books/Equipments', 'Living Expenses', 'Others'];
        const purposeList = r.purpose_list || [];

        const purposeHTML = allPurposes.map(p => {
            const checked = purposeList.some(v => v.toLowerCase().replace(/\s/g,'') === p.toLowerCase().replace(/\s/g,''));
            const extra = (p === 'Others' && r.purpose_others) ? ` (${r.purpose_others})` : '';
            return `<div class="schol-pdf-check-item">
                <span class="schol-pdf-checkbox ${checked ? 'schol-pdf-checked' : ''}"></span>
                ${p}${extra}
            </div>`;
        }).join('');

        const statusCls = r.status === 'Approved' ? 'schol-pill-approved'
                        : r.status === 'Rejected' ? 'schol-pill-rejected'
                        : 'schol-pill-pending';

        const f = (val, w) => `<span class="schol-pdf-inline-filled" style="min-width:${w||80}px;">${val||'—'}</span>`;

        viewBody.innerHTML = `
            <div class="schol-pdf-form">

                <div class="schol-pdf-header">
                    <img src="/images/barangay_logo.png" alt="Barangay Calios" class="schol-pdf-logo-img">
                    <h2 class="schol-pdf-title">SCHOLARSHIP APPLICATION FORM</h2>
                    <div style="display:flex;flex-direction:column;align-items:center;gap:6px;">
                        <div class="schol-pdf-picture-box"><span>Picture<br>Here</span></div>
                        <span class="schol-pill ${statusCls}" style="font-size:10px;">${r.status}</span>
                    </div>
                </div>

                <div class="schol-pdf-section">
                    <p class="schol-pdf-inline-title">APPLICANT'S PERSONAL INFORMATION:</p>
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">Last Name:</span>${f(r.last_name,110)}
                        <span class="schol-pdf-inline-label">First Name:</span>${f(r.first_name,110)}
                        <span class="schol-pdf-inline-label">Middle Name:</span>${f(r.middle_name,100)}
                    </div>
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">Date of Birth:</span>${f(r.date_of_birth,90)}
                        <span class="schol-pdf-inline-label">Gender:</span>${f(r.gender,70)}
                        <span class="schol-pdf-inline-label">Age:</span>${f(r.age,40)}
                        <span class="schol-pdf-inline-label">Contact No:</span>${f(r.contact_no,110)}
                    </div>
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">Complete Address:</span>
                        <span class="schol-pdf-inline-filled" style="flex:1;">${r.address||'—'}</span>
                    </div>
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">Email Address:</span>
                        <span class="schol-pdf-inline-filled" style="min-width:220px;">${r.email||'—'}</span>
                    </div>
                </div>

                <div class="schol-pdf-section">
                    <p class="schol-pdf-inline-title">ACADEMIC INFORMATION:</p>
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">Name of School:</span>
                        <span class="schol-pdf-inline-filled" style="flex:1;">${r.school_name||'—'}</span>
                    </div>
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">School Address:</span>
                        <span class="schol-pdf-inline-filled" style="flex:1;">${r.school_address||'—'}</span>
                    </div>
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">Year/Grade Level:</span>${f(r.year_level,120)}
                        <span class="schol-pdf-inline-label" style="margin-left:16px;">Program/Strand:</span>${f(r.program_strand,120)}
                    </div>
                </div>

                <div class="schol-pdf-section schol-pdf-bottom-section">
                    <div class="schol-pdf-bottom-left">
                        <p class="schol-pdf-inline-title">SCHOLARSHIP INFORMATION:</p>
                        <p class="schol-pdf-purpose-label">Purpose of Scholarship:</p>
                        <div class="schol-pdf-check-list">${purposeHTML}</div>
                    </div>
                    <div class="schol-pdf-bottom-right">
                        <p class="schol-pdf-inline-title">SUBMITTED REQUIRMENTS: <span style="font-weight:400;font-size:10px;">Note: To be filled out by sk officials</span></p>
                        <div class="schol-pdf-check-list" style="margin-top:8px;">
                            <div class="schol-pdf-check-item">
                                <span class="schol-pdf-checkbox ${r.cor_certified ? 'schol-pdf-checked' : ''}"></span>
                                COR-CERTIFIED TRUE COPY
                            </div>
                            <div class="schol-pdf-check-item">
                                <span class="schol-pdf-checkbox ${r.photo_id ? 'schol-pdf-checked' : ''}"></span>
                                PHOTO COPY OF ID (FRONT AND BACK)
                            </div>
                        </div>
                    </div>
                </div>

                <div class="schol-pdf-sig-section">
                    <div class="schol-pdf-sig-line"></div>
                    <p class="schol-pdf-sig-label">${r.first_name} ${r.middle_name ? r.middle_name + ' ' : ''}${r.last_name}</p>
                </div>

            </div>
        `;
        viewModal.style.display = 'flex';
    }

    function closeViewModal() {
        viewModal.style.display = 'none';
        viewTargetId = null;
        viewModal.classList.remove('schol-modal-maximized');
        const vBox = document.getElementById('scholViewBox');
        if (vBox) vBox.classList.remove('schol-modal-maximized');
        const maxBtn = document.getElementById('scholViewMaximize');
        if (maxBtn) maxBtn.textContent = '□';
    }
    [viewClose].forEach(btn => { if (btn) btn.addEventListener('click', closeViewModal); });
    viewModal.addEventListener('click', e => { if (e.target === viewModal) closeViewModal(); });

    // Maximize / restore for scholViewModal
    const scholViewMaxBtn = document.getElementById('scholViewMaximize');
    const scholViewBox    = document.getElementById('scholViewBox');
    if (scholViewMaxBtn && scholViewBox) {
        scholViewMaxBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isMax = !scholViewBox.classList.contains('schol-modal-maximized');
            viewModal.classList.toggle('schol-modal-maximized', isMax);
            scholViewBox.classList.toggle('schol-modal-maximized', isMax);
            scholViewMaxBtn.textContent = isMax ? '⧉' : '□';
        });
    }

    // Approve / Reject
    if (approveBtn) {
        approveBtn.addEventListener('click', () => {
            if (!viewTargetId) return;
            const idx = records.findIndex(r => r.id === viewTargetId);
            if (idx !== -1) {
                records[idx].status = 'Approved';
                if (!records[idx].result) records[idx].result = 'Defined';
                if (!records[idx].approved_at) records[idx].approved_at = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                save(); render();
            }
            closeViewModal();
            showScholToast('Application approved!');
        });
    }
    if (rejectBtn) {
        rejectBtn.addEventListener('click', () => {
            if (!viewTargetId) return;
            const idx = records.findIndex(r => r.id === viewTargetId);
            if (idx !== -1) { records[idx].status = 'Rejected'; save(); render(); }
            closeViewModal();
            showScholToast('Application rejected.', 'error');
        });
    }

    // ── Delete modal ─────────────────────────────────────────────────────────
    [deleteClose, deleteCancel].forEach(btn => {
        if (btn) btn.addEventListener('click', () => { deleteModal.style.display = 'none'; deleteTargetId = null; });
    });
    deleteModal.addEventListener('click', e => { if (e.target === deleteModal) { deleteModal.style.display = 'none'; deleteTargetId = null; } });
    if (deleteConfirm) {
        deleteConfirm.addEventListener('click', () => {
            records = records.filter(r => r.id !== deleteTargetId);
            save(); render();
            deleteModal.style.display = 'none';
            deleteTargetId = null;
            showScholToast('Application deleted.');
        });
    }

    // ── Filters ──────────────────────────────────────────────────────────────
    if (searchInput) searchInput.addEventListener('input', () => { filterSearch = searchInput.value.trim(); render(); });
    if (statusFilter) statusFilter.addEventListener('change', () => { filterStatus = statusFilter.value; render(); });

    render();
}

function showScholToast(msg, type = 'success') {
    const toast = document.getElementById('scholToast');
    const msgEl = document.getElementById('scholToastMsg');
    if (!toast) return;
    if (msgEl) msgEl.textContent = msg;
    toast.className = 'schol-toast schol-toast-show' + (type === 'error' ? ' schol-toast-error' : '');
    toast.style.display = 'flex';
    setTimeout(() => {
        toast.classList.remove('schol-toast-show');
        setTimeout(() => { toast.style.display = 'none'; }, 300);
    }, 3000);
}
