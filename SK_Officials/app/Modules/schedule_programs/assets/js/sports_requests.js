document.addEventListener('DOMContentLoaded', () => {
    initSportsRequests();
});

// ── Sample Data ────────────────────────────────────────────────────────────
const SAMPLE_SPORTS_DATA = [
    {
        id: 2001,
        lastName: 'Dela Cruz',
        firstName: 'Juan',
        middleName: 'Santos',
        suffix: '',
        dateOfBirth: '2003-05-15',
        age: 22,
        contact: '09171234567',
        email: 'juan.delacruz@email.com',
        address: '123 Main St., Brgy. Calios, Santa Cruz, Laguna',
        sport: 'Basketball',
        division: 'Youth Division (18-21)',
        dateApplied: 'Apr 28, 2026',
        requirements: {
            birthCertificate: true,
            validId: true,
            medicalCertificate: true,
            parentConsent: false
        },
        status: 'Pending',
        paymentStatus: null,
        rejectionReasons: []
    },
    {
        id: 2002,
        lastName: 'Santos',
        firstName: 'Maria',
        middleName: 'Reyes',
        suffix: '',
        dateOfBirth: '2001-08-22',
        age: 24,
        contact: '09281234567',
        email: 'maria.santos@email.com',
        address: '456 Rizal Ave., Brgy. Calios, Santa Cruz, Laguna',
        sport: 'Volleyball',
        division: 'Young Adult (22-25)',
        dateApplied: 'Apr 29, 2026',
        requirements: {
            birthCertificate: true,
            validId: true,
            medicalCertificate: false,
            parentConsent: false
        },
        status: 'Pending',
        paymentStatus: null,
        rejectionReasons: []
    },
    {
        id: 2003,
        lastName: 'Reyes',
        firstName: 'Pedro',
        middleName: 'Garcia',
        suffix: 'Jr.',
        dateOfBirth: '2008-03-10',
        age: 18,
        contact: '09391234567',
        email: 'pedro.reyes@email.com',
        address: '789 Bonifacio St., Brgy. Calios, Santa Cruz, Laguna',
        sport: 'Basketball',
        division: 'Cadet Division (15-17)',
        dateApplied: 'Apr 30, 2026',
        requirements: {
            birthCertificate: true,
            validId: true,
            medicalCertificate: true,
            parentConsent: true
        },
        status: 'Pending',
        paymentStatus: null,
        rejectionReasons: []
    },
    {
        id: 2004,
        lastName: 'Lim',
        firstName: 'Ana',
        middleName: 'Cruz',
        suffix: '',
        dateOfBirth: '2004-11-18',
        age: 21,
        contact: '09501234567',
        email: 'ana.lim@email.com',
        address: '321 Mabini St., Brgy. Calios, Santa Cruz, Laguna',
        sport: 'Volleyball',
        division: 'Youth Division (18-21)',
        dateApplied: 'May 1, 2026',
        requirements: {
            birthCertificate: true,
            validId: true,
            medicalCertificate: true,
            parentConsent: false
        },
        status: 'Pending',
        paymentStatus: null,
        rejectionReasons: []
    },
    {
        id: 2005,
        lastName: 'Garcia',
        firstName: 'Carlos',
        middleName: 'Mendoza',
        suffix: '',
        dateOfBirth: '1996-07-05',
        age: 29,
        contact: '09611234567',
        email: 'carlos.garcia@email.com',
        address: '654 Quezon Blvd., Brgy. Calios, Santa Cruz, Laguna',
        sport: 'Basketball',
        division: 'Senior Division (26-30)',
        dateApplied: 'May 2, 2026',
        requirements: {
            birthCertificate: true,
            validId: true,
            medicalCertificate: true,
            parentConsent: false
        },
        status: 'Pending',
        paymentStatus: null,
        rejectionReasons: []
    },
    {
        id: 2006,
        lastName: 'Mendoza',
        firstName: 'Sofia',
        middleName: 'Torres',
        suffix: '',
        dateOfBirth: '2009-02-14',
        age: 17,
        contact: '09721234567',
        email: 'sofia.mendoza@email.com',
        address: '987 Luna St., Brgy. Calios, Santa Cruz, Laguna',
        sport: 'Volleyball',
        division: 'Cadet Division (15-17)',
        dateApplied: 'May 3, 2026',
        requirements: {
            birthCertificate: true,
            validId: false,
            medicalCertificate: true,
            parentConsent: true
        },
        status: 'Pending',
        paymentStatus: null,
        rejectionReasons: []
    },
    {
        id: 2007,
        lastName: 'Torres',
        firstName: 'Miguel',
        middleName: 'Bautista',
        suffix: '',
        dateOfBirth: '2002-09-30',
        age: 23,
        contact: '09831234567',
        email: 'miguel.torres@email.com',
        address: '147 Aguinaldo Ave., Brgy. Calios, Santa Cruz, Laguna',
        sport: 'Basketball',
        division: 'Young Adult (22-25)',
        dateApplied: 'May 4, 2026',
        requirements: {
            birthCertificate: true,
            validId: true,
            medicalCertificate: true,
            parentConsent: false
        },
        status: 'Pending',
        paymentStatus: null,
        rejectionReasons: []
    },
    {
        id: 2008,
        lastName: 'Cruz',
        firstName: 'Isabella',
        middleName: 'Villanueva',
        suffix: '',
        dateOfBirth: '2005-12-08',
        age: 20,
        contact: '09941234567',
        email: 'isabella.cruz@email.com',
        address: '258 Del Pilar St., Brgy. Calios, Santa Cruz, Laguna',
        sport: 'Volleyball',
        division: 'Youth Division (18-21)',
        dateApplied: 'May 5, 2026',
        requirements: {
            birthCertificate: true,
            validId: true,
            medicalCertificate: true,
            parentConsent: false
        },
        status: 'Pending',
        paymentStatus: null,
        rejectionReasons: []
    }
];

