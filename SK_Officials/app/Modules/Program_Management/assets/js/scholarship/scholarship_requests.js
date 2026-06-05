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
    let viewTargetId = null;

    const tbody = document.getElementById('scholTableBody');
    const searchInput = document.getElementById('scholSearch');
    const startDateFilter = document.getElementById('scholStartDate');
    const endDateFilter = document.getElementById('scholEndDate');
    function getTimeFromDropdowns(prefix) {
        const h = document.getElementById(prefix + 'Hour')?.value || '';
        const m = document.getElementById(prefix + 'Min')?.value || '';
        const p = document.getElementById(prefix + 'Period')?.value || '';
        if (!h || !m || !p) return '';
        return `${h}:${m} ${p}`;
    }

    function parseTime12ToMinutes(str) {
        if (!str) return null;
        const m = String(str).trim().match(/^(1[0-2]|0?[1-9]):([0-5][0-9])\s*(AM|PM)$/i);
        if (!m) return null;
        let h = parseInt(m[1], 10);
        const min = parseInt(m[2], 10);
        const period = m[3].toUpperCase();
        if (period === 'AM' && h === 12) h = 0;
        if (period === 'PM' && h !== 12) h += 12;
        return h * 60 + min;
    }

    function time12To24Hour(str) {
        const mins = parseTime12ToMinutes(str);
        if (mins === null) return '';
        const h = Math.floor(mins / 60);
        const m = mins % 60;
        return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
    }

    function setTimeToDropdowns(prefix, timeStr) {
        if (!timeStr) return;
        let h12, min, period;
        const m24 = String(timeStr).match(/^(\d{1,2}):(\d{2})$/);
        if (m24) {
            let h = parseInt(m24[1], 10);
            min = m24[2];
            period = h >= 12 ? 'PM' : 'AM';
            if (h === 0) h = 12;
            else if (h > 12) h -= 12;
            h12 = String(h);
        } else {
            const parsed = String(timeStr).trim().match(/^(1[0-2]|0?[1-9]):([0-5][0-9])\s*(AM|PM)$/i);
            if (!parsed) return;
            h12 = String(parseInt(parsed[1], 10));
            min = parsed[2];
            period = parsed[3].toUpperCase();
        }
        const hourEl = document.getElementById(prefix + 'Hour');
        const minEl = document.getElementById(prefix + 'Min');
        const periodEl = document.getElementById(prefix + 'Period');
        if (hourEl) hourEl.value = h12;
        if (minEl) minEl.value = min;
        if (periodEl) periodEl.value = period;
    }

    function bindTimeFilter(prefix, onChange) {
        ['Hour', 'Min', 'Period'].forEach(part => {
            const el = document.getElementById(prefix + part);
            if (el) el.addEventListener('change', onChange);
        });
    }
    const viewModal = document.getElementById('scholViewModal');
    const viewBody = document.getElementById('scholViewBody');
    const viewClose = document.getElementById('scholViewClose');
    const viewCloseFooter = document.getElementById('scholViewCloseFooter');
    const approveBtn = document.getElementById('scholApproveBtn');
    const rejectBtn = document.getElementById('scholRejectBtn');
    const deleteModal = document.getElementById('scholDeleteModal');
    const deleteClose = document.getElementById('scholDeleteClose');
    const deleteCancel = document.getElementById('scholDeleteCancel');
    const deleteConfirm = document.getElementById('scholDeleteConfirm');
    const makeFormBtn = document.getElementById('btnMakeForm');
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
    let filterType = 'all'; // New filter for all/recent/monthly/yearly

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
    const makeFormBox = document.getElementById('makeFormBox');
    if (makeFormMaxBtn && makeFormBox) {
        makeFormMaxBtn.addEventListener('click', function (e) {
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
                setTimeToDropdowns('schedOpenTime', schedule.openTime);
                document.getElementById('schedCloseDate').value = schedule.closeDate;
                setTimeToDropdowns('schedCloseTime', schedule.closeTime);
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
        scheduleListMaximize.addEventListener('click', function (e) {
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
            const openDate = document.getElementById('schedOpenDate').value;
            const openTime = getTimeFromDropdowns('schedOpenTime');
            const closeDate = document.getElementById('schedCloseDate').value;
            const closeTime = getTimeFromDropdowns('schedCloseTime');
            const status = document.getElementById('schedStatus').value;

            if (!openDate || !closeDate) {
                showScholToast('Please set both open and close dates.', 'error');
                return;
            }
            const openMins = parseTime12ToMinutes(openTime);
            const closeMins = parseTime12ToMinutes(closeTime);
            if (closeDate < openDate || (closeDate === openDate && openMins !== null && closeMins !== null && closeMins <= openMins)) {
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
            const name = `${r.last_name} ${r.first_name}`.toLowerCase();
            const school = (r.school_name || '').toLowerCase();
            const q = filterSearch.toLowerCase();

            // Search filter
            const matchesSearch = !filterSearch || name.includes(q) || school.includes(q);

            // Filter type (all/recent/monthly/yearly)
            let matchesFilterType = true;
            if (filterType !== 'all') {
                const submittedDate = parseDate(r.submitted_at);
                if (submittedDate) {
                    const now = new Date();
                    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

                    switch (filterType) {
                        case 'recent': {
                            const sevenDaysAgo = new Date(today);
                            sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
                            matchesFilterType = submittedDate >= sevenDaysAgo;
                            break;
                        }
                        case 'monthly': {
                            matchesFilterType = submittedDate.getMonth() === now.getMonth() &&
                                submittedDate.getFullYear() === now.getFullYear();
                            break;
                        }
                        case 'yearly': {
                            matchesFilterType = submittedDate.getFullYear() === now.getFullYear();
                            break;
                        }
                    }
                }
            }

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

            return r.status === 'Pending' && matchesSearch && matchesFilterType && matchesDateRange && matchesTimeRange;
        }).sort((a, b) => parseSubmitted(a) - parseSubmitted(b));

        tbody.innerHTML = '';

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr class="schol-empty-row"><td colspan="8">No applications found.</td></tr>`;
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
                        <div class="schol-tbl-actions prog-tbl-actions">
                            <button class="schol-tbl-btn schol-tbl-btn-view prog-btn prog-btn-view" data-action="view" data-id="${r.id}">View</button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        updateStats();
    }

    function updateStats() {
        document.getElementById('statTotal').textContent = records.length;
        document.getElementById('statPending').textContent = records.filter(r => r.status === 'Pending').length;
        document.getElementById('statApproved').textContent = records.filter(r => r.status === 'Approved').length;
        document.getElementById('statRejected').textContent = records.filter(r => r.status === 'Rejected').length;
    }

    function save() { localStorage.setItem('scholarship_requests', JSON.stringify(records)); }

    // ── Table click ─────────────────────────────────────────────────────────
    tbody.addEventListener('click', e => {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const action = btn.getAttribute('data-action');
        const id = parseInt(btn.getAttribute('data-id'), 10);
        const record = records.find(r => r.id === id);
        if (!record) return;

        if (action === 'view') { viewTargetId = id; openViewModal(record); }
    });

    // ── View modal — Simple participant details for sports ──────────────
    function openViewModal(r) {
        const statusCls = r.status === 'Approved' ? 'schol-pill-approved'
            : r.status === 'Rejected' ? 'schol-pill-rejected'
                : 'schol-pill-pending';

        // Format requirements
        const reqList = [];
        if (r.cor_certified) reqList.push('COR – Certified True Copy');
        if (r.photo_id) reqList.push('Photo Copy of ID');

        viewBody.innerHTML = `
            <div style="max-width:800px;margin:0 auto;">
                <!-- Header Card -->
                <div style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);border-radius:12px;padding:32px;margin-bottom:24px;box-shadow:0 8px 16px rgba(102,126,234,0.2);color:white;text-align:center;">
                    <div style="width:100px;height:100px;background:rgba(255,255,255,0.2);border-radius:50%;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;font-size:42px;font-weight:700;border:3px solid rgba(255,255,255,0.3);">
                        ${(r.first_name?.[0] || '') + (r.last_name?.[0] || '')}
                    </div>
                    <h2 style="font-size:28px;font-weight:700;margin:0 0 8px;">${r.first_name || ''} ${r.middle_name ? r.middle_name.charAt(0) + '. ' : ''}${r.last_name || ''}</h2>
                    <div style="font-size:16px;opacity:0.95;margin-bottom:12px;">${r.sports_type || 'Sports Program'}</div>
                    <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.25);padding:6px 16px;border-radius:999px;font-size:14px;font-weight:600;">
                        <span class="schol-pill ${statusCls}" style="margin:0;">${r.status}</span>
                    </div>
                </div>

                <!-- Personal Information -->
                <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                    <h3 style="font-size:18px;font-weight:700;color:#111827;margin:0 0 20px;padding-bottom:12px;border-bottom:2px solid #e5e7eb;display:flex;align-items:center;gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Personal Information
                    </h3>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
                        <div>
                            <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Date of Birth</div>
                            <div style="font-size:15px;font-weight:600;color:#111827;">${r.date_of_birth || '—'}</div>
                        </div>
                        <div>
                            <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Gender</div>
                            <div style="font-size:15px;font-weight:600;color:#111827;">${r.gender || '—'}</div>
                        </div>
                        <div>
                            <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Age</div>
                            <div style="font-size:15px;font-weight:600;color:#111827;">${r.age || '—'}</div>
                        </div>
                        <div>
                            <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Contact Number</div>
                            <div style="font-size:15px;font-weight:600;color:#111827;">${r.contact_no || '—'}</div>
                        </div>
                        <div style="grid-column:1/-1;">
                            <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Address</div>
                            <div style="font-size:15px;font-weight:600;color:#111827;">${r.address || '—'}</div>
                        </div>
                        <div style="grid-column:1/-1;">
                            <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Email Address</div>
                            <div style="font-size:15px;font-weight:600;color:#111827;">${r.email || '—'}</div>
                        </div>
                    </div>
                </div>

                <!-- Sports & Requirements -->
                <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                    <h3 style="font-size:18px;font-weight:700;color:#111827;margin:0 0 20px;padding-bottom:12px;border-bottom:2px solid #e5e7eb;display:flex;align-items:center;gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        Sports & Requirements
                    </h3>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                        <div>
                            <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Sports Type</div>
                            <div style="font-size:16px;font-weight:700;color:#667eea;">${r.sports_type || '—'}</div>
                        </div>
                        <div>
                            <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Submitted Requirements</div>
                            ${reqList.length > 0 ? reqList.map(req => `
                                <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                    <span style="font-size:13px;color:#111827;">${req}</span>
                                </div>
                            `).join('') : '<span style="font-size:13px;color:#9ca3af;">No requirements submitted</span>'}
                        </div>
                    </div>
                </div>

                <!-- Submission Details -->
                <div style="background:white;border-radius:12px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                    <h3 style="font-size:18px;font-weight:700;color:#111827;margin:0 0 20px;padding-bottom:12px;border-bottom:2px solid #e5e7eb;display:flex;align-items:center;gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Submission Details
                    </h3>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
                        <div>
                            <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Date Submitted</div>
                            <div style="font-size:15px;font-weight:600;color:#111827;">${r.submitted_at || '—'}</div>
                        </div>
                        <div>
                            <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Time Submitted</div>
                            <div style="font-size:15px;font-weight:600;color:#111827;">${r.submitted_time || '—'}</div>
                        </div>
                    </div>
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
    const scholViewBox = document.getElementById('scholViewBox');
    if (scholViewMaxBtn && scholViewBox) {
        scholViewMaxBtn.addEventListener('click', function (e) {
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

            // Approve the application
            records[idx].status = 'Approved';
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
    const filterDropdown = document.getElementById('scholFilter');
    if (filterDropdown) filterDropdown.addEventListener('change', () => { filterType = filterDropdown.value; render(); });
    if (searchInput) searchInput.addEventListener('input', () => { filterSearch = searchInput.value.trim(); render(); });
    if (startDateFilter) startDateFilter.addEventListener('change', () => { filterStartDate = startDateFilter.value; render(); });
    if (endDateFilter) endDateFilter.addEventListener('change', () => { filterEndDate = endDateFilter.value; render(); });
    bindTimeFilter('scholFilterStartTime', () => {
        filterStartTime = time12To24Hour(getTimeFromDropdowns('scholFilterStartTime'));
        render();
    });
    bindTimeFilter('scholFilterEndTime', () => {
        filterEndTime = time12To24Hour(getTimeFromDropdowns('scholFilterEndTime'));
        render();
    });

    setTimeToDropdowns('schedOpenTime', '8:00 AM');
    setTimeToDropdowns('schedCloseTime', '5:00 PM');

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
