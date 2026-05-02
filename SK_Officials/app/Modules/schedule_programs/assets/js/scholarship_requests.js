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
        school_address: 'Brgy. Siniloan, Siniloan, Laguna 4019',
        year_level: '2nd Year',
        program_strand: 'Bachelor of Secondary Education (BSED)',
        purpose: 'Tuition Fees, Books / Equipments',
        purpose_list: ['Tuition Fees', 'Books / Equipments'],
        purpose_others: '',
        cor_certified: false, photo_id: false,
        status: 'Pending',
        submitted_at: 'Jan 10, 2025',
        submitted_time: '08:32 AM',
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
        school_address: 'College, Los Baños, Laguna 4031',
        year_level: '3rd Year',
        program_strand: 'Bachelor of Science in Agriculture (BS Agriculture)',
        purpose: 'Tuition Fees, Living Expenses',
        purpose_list: ['Tuition Fees', 'Living Expenses'],
        purpose_others: '',
        cor_certified: false, photo_id: false,
        status: 'Pending',
        submitted_at: 'Feb 3, 2025',
        submitted_time: '09:15 AM',
    },
    {
        id: 1003,
        last_name: 'Garcia', first_name: 'Ana', middle_name: 'Lim',
        date_of_birth: '2006-11-05', gender: 'Female', age: 18,
        contact_no: '09391234567',
        address: '78 Mabini St., Brgy. Calios, Santa Cruz, Laguna',
        email: 'ana.garcia@email.com',
        school_name: 'Santa Cruz National High School',
        school_address: 'Poblacion, Santa Cruz, Laguna 4009',
        year_level: 'Grade 12',
        program_strand: 'Science, Technology, Engineering and Mathematics (STEM)',
        purpose: 'Books / Equipments',
        purpose_list: ['Books / Equipments'],
        purpose_others: '',
        cor_certified: false, photo_id: false,
        status: 'Pending',
        submitted_at: 'Feb 15, 2025',
        submitted_time: '10:45 AM',
    },
    {
        id: 1004,
        last_name: 'Mendoza', first_name: 'Carlo', middle_name: 'Bautista',
        date_of_birth: '2003-05-18', gender: 'Male', age: 22,
        contact_no: '09501234567',
        address: '12 Bonifacio Rd., Brgy. Calios, Santa Cruz, Laguna',
        email: 'carlo.mendoza@email.com',
        school_name: 'Laguna College of Business and Arts',
        school_address: 'National Highway, Calamba City, Laguna 4027',
        year_level: '4th Year',
        program_strand: 'Bachelor of Science in Business Administration (BSBA)',
        purpose: 'Tuition Fees, Living Expenses, Others (Transportation)',
        purpose_list: ['Tuition Fees', 'Living Expenses', 'Others'],
        purpose_others: 'Transportation',
        cor_certified: false, photo_id: false,
        status: 'Pending',
        submitted_at: 'Mar 1, 2025',
        submitted_time: '02:10 PM',
    },
    {
        id: 1005,
        last_name: 'Torres', first_name: 'Liza', middle_name: 'Villanueva',
        date_of_birth: '2007-09-30', gender: 'Female', age: 17,
        contact_no: '09611234567',
        address: '56 Aguinaldo St., Brgy. Calios, Santa Cruz, Laguna',
        email: 'liza.torres@email.com',
        school_name: 'Calios Elementary School',
        school_address: 'Brgy. Calios, Santa Cruz, Laguna 4009',
        year_level: 'Grade 10',
        program_strand: '',
        purpose: 'Books / Equipments, Living Expenses',
        purpose_list: ['Books / Equipments', 'Living Expenses'],
        purpose_others: '',
        cor_certified: false, photo_id: false,
        status: 'Pending',
        submitted_at: 'Apr 5, 2025',
        submitted_time: '03:55 PM',
    },
    {
        id: 1006,
        last_name: 'Dela Cruz', first_name: 'Jose', middle_name: 'Ramos',
        date_of_birth: '2004-11-20', gender: 'Male', age: 21,
        contact_no: '09721234567',
        address: '88 Magsaysay St., Brgy. Calios, Santa Cruz, Laguna',
        email: 'jose.delacruz@email.com',
        school_name: 'Laguna State Polytechnic University',
        school_address: 'Brgy. Siniloan, Siniloan, Laguna 4019',
        year_level: '3rd Year',
        program_strand: 'Bachelor of Science in Information Technology (BSIT)',
        purpose: 'Tuition Fees, Living Expenses',
        purpose_list: ['Tuition Fees', 'Living Expenses'],
        purpose_others: '',
        cor_certified: false, photo_id: false,
        status: 'Pending',
        submitted_at: 'Jan 20, 2025',
        submitted_time: '07:50 AM',
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
        school_address: 'DBB Road, City of Dasmariñas, Cavite 4114',
        year_level: '2nd Year',
        program_strand: 'Bachelor of Science in Nursing (BSN)',
        purpose: 'Tuition Fees, Books / Equipments',
        purpose_list: ['Tuition Fees', 'Books / Equipments'],
        purpose_others: '',
        cor_certified: false, photo_id: false,
        status: 'Pending',
        submitted_at: 'Feb 5, 2025',
        submitted_time: '11:20 AM',
        approved_at: 'Feb 10, 2025',
        result: 'Passed',
    },
    {
        id: 1008,
        last_name: 'Villanueva', first_name: 'Patrick', middle_name: 'Santos',
        date_of_birth: '2004-03-12', gender: 'Male', age: 21,
        contact_no: '09941234567',
        address: '33 Mabini St., Brgy. Calios, Santa Cruz, Laguna',
        email: 'patrick.villanueva@email.com',
        school_name: 'Laguna University',
        school_address: 'Brgy. Bubukal, Santa Cruz, Laguna 4009',
        year_level: '2nd Year',
        program_strand: 'Bachelor of Science in Information and Communications Technology (BSICT)',
        purpose: 'Tuition Fees, Books / Equipments',
        purpose_list: ['Tuition Fees', 'Books / Equipments'],
        purpose_others: '',
        cor_certified: false, photo_id: false,
        status: 'Pending',
        submitted_at: 'Jan 8, 2025',
        submitted_time: '08:05 AM',
    },
];