function initSportsRequests() {
    // Seed sample data if localStorage is empty
    if (!localStorage.getItem('sports_applications_seeded_v1')) {
        localStorage.setItem('sports_applications', JSON.stringify(SAMPLE_SPORTS_DATA));
        localStorage.setItem('sports_applications_seeded_v1', '1');
    }

    let applications = JSON.parse(localStorage.getItem('sports_applications') || '[]');
    let currentApplicationId = null;

    const tbody = document.getElementById('sportsTableBody');
    const searchInput = document.getElementById('sportsSearch');
    const filterSport = document.getElementById('filterSport');
    const filterDivision = document.getElementById('filterDivision');
    const filterStatus = document.getElementById('filterStatus');
    
    const createProgramModal = document.getElementById('createProgramModal');
    const createProgramClose = document.getElementById('createProgramClose');
    const btnCreateProgram = document.getElementById('btnCreateProgram');
    const programCancelBtn = document.getElementById('programCancelBtn');
    const programSaveBtn = document.getElementById('programSaveBtn');
    
    const viewModal = document.getElementById('viewModal');
    const viewModalBody = document.getElementById('viewModalBody');
    const viewClose = document.getElementById('viewClose');
    const btnApprove = document.getElementById('btnApprove');
    const btnReject = document.getElementById('btnReject');
    
    const rejectReasonModal = document.getElementById('rejectReasonModal');
    const rejectReasonClose = document.getElementById('rejectReasonClose');
    const rejectReasonCancel = document.getElementById('rejectReasonCancel');
    const rejectReasonConfirm = document.getElementById('rejectReasonConfirm');
    const rejectReasonOtherCheckbox = document.getElementById('rejectReasonOtherCheckbox');
    const rejectReasonOtherField = document.getElementById('rejectReasonOtherField');
    const rejectReasonOtherText = document.getElementById('rejectReasonOtherText');

    let filterSearchText = '';
    let filterSportValue = '';
    let filterDivisionValue = '';
    let filterStatusValue = '';

    // ── Initialize ──────────────────────────────────────────────────────────
    renderTable();
    updateStats();

    // ── Event Listeners ─────────────────────────────────────────────────────
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            filterSearchText = e.target.value.toLowerCase();
            renderTable();
        });
    }

    if (filterSport) {
        filterSport.addEventListener('change', (e) => {
            filterSportValue = e.target.value;
            renderTable();
        });
    }

    if (filterDivision) {
        filterDivision.addEventListener('change', (e) => {
            filterDivisionValue = e.target.value;
            renderTable();
        });
    }

    if (filterStatus) {
        filterStatus.addEventListener('change', (e) => {
            filterStatusValue = e.target.value;
            renderTable();
        });
    }

    // Create Program Modal — open is now handled inside the question builder block above
    // (btnCreateProgram listener is wired in the question builder section)

    [createProgramClose, programCancelBtn].forEach(btn => {
        if (btn) btn.addEventListener('click', closeCreateProgramModal);
    });

    if (createProgramModal) {
        createProgramModal.addEventListener('click', (e) => {
            if (e.target === createProgramModal) closeCreateProgramModal();
        });
    }

    if (programSaveBtn) {
        programSaveBtn.addEventListener('click', handleCreateProgram);
    }

    // View Modal
    if (viewClose) {
        viewClose.addEventListener('click', closeViewModal);
    }

    if (viewModal) {
        viewModal.addEventListener('click', (e) => {
            if (e.target === viewModal) closeViewModal();
        });
    }

    if (btnApprove) {
        btnApprove.addEventListener('click', handleApprove);
    }

    if (btnReject) {
        btnReject.addEventListener('click', handleReject);
    }

    // Maximize button for view modal
    const viewMaximize = document.getElementById('viewMaximize');
    const viewBox = document.getElementById('viewBox');
    if (viewMaximize && viewBox) {
        viewMaximize.addEventListener('click', (e) => {
            e.stopPropagation();
            const isMax = !viewBox.classList.contains('sports-modal-maximized');
            viewModal.classList.toggle('sports-modal-maximized', isMax);
            viewBox.classList.toggle('sports-modal-maximized', isMax);
            viewMaximize.textContent = isMax ? '⧉' : '□';
        });
    }

    // Rejection Reason Modal
    if (rejectReasonOtherCheckbox && rejectReasonOtherField) {
        rejectReasonOtherCheckbox.addEventListener('change', () => {
            rejectReasonOtherField.style.display = rejectReasonOtherCheckbox.checked ? 'block' : 'none';
            if (!rejectReasonOtherCheckbox.checked) {
                rejectReasonOtherText.value = '';
            }
        });
    }

    [rejectReasonClose, rejectReasonCancel].forEach(btn => {
        if (btn) btn.addEventListener('click', closeRejectReasonModal);
    });

    if (rejectReasonModal) {
        rejectReasonModal.addEventListener('click', (e) => {
            if (e.target === rejectReasonModal) closeRejectReasonModal();
        });
    }

    if (rejectReasonConfirm) {
        rejectReasonConfirm.addEventListener('click', confirmReject);
    }

    // ── Functions ───────────────────────────────────────────────────────────
    function renderTable() {
        const filtered = getFilteredApplications();
        
        if (filtered.length === 0) {
            tbody.innerHTML = '<tr class="sports-empty-row"><td colspan="7">No applications found.</td></tr>';
            return;
        }

        tbody.innerHTML = filtered.map(app => {
            const fullName = formatFullName(app);
            const statusBadge = getStatusBadge(app.status);

            return `
                <tr>
                    <td style="font-weight:600;">${fullName}</td>
                    <td>${app.sport}</td>
                    <td>${app.division}</td>
                    <td>${app.contact}</td>
                    <td>${app.dateApplied}</td>
                    <td>${statusBadge}</td>
                    <td class="col-actions">
                        <button class="sports-tbl-btn sports-tbl-btn-view" data-id="${app.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            View
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        // Attach event listeners to view buttons
        tbody.querySelectorAll('.sports-tbl-btn-view').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = parseInt(btn.getAttribute('data-id'), 10);
                openViewModal(id);
            });
        });
    }

    function getFilteredApplications() {
        return applications.filter(app => {
            // Search filter
            if (filterSearchText) {
                const fullName = formatFullName(app).toLowerCase();
                const searchMatch = fullName.includes(filterSearchText) ||
                                  app.sport.toLowerCase().includes(filterSearchText) ||
                                  app.division.toLowerCase().includes(filterSearchText);
                if (!searchMatch) return false;
            }

            // Sport filter
            if (filterSportValue && app.sport !== filterSportValue) return false;

            // Division filter
            if (filterDivisionValue && app.division !== filterDivisionValue) return false;

            // Status filter
            if (filterStatusValue && app.status !== filterStatusValue) return false;

            return true;
        });
    }

    function formatFullName(app) {
        let name = `${app.lastName}, ${app.firstName}`;
        if (app.middleName) name += ` ${app.middleName.charAt(0)}.`;
        if (app.suffix) name += ` ${app.suffix}`;
        return name;
    }

    function getStatusBadge(status) {
        const badges = {
            'Pending': '<span class="sports-badge sports-badge-warning">Pending</span>',
            'Approved': '<span class="sports-badge sports-badge-success">Approved</span>',
            'Rejected': '<span class="sports-badge sports-badge-danger">Rejected</span>'
        };
        return badges[status] || badges['Pending'];
    }

    function updateStats() {
        const total = applications.length;
        const pending = applications.filter(a => a.status === 'Pending').length;
        const approved = applications.filter(a => a.status === 'Approved').length;
        const rejected = applications.filter(a => a.status === 'Rejected').length;

        document.getElementById('statTotal').textContent = total;
        document.getElementById('statPending').textContent = pending;
        document.getElementById('statApproved').textContent = approved;
        document.getElementById('statRejected').textContent = rejected;
    }

    // ── Create Program Modal + Question Builder ─────────────────────────────

    // Question builder state
    let spfbQuestions = [];   // Array of question objects
    let spfbEditingId = null; // ID of question currently being edited inline

    function closeCreateProgramModal() {
        createProgramModal.style.display = 'none';
        resetProgramForm();
    }

    function resetProgramForm() {
        const fields = ['programName', 'startDate', 'endDate', 'startTime', 'endTime'];
        fields.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        spfbQuestions = [];
        spfbEditingId = null;
        renderQuestionList();
    }

    // ── Date input auto-formatting (mm/dd/yyyy) ──────────────────────────
    function initDateInputs() {
        ['startDate', 'endDate'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', function () {
                let v = this.value.replace(/\D/g, '').slice(0, 8);
                if (v.length >= 5) v = v.slice(0,2) + '/' + v.slice(2,4) + '/' + v.slice(4);
                else if (v.length >= 3) v = v.slice(0,2) + '/' + v.slice(2);
                this.value = v;
            });
        });
    }

    // ── Time input auto-formatting (HH:MM AM/PM) ─────────────────────────
    function initTimeInputs() {
        ['startTime', 'endTime'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', function () {
                let v = this.value.replace(/[^0-9APMapm:]/g, '');
                // Auto-insert colon after 2 digits
                if (/^\d{2}$/.test(v) && !v.includes(':')) v = v + ':';
                this.value = v;
            });
            el.addEventListener('blur', function () {
                // Normalize to HH:MM AM/PM on blur
                const raw = this.value.trim();
                const match = raw.match(/^(\d{1,2}):(\d{2})\s*(AM|PM|am|pm)?$/i);
                if (match) {
                    let h = parseInt(match[1], 10);
                    const m = match[2];
                    let period = (match[3] || '').toUpperCase();
                    if (!period) period = h < 12 ? 'AM' : 'PM';
                    if (h > 12) { h -= 12; period = 'PM'; }
                    if (h === 0) { h = 12; period = 'AM'; }
                    this.value = `${String(h).padStart(2,'0')}:${m} ${period}`;
                }
            });
        });
    }

    // ── Parse date string mm/dd/yyyy → Date ──────────────────────────────
    function parseDateMMDDYYYY(str) {
        const m = str.trim().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if (!m) return null;
        return new Date(parseInt(m[3]), parseInt(m[1])-1, parseInt(m[2]));
    }

    // ── Parse time string HH:MM AM/PM → minutes since midnight ──────────
    function parseTimeToMinutes(str) {
        const m = str.trim().match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
        if (!m) return null;
        let h = parseInt(m[1], 10);
        const min = parseInt(m[2], 10);
        const period = m[3].toUpperCase();
        if (period === 'AM' && h === 12) h = 0;
        if (period === 'PM' && h !== 12) h += 12;
        return h * 60 + min;
    }

    function handleCreateProgram() {
        const programName = document.getElementById('programName').value.trim();
        const startDateStr = document.getElementById('startDate').value.trim();
        const endDateStr   = document.getElementById('endDate').value.trim();
        const startTimeStr = document.getElementById('startTime').value.trim();
        const endTimeStr   = document.getElementById('endTime').value.trim();

        // Required field check
        if (!programName || !startDateStr || !endDateStr || !startTimeStr || !endTimeStr) {
            showToast('Please fill in all required fields', 'error');
            return;
        }

        // Date format validation
        const startDate = parseDateMMDDYYYY(startDateStr);
        const endDate   = parseDateMMDDYYYY(endDateStr);
        if (!startDate) { showToast('Start Date must be in mm/dd/yyyy format', 'error'); return; }
        if (!endDate)   { showToast('End Date must be in mm/dd/yyyy format', 'error'); return; }
        if (endDate < startDate) { showToast('End date must be on or after start date', 'error'); return; }

        // Time format validation
        const startMins = parseTimeToMinutes(startTimeStr);
        const endMins   = parseTimeToMinutes(endTimeStr);
        if (startMins === null) { showToast('Start Time must be in HH:MM AM/PM format (e.g., 08:00 AM)', 'error'); return; }
        if (endMins   === null) { showToast('End Time must be in HH:MM AM/PM format (e.g., 05:00 PM)', 'error'); return; }
        if (startDate.getTime() === endDate.getTime() && endMins <= startMins) {
            showToast('End time must be after start time on the same date', 'error');
            return;
        }

        // Build program object with questions
        const program = {
            id: Date.now(),
            programName,
            startDate: startDateStr,
            endDate:   endDateStr,
            startTime: startTimeStr,
            endTime:   endTimeStr,
            questions: JSON.parse(JSON.stringify(spfbQuestions)),
            createdAt: new Date().toISOString()
        };

        const programs = JSON.parse(localStorage.getItem('sports_programs') || '[]');
        programs.push(program);
        localStorage.setItem('sports_programs', JSON.stringify(programs));

        const qCount = spfbQuestions.length;
        showToast(`Sports program created with ${qCount} question${qCount !== 1 ? 's' : ''}!`, 'success');
        closeCreateProgramModal();
    }

    // ── Question Builder ─────────────────────────────────────────────────

    const QUESTION_TYPES = [
        { value: 'text',      label: 'Short Answer',    icon: '✏️' },
        { value: 'paragraph', label: 'Paragraph',       icon: '📝' },
        { value: 'checkbox',  label: 'Checkboxes',      icon: '☑️' },
        { value: 'radio',     label: 'Multiple Choice', icon: '🔘' },
        { value: 'file',      label: 'File Upload',     icon: '📎' },
    ];

    function getTypeLabel(value) {
        const t = QUESTION_TYPES.find(t => t.value === value);
        return t ? `${t.icon} ${t.label}` : value;
    }

    function generateQId() {
        return 'q_' + Date.now() + '_' + Math.floor(Math.random() * 10000);
    }

    function addQuestion() {
        // If currently editing another question, commit it first
        if (spfbEditingId) commitEdit(spfbEditingId);

        const newQ = {
            id:       generateQId(),
            label:    '',
            type:     'text',
            options:  ['Option 1'],
            required: false,
        };
        spfbQuestions.push(newQ);
        spfbEditingId = newQ.id;
        renderQuestionList();
        // Scroll to the new card
        setTimeout(() => {
            const card = document.querySelector(`[data-qid="${newQ.id}"]`);
            if (card) card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 60);
    }

    function deleteQuestion(qid) {
        spfbQuestions = spfbQuestions.filter(q => q.id !== qid);
        if (spfbEditingId === qid) spfbEditingId = null;
        renderQuestionList();
    }

    function startEdit(qid) {
        if (spfbEditingId && spfbEditingId !== qid) commitEdit(spfbEditingId);
        spfbEditingId = qid;
        renderQuestionList();
        setTimeout(() => {
            const inp = document.querySelector(`[data-qid="${qid}"] .spfb-q-label-input`);
            if (inp) inp.focus();
        }, 40);
    }

    function commitEdit(qid) {
        const card = document.querySelector(`[data-qid="${qid}"]`);
        if (!card) return;
        const q = spfbQuestions.find(q => q.id === qid);
        if (!q) return;

        // Read label
        const labelInp = card.querySelector('.spfb-q-label-input');
        if (labelInp) q.label = labelInp.value.trim();

        // Read type
        const typeSelect = card.querySelector('.spfb-q-type-select');
        if (typeSelect) q.type = typeSelect.value;

        // Read options (for checkbox/radio)
        if (q.type === 'checkbox' || q.type === 'radio') {
            const optInputs = card.querySelectorAll('.spfb-option-input');
            q.options = Array.from(optInputs).map(i => i.value.trim()).filter(v => v !== '');
            if (q.options.length === 0) q.options = ['Option 1'];
        }

        // Read required toggle
        const reqToggle = card.querySelector('.spfb-req-toggle');
        if (reqToggle) q.required = reqToggle.checked;
    }

    function renderQuestionList() {
        const list = document.getElementById('spfbQuestionList');
        const emptyState = document.getElementById('spfbEmptyState');
        const countBadge = document.getElementById('spfbQuestionCount');
        if (!list) return;

        // Update count badge
        if (countBadge) {
            countBadge.textContent = `${spfbQuestions.length} question${spfbQuestions.length !== 1 ? 's' : ''}`;
        }

        // Remove all question cards (keep empty state)
        list.querySelectorAll('.spfb-question-card').forEach(c => c.remove());

        if (spfbQuestions.length === 0) {
            if (emptyState) emptyState.style.display = 'flex';
            return;
        }
        if (emptyState) emptyState.style.display = 'none';

        spfbQuestions.forEach((q, idx) => {
            const isEditing = spfbEditingId === q.id;
            const card = document.createElement('div');
            card.className = `spfb-question-card${isEditing ? ' spfb-question-card--editing' : ''}`;
            card.setAttribute('data-qid', q.id);

            if (isEditing) {
                card.innerHTML = buildEditCard(q, idx);
            } else {
                card.innerHTML = buildPreviewCard(q, idx);
            }

            list.appendChild(card);
            attachCardEvents(card, q);
        });
    }

    function buildPreviewCard(q, idx) {
        const typeLabel = getTypeLabel(q.type);
        const reqBadge  = q.required
            ? '<span class="spfb-req-badge spfb-req-badge--on">Required</span>'
            : '<span class="spfb-req-badge spfb-req-badge--off">Optional</span>';

        let previewHTML = '';
        if (q.type === 'text') {
            previewHTML = '<div class="spfb-preview-input">Short answer text</div>';
        } else if (q.type === 'paragraph') {
            previewHTML = '<div class="spfb-preview-input spfb-preview-paragraph">Long answer text</div>';
        } else if (q.type === 'checkbox' || q.type === 'radio') {
            const inputType = q.type === 'checkbox' ? 'checkbox' : 'radio';
            const opts = (q.options && q.options.length) ? q.options : ['Option 1'];
            previewHTML = `<div class="spfb-preview-options">${opts.map(o =>
                `<label class="spfb-preview-option"><input type="${inputType}" disabled> <span>${o || '(empty)'}</span></label>`
            ).join('')}</div>`;
        } else if (q.type === 'file') {
            previewHTML = '<div class="spfb-preview-file"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> File upload field</div>';
        }

        const labelDisplay = q.label || `<em style="color:#9ca3af;">Untitled Question ${idx + 1}</em>`;

        return `
        <div class="spfb-card-preview-header">
            <div class="spfb-card-num">${idx + 1}</div>
            <div class="spfb-card-meta">
                <div class="spfb-card-label">${labelDisplay}</div>
                <div class="spfb-card-type-tag">${typeLabel}</div>
            </div>
            <div class="spfb-card-actions">
                ${reqBadge}
                <button type="button" class="spfb-icon-btn spfb-edit-btn" title="Edit question">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button type="button" class="spfb-icon-btn spfb-delete-btn" title="Delete question">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
            </div>
        </div>
        <div class="spfb-card-preview-body">${previewHTML}</div>`;
    }

    function buildEditCard(q, idx) {
        const typeOptions = QUESTION_TYPES.map(t =>
            `<option value="${t.value}" ${q.type === t.value ? 'selected' : ''}>${t.icon} ${t.label}</option>`
        ).join('');

        const hasOptions = q.type === 'checkbox' || q.type === 'radio';
        const opts = (q.options && q.options.length) ? q.options : ['Option 1'];

        const optionsHTML = hasOptions ? `
        <div class="spfb-options-section" id="spfbOptionsSection_${q.id}">
            <label class="spfb-options-label">Answer Options</label>
            <div class="spfb-options-list" id="spfbOptionsList_${q.id}">
                ${opts.map((o, oi) => `
                <div class="spfb-option-row">
                    <span class="spfb-option-bullet">${q.type === 'radio' ? '●' : '▪'}</span>
                    <input type="text" class="spfb-option-input sports-input" value="${escapeHtml(o)}" placeholder="Option ${oi + 1}" maxlength="200">
                    <button type="button" class="spfb-icon-btn spfb-remove-option-btn" data-oi="${oi}" title="Remove option">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>`).join('')}
            </div>
            <button type="button" class="spfb-add-option-btn" id="spfbAddOptionBtn_${q.id}">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Option
            </button>
        </div>` : '';

        return `
        <div class="spfb-edit-header">
            <div class="spfb-card-num spfb-card-num--edit">${idx + 1}</div>
            <span class="spfb-editing-label">Editing Question</span>
            <button type="button" class="spfb-icon-btn spfb-delete-btn" title="Delete question" style="margin-left:auto;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            </button>
        </div>

        <div class="spfb-edit-body">
            <!-- Question Label -->
            <div class="spfb-edit-field">
                <label class="spfb-edit-field-label">Question Label <span class="sports-req">*</span></label>
                <input type="text" class="spfb-q-label-input sports-input" value="${escapeHtml(q.label)}" placeholder="Enter your question here..." maxlength="300">
            </div>

            <!-- Question Type -->
            <div class="spfb-edit-field">
                <label class="spfb-edit-field-label">Input Type</label>
                <select class="spfb-q-type-select sports-input" style="cursor:pointer;">
                    ${typeOptions}
                </select>
            </div>

            <!-- Options (checkbox/radio only) -->
            ${optionsHTML}

            <!-- Required Toggle -->
            <div class="spfb-edit-field spfb-req-row">
                <label class="spfb-edit-field-label">Required</label>
                <label class="spfb-toggle-switch">
                    <input type="checkbox" class="spfb-req-toggle" ${q.required ? 'checked' : ''}>
                    <span class="spfb-toggle-slider"></span>
                </label>
                <span class="spfb-toggle-hint" id="spfbReqHint_${q.id}">${q.required ? 'Required' : 'Optional'}</span>
            </div>
        </div>

        <div class="spfb-edit-footer">
            <button type="button" class="spfb-done-btn" id="spfbDoneBtn_${q.id}">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Done
            </button>
        </div>`;
    }

    function attachCardEvents(card, q) {
        const qid = q.id;

        // Edit button (preview mode)
        const editBtn = card.querySelector('.spfb-edit-btn');
        if (editBtn) editBtn.addEventListener('click', () => startEdit(qid));

        // Delete button
        const deleteBtn = card.querySelector('.spfb-delete-btn');
        if (deleteBtn) deleteBtn.addEventListener('click', () => deleteQuestion(qid));

        // Done button (edit mode)
        const doneBtn = card.querySelector(`#spfbDoneBtn_${qid}`);
        if (doneBtn) {
            doneBtn.addEventListener('click', () => {
                commitEdit(qid);
                spfbEditingId = null;
                renderQuestionList();
            });
        }

        // Type select change → re-render to show/hide options
        const typeSelect = card.querySelector('.spfb-q-type-select');
        if (typeSelect) {
            typeSelect.addEventListener('change', () => {
                commitEdit(qid);
                // Keep editing same question
                spfbEditingId = qid;
                renderQuestionList();
            });
        }

        // Required toggle hint update
        const reqToggle = card.querySelector('.spfb-req-toggle');
        const reqHint   = card.querySelector(`#spfbReqHint_${qid}`);
        if (reqToggle && reqHint) {
            reqToggle.addEventListener('change', () => {
                reqHint.textContent = reqToggle.checked ? 'Required' : 'Optional';
            });
        }

        // Add option button
        const addOptBtn = card.querySelector(`#spfbAddOptionBtn_${qid}`);
        if (addOptBtn) {
            addOptBtn.addEventListener('click', () => {
                commitEdit(qid);
                const qObj = spfbQuestions.find(x => x.id === qid);
                if (qObj) {
                    qObj.options.push(`Option ${qObj.options.length + 1}`);
                    spfbEditingId = qid;
                    renderQuestionList();
                    // Focus last option input
                    setTimeout(() => {
                        const inputs = document.querySelectorAll(`[data-qid="${qid}"] .spfb-option-input`);
                        if (inputs.length) inputs[inputs.length - 1].focus();
                    }, 40);
                }
            });
        }

        // Remove option buttons
        card.querySelectorAll('.spfb-remove-option-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const oi = parseInt(btn.getAttribute('data-oi'), 10);
                commitEdit(qid);
                const qObj = spfbQuestions.find(x => x.id === qid);
                if (qObj && qObj.options.length > 1) {
                    qObj.options.splice(oi, 1);
                    spfbEditingId = qid;
                    renderQuestionList();
                } else {
                    showToast('At least one option is required', 'error');
                }
            });
        });

        // Click on preview card body → start edit
        const previewBody = card.querySelector('.spfb-card-preview-body');
        if (previewBody) {
            previewBody.addEventListener('click', () => startEdit(qid));
        }
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Wire up Add Question button
    const spfbAddBtn = document.getElementById('spfbAddQuestionBtn');
    if (spfbAddBtn) spfbAddBtn.addEventListener('click', addQuestion);

    // Initialize date/time formatters when modal opens
    if (btnCreateProgram) {
        btnCreateProgram.addEventListener('click', () => {
            createProgramModal.style.display = 'flex';
            initDateInputs();
            initTimeInputs();
        });
    }

    // ── View Modal ──────────────────────────────────────────────────────────
    function openViewModal(appId) {
        const app = applications.find(a => a.id === appId);
        if (!app) return;

        currentApplicationId = appId;

        viewModalBody.innerHTML = `
            <!-- Personal Information -->
            <div class="sports-info-section">
                <h4 class="sports-info-title">Personal Information</h4>
                <div class="sports-info-grid">
                    <div class="sports-info-item">
                        <label>Full Name:</label>
                        <span>${formatFullName(app)}</span>
                    </div>
                    <div class="sports-info-item">
                        <label>Date of Birth:</label>
                        <span>${app.dateOfBirth}</span>
                    </div>
                    <div class="sports-info-item">
                        <label>Age:</label>
                        <span>${app.age}</span>
                    </div>
                    <div class="sports-info-item">
                        <label>Contact Number:</label>
                        <span>${app.contact}</span>
                    </div>
                    <div class="sports-info-item">
                        <label>Email:</label>
                        <span>${app.email}</span>
                    </div>
                    <div class="sports-info-item sports-info-full">
                        <label>Complete Address:</label>
                        <span>${app.address}</span>
                    </div>
                </div>
            </div>

            <!-- Sports Information -->
            <div class="sports-info-section">
                <h4 class="sports-info-title">Sports Information</h4>
                <div class="sports-info-grid">
                    <div class="sports-info-item">
                        <label>Sport:</label>
                        <span>${app.sport}</span>
                    </div>
                    <div class="sports-info-item">
                        <label>Division:</label>
                        <span>${app.division}</span>
                    </div>
                    <div class="sports-info-item">
                        <label>Date Applied:</label>
                        <span>${app.dateApplied}</span>
                    </div>
                </div>
            </div>

            <!-- Submitted Requirements -->
            <div class="sports-info-section">
                <h4 class="sports-info-title">Submitted Requirements</h4>
                <div class="sports-requirements-grid">
                    ${renderRequirement('Birth Certificate', app.requirements.birthCertificate)}
                    ${renderRequirement('Valid ID', app.requirements.validId)}
                    ${renderRequirement('Medical Certificate', app.requirements.medicalCertificate)}
                    ${renderRequirement('Parent Consent', app.requirements.parentConsent)}
                </div>
            </div>

            <!-- Payment Status -->
            <div class="sports-info-section">
                <h4 class="sports-info-title">Payment Status <span class="sports-req">*</span></h4>
                <div class="sports-radio-group">
                    <label class="sports-radio-label">
                        <input type="radio" name="payment" value="Paid" ${app.paymentStatus === 'Paid' ? 'checked' : ''}>
                        <span>Paid</span>
                    </label>
                    <label class="sports-radio-label">
                        <input type="radio" name="payment" value="Not Paid" ${app.paymentStatus === 'Not Paid' ? 'checked' : ''}>
                        <span>Not Paid</span>
                    </label>
                </div>
            </div>
        `;

        viewModal.style.display = 'flex';
    }

    function renderRequirement(label, submitted) {
        const icon = submitted 
            ? '<svg class="sports-req-icon sports-req-check" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>' 
            : '<svg class="sports-req-icon sports-req-x" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
        const className = submitted ? 'sports-req-met' : 'sports-req-missing';
        
        return `
            <div class="sports-req-item ${className}">
                ${icon}
                <span>${label}</span>
            </div>
        `;
    }

    function closeViewModal() {
        viewModal.style.display = 'none';
        viewModal.classList.remove('sports-modal-maximized');
        const viewBox = document.getElementById('viewBox');
        if (viewBox) viewBox.classList.remove('sports-modal-maximized');
        const viewMaximize = document.getElementById('viewMaximize');
        if (viewMaximize) viewMaximize.textContent = '□';
        currentApplicationId = null;
    }

    // ── Approve/Reject ──────────────────────────────────────────────────────
    function handleApprove() {
        const paymentStatus = document.querySelector('input[name="payment"]:checked');
        
        if (!paymentStatus) {
            showToast('Please select payment status before approving', 'error');
            return;
        }

        const app = applications.find(a => a.id === currentApplicationId);
        if (!app) return;

        app.status = 'Approved';
        app.paymentStatus = paymentStatus.value;
        app.approvedDate = new Date().toISOString();

        // Move to approved list
        const approvedApps = JSON.parse(localStorage.getItem('sports_approved') || '[]');
        approvedApps.push(app);
        localStorage.setItem('sports_approved', JSON.stringify(approvedApps));

        // Remove from pending
        applications = applications.filter(a => a.id !== currentApplicationId);
        localStorage.setItem('sports_applications', JSON.stringify(applications));

        closeViewModal();
        renderTable();
        updateStats();
        showToast('Application approved successfully!', 'success');
    }

    function handleReject() {
        const paymentStatus = document.querySelector('input[name="payment"]:checked');
        
        if (!paymentStatus) {
            showToast('Please select payment status before rejecting', 'error');
            return;
        }

        closeViewModal();
        openRejectReasonModal();
    }

    // ── Rejection Reason Modal ──────────────────────────────────────────────
    function openRejectReasonModal() {
        // Reset all checkboxes and other field
        document.querySelectorAll('.reject-reason-checkbox').forEach(cb => cb.checked = false);
        if (rejectReasonOtherText) rejectReasonOtherText.value = '';
        if (rejectReasonOtherField) rejectReasonOtherField.style.display = 'none';
        if (rejectReasonModal) rejectReasonModal.style.display = 'flex';
    }

    function closeRejectReasonModal() {
        if (rejectReasonModal) rejectReasonModal.style.display = 'none';
    }

    function confirmReject() {
        const reasons = getSelectedReasons();
        
        if (reasons.length === 0) {
            showToast('Please select at least one reason for rejection', 'error');
            return;
        }

        // Check if "Other" is selected and textarea is empty
        const otherCheckbox = document.getElementById('rejectReasonOtherCheckbox');
        if (otherCheckbox && otherCheckbox.checked) {
            const otherText = document.getElementById('rejectReasonOtherText').value.trim();
            if (!otherText) {
                showToast('Please specify the other reason', 'error');
                return;
            }
        }

        const app = applications.find(a => a.id === currentApplicationId);
        if (!app) return;

        app.status = 'Rejected';
        app.rejectionReasons = reasons;
        app.rejectedDate = new Date().toISOString();

        // Remove from pending
        applications = applications.filter(a => a.id !== currentApplicationId);
        localStorage.setItem('sports_applications', JSON.stringify(applications));

        closeRejectReasonModal();
        renderTable();
        updateStats();
        showToast('Application rejected successfully', 'success');
    }

    function getSelectedReasons() {
        const reasons = [];
        document.querySelectorAll('.reject-reason-checkbox:checked').forEach(cb => {
            if (cb.value === 'Other') {
                const otherText = document.getElementById('rejectReasonOtherText').value.trim();
                reasons.push(`Other: ${otherText}`);
            } else {
                reasons.push(cb.value);
            }
        });
        return reasons;
    }

    // ── Toast Notification ──────────────────────────────────────────────────
    function showToast(message, type = 'success') {
        const toast = document.getElementById('sportsToast');
        const toastMsg = document.getElementById('sportsToastMsg');
        
        if (!toast || !toastMsg) return;

        toastMsg.textContent = message;
        
        // Set color based on type
        if (type === 'error') {
            toast.style.background = '#ef4444';
        } else {
            toast.style.background = '#10b981';
        }

        toast.style.display = 'flex';
        
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000);
    }
}
