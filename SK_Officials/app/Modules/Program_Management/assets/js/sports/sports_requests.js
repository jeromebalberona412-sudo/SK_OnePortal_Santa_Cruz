document.addEventListener('DOMContentLoaded', () => {
    initSportsRequests();
});

// â”€â”€ Sample Data â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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
    }
];

function initSportsRequests() {
    const pageMode = document.body.getAttribute('data-sports-page') || 'requests';

    // Seed sample data if localStorage is empty
    if (!localStorage.getItem('sports_applications_seeded_v2')) {
        localStorage.setItem('sports_applications', JSON.stringify(SAMPLE_SPORTS_DATA));
        localStorage.setItem('sports_applications_seeded_v2', '1');
    }
    
    // Force re-seed to ensure sample data is present
    let applications = JSON.parse(localStorage.getItem('sports_applications') || '[]');
    if (applications.length === 0) {
        localStorage.setItem('sports_applications', JSON.stringify(SAMPLE_SPORTS_DATA));
        applications = JSON.parse(localStorage.getItem('sports_applications') || '[]');
    }
    
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

    // â”€â”€ Initialize (applications page only) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    if (pageMode === 'requests' && tbody) {
        renderTable();
        updateStats();
    }

    // â”€â”€ Event Listeners â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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

    // Create Program Modal â€” open is now handled inside the question builder block above
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
            viewMaximize.textContent = isMax ? 'â§‰' : 'â–¡';
            viewMaximize.title = isMax ? 'Restore Down' : 'Fullscreen';
        });
    }

    // Rejection Reason Modal â€” mutual exclusion + char counter
    const regularCheckboxes = document.querySelectorAll('.reject-reason-checkbox:not(#rejectReasonOtherCheckbox)');

    // When "Other" is checked â†’ uncheck all regular checkboxes
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

    // When any regular checkbox is checked â†’ uncheck "Other"
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

    // â”€â”€ Functions â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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

        // Attach event listeners to delete buttons
        tbody.querySelectorAll('.sports-tbl-btn-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = parseInt(btn.getAttribute('data-id'), 10);
                if (typeof window._sportsRequestsDeleteModal === 'function') {
                    window._sportsRequestsDeleteModal(id);
                }
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

            // Age category filter â€” match by age range
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

    // â”€â”€ Create Program Modal + Question Builder â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    function closeCreateProgramModal() {
        if (createProgramModal) createProgramModal.style.display = 'none';
        resetProgramForm();
        if (pageMode === 'create-program') {
            window.location.href = '/sports-requests';
        }
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
        const committeeHeadEl = document.getElementById('committeeHead');
        if (committeeHeadEl) committeeHeadEl.value = committeeHeadEl.defaultValue || 'SK Jerome Balberona';
        // Reset announcement
        const announcementEl = document.getElementById('spfbAnnouncement');
        if (announcementEl) announcementEl.value = '';
        const countEl = document.getElementById('spfbAnnouncementCount');
        if (countEl) countEl.textContent = '0';
        if (window.GFormBuilder) window.GFormBuilder.reset();
    }

    // â”€â”€ Date input â€” native date picker with today-min â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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

        // Start date change â†’ update end date min
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

    // â”€â”€ Time dropdowns â€” no free-text, just read the selects â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function initTimeInputs() {
        // Nothing to wire â€” dropdowns handle themselves.
        // Validation happens in handleCreateProgram.
    }

    // â”€â”€ Get time string from dropdowns â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function getTimeFromDropdowns(prefix) {
        const h = document.getElementById(prefix + 'Hour')?.value || '';
        const m = document.getElementById(prefix + 'Min')?.value || '';
        const p = document.getElementById(prefix + 'Period')?.value || '';
        if (!h || !m || !p) return '';
        return `${h}:${m} ${p}`;
    }

    // â”€â”€ Reset time dropdowns â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function resetTimeDropdowns(prefix) {
        ['Hour', 'Min', 'Period'].forEach(part => {
            const el = document.getElementById(prefix + part);
            if (el) el.value = '';
        });
    }

    // â”€â”€ Parse date YYYY-MM-DD (native date input value) â†’ Date â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function parseDateYYYYMMDD(str) {
        if (!str) return null;
        const parts = str.split('-');
        if (parts.length !== 3) return null;
        return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
    }

    // â”€â”€ Parse time string HH:MM AM/PM â†’ minutes since midnight â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function parseTimeToMinutes(str) {
        const m = str.trim().match(/^(1[0-2]|0?[1-9]):([0-5][0-9])\s*(AM|PM)$/i);
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
        const committeeHead = (document.getElementById('committeeHead')?.value || '').trim();

        // Date values (YYYY-MM-DD from native date input)
        const startDateRaw = (document.getElementById('startDate')?.value || '').trim();
        const endDateRaw   = (document.getElementById('endDate')?.value || '').trim();

        // Time values from dropdowns
        const startTimeStr = getTimeFromDropdowns('startTime');
        const endTimeStr   = getTimeFromDropdowns('endTime');

        // â”€â”€ Required field check â”€â”€
        if (!programName) { showToast('Please select a Program Name.', 'error'); return; }
        if (!committeeHead) { showToast('Please enter the Program / Committee Head (SK).', 'error'); return; }
        if (!announcement) { showToast('Please enter an Announcement.', 'error'); return; }
        if (!startDateRaw) { showToast('Please select a Start Date.', 'error'); return; }
        if (!endDateRaw)   { showToast('Please select an End Date.', 'error'); return; }
        if (!startTimeStr) { showToast('Please select a complete Start Time (Hour, Minute, AM/PM).', 'error'); return; }
        if (!endTimeStr)   { showToast('Please select a complete End Time (Hour, Minute, AM/PM).', 'error'); return; }

        // â”€â”€ Date validation â”€â”€
        const today = new Date(); today.setHours(0,0,0,0);
        const startDate = parseDateYYYYMMDD(startDateRaw);
        const endDate   = parseDateYYYYMMDD(endDateRaw);

        if (!startDate) { showToast('Invalid Start Date.', 'error'); return; }
        if (startDate < today) { showToast('Start Date must be today or a future date.', 'error'); return; }
        if (!endDate) { showToast('Invalid End Date.', 'error'); return; }
        if (endDate < startDate) { showToast('End Date must be the same or after the Start Date.', 'error'); return; }

        // â”€â”€ Time validation â”€â”€
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

        const programQuestions = window.GFormBuilder
            ? window.GFormBuilder.getQuestions()
            : [];
        const qCount = programQuestions.length;

        const finishCreate = () => {
            const program = {
                id: Date.now(),
                programName,
                committeeHead,
                announcement,
                startDate: fmtDate(startDateRaw),
                endDate:   fmtDate(endDateRaw),
                startTime: startTimeStr,
                endTime:   endTimeStr,
                questions: JSON.parse(JSON.stringify(programQuestions)),
                createdAt: new Date().toISOString()
            };

            const programs = JSON.parse(localStorage.getItem('sports_programs') || '[]');
            programs.push(program);
            localStorage.setItem('sports_programs', JSON.stringify(programs));

            if (typeof window.hideLoading === 'function') window.hideLoading();

            if (pageMode === 'create-program') {
                showProgramSuccessModal(programName, qCount);
                return;
            }
            showToast(`Sports program created with ${qCount} question${qCount !== 1 ? 's' : ''}!`, 'success');
            closeCreateProgramModal();
        };

        if (typeof window.showLoading === 'function') {
            window.showLoading('Creating sports program');
        }
        setTimeout(finishCreate, 500);
    }

    function showProgramSuccessModal(programName, qCount) {
        const modal = document.getElementById('sportsProgramSuccessModal');
        const msgEl = document.getElementById('sportsProgramSuccessMessage');
        const okBtn = document.getElementById('sportsProgramSuccessOk');
        if (!modal) {
            window.location.href = '/sports-application-history';
            return;
        }
        if (msgEl) {
            msgEl.textContent = `"${programName}" was created successfully with ${qCount} question${qCount !== 1 ? 's' : ''}.`;
        }
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        const goHistory = () => {
            if (typeof window.showLoading === 'function') window.showLoading('Loading');
            window.location.href = '/sports-application-history';
        };

        if (okBtn && !okBtn.dataset.wired) {
            okBtn.dataset.wired = '1';
            okBtn.addEventListener('click', goHistory);
        }
        if (!modal.dataset.wired) {
            modal.dataset.wired = '1';
            modal.addEventListener('click', (e) => { if (e.target === modal) goHistory(); });
        }
    }

    if (window.GFormBuilder && document.getElementById('spfbQuestionList')) {
        window.GFormBuilder.init({ showToast });
    }


    // â”€â”€ Fullscreen toggle helper â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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
            btn.textContent = isMax ? 'â§‰' : 'â–¡';
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
            btn.textContent = isMax ? 'â§‰' : 'â–¡';
            btn.title = isMax ? 'Restore Down' : 'Fullscreen';
        });
    })();

    // Initialize date/time formatters when modal opens
    function wireCreateProgramForm() {
        initDateInputs();
        initTimeInputs();

        const programNameSelect = document.getElementById('programName');
        const programNameOther = document.getElementById('programNameOther');
        if (programNameSelect && programNameOther && !programNameSelect.dataset.wired) {
            programNameSelect.dataset.wired = '1';
            programNameSelect.addEventListener('change', function () {
                programNameOther.style.display = this.value === 'Other' ? 'block' : 'none';
                if (this.value !== 'Other') programNameOther.value = '';
            });
        }

        const announcementEl = document.getElementById('spfbAnnouncement');
        const countEl = document.getElementById('spfbAnnouncementCount');
        if (announcementEl && countEl && !announcementEl.dataset.wired) {
            announcementEl.dataset.wired = '1';
            announcementEl.addEventListener('input', function () {
                countEl.textContent = String(this.value.length);
            });
        }
    }

    if (btnCreateProgram && createProgramModal) {
        btnCreateProgram.addEventListener('click', () => {
            createProgramModal.style.display = 'flex';
            wireCreateProgramForm();
        });
    }

    if (pageMode === 'create-program') {
        wireCreateProgramForm();
    }

    // â”€â”€ Created Sports Programs Modal â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    const btnViewCreatedPrograms  = document.getElementById('btnViewCreatedPrograms');
    const createdProgramsModal    = document.getElementById('createdProgramsModal');
    const createdProgramsBox      = document.getElementById('createdProgramsBox');
    const createdProgramsClose    = document.getElementById('createdProgramsClose');
    const createdProgramsMaximize = document.getElementById('createdProgramsMaximize');
    const createdProgramsTableBody = document.getElementById('createdProgramsTableBody');

    if (pageMode === 'history' && createdProgramsTableBody) {
        renderCreatedProgramsTable();
    }

    if (btnViewCreatedPrograms && createdProgramsModal) {
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
            createdProgramsMaximize.textContent = 'â–¡';
        });
    }
    if (createdProgramsModal) {
        createdProgramsModal.addEventListener('click', (e) => {
            if (e.target === createdProgramsModal) {
                createdProgramsModal.style.display = 'none';
                createdProgramsBox.classList.remove('sports-modal-maximized');
                createdProgramsModal.classList.remove('sports-overlay-maximized');
                createdProgramsMaximize.textContent = 'â–¡';
            }
        });
    }
    if (createdProgramsMaximize && createdProgramsBox) {
        createdProgramsMaximize.addEventListener('click', (e) => {
            e.stopPropagation();
            const isMax = !createdProgramsBox.classList.contains('sports-modal-maximized');
            createdProgramsBox.classList.toggle('sports-modal-maximized', isMax);
            createdProgramsModal.classList.toggle('sports-overlay-maximized', isMax);
            createdProgramsMaximize.textContent = isMax ? 'â§‰' : 'â–¡';
            createdProgramsMaximize.title = isMax ? 'Restore Down' : 'Fullscreen';
        });
    }

    function renderCreatedProgramsTable() {
        const programs = JSON.parse(localStorage.getItem('sports_programs') || '[]');
        if (!createdProgramsTableBody) return;

        if (programs.length === 0) {
            createdProgramsTableBody.innerHTML = `<tr class="sports-empty-row"><td colspan="10" style="text-align:center;padding:32px;color:#6b7280;font-size:13px;">No sports programs created yet. <a href="/sports-create-program" style="color:#2563eb;font-weight:600;">Create Sports Program</a></td></tr>`;
            return;
        }

        // Show newest first
        const sorted = [...programs].reverse();
        createdProgramsTableBody.innerHTML = sorted.map((p, i) => {
            const createdDate = p.createdAt
                ? new Date(p.createdAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
                : 'â€”';
            const createdTime = p.createdAt
                ? new Date(p.createdAt).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })
                : 'â€”';
            const qCount = (p.questions || []).length;
            return `
            <tr>
                <td style="font-weight:600;text-align:center;padding-left:12px;">${p.programName || 'â€”'}</td>
                <td style="text-align:center;">${p.committeeHead || 'â€”'}</td>
                <td style="text-align:center;">${p.startDate || 'â€”'}</td>
                <td style="text-align:center;">${p.endDate || 'â€”'}</td>
                <td style="text-align:center;">${p.startTime || 'â€”'}</td>
                <td style="text-align:center;">${p.endTime || 'â€”'}</td>
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

    // â”€â”€ View Program Details Modal â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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
            viewProgramMaximize.textContent = 'â–¡';
        });
    }
    if (viewProgramModal) {
        viewProgramModal.addEventListener('click', (e) => {
            if (e.target === viewProgramModal) {
                viewProgramModal.style.display = 'none';
                viewProgramBox.classList.remove('sports-modal-maximized');
                viewProgramModal.classList.remove('sports-overlay-maximized');
                viewProgramMaximize.textContent = 'â–¡';
            }
        });
    }
    if (viewProgramMaximize && viewProgramBox) {
        viewProgramMaximize.addEventListener('click', (e) => {
            e.stopPropagation();
            const isMax = !viewProgramBox.classList.contains('sports-modal-maximized');
            viewProgramBox.classList.toggle('sports-modal-maximized', isMax);
            viewProgramModal.classList.toggle('sports-overlay-maximized', isMax);
            viewProgramMaximize.textContent = isMax ? 'â§‰' : 'â–¡';
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
                    const opts = (q.options || []).map(o => `<li style="font-size:12px;color:#374151;margin-bottom:3px;">â€¢ ${o}</li>`).join('');
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
                    <span>${p.programName || 'â€”'}</span>
                </div>
                <div class="sports-info-item sports-info-full">
                    <label>Program / Committee Head (SK)</label>
                    <span>${p.committeeHead || 'â€”'}</span>
                </div>
                <div class="sports-info-item">
                    <label>Date Created</label>
                    <span>${p.createdAt ? new Date(p.createdAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'â€”'}</span>
                </div>
                <div class="sports-info-item">
                    <label>Start Date</label>
                    <span>${p.startDate || 'â€”'}</span>
                </div>
                <div class="sports-info-item">
                    <label>End Date</label>
                    <span>${p.endDate || 'â€”'}</span>
                </div>
                <div class="sports-info-item">
                    <label>Start Time</label>
                    <span>${p.startTime || 'â€”'}</span>
                </div>
                <div class="sports-info-item">
                    <label>End Time</label>
                    <span>${p.endTime || 'â€”'}</span>
                </div>
            </div>
        </div>

        ${p.committeeHead ? `
        <div class="spfb-gc-head-banner" style="margin-bottom:14px;">
            <span class="spfb-gc-head-label">Program / Committee Head</span>
            <span class="spfb-gc-head-name">${p.committeeHead}</span>
        </div>` : ''}

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

    // â”€â”€ View Modal â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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
                    <div class="sports-req-file-meta">${file.size} &nbsp;Â·&nbsp; Max 10 MB</div>
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
        if (viewMaximize) { viewMaximize.textContent = 'â–¡'; viewMaximize.title = 'Fullscreen'; }
        if (!preserveAppId) currentApplicationId = null;
    }

    // â”€â”€ Approve/Reject â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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

    // â”€â”€ Rejection Reason Modal â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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

    // â”€â”€ Toast Notification â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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

// ── Sports Requests: Delete Application (wired after DOMContentLoaded) ───────
document.addEventListener('DOMContentLoaded', () => {
    // We wire delete separately so it doesn't conflict with the main initSportsRequests function
    let _pendingDeleteAppId = null;

    const _scholDeleteModal   = document.getElementById('scholDeleteModal');
    const _scholDeleteClose   = document.getElementById('scholDeleteClose');
    const _scholDeleteCancel  = document.getElementById('scholDeleteCancel');
    const _scholDeleteConfirm = document.getElementById('scholDeleteConfirm');

    function _openDeleteModal(appId) {
        _pendingDeleteAppId = appId;
        if (_scholDeleteModal) _scholDeleteModal.style.display = 'flex';
    }

    function _closeDeleteModal() {
        _pendingDeleteAppId = null;
        if (_scholDeleteModal) _scholDeleteModal.style.display = 'none';
    }

    if (_scholDeleteClose)  _scholDeleteClose.addEventListener('click', _closeDeleteModal);
    if (_scholDeleteCancel) _scholDeleteCancel.addEventListener('click', _closeDeleteModal);
    if (_scholDeleteModal)  _scholDeleteModal.addEventListener('click', e => { if (e.target === _scholDeleteModal) _closeDeleteModal(); });

    if (_scholDeleteConfirm) {
        _scholDeleteConfirm.addEventListener('click', () => {
            if (_pendingDeleteAppId === null) return;
            let apps = JSON.parse(localStorage.getItem('sports_applications') || '[]');
            apps = apps.filter(a => a.id !== _pendingDeleteAppId);
            localStorage.setItem('sports_applications', JSON.stringify(apps));
            _closeDeleteModal();

            // Re-render the table by re-triggering the table render
            const tbody = document.getElementById('scholTableBody');
            const total  = document.getElementById('statTotal');
            const pending = document.getElementById('statPending');
            const approved = document.getElementById('statApproved');
            const rejected = document.getElementById('statRejected');

            if (total)    total.textContent    = apps.length;
            if (pending)  pending.textContent  = apps.filter(a => a.status === 'Pending').length;
            if (approved) approved.textContent = apps.filter(a => a.status === 'Approved').length;
            if (rejected) rejected.textContent = apps.filter(a => a.status === 'Rejected').length;

            // Show toast
            const toastEl = document.getElementById('scholToast');
            const toastMsg = document.getElementById('scholToastMsg');
            if (toastEl && toastMsg) {
                toastMsg.textContent = 'Application deleted successfully.';
                toastEl.style.background = '#22c55e';
                toastEl.style.display = 'flex';
                setTimeout(() => { toastEl.style.display = 'none'; }, 2800);
            }

            // Reload the table rows by firing a search input event
            const searchInput = document.getElementById('scholSearch');
            if (searchInput) searchInput.dispatchEvent(new Event('input'));
        });
    }

    // Expose openDeleteModal globally so the renderTable buttons can call it
    window._sportsRequestsDeleteModal = _openDeleteModal;
});