function initScholarshipRequests() {
    // Seed sample data if localStorage is empty
    if (!localStorage.getItem('scholarship_requests_seeded_v5')) {
        localStorage.setItem('scholarship_requests', JSON.stringify(SAMPLE_DATA));
        localStorage.setItem('scholarship_requests_seeded_v5', '1');
    }

    let records = JSON.parse(localStorage.getItem('scholarship_requests') || '[]');
    let deleteTargetId = null;
    let viewTargetId   = null;

    const tbody         = document.getElementById('scholTableBody');
    const searchInput   = document.getElementById('scholSearch');
    const startDateFilter = document.getElementById('scholStartDate');
    const endDateFilter = document.getElementById('scholEndDate');
    const startTimeFilter = document.getElementById('scholStartTime');
    const endTimeFilter = document.getElementById('scholEndTime');
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
    const rejectReasonModal = document.getElementById('scholRejectReasonModal');
    const rejectReasonClose = document.getElementById('scholRejectReasonClose');
    const rejectReasonCancel = document.getElementById('scholRejectReasonCancel');
    const rejectReasonConfirm = document.getElementById('scholRejectReasonConfirm');
    const rejectReasonOtherCheckbox = document.getElementById('rejectReasonOtherCheckbox');
    const rejectReasonOtherField = document.getElementById('rejectReasonOtherField');
    const rejectReasonOtherText = document.getElementById('rejectReasonOtherText');

    let filterSearch = '';
    let filterStartDate = '';
    let filterEndDate = '';
    let filterStartTime = '';
    let filterEndTime = '';

    // ── Rejection Reason Modal Handler ──────────────────────────────────────
    if (rejectReasonOtherCheckbox && rejectReasonOtherField) {
        rejectReasonOtherCheckbox.addEventListener('change', () => {
            rejectReasonOtherField.style.display = rejectReasonOtherCheckbox.checked ? 'block' : 'none';
            if (!rejectReasonOtherCheckbox.checked) {
                rejectReasonOtherText.value = '';
            }
        });
    }

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

    [rejectReasonClose, rejectReasonCancel].forEach(btn => {
        if (btn) btn.addEventListener('click', closeRejectReasonModal);
    });
    if (rejectReasonModal) {
        rejectReasonModal.addEventListener('click', e => {
            if (e.target === rejectReasonModal) closeRejectReasonModal();
        });
    }

    // ── Make Form modal ─────────────────────────────────────────────────────
    if (makeFormBtn) makeFormBtn.addEventListener('click', () => {
        makeFormModal.style.display = 'flex';
    });
    [makeFormClose, makeFormCloseFooter].forEach(btn => {
        if (btn) btn.addEventListener('click', () => {
            makeFormModal.style.display = 'none';
            makeFormModal.classList.remove('schol-modal-maximized');
            const mBox = document.getElementById('makeFormBox');
            if (mBox) mBox.classList.remove('schol-modal-maximized');
            const mMaxBtn = document.getElementById('makeFormMaximize');
            if (mMaxBtn) mMaxBtn.textContent = '□';
        });
    });
    if (makeFormModal) makeFormModal.addEventListener('click', e => { if (e.target === makeFormModal) makeFormModal.style.display = 'none'; });

    // Maximize / restore for makeFormModal
    const makeFormMaxBtn = document.getElementById('makeFormMaximize');
    const makeFormBox    = document.getElementById('makeFormBox');
    if (makeFormMaxBtn && makeFormBox) {
        makeFormMaxBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isMax = !makeFormBox.classList.contains('schol-modal-maximized');
            makeFormModal.classList.toggle('schol-modal-maximized', isMax);
            makeFormBox.classList.toggle('schol-modal-maximized', isMax);
            makeFormMaxBtn.textContent = isMax ? '⧉' : '□';
        });
    }

    // ── Display scheduled application info ──────────────────────────────────
    function convert24to12(time24) {
        const [hours, minutes] = time24.split(':');
        const hour = parseInt(hours, 10);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12;
        return `${hour12}:${minutes} ${ampm}`;
    }

    function getScheduleStatus(schedule) {
        // If manual status is set, use it
        if (schedule.status && schedule.status !== 'auto') {
            const statusMap = {
                'open': { status: 'Open', color: '#22c55e', bgColor: '#dcfce7', textColor: '#15803d' },
                'closed': { status: 'Closed', color: '#ef4444', bgColor: '#fee2e2', textColor: '#b91c1c' },
                'upcoming': { status: 'Upcoming', color: '#fbbf24', bgColor: '#fef3c7', textColor: '#92400e' }
            };
            return statusMap[schedule.status] || statusMap['upcoming'];
        }
        
        // Auto-calculate based on date/time
        const now = new Date();
        const openDateTime = new Date(`${schedule.openDate}T${schedule.openTime}`);
        const closeDateTime = new Date(`${schedule.closeDate}T${schedule.closeTime}`);

        if (now < openDateTime) {
            return { status: 'Upcoming', color: '#fbbf24', bgColor: '#fef3c7', textColor: '#92400e' };
        } else if (now >= openDateTime && now <= closeDateTime) {
            return { status: 'Open', color: '#22c55e', bgColor: '#dcfce7', textColor: '#15803d' };
        } else {
            return { status: 'Closed', color: '#ef4444', bgColor: '#fee2e2', textColor: '#b91c1c' };
        }
    }

    function displayScheduledInfo() {
        const schedule = JSON.parse(localStorage.getItem('scholarship_schedule') || 'null');
        const scheduledAppInfo = document.getElementById('scheduledAppInfo');
        const scheduleInfoText = document.getElementById('scheduleInfoText');
        const scheduleStatusBadge = document.getElementById('scheduleStatusBadge');
        const makeFormBtn = document.getElementById('btnMakeForm');
        
        if (schedule && scheduledAppInfo && scheduleInfoText && scheduleStatusBadge) {
            const fmt = d => new Date(d + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const openTime12 = convert24to12(schedule.openTime);
            const closeTime12 = convert24to12(schedule.closeTime);
            
            const statusInfo = getScheduleStatus(schedule);
            
            scheduleInfoText.innerHTML = `
                <div style="margin-bottom:6px;"><strong>Opens:</strong> ${fmt(schedule.openDate)} at ${openTime12}</div>
                <div><strong>Closes:</strong> ${fmt(schedule.closeDate)} at ${closeTime12}</div>
            `;
            
            scheduleStatusBadge.textContent = statusInfo.status;
            scheduleStatusBadge.style.backgroundColor = statusInfo.bgColor;
            scheduleStatusBadge.style.color = statusInfo.textColor;
            
            scheduledAppInfo.style.display = 'block';
            
            // Disable the "Schedule" button when a schedule exists
            if (makeFormBtn) {
                makeFormBtn.disabled = true;
                makeFormBtn.style.opacity = '0.5';
                makeFormBtn.style.cursor = 'not-allowed';
                makeFormBtn.title = 'A schedule already exists. Edit the existing schedule to make changes.';
            }
        } else {
            // Enable the button if no schedule exists
            if (makeFormBtn) {
                makeFormBtn.disabled = false;
                makeFormBtn.style.opacity = '1';
                makeFormBtn.style.cursor = 'pointer';
                makeFormBtn.title = '';
            }
        }
    }

    // Edit schedule button
    const btnEditSchedule = document.getElementById('btnEditSchedule');
    if (btnEditSchedule) {
        btnEditSchedule.addEventListener('click', () => {
            makeFormModal.style.display = 'flex';
            // Load existing schedule into form
            const schedule = JSON.parse(localStorage.getItem('scholarship_schedule') || 'null');
            if (schedule) {
                document.getElementById('schedOpenDate').value = schedule.openDate;
                document.getElementById('schedOpenTime').value = schedule.openTime;
                document.getElementById('schedCloseDate').value = schedule.closeDate;
                document.getElementById('schedCloseTime').value = schedule.closeTime;
                document.getElementById('schedStatus').value = schedule.status || 'auto';
            }
        });
    }

    // View Schedule List button
    const btnViewScheduleList = document.getElementById('btnViewScheduleList');
    const scheduleListModal = document.getElementById('scheduleListModal');
    const scheduleListClose = document.getElementById('scheduleListClose');
    const scheduleListTableBody = document.getElementById('scheduleListTableBody');
    const scheduleListBox = document.getElementById('scheduleListBox');
    const scheduleListMaximize = document.getElementById('scheduleListMaximize');

    // View Schedule Details Modal
    const viewScheduleModal = document.getElementById('viewScheduleModal');
    const viewScheduleClose = document.getElementById('viewScheduleClose');
    const viewScheduleBody = document.getElementById('viewScheduleBody');

    // Activate and Delete confirmation modals
    const activateScheduleModal = document.getElementById('activateScheduleModal');
    const activateScheduleClose = document.getElementById('activateScheduleClose');
    const activateScheduleCancel = document.getElementById('activateScheduleCancel');
    const activateScheduleConfirm = document.getElementById('activateScheduleConfirm');
    
    const deleteScheduleModal = document.getElementById('deleteScheduleModal');
    const deleteScheduleClose = document.getElementById('deleteScheduleClose');
    const deleteScheduleCancel = document.getElementById('deleteScheduleCancel');
    const deleteScheduleConfirm = document.getElementById('deleteScheduleConfirm');

    let pendingScheduleAction = null; // Store pending action {type: 'activate'|'delete', id: number}
    let viewScheduleId = null; // Store schedule ID for viewing

    if (btnViewScheduleList) {
        btnViewScheduleList.addEventListener('click', () => {
            renderScheduleList();
            scheduleListModal.style.display = 'flex';
        });
    }

    // Maximize / restore for scheduleListModal
    if (scheduleListMaximize && scheduleListBox) {
        scheduleListMaximize.addEventListener('click', function(e) {
            e.stopPropagation();
            const isMax = !scheduleListBox.classList.contains('schol-modal-maximized');
            scheduleListModal.classList.toggle('schol-modal-maximized', isMax);
            scheduleListBox.classList.toggle('schol-modal-maximized', isMax);
            scheduleListMaximize.textContent = isMax ? '⧉' : '□';
        });
    }

    // View Schedule Details Modal handlers
    function openViewScheduleModal(scheduleId) {
        const scheduleList = JSON.parse(localStorage.getItem('scholarship_schedule_list') || '[]');
        const schedule = scheduleList.find(s => s.id === scheduleId);
        
        if (!schedule) return;

        const fmt = d => new Date(d + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        const openTime12 = convert24to12(schedule.openTime);
        const closeTime12 = convert24to12(schedule.closeTime);
        const statusInfo = getScheduleStatus(schedule);
        const createdDate = new Date(schedule.createdAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });

        viewScheduleBody.innerHTML = `
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div style="background:#f9fafb;border-radius:10px;padding:16px;border:1.5px solid #e5e7eb;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                        <span style="font-size:13px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;">Schedule ID</span>
                        <span style="font-size:16px;font-weight:800;color:#111827;">#${scheduleId}</span>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span style="font-size:13px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;">Status</span>
                        <span class="schol-pill" style="background:${statusInfo.bgColor};color:${statusInfo.textColor};">${statusInfo.status}</span>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div style="background:#fff;border-radius:10px;padding:14px;border:1.5px solid #e5e7eb;">
                        <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:8px;">Open Date</div>
                        <div style="font-size:15px;font-weight:700;color:#111827;">${fmt(schedule.openDate)}</div>
                    </div>
                    <div style="background:#fff;border-radius:10px;padding:14px;border:1.5px solid #e5e7eb;">
                        <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:8px;">Open Time</div>
                        <div style="font-size:15px;font-weight:700;color:#111827;">${openTime12}</div>
                    </div>
                    <div style="background:#fff;border-radius:10px;padding:14px;border:1.5px solid #e5e7eb;">
                        <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:8px;">Close Date</div>
                        <div style="font-size:15px;font-weight:700;color:#111827;">${fmt(schedule.closeDate)}</div>
                    </div>
                    <div style="background:#fff;border-radius:10px;padding:14px;border:1.5px solid #e5e7eb;">
                        <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:8px;">Close Time</div>
                        <div style="font-size:15px;font-weight:700;color:#111827;">${closeTime12}</div>
                    </div>
                </div>

                <div style="background:#fff;border-radius:10px;padding:14px;border:1.5px solid #e5e7eb;">
                    <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:8px;">Created At</div>
                    <div style="font-size:14px;font-weight:600;color:#374151;">${createdDate}</div>
                </div>

                ${schedule.status && schedule.status !== 'auto' ? `
                <div style="background:#fffbeb;border-radius:10px;padding:14px;border:1.5px solid #fde68a;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <div style="font-size:11px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:0.04em;">Manual Status Override</div>
                    </div>
                    <div style="font-size:13px;color:#78350f;">This schedule has a manually set status: <strong>${schedule.status.charAt(0).toUpperCase() + schedule.status.slice(1)}</strong></div>
                </div>
                ` : ''}
            </div>
        `;

        viewScheduleModal.style.display = 'flex';
    }

    function closeViewScheduleModal() {
        viewScheduleModal.style.display = 'none';
        viewScheduleId = null;
    }

    [viewScheduleClose].forEach(btn => {
        if (btn) btn.addEventListener('click', closeViewScheduleModal);
    });

    if (viewScheduleModal) {
        viewScheduleModal.addEventListener('click', (e) => {
            if (e.target === viewScheduleModal) closeViewScheduleModal();
        });
    }

    function renderScheduleList() {
        const scheduleList = JSON.parse(localStorage.getItem('scholarship_schedule_list') || '[]');
        scheduleListTableBody.innerHTML = '';

        if (scheduleList.length === 0) {
            scheduleListTableBody.innerHTML = '<tr class="schol-empty-row"><td colspan="8">No scheduled applications found.</td></tr>';
            return;
        }

        scheduleList.reverse().forEach((sched, index) => {
            const fmt = d => new Date(d + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const openTime12 = convert24to12(sched.openTime);
            const closeTime12 = convert24to12(sched.closeTime);
            const statusInfo = getScheduleStatus(sched);
            const createdDate = new Date(sched.createdAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="text-align:center;font-weight:600;">#${sched.id || (scheduleList.length - index)}</td>
                <td style="text-align:center;">${fmt(sched.openDate)}</td>
                <td style="text-align:center;">${openTime12}</td>
                <td style="text-align:center;">${fmt(sched.closeDate)}</td>
                <td style="text-align:center;">${closeTime12}</td>
                <td style="text-align:center;"><span class="schol-pill" style="background:${statusInfo.bgColor};color:${statusInfo.textColor};">${statusInfo.status}</span></td>
                <td style="text-align:center;">${createdDate}</td>
                <td style="text-align:center;">
                    <div class="schol-tbl-actions">
                        <button class="schol-tbl-btn schol-tbl-btn-view" data-action="view-schedule" data-id="${sched.id}">View</button>
                    </div>
                </td>
            `;
            scheduleListTableBody.appendChild(tr);
        });
    }

    // Handle schedule list actions
    if (scheduleListTableBody) {
        scheduleListTableBody.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-action]');
            if (!btn) return;
            
            const action = btn.getAttribute('data-action');
            const id = parseInt(btn.getAttribute('data-id'), 10);
            
            if (action === 'view-schedule') {
                openViewScheduleModal(id);
            }
        });
    }

    [scheduleListClose].forEach(btn => {
        if (btn) btn.addEventListener('click', () => {
            scheduleListModal.style.display = 'none';
            scheduleListModal.classList.remove('schol-modal-maximized');
            if (scheduleListBox) scheduleListBox.classList.remove('schol-modal-maximized');
            if (scheduleListMaximize) scheduleListMaximize.textContent = '□';
        });
    });

    if (scheduleListModal) {
        scheduleListModal.addEventListener('click', (e) => {
            if (e.target === scheduleListModal) {
                scheduleListModal.style.display = 'none';
                scheduleListModal.classList.remove('schol-modal-maximized');
                if (scheduleListBox) scheduleListBox.classList.remove('schol-modal-maximized');
                if (scheduleListMaximize) scheduleListMaximize.textContent = '□';
            }
        });
    }

    // Display schedule on page load
    displayScheduledInfo();

    // ── Save Schedule ────────────────────────────────────────────────────────
    const btnSaveSchedule = document.getElementById('btnSaveSchedule');
    if (btnSaveSchedule) {
        btnSaveSchedule.addEventListener('click', () => {
            const openDate  = document.getElementById('schedOpenDate').value;
            const openTime  = document.getElementById('schedOpenTime').value;
            const closeDate = document.getElementById('schedCloseDate').value;
            const closeTime = document.getElementById('schedCloseTime').value;
            const status    = document.getElementById('schedStatus').value;

            if (!openDate || !closeDate) {
                showScholToast('Please set both open and close dates.', 'error');
                return;
            }
            if (closeDate < openDate || (closeDate === openDate && closeTime <= openTime)) {
                showScholToast('Close date/time must be after open date/time.', 'error');
                return;
            }

            const schedule = { 
                openDate, 
                openTime, 
                closeDate, 
                closeTime, 
                status,
                createdAt: new Date().toISOString()
            };
            
            // Store as current active schedule
            localStorage.setItem('scholarship_schedule', JSON.stringify(schedule));
            
            // Also add to schedule history list
            let scheduleList = JSON.parse(localStorage.getItem('scholarship_schedule_list') || '[]');
            schedule.id = Date.now(); // Generate unique ID
            scheduleList.push(schedule);
            localStorage.setItem('scholarship_schedule_list', JSON.stringify(scheduleList));

            // Close modal
            makeFormModal.style.display = 'none';
            
            // Display scheduled info
            displayScheduledInfo();

            showScholToast('Schedule saved successfully!');
        });
    }

    // ── Render ──────────────────────────────────────────────────────────────
    function render() {
        // Sort by submitted date+time ascending (earliest first)
        const parseSubmitted = r => {
            const d = r.submitted_at || '';
            const t = r.submitted_time || '12:00 AM';
            return new Date(`${d} ${t}`).getTime() || 0;
        };

        // Helper function to parse date from "Jan 10, 2025" format to Date object
        const parseDate = (dateStr) => {
            if (!dateStr) return null;
            return new Date(dateStr);
        };

        // Helper function to convert 12-hour time to 24-hour for comparison
        const convertTo24Hour = (time12) => {
            if (!time12) return '';
            const [time, period] = time12.split(' ');
            let [hours, minutes] = time.split(':');
            hours = parseInt(hours, 10);
            
            if (period === 'PM' && hours !== 12) hours += 12;
            if (period === 'AM' && hours === 12) hours = 0;
            
            return `${hours.toString().padStart(2, '0')}:${minutes}`;
        };

        // Filter out Approved and Rejected records from the table display
        const filtered = records.filter(r => {
            const name   = `${r.last_name} ${r.first_name}`.toLowerCase();
            const school = (r.school_name || '').toLowerCase();
            const q      = filterSearch.toLowerCase();
            
            // Search filter
            const matchesSearch = !filterSearch || name.includes(q) || school.includes(q);
            
            // Date range filter
            let matchesDateRange = true;
            if (filterStartDate || filterEndDate) {
                const submittedDate = parseDate(r.submitted_at);
                if (submittedDate) {
                    if (filterStartDate) {
                        const startDate = new Date(filterStartDate);
                        if (submittedDate < startDate) matchesDateRange = false;
                    }
                    if (filterEndDate) {
                        const endDate = new Date(filterEndDate);
                        endDate.setHours(23, 59, 59, 999); // Include the entire end date
                        if (submittedDate > endDate) matchesDateRange = false;
                    }
                }
            }
            
            // Time range filter
            let matchesTimeRange = true;
            if (filterStartTime || filterEndTime) {
                const submittedTime24 = convertTo24Hour(r.submitted_time);
                if (submittedTime24) {
                    if (filterStartTime && submittedTime24 < filterStartTime) matchesTimeRange = false;
                    if (filterEndTime && submittedTime24 > filterEndTime) matchesTimeRange = false;
                }
            }
            
            return r.status === 'Pending' && matchesSearch && matchesDateRange && matchesTimeRange;
        }).sort((a, b) => parseSubmitted(a) - parseSubmitted(b));

        tbody.innerHTML = '';

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr class="schol-empty-row"><td colspan="9">No applications found.</td></tr>`;
        } else {
            filtered.forEach((r, i) => {
                const statusCls = r.status === 'Approved' ? 'schol-pill-approved'
                                : r.status === 'Rejected' ? 'schol-pill-rejected'
                                : 'schol-pill-pending';
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="text-align:center;font-weight:600;">${r.last_name}, ${r.first_name}${r.middle_name ? ' ' + r.middle_name.charAt(0) + '.' : ''}</td>
                    <td style="text-align:center;font-size:12px;">${r.school_name || '—'}</td>
                    <td style="text-align:center;">${r.year_level || '—'}</td>
                    <td style="text-align:center;font-size:12px;">${r.purpose || '—'}</td>
                    <td style="text-align:center;font-size:10px;">
                        <div style="display:flex;flex-direction:column;gap:3px;align-items:center;line-height:1.3;">
                            <span>COR – CERTIFIED TRUE COPY</span>
                            <span>PHOTO COPY OF ID (FRONT AND BACK)</span>
                        </div>
                    </td>
                    <td style="text-align:center;"><span class="schol-pill ${statusCls}">${r.status}</span></td>
                    <td style="text-align:center;">${r.submitted_at || '—'}</td>
                    <td style="text-align:center;font-size:12px;color:#6b7280;">${r.submitted_time || '—'}</td>
                    <td style="text-align:center;">
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
                    <div class="schol-pdf-picture-box"><span>Picture<br>Here</span></div>
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
                        <p class="schol-pdf-inline-title">SUBMITTED REQUIREMENTS: <span style="font-weight:400;font-size:10px;">Note: To be filled out by SK officials</span></p>
                        <div class="schol-pdf-check-list" style="margin-top:8px;">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                    <input type="checkbox" class="view-req-checkbox" data-id="${r.id}" data-field="cor_certified" ${r.cor_certified ? 'checked' : ''} style="cursor:pointer;width:16px;height:16px;accent-color:#000;">
                                    <span style="font-size:11px;font-weight:600;color:#111827;">COR – CERTIFIED TRUE COPY</span>
                                </label>
                            </div>
                            <div style="margin-left:22px;margin-bottom:12px;">
                                <a href="data:application/pdf;base64,JVBERi0xLjQKJcOkw7zDtsOfCjIgMCBvYmoKPDwvTGVuZ3RoIDMgMCBSL0ZpbHRlci9GbGF0ZURlY29kZT4+CnN0cmVhbQp4nCtUMlQyULIGAAMiAWUKZW5kc3RyZWFtCmVuZG9iagozIDAgb2JqCjE4CmVuZG9iagoxIDAgb2JqCjw8L1R5cGUvUGFnZS9NZWRpYUJveFswIDAgNjEyIDc5Ml0vUmVzb3VyY2VzPDwvRm9udDw8L0YxIDQgMCBSPj4+Pi9Db250ZW50cyAyIDAgUi9QYXJlbnQgNSAwIFI+PgplbmRvYmoKNCAwIG9iago8PC9UeXBlL0ZvbnQvU3VidHlwZS9UeXBlMS9CYXNlRm9udC9IZWx2ZXRpY2E+PgplbmRvYmoKNSAwIG9iago8PC9UeXBlL1BhZ2VzL0tpZHNbMSAwIFJdL0NvdW50IDE+PgplbmRvYmoKNiAwIG9iago8PC9UeXBlL0NhdGFsb2cvUGFnZXMgNSAwIFI+PgplbmRvYmoKeHJlZgowIDcKMDAwMDAwMDAwMCA2NTUzNSBmIAowMDAwMDAwMTI1IDAwMDAwIG4gCjAwMDAwMDAwMTUgMDAwMDAgbiAKMDAwMDAwMDEwNiAwMDAwMCBuIAowMDAwMDAwMjQ0IDAwMDAwIG4gCjAwMDAwMDAzMTMgMDAwMDAgbiAKMDAwMDAwMDM2NiAwMDAwMCBuIAp0cmFpbGVyCjw8L1NpemUgNy9Sb290IDYgMCBSPj4Kc3RhcnR4cmVmCjQxNQolJUVPRgo=" download="COR-Certified-True-Copy.pdf"
                                   style="display:inline-flex;align-items:center;gap:7px;background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:8px;padding:6px 12px;text-decoration:none;font-size:11px;font-weight:700;color:#1d4ed8;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    View PDF — COR
                                </a>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                    <input type="checkbox" class="view-req-checkbox" data-id="${r.id}" data-field="photo_id" ${r.photo_id ? 'checked' : ''} style="cursor:pointer;width:16px;height:16px;accent-color:#000;">
                                    <span style="font-size:11px;font-weight:600;color:#111827;">PHOTO COPY OF ID (FRONT AND BACK)</span>
                                </label>
                            </div>
                            <div style="margin-left:22px;margin-top:4px;">
                                <a href="data:application/pdf;base64,JVBERi0xLjQKJcOkw7zDtsOfCjIgMCBvYmoKPDwvTGVuZ3RoIDMgMCBSL0ZpbHRlci9GbGF0ZURlY29kZT4+CnN0cmVhbQp4nCtUMlQyULIGAAMiAWUKZW5kc3RyZWFtCmVuZG9iagozIDAgb2JqCjE4CmVuZG9iagoxIDAgb2JqCjw8L1R5cGUvUGFnZS9NZWRpYUJveFswIDAgNjEyIDc5Ml0vUmVzb3VyY2VzPDwvRm9udDw8L0YxIDQgMCBSPj4+Pi9Db250ZW50cyAyIDAgUi9QYXJlbnQgNSAwIFI+PgplbmRvYmoKNCAwIG9iago8PC9UeXBlL0ZvbnQvU3VidHlwZS9UeXBlMS9CYXNlRm9udC9IZWx2ZXRpY2E+PgplbmRvYmoKNSAwIG9iago8PC9UeXBlL1BhZ2VzL0tpZHNbMSAwIFJdL0NvdW50IDE+PgplbmRvYmoKNiAwIG9iago8PC9UeXBlL0NhdGFsb2cvUGFnZXMgNSAwIFI+PgplbmRvYmoKeHJlZgowIDcKMDAwMDAwMDAwMCA2NTUzNSBmIAowMDAwMDAwMTI1IDAwMDAwIG4gCjAwMDAwMDAwMTUgMDAwMDAgbiAKMDAwMDAwMDEwNiAwMDAwMCBuIAowMDAwMDAwMjQ0IDAwMDAwIG4gCjAwMDAwMDAzMTMgMDAwMDAgbiAKMDAwMDAwMDM2NiAwMDAwMCBuIAp0cmFpbGVyCjw8L1NpemUgNy9Sb290IDYgMCBSPj4Kc3RhcnR4cmVmCjQxNQolJUVPRgo=" download="Photo-ID-Front-Back.pdf"
                                   style="display:inline-flex;align-items:center;gap:7px;background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:8px;padding:6px 12px;text-decoration:none;font-size:11px;font-weight:700;color:#1d4ed8;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    View PDF — Photo ID
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="schol-pdf-sig-section">
                    <!-- Signature and Name above the line -->
                    <div style="text-align:center;padding-top:30px;">
                        <div style="width:300px;margin:0 auto;">
                            <div style="min-height:60px;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;padding-bottom:8px;">
                                <!-- Cursive signature -->
                                <span style="font-family:'Brush Script MT','Segoe Script','Comic Sans MS',cursive;font-size:24px;color:#1e3a5f;line-height:1;letter-spacing:1px;margin-bottom:4px;transform:rotate(-2deg);">
                                    ${r.first_name||''} ${r.last_name||''}
                                </span>
                                <!-- Printed name below signature -->
                                <span style="font-size:13px;font-weight:700;color:#111827;letter-spacing:0.3px;">
                                    ${r.first_name||''} ${r.middle_name ? r.middle_name.charAt(0) + '. ' : ''}${r.last_name||''}
                                </span>
                            </div>
                            <div style="border-bottom:2px solid #374151;width:100%;"></div>
                            <p style="font-size:10px;color:#6b7280;margin-top:4px;font-weight:500;">Name and Signature of Participant</p>
                        </div>
                    </div>
                </div>

            </div>
        `;
        viewModal.style.display = 'flex';

        // Add event listener for checkboxes in view modal
        viewBody.querySelectorAll('.view-req-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const id = parseInt(checkbox.getAttribute('data-id'), 10);
                const field = checkbox.getAttribute('data-field');
                const idx = records.findIndex(rec => rec.id === id);
                if (idx !== -1) {
                    records[idx][field] = checkbox.checked;
                    save();
                    render(); // Update the table to reflect the change
                }
            });
        });
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
            if (idx === -1) return;

            const record = records[idx];
            const name = `${record.first_name} ${record.last_name}`;

            // Check if both COR and Photo ID are checked
            if (!record.cor_certified || !record.photo_id) {
                showScholToast('Please check both COR and Photo ID requirements before approving.', 'error');
                return;
            }

            // Approve the application
            records[idx].status = 'Approved';
            if (!records[idx].result) records[idx].result = 'Defined';
            if (!records[idx].approved_at) records[idx].approved_at = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            save();
            render();
            closeViewModal();
            showScholToast(`✓ Application of ${name} has been approved successfully!`);
        });
    }
    if (rejectBtn) {
        rejectBtn.addEventListener('click', () => {
            if (!viewTargetId) return;
            const idx = records.findIndex(r => r.id === viewTargetId);
            if (idx !== -1) {
                // Auto-uncheck both requirements when rejecting
                records[idx].cor_certified = false;
                records[idx].photo_id = false;
                save();
            }
            closeViewModal();
            openRejectReasonModal();
        });
    }

    // Confirm Rejection with Reasons
    if (rejectReasonConfirm) {
        rejectReasonConfirm.addEventListener('click', () => {
            const selectedReasons = [];
            document.querySelectorAll('.reject-reason-checkbox:checked').forEach(cb => {
                if (cb.value === 'Other' && rejectReasonOtherText && rejectReasonOtherText.value.trim()) {
                    selectedReasons.push(`Other: ${rejectReasonOtherText.value.trim()}`);
                } else if (cb.value !== 'Other') {
                    selectedReasons.push(cb.value);
                }
            });

            if (selectedReasons.length === 0) {
                showScholToast('Please select at least one rejection reason.', 'error');
                return;
            }

            const idx = records.findIndex(r => r.id === viewTargetId);
            const name = idx !== -1 ? `${records[idx].first_name} ${records[idx].last_name}` : 'Applicant';
            if (idx !== -1) {
                records[idx].status = 'Rejected';
                records[idx].rejection_reasons = selectedReasons;
                records[idx].rejected_at = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                save();
                render();
            }
            closeRejectReasonModal();
            showScholToast(`Application of ${name} has been rejected.`, 'error');
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
    if (startDateFilter) startDateFilter.addEventListener('change', () => { filterStartDate = startDateFilter.value; render(); });
    if (endDateFilter) endDateFilter.addEventListener('change', () => { filterEndDate = endDateFilter.value; render(); });
    if (startTimeFilter) startTimeFilter.addEventListener('change', () => { filterStartTime = startTimeFilter.value; render(); });
    if (endTimeFilter) endTimeFilter.addEventListener('change', () => { filterEndTime = endTimeFilter.value; render(); });

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
