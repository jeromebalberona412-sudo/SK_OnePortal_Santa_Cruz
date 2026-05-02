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
        requirementFile: { name: 'requirements.pdf', size: '4.2 MB' },
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
        requirementFile: { name: 'requirements.pdf', size: '3.8 MB' },
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
        requirementFile: { name: 'requirements.pdf', size: '5.1 MB' },
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
        requirementFile: { name: 'requirements.pdf', size: '2.9 MB' },
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
        requirementFile: { name: 'requirements.pdf', size: '6.7 MB' },
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
        requirementFile: { name: 'requirements.pdf', size: '3.3 MB' },
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
        requirementFile: { name: 'requirements.pdf', size: '7.5 MB' },
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
        requirementFile: { name: 'requirements.pdf', size: '4.8 MB' },
        status: 'Pending',
        paymentStatus: null,
        rejectionReasons: []
    }
];

function initSportsRequests() {
    // Seed sample data if localStorage is empty
    if (!localStorage.getItem('sports_applications_seeded_v2')) {
        localStorage.setItem('sports_applications', JSON.stringify(SAMPLE_SPORTS_DATA));
        localStorage.setItem('sports_applications_seeded_v2', '1');
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
            viewBox.classList.toggle('sports-modal-maximized', isMax);
            viewModal.classList.toggle('sports-overlay-maximized', isMax);
            viewMaximize.textContent = isMax ? '⧉' : '□';
            viewMaximize.title = isMax ? 'Restore Down' : 'Fullscreen';
        });
    }

    // Rejection Reason Modal — mutual exclusion + char counter
    const regularCheckboxes = document.querySelectorAll('.reject-reason-checkbox:not(#rejectReasonOtherCheckbox)');

    // When "Other" is checked → uncheck all regular checkboxes
    if (rejectReasonOtherCheckbox && rejectReasonOtherField) {
        rejectReasonOtherCheckbox.addEventListener('change', () => {
            if (rejectReasonOtherCheckbox.checked) {
                regularCheckboxes.forEach(cb => { cb.checked = false; });
                rejectReasonOtherField.style.display = 'block';
            } else {
                rejectReasonOtherField.style.display = 'none';
                if (rejectReasonOtherText) rejectReasonOtherText.value = '';
                const counter = document.getElementById('rejectOtherCharCount');
                if (counter) counter.textContent = '0 / 500';
            }
        });
    }

    // When any regular checkbox is checked → uncheck "Other"
    regularCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            if (cb.checked && rejectReasonOtherCheckbox) {
                rejectReasonOtherCheckbox.checked = false;
                if (rejectReasonOtherField) rejectReasonOtherField.style.display = 'none';
                if (rejectReasonOtherText) rejectReasonOtherText.value = '';
                const counter = document.getElementById('rejectOtherCharCount');
                if (counter) counter.textContent = '0 / 500';
            }
        });
    });

    // Char counter for Other textarea
    if (rejectReasonOtherText) {
        rejectReasonOtherText.addEventListener('input', () => {
            const len = rejectReasonOtherText.value.length;
            const counter = document.getElementById('rejectOtherCharCount');
            if (counter) {
                counter.textContent = `${len} / 500`;
                counter.style.color = len >= 450 ? '#ef4444' : '#9ca3af';
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
                    <td style="font-weight:600;text-align:center;">${fullName}</td>
                    <td>${app.sport}</td>
                    <td>${app.division}</td>
                    <td>${app.contact}</td>
                    <td>${app.dateApplied}</td>
                    <td>${statusBadge}</td>
                    <td class="col-actions">
                        <button class="sports-tbl-btn sports-tbl-btn-view" data-id="${app.id}">
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
                                  app.sport.toLowerCase().includes(filterSearchText);
                if (!searchMatch) return false;
            }

            // Sport filter
            if (filterSportValue && app.sport !== filterSportValue) return false;

            // Age category filter — match by age range
            if (filterDivisionValue) {
                const age = parseInt(app.age, 10);
                let match = false;
                if (filterDivisionValue === 'Youth Beginner (15-17)'    && age >= 15 && age <= 17) match = true;
                if (filterDivisionValue === 'Youth Competitive (18-21)' && age >= 18 && age <= 21) match = true;
                if (filterDivisionValue === 'Young Adult (22-25)'       && age >= 22 && age <= 25) match = true;
                if (filterDivisionValue === 'Adult Competitive (26-28)' && age >= 26 && age <= 28) match = true;
                if (filterDivisionValue === 'Senior Youth (29-30)'      && age >= 29 && age <= 30) match = true;
                if (!match) return false;
            }

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
        // Reset date pickers
        ['startDate', 'endDate'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        // Reset time dropdowns
        resetTimeDropdowns('startTime');
        resetTimeDropdowns('endTime');
        // Hide error messages
        ['startDateError','endDateError','startTimeError','endTimeError'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
        // Reset program name select
        const programNameSelect = document.getElementById('programName');
        if (programNameSelect) programNameSelect.value = '';
        const programNameOther = document.getElementById('programNameOther');
        if (programNameOther) { programNameOther.value = ''; programNameOther.style.display = 'none'; }
        // Reset announcement
        const announcementEl = document.getElementById('spfbAnnouncement');
        if (announcementEl) announcementEl.value = '';
        const countEl = document.getElementById('spfbAnnouncementCount');
        if (countEl) countEl.textContent = '0';
        spfbQuestions = [];
        spfbEditingId = null;
        renderQuestionList();
    }

    // ── Date input — native date picker with today-min ────────────────────
    function initDateInputs() {
        const today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
        ['startDate', 'endDate'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.setAttribute('min', today);
            // Prevent year > 4 digits by clamping on change
            el.addEventListener('change', function () {
                if (!this.value) return;
                const parts = this.value.split('-');
                if (parts[0] && parts[0].length > 4) {
                    this.value = '';
                    const errId = id === 'startDate' ? 'startDateError' : 'endDateError';
                    const errEl = document.getElementById(errId);
                    if (errEl) { errEl.textContent = 'Year must be exactly 4 digits (e.g. 2026).'; errEl.style.display = 'block'; }
                }
            });
        });

        // Start date change → update end date min
        const startEl = document.getElementById('startDate');
        const endEl   = document.getElementById('endDate');
        if (startEl && endEl) {
            startEl.addEventListener('change', function () {
                const errEl = document.getElementById('startDateError');
                if (this.value && this.value < today) {
                    if (errEl) { errEl.textContent = 'Start Date must be today or a future date.'; errEl.style.display = 'block'; }
                    this.value = '';
                    return;
                }
                if (errEl) errEl.style.display = 'none';
                // End date must be >= start date
                if (this.value) endEl.setAttribute('min', this.value);
                // Clear end date if it's now before start
                if (endEl.value && endEl.value < this.value) {
                    endEl.value = '';
                    const endErrEl = document.getElementById('endDateError');
                    if (endErrEl) { endErrEl.textContent = 'End Date must be the same or after the Start Date.'; endErrEl.style.display = 'block'; }
                }
            });
            endEl.addEventListener('change', function () {
                const errEl = document.getElementById('endDateError');
                if (startEl.value && this.value && this.value < startEl.value) {
                    if (errEl) { errEl.textContent = 'End Date must be the same or after the Start Date.'; errEl.style.display = 'block'; }
                    this.value = '';
                    return;
                }
                if (errEl) errEl.style.display = 'none';
            });
        }
    }

    // ── Time dropdowns — no free-text, just read the selects ─────────────
    function initTimeInputs() {
        // Nothing to wire — dropdowns handle themselves.
        // Validation happens in handleCreateProgram.
    }

    // ── Get time string from dropdowns ────────────────────────────────────
    function getTimeFromDropdowns(prefix) {
        const h = document.getElementById(prefix + 'Hour')?.value || '';
        const m = document.getElementById(prefix + 'Min')?.value || '';
        const p = document.getElementById(prefix + 'Period')?.value || '';
        if (!h || !m || !p) return '';
        return `${h}:${m} ${p}`;
    }

    // ── Reset time dropdowns ──────────────────────────────────────────────
    function resetTimeDropdowns(prefix) {
        ['Hour', 'Min', 'Period'].forEach(part => {
            const el = document.getElementById(prefix + part);
            if (el) el.value = '';
        });
    }

    // ── Parse date YYYY-MM-DD (native date input value) → Date ───────────
    function parseDateYYYYMMDD(str) {
        if (!str) return null;
        const parts = str.split('-');
        if (parts.length !== 3) return null;
        return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
    }

    // ── Parse time string HH:MM AM/PM → minutes since midnight ──────────
    function parseTimeToMinutes(str) {
        const m = str.trim().match(/^(1[0-2]|0?[1-9]):([1-9]|[0-5][0-9])\s*(AM|PM)$/i);
        if (!m) return null;
        let h = parseInt(m[1], 10);
        const min = parseInt(m[2], 10);
        const period = m[3].toUpperCase();
        if (period === 'AM' && h === 12) h = 0;
        if (period === 'PM' && h !== 12) h += 12;
        return h * 60 + min;
    }

    function handleCreateProgram() {
        const programNameSelect = document.getElementById('programName');
        const programNameOther = document.getElementById('programNameOther');
        let programName = '';
        if (programNameSelect) {
            if (programNameSelect.value === 'Other') {
                programName = programNameOther ? programNameOther.value.trim() : '';
            } else {
                programName = programNameSelect.value.trim();
            }
        }
        const announcementEl = document.getElementById('spfbAnnouncement');
        const announcement = announcementEl ? announcementEl.value.trim() : '';

        // Date values (YYYY-MM-DD from native date input)
        const startDateRaw = (document.getElementById('startDate')?.value || '').trim();
        const endDateRaw   = (document.getElementById('endDate')?.value || '').trim();

        // Time values from dropdowns
        const startTimeStr = getTimeFromDropdowns('startTime');
        const endTimeStr   = getTimeFromDropdowns('endTime');

        // ── Required field check ──
        if (!programName) { showToast('Please select a Program Name.', 'error'); return; }
        if (!announcement) { showToast('Please enter an Announcement.', 'error'); return; }
        if (!startDateRaw) { showToast('Please select a Start Date.', 'error'); return; }
        if (!endDateRaw)   { showToast('Please select an End Date.', 'error'); return; }
        if (!startTimeStr) { showToast('Please select a complete Start Time (Hour, Minute, AM/PM).', 'error'); return; }
        if (!endTimeStr)   { showToast('Please select a complete End Time (Hour, Minute, AM/PM).', 'error'); return; }

        // ── Date validation ──
        const today = new Date(); today.setHours(0,0,0,0);
        const startDate = parseDateYYYYMMDD(startDateRaw);
        const endDate   = parseDateYYYYMMDD(endDateRaw);

        if (!startDate) { showToast('Invalid Start Date.', 'error'); return; }
        if (startDate < today) { showToast('Start Date must be today or a future date.', 'error'); return; }
        if (!endDate) { showToast('Invalid End Date.', 'error'); return; }
        if (endDate < startDate) { showToast('End Date must be the same or after the Start Date.', 'error'); return; }

        // ── Time validation ──
        const startMins = parseTimeToMinutes(startTimeStr);
        const endMins   = parseTimeToMinutes(endTimeStr);
        if (startMins === null) { showToast('Invalid Start Time format. Use hh:mm AM/PM.', 'error'); return; }
        if (endMins   === null) { showToast('Invalid End Time format. Use hh:mm AM/PM.', 'error'); return; }
        if (startDate.getTime() === endDate.getTime() && endMins <= startMins) {
            showToast('End Time must be after Start Time on the same date.', 'error');
            return;
        }

        // Format date for display as MM/DD/YYYY
        const fmtDate = (raw) => {
            const [y, mo, d] = raw.split('-');
            return `${mo}/${d}/${y}`;
        };

        // Build program object
        const program = {
            id: Date.now(),
            programName,
            announcement,
            startDate: fmtDate(startDateRaw),
            endDate:   fmtDate(endDateRaw),
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
        { value: 'text',      label: 'Short Answer'    },
        { value: 'paragraph', label: 'Paragraph'       },
        { value: 'number',    label: 'Number'          },
        { value: 'checkbox',  label: 'Checkboxes'      },
        { value: 'radio',     label: 'Multiple Choice' },
        { value: 'file',      label: 'File Upload'     },
    ];

    function getTypeLabel(value) {
        const t = QUESTION_TYPES.find(t => t.value === value);
        return t ? t.label : value;
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

    function copyQuestion(qid) {
        if (spfbEditingId) commitEdit(spfbEditingId);
        const original = spfbQuestions.find(q => q.id === qid);
        if (!original) return;
        const copy = JSON.parse(JSON.stringify(original));
        copy.id = generateQId();
        const idx = spfbQuestions.findIndex(q => q.id === qid);
        spfbQuestions.splice(idx + 1, 0, copy);
        renderQuestionList();
        setTimeout(() => {
            const card = document.querySelector(`[data-qid="${copy.id}"]`);
            if (card) card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 60);
    }

    // ── Delete Confirmation Modal ─────────────────────────────────────────
    let _pendingDeleteQid = null;

    function showDeleteConfirm(qid) {
        _pendingDeleteQid = qid;
        let modal = document.getElementById('spfbDeleteConfirmModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'spfbDeleteConfirmModal';
            modal.className = 'spfb-delete-confirm-overlay';
            modal.innerHTML = `
                <div class="spfb-delete-confirm-box">
                    <div class="spfb-delete-confirm-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </div>
                    <p class="spfb-delete-confirm-text">Delete this question?</p>
                    <p class="spfb-delete-confirm-sub">This action cannot be undone.</p>
                    <div class="spfb-delete-confirm-actions">
                        <button type="button" class="sports-btn sports-btn-outline" id="spfbDeleteCancelBtn">Cancel</button>
                        <button type="button" class="sports-btn sports-btn-danger" id="spfbDeleteConfirmBtn">Yes, Delete</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            document.getElementById('spfbDeleteCancelBtn').addEventListener('click', () => {
                modal.style.display = 'none';
                _pendingDeleteQid = null;
            });
            document.getElementById('spfbDeleteConfirmBtn').addEventListener('click', () => {
                if (_pendingDeleteQid) deleteQuestion(_pendingDeleteQid);
                modal.style.display = 'none';
                _pendingDeleteQid = null;
            });
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    _pendingDeleteQid = null;
                }
            });
        }
        modal.style.display = 'flex';
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
        } else if (q.type === 'number') {
            previewHTML = '<div class="spfb-preview-input" style="min-width:100px;">0</div>';
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
                <button type="button" class="spfb-icon-btn spfb-copy-btn" title="Duplicate question">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                </button>
                <button type="button" class="spfb-icon-btn spfb-delete-btn" title="Delete question">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
            </div>
        </div>
        <div class="spfb-card-preview-body">${previewHTML}</div>`;
    }

    function buildEditCard(q, idx) {
        const typeOptions = `<option value="" disabled>Select input type</option>` + QUESTION_TYPES.map(t =>
            `<option value="${t.value}" ${q.type === t.value ? 'selected' : ''}>${t.label}</option>`
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
            <div style="display:flex;align-items:center;gap:4px;margin-left:auto;">
                <button type="button" class="spfb-icon-btn spfb-copy-btn" title="Duplicate question">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                </button>
                <button type="button" class="spfb-icon-btn spfb-delete-btn" title="Delete question">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
            </div>
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
        </div>`;
    }

    function attachCardEvents(card, q) {
        const qid = q.id;

        // Edit button (preview mode)
        const editBtn = card.querySelector('.spfb-edit-btn');
        if (editBtn) editBtn.addEventListener('click', () => startEdit(qid));

        // Copy/duplicate button
        const copyBtn = card.querySelector('.spfb-copy-btn');
        if (copyBtn) copyBtn.addEventListener('click', () => copyQuestion(qid));

        // Delete button — show confirmation modal
        const deleteBtn = card.querySelector('.spfb-delete-btn');
        if (deleteBtn) deleteBtn.addEventListener('click', () => showDeleteConfirm(qid));

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

    // ── Fullscreen toggle helper ──────────────────────────────────────────
    function initModalFullscreen(overlayId, boxId, btnId) {
        const overlay = document.getElementById(overlayId);
        const box     = document.getElementById(boxId);
        const btn     = document.getElementById(btnId);
        if (!overlay || !box || !btn) return;
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isMax = !box.classList.contains('sports-modal-maximized');
            box.classList.toggle('sports-modal-maximized', isMax);
            overlay.classList.toggle('sports-overlay-maximized', isMax);
            btn.textContent = isMax ? '⧉' : '□';
            btn.title = isMax ? 'Restore Down' : 'Fullscreen';
        });
    }

    // Initialize fullscreen for Create Program modal
    initModalFullscreen('createProgramModal', 'sports-modal-form-builder-box', 'createProgramMaximize');
    // Fallback: find the box by class since it doesn't have an id
    (() => {
        const overlay = document.getElementById('createProgramModal');
        const box     = overlay ? overlay.querySelector('.sports-modal-form-builder') : null;
        const btn     = document.getElementById('createProgramMaximize');
        if (!overlay || !box || !btn) return;
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isMax = !box.classList.contains('sports-modal-maximized');
            box.classList.toggle('sports-modal-maximized', isMax);
            overlay.classList.toggle('sports-overlay-maximized', isMax);
            btn.textContent = isMax ? '⧉' : '□';
            btn.title = isMax ? 'Restore Down' : 'Fullscreen';
        });
    })();

    // Initialize date/time formatters when modal opens
    if (btnCreateProgram) {
        btnCreateProgram.addEventListener('click', () => {
            createProgramModal.style.display = 'flex';
            initDateInputs();
            initTimeInputs();

            // Wire up programName "Other" toggle
            const programNameSelect = document.getElementById('programName');
            const programNameOther = document.getElementById('programNameOther');
            if (programNameSelect && programNameOther) {
                programNameSelect.addEventListener('change', function () {
                    programNameOther.style.display = this.value === 'Other' ? 'block' : 'none';
                    if (this.value !== 'Other') programNameOther.value = '';
                });
            }

            // Wire up announcement character counter
            const announcementEl = document.getElementById('spfbAnnouncement');
            const countEl = document.getElementById('spfbAnnouncementCount');
            if (announcementEl && countEl) {
                announcementEl.addEventListener('input', function () {
                    countEl.textContent = this.value.length;
                });
            }
        });
    }

    // ── Created Sports Programs Modal ─────────────────────────────────────
    const btnViewCreatedPrograms  = document.getElementById('btnViewCreatedPrograms');
    const createdProgramsModal    = document.getElementById('createdProgramsModal');
    const createdProgramsBox      = document.getElementById('createdProgramsBox');
    const createdProgramsClose    = document.getElementById('createdProgramsClose');
    const createdProgramsMaximize = document.getElementById('createdProgramsMaximize');
    const createdProgramsTableBody = document.getElementById('createdProgramsTableBody');

    if (btnViewCreatedPrograms) {
        btnViewCreatedPrograms.addEventListener('click', () => {
            renderCreatedProgramsTable();
            createdProgramsModal.style.display = 'flex';
        });
    }

    if (createdProgramsClose) {
        createdProgramsClose.addEventListener('click', () => {
            createdProgramsModal.style.display = 'none';
            createdProgramsBox.classList.remove('sports-modal-maximized');
            createdProgramsModal.classList.remove('sports-overlay-maximized');
            createdProgramsMaximize.textContent = '□';
        });
    }
    if (createdProgramsModal) {
        createdProgramsModal.addEventListener('click', (e) => {
            if (e.target === createdProgramsModal) {
                createdProgramsModal.style.display = 'none';
                createdProgramsBox.classList.remove('sports-modal-maximized');
                createdProgramsModal.classList.remove('sports-overlay-maximized');
                createdProgramsMaximize.textContent = '□';
            }
        });
    }
    if (createdProgramsMaximize && createdProgramsBox) {
        createdProgramsMaximize.addEventListener('click', (e) => {
            e.stopPropagation();
            const isMax = !createdProgramsBox.classList.contains('sports-modal-maximized');
            createdProgramsBox.classList.toggle('sports-modal-maximized', isMax);
            createdProgramsModal.classList.toggle('sports-overlay-maximized', isMax);
            createdProgramsMaximize.textContent = isMax ? '⧉' : '□';
            createdProgramsMaximize.title = isMax ? 'Restore Down' : 'Fullscreen';
        });
    }

    function renderCreatedProgramsTable() {
        const programs = JSON.parse(localStorage.getItem('sports_programs') || '[]');
        if (!createdProgramsTableBody) return;

        if (programs.length === 0) {
            createdProgramsTableBody.innerHTML = `<tr class="sports-empty-row"><td colspan="9" style="text-align:center;padding:32px;color:#6b7280;font-size:13px;">No sports programs created yet.</td></tr>`;
            return;
        }

        // Show newest first
        const sorted = [...programs].reverse();
        createdProgramsTableBody.innerHTML = sorted.map((p, i) => {
            const createdDate = p.createdAt
                ? new Date(p.createdAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
                : '—';
            const createdTime = p.createdAt
                ? new Date(p.createdAt).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })
                : '—';
            const qCount = (p.questions || []).length;
            return `
            <tr>
                <td style="font-weight:600;text-align:center;padding-left:12px;">${p.programName || '—'}</td>
                <td style="text-align:center;">${p.startDate || '—'}</td>
                <td style="text-align:center;">${p.endDate || '—'}</td>
                <td style="text-align:center;">${p.startTime || '—'}</td>
                <td style="text-align:center;">${p.endTime || '—'}</td>
                <td style="text-align:center;"><span style="background:#f3f4f6;color:#374151;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;">${qCount} question${qCount !== 1 ? 's' : ''}</span></td>
                <td style="text-align:center;">${createdDate}</td>
                <td style="text-align:center;">${createdTime}</td>
                <td style="text-align:center;" class="col-actions">
                    <button class="sports-tbl-btn sports-tbl-btn-view" data-prog-idx="${sorted.length - 1 - i}">View</button>
                </td>
            </tr>`;
        }).join('');

        createdProgramsTableBody.querySelectorAll('button[data-prog-idx]').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.getAttribute('data-prog-idx'), 10);
                openViewProgramModal(programs[idx]);
            });
        });
    }

    // ── View Program Details Modal ─────────────────────────────────────────
    const viewProgramModal    = document.getElementById('viewProgramModal');
    const viewProgramBox      = document.getElementById('viewProgramBox');
    const viewProgramClose    = document.getElementById('viewProgramClose');
    const viewProgramMaximize = document.getElementById('viewProgramMaximize');
    const viewProgramBody     = document.getElementById('viewProgramBody');

    if (viewProgramClose) {
        viewProgramClose.addEventListener('click', () => {
            viewProgramModal.style.display = 'none';
            viewProgramBox.classList.remove('sports-modal-maximized');
            viewProgramModal.classList.remove('sports-overlay-maximized');
            viewProgramMaximize.textContent = '□';
        });
    }
    if (viewProgramModal) {
        viewProgramModal.addEventListener('click', (e) => {
            if (e.target === viewProgramModal) {
                viewProgramModal.style.display = 'none';
                viewProgramBox.classList.remove('sports-modal-maximized');
                viewProgramModal.classList.remove('sports-overlay-maximized');
                viewProgramMaximize.textContent = '□';
            }
        });
    }
    if (viewProgramMaximize && viewProgramBox) {
        viewProgramMaximize.addEventListener('click', (e) => {
            e.stopPropagation();
            const isMax = !viewProgramBox.classList.contains('sports-modal-maximized');
            viewProgramBox.classList.toggle('sports-modal-maximized', isMax);
            viewProgramModal.classList.toggle('sports-overlay-maximized', isMax);
            viewProgramMaximize.textContent = isMax ? '⧉' : '□';
            viewProgramMaximize.title = isMax ? 'Restore Down' : 'Fullscreen';
        });
    }

    function openViewProgramModal(p) {
        if (!viewProgramModal || !viewProgramBody) return;

        const qList = (p.questions || []);
        const typeLabels = {
            text: 'Short Answer', paragraph: 'Paragraph', number: 'Number',
            checkbox: 'Checkboxes', radio: 'Multiple Choice', file: 'File Upload'
        };

        const questionsHTML = qList.length === 0
            ? `<p style="color:#9ca3af;font-size:13px;font-style:italic;">No questions in this program.</p>`
            : qList.map((q, i) => {
                const typeLabel = typeLabels[q.type] || q.type;
                const reqBadge = q.required
                    ? `<span style="background:#fee2e2;color:#b91c1c;font-size:10px;font-weight:700;padding:2px 7px;border-radius:999px;text-transform:uppercase;">Required</span>`
                    : `<span style="background:#f3f4f6;color:#6b7280;font-size:10px;font-weight:700;padding:2px 7px;border-radius:999px;text-transform:uppercase;">Optional</span>`;

                let answerPreview = '';
                if (q.type === 'checkbox' || q.type === 'radio') {
                    const opts = (q.options || []).map(o => `<li style="font-size:12px;color:#374151;margin-bottom:3px;">• ${o}</li>`).join('');
                    answerPreview = `<ul style="margin:8px 0 0 4px;padding:0;list-style:none;">${opts}</ul>`;
                } else if (q.type === 'file') {
                    answerPreview = `<div style="font-size:12px;color:#6b7280;margin-top:6px;border:1.5px dashed #d1d5db;border-radius:6px;padding:6px 10px;background:#f9fafb;">File upload field</div>`;
                } else if (q.type === 'number') {
                    answerPreview = `<div style="font-size:12px;color:#9ca3af;margin-top:6px;border-bottom:1.5px solid #d1d5db;padding-bottom:4px;width:80px;">0</div>`;
                } else {
                    answerPreview = `<div style="font-size:12px;color:#9ca3af;margin-top:6px;border-bottom:1.5px solid #d1d5db;padding-bottom:4px;">${q.type === 'paragraph' ? 'Long answer text' : 'Short answer text'}</div>`;
                }

                return `
                <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:10px;padding:14px 16px;margin-bottom:10px;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                        <span style="width:22px;height:22px;border-radius:50%;background:#2c2c3e;color:#fff;font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">${i + 1}</span>
                        <span style="font-size:13px;font-weight:600;color:#111827;flex:1;">${q.label || `<em style="color:#9ca3af;">Untitled Question ${i + 1}</em>`}</span>
                        ${reqBadge}
                    </div>
                    <div style="font-size:11px;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.04em;font-weight:600;">${typeLabel}</div>
                    ${answerPreview}
                </div>`;
            }).join('');

        viewProgramBody.innerHTML = `
        <div class="sports-info-section">
            <h4 class="sports-info-title">Program Information</h4>
            <div class="sports-info-grid">
                <div class="sports-info-item">
                    <label>Program Name</label>
                    <span>${p.programName || '—'}</span>
                </div>
                <div class="sports-info-item">
                    <label>Date Created</label>
                    <span>${p.createdAt ? new Date(p.createdAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'}</span>
                </div>
                <div class="sports-info-item">
                    <label>Start Date</label>
                    <span>${p.startDate || '—'}</span>
                </div>
                <div class="sports-info-item">
                    <label>End Date</label>
                    <span>${p.endDate || '—'}</span>
                </div>
                <div class="sports-info-item">
                    <label>Start Time</label>
                    <span>${p.startTime || '—'}</span>
                </div>
                <div class="sports-info-item">
                    <label>End Time</label>
                    <span>${p.endTime || '—'}</span>
                </div>
            </div>
        </div>

        ${p.announcement ? `
        <div class="sports-info-section">
            <h4 class="sports-info-title">Announcement</h4>
            <p style="font-size:13px;color:#374151;line-height:1.6;">${p.announcement}</p>
        </div>` : ''}

        <div class="sports-info-section">
            <h4 class="sports-info-title">
                Application Form Questions
                <span style="margin-left:8px;background:#f3f4f6;color:#6b7280;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;">${qList.length} question${qList.length !== 1 ? 's' : ''}</span>
            </h4>
            ${questionsHTML}
        </div>
        `;

        viewProgramModal.style.display = 'flex';
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
                        <label>Full Name</label>
                        <span>${formatFullName(app)}</span>
                    </div>
                    <div class="sports-info-item">
                        <label>Date of Birth</label>
                        <span>${app.dateOfBirth}</span>
                    </div>
                    <div class="sports-info-item">
                        <label>Age</label>
                        <span>${app.age}</span>
                    </div>
                    <div class="sports-info-item">
                        <label>Contact Number</label>
                        <span>${app.contact}</span>
                    </div>
                    <div class="sports-info-item sports-info-full">
                        <label>Email</label>
                        <span>${app.email}</span>
                    </div>
                    <div class="sports-info-item sports-info-full">
                        <label>Complete Address</label>
                        <span>${app.address}</span>
                    </div>
                </div>
            </div>

            <!-- Sports Information -->
            <div class="sports-info-section">
                <h4 class="sports-info-title">Sports Information</h4>
                <div class="sports-info-grid">
                    <div class="sports-info-item">
                        <label>Sport</label>
                        <span>${app.sport}</span>
                    </div>
                    <div class="sports-info-item">
                        <label>Division</label>
                        <span>${app.division}</span>
                    </div>
                    <div class="sports-info-item">
                        <label>Date Applied</label>
                        <span>${app.dateApplied}</span>
                    </div>
                </div>
            </div>

            <!-- Submitted Requirements -->
            <div class="sports-info-section">
                <h4 class="sports-info-title">Submitted Requirements</h4>
                ${renderRequirementFile(app.requirementFile)}
            </div>

            <!-- Payment Status -->
            <div class="sports-info-section">
                <h4 class="sports-info-title">Payment Status <span class="sports-req">*</span></h4>
                <div class="sports-payment-group">
                    <label class="sports-payment-label sports-payment-paid ${app.paymentStatus === 'Paid' ? 'is-selected' : ''}" id="payLabelPaid">
                        <input type="radio" name="payment" value="Paid" ${app.paymentStatus === 'Paid' ? 'checked' : ''} style="display:none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Paid
                    </label>
                    <label class="sports-payment-label sports-payment-notpaid ${app.paymentStatus === 'Not Paid' ? 'is-selected' : ''}" id="payLabelNotPaid">
                        <input type="radio" name="payment" value="Not Paid" ${app.paymentStatus === 'Not Paid' ? 'checked' : ''} style="display:none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Not Paid
                    </label>
                </div>
            </div>
        `;

        // Wire up payment radio toggle styles
        viewModalBody.querySelectorAll('input[name="payment"]').forEach(radio => {
            radio.addEventListener('change', () => {
                const paidLabel    = viewModalBody.querySelector('#payLabelPaid');
                const notPaidLabel = viewModalBody.querySelector('#payLabelNotPaid');
                if (paidLabel)    paidLabel.classList.toggle('is-selected',    radio.value === 'Paid'     && radio.checked);
                if (notPaidLabel) notPaidLabel.classList.toggle('is-selected', radio.value === 'Not Paid' && radio.checked);
            });
        });

        viewModal.style.display = 'flex';
    }

    function renderRequirementFile(file) {
        if (!file) {
            return `<div style="font-size:13px;color:#9ca3af;font-style:italic;">No file uploaded.</div>`;
        }
        return `
            <a href="#" download="${file.name}" class="sports-req-file-card" title="Click to download ${file.name}">
                <div class="sports-req-file-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><polyline points="9 15 12 18 15 15"/></svg>
                </div>
                <div class="sports-req-file-info">
                    <div class="sports-req-file-name">${file.name}</div>
                    <div class="sports-req-file-meta">${file.size} &nbsp;·&nbsp; Max 10 MB</div>
                </div>
                <span class="sports-req-file-badge">Uploaded</span>
            </a>
        `;
    }

    function closeViewModal(preserveAppId = false) {
        viewModal.style.display = 'none';
        viewModal.classList.remove('sports-modal-maximized');
        viewModal.classList.remove('sports-overlay-maximized');
        const viewBox = document.getElementById('viewBox');
        if (viewBox) viewBox.classList.remove('sports-modal-maximized');
        const viewMaximize = document.getElementById('viewMaximize');
        if (viewMaximize) { viewMaximize.textContent = '□'; viewMaximize.title = 'Fullscreen'; }
        if (!preserveAppId) currentApplicationId = null;
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

        // Close view modal but preserve currentApplicationId for the rejection flow
        closeViewModal(true);
        openRejectReasonModal();
    }

    // ── Rejection Reason Modal ──────────────────────────────────────────────
    function openRejectReasonModal() {
        // Reset all checkboxes and other field
        document.querySelectorAll('.reject-reason-checkbox').forEach(cb => cb.checked = false);
        if (rejectReasonOtherText) rejectReasonOtherText.value = '';
        if (rejectReasonOtherField) rejectReasonOtherField.style.display = 'none';
        const counter = document.getElementById('rejectOtherCharCount');
        if (counter) { counter.textContent = '0 / 500'; counter.style.color = '#9ca3af'; }
        // Clear any previous inline error
        const inlineErr = document.getElementById('rejectInlineError');
        if (inlineErr) inlineErr.style.display = 'none';
        if (rejectReasonModal) rejectReasonModal.style.display = 'flex';
    }

    function closeRejectReasonModal() {
        if (rejectReasonModal) rejectReasonModal.style.display = 'none';
        currentApplicationId = null;
    }

    function confirmReject() {
        // Clear previous inline error
        const inlineErr = document.getElementById('rejectInlineError');
        if (inlineErr) inlineErr.style.display = 'none';

        const reasons = getSelectedReasons();

        // Validate: at least one reason must be selected
        if (reasons.length === 0) {
            if (inlineErr) {
                inlineErr.textContent = 'Rejection reason is required. Please select at least one reason.';
                inlineErr.style.display = 'block';
            } else {
                showToast('Rejection reason is required', 'error');
            }
            return;
        }

        // If "Other" is selected, validate the textarea
        const otherCheckbox = document.getElementById('rejectReasonOtherCheckbox');
        if (otherCheckbox && otherCheckbox.checked) {
            const otherText = document.getElementById('rejectReasonOtherText').value.trim();
            if (!otherText) {
                if (inlineErr) {
                    inlineErr.textContent = 'Please specify the reason in the Other field.';
                    inlineErr.style.display = 'block';
                } else {
                    showToast('Please specify the reason in the Other field', 'error');
                }
                return;
            }
            if (otherText.length > 500) {
                if (inlineErr) {
                    inlineErr.textContent = 'Other reason must not exceed 500 characters.';
                    inlineErr.style.display = 'block';
                } else {
                    showToast('Other reason must not exceed 500 characters', 'error');
                }
                return;
            }
        }

        const app = applications.find(a => a.id === currentApplicationId);
        if (!app) {
            showToast('Application not found. Please try again.', 'error');
            closeRejectReasonModal();
            return;
        }

        // Save rejection data
        app.status = 'Rejected';
        app.rejectionReasons = reasons;
        app.rejectedDate = new Date().toISOString();

        // Persist to rejected list
        const rejectedApps = JSON.parse(localStorage.getItem('sports_rejected') || '[]');
        rejectedApps.push(app);
        localStorage.setItem('sports_rejected', JSON.stringify(rejectedApps));

        // Remove from active applications list
        applications = applications.filter(a => a.id !== currentApplicationId);
        localStorage.setItem('sports_applications', JSON.stringify(applications));

        closeRejectReasonModal();
        renderTable();
        updateStats();
        showToast('Application successfully rejected', 'success');
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
