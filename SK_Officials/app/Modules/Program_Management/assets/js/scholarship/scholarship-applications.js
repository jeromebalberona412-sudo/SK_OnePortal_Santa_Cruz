document.addEventListener('DOMContentLoaded', () => {
    initScholarshipRequests();
});

// ── Sample Data ────────────────────────────────────────────────────────────
const SAMPLE_DATA = [
    {
        id: 1001,
        last_name: 'REYES', first_name: 'MARIA', middle_name: 'SANTOS',
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
        last_name: 'CRUZ', first_name: 'JUAN', middle_name: 'DELA',
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
        last_name: 'GARCIA', first_name: 'ANA', middle_name: 'LIM',
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
        last_name: 'MENDOZA', first_name: 'CARLO', middle_name: 'BAUTISTA',
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
        last_name: 'TORRES', first_name: 'LIZA', middle_name: 'VILLANUEVA',
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
        last_name: 'DELA CRUZ', first_name: 'JOSE', middle_name: 'RAMOS',
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
        last_name: 'BAUTISTA', first_name: 'KRISTINE', middle_name: 'FLORES',
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
        last_name: 'VILLANUEVA', first_name: 'PATRICK', middle_name: 'SANTOS',
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
        gpa: '1.85',
        employed: false,
        household_dependents: 5,
        form_answers: [
            {
                question: 'Why do you need this scholarship assistance?',
                answer: 'I need assistance to pay tuition and purchase books for my ICT program. My family income is limited and I want to finish my degree on time.',
            },
            {
                question: 'What is your current GPA or general average?',
                answer: '1.85 (Good Standing)',
            },
            {
                question: 'Are you currently employed or receiving other income?',
                answer: 'No',
            },
            {
                question: 'Purpose of assistance (select all that apply)',
                answer: 'Tuition Fees, Books / Equipments',
            },
            {
                question: 'How many dependents are in your household?',
                answer: '5',
            },
        ],
    },
];

function initScholarshipRequests() {
    if (window.ScholarshipViewShared) {
        window.ScholarshipViewShared.seedScholarshipProgramIfNeeded();
    }

    // Seed sample data if localStorage is empty
    if (!localStorage.getItem('scholarship_requests_seeded_v7')) {
        localStorage.setItem('scholarship_requests', JSON.stringify(SAMPLE_DATA));
        localStorage.setItem('scholarship_requests_seeded_v7', '1');
    }

    let records = JSON.parse(localStorage.getItem('scholarship_requests') || '[]');
    const patrickSample = SAMPLE_DATA.find(s => s.id === 1008);
    if (patrickSample) {
        records = records.map(r => {
            if (r.id === 1008 && (!r.form_answers || !r.form_answers.length)) {
                return { ...r, ...patrickSample };
            }
            return r;
        });
        localStorage.setItem('scholarship_requests', JSON.stringify(records));
    }
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
    const rejectReasonOther = document.getElementById('rejectReasonOther');
    const rejectReasonOtherInput = document.getElementById('rejectReasonOtherInput');
    const rejectReasonOtherCount = document.getElementById('rejectReasonOtherCount');
    let filterSearch = '';

    // Rejection Reason "Other" checkbox handler
    if (rejectReasonOther && rejectReasonOtherInput && rejectReasonOtherCount) {
        rejectReasonOther.addEventListener('change', () => {
            if (rejectReasonOther.checked) {
                rejectReasonOtherInput.style.display = 'block';
                rejectReasonOtherCount.style.display = 'block';
            } else {
                rejectReasonOtherInput.style.display = 'none';
                rejectReasonOtherCount.style.display = 'none';
                rejectReasonOtherInput.value = '';
                rejectReasonOtherCount.textContent = '0/500 characters';
            }
        });

        // Character counter for "other" input
        rejectReasonOtherInput.addEventListener('input', () => {
            rejectReasonOtherCount.textContent = `${rejectReasonOtherInput.value.length}/500 characters`;
        });
    }
    let filterStartDate = '';
    let filterEndDate = '';
    let filterStartTime = '';
    let filterEndTime = '';
    let filterType = 'all'; // New filter for all/recent/monthly/yearly

    function openRejectReasonModal() {
        document.querySelectorAll('.reject-reason-checkbox').forEach(cb => { cb.checked = false; });
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
        }).sort((a, b) => {
            // Sort alphabetically by last name, then first name
            const lastNameA = (a.last_name || '').toLowerCase();
            const lastNameB = (b.last_name || '').toLowerCase();
            if (lastNameA !== lastNameB) {
                return lastNameA.localeCompare(lastNameB);
            }
            const firstNameA = (a.first_name || '').toLowerCase();
            const firstNameB = (b.first_name || '').toLowerCase();
            return firstNameA.localeCompare(firstNameB);
        });

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
                    <td class="schol-fullname-cell"><span class="schol-fullname">${formatApplicantName(r)}</span></td>
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

    function formatApplicantName(r) {
        if (window.ScholarshipViewShared?.formatScholarshipFullName) {
            return window.ScholarshipViewShared.formatScholarshipFullName(r);
        }
        const ln = (r.last_name || '').toUpperCase();
        const fn = (r.first_name || '').toUpperCase();
        const mn = (r.middle_name || '').toUpperCase();
        const parts = [fn, mn].filter(Boolean);
        return parts.length ? `${ln},${parts.join(',')}` : ln || '—';
    }

    function getApplicantInitials(r) {
        return ((r.first_name?.[0] || '') + (r.last_name?.[0] || '')).toUpperCase();
    }

    // ── View modal — Scholarship application PDF-style details ───────────
    function openViewModal(r) {
        const statusCls = r.status === 'Approved' ? 'schol-pill-approved'
            : r.status === 'Rejected' ? 'schol-pill-rejected'
                : 'schol-pill-pending';

        const reqList = [];
        if (r.cor_certified) reqList.push('COR – Certified True Copy');
        if (r.photo_id) reqList.push('Photo Copy of ID');
        const purposeText = r.purpose || (Array.isArray(r.purpose_list) ? r.purpose_list.join(', ') : '—');
        const fullName = formatApplicantName(r);
        const initials = getApplicantInitials(r);
        const SV = window.ScholarshipViewShared;
        const esc = (s) => (SV ? SV.escapeHtml(s) : String(s ?? ''));
        const program = SV ? SV.loadScholarshipProgram() : null;
        const programHtml = SV ? SV.renderProgramInformationSection(program) : '';
        const formAnswersHtml = SV ? SV.renderFormAnswersSection(r, program) : '';

        // KK Profile Data Section
        const kkProfileData = r.kk_profile_data || {};
        const kkProfilingFields = program?.kkProfilingFields || [];
        const hasKKProfileData = kkProfilingFields.length > 0 && Object.keys(kkProfileData).length > 0;

        const kkProfileHtml = hasKKProfileData ? `
            <!-- KK Profile Information -->
            <div style="background:#f0f9ff;border:2px solid #0ea5e9;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h4 style="font-size:16px;font-weight:700;color:#0369a1;margin:0 0 20px;display:flex;align-items:center;gap:8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                    KK Profile Information
                    <span style="margin-left:auto;font-size:12px;font-weight:600;color:#64748b;background:#fff;padding:4px 12px;border-radius:20px;border:1px solid #0ea5e9;">Auto-filled from KK Profile</span>
                </h4>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
                    ${kkProfilingFields.map(field => {
                        const fieldLabels = {
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
                            home_address: 'Home Address',
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
                            kk_assembly: 'Attended KK Assembly',
                            vote_frequency: 'Number of KK Assembly Attendances'
                        };
                        const value = kkProfileData[field];
                        if (!value) return '';
                        return `
                            <div style="${field === 'home_address' || field === 'full_name' ? 'grid-column:1/-1;' : ''}">
                                <label style="font-size:13px;font-weight:600;color:#0369a1;margin-bottom:6px;display:block;">${fieldLabels[field] || field}</label>
                                <div style="font-size:15px;color:#111827;padding:10px 14px;background:#fff;border-radius:6px;border:1px solid #bae6fd;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${esc(value)}</div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        ` : '';

        viewBody.innerHTML = `
            <div style="padding:24px;background:#f0f1f5;">
                ${kkProfileHtml}

                <!-- Scholarship Application Responses -->
                <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;border:2px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <h4 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 20px;display:flex;align-items:center;gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.1 2.7 2 6 2s6-.9 6-2v-5"/></svg>
                        Scholarship Application Responses
                    </h4>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                        <div style="grid-column:1/-1;">
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">School Name</label>
                            <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${r.school_name || 'Not specified'}</div>
                        </div>
                        <div style="grid-column:1/-1;">
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">School Address</label>
                            <div style="font-size:15px;color:#374151;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);min-height:50px;">${r.school_address || 'Not specified'}</div>
                        </div>
                        <div>
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Year Level</label>
                            <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${r.year_level || 'Not specified'}</div>
                        </div>
                        <div>
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Program / Strand</label>
                            <div style="font-size:15px;color:#374151;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);min-height:50px;">${r.program_strand || 'Not specified'}</div>
                        </div>
                        <div style="grid-column:1/-1;">
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Purpose of Application</label>
                            <div style="font-size:15px;color:#374151;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);min-height:50px;">${purposeText || 'Not specified'}</div>
                        </div>
                        <div style="grid-column:1/-1;">
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Submitted Requirements</label>
                            <div style="background:#f9fafb;border-radius:8px;padding:16px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                                ${reqList.length > 0 ? reqList.map(req => `
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        <span style="font-size:14px;color:#111827;">${req}</span>
                                    </div>
                                `).join('') : '<span style="font-size:14px;color:#9ca3af;">No requirements submitted</span>'}
                            </div>
                        </div>
                    </div>
                </div>

                ${programHtml}

                ${formAnswersHtml}

                <!-- Submission Details -->
                <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;border:2px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <h4 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 20px;display:flex;align-items:center;gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Submission Details
                    </h4>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                        <div>
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Date Submitted</label>
                            <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${r.submitted_at || 'Not specified'}</div>
                        </div>
                        <div>
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Time Submitted</label>
                            <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${r.submitted_time || 'Not specified'}</div>
                        </div>
                    </div>
                </div>

                <!-- Applicant summary -->
                <div style="background:white;border-radius:12px;padding:20px 24px;box-shadow:0 1px 3px rgba(0,0,0,0.1);display:flex;align-items:center;gap:16px;border-top:3px solid #213F99;">
                    <div style="width:56px;height:56px;background:#e8eef9;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#213F99;flex-shrink:0;">${initials}</div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:18px;font-weight:700;color:#111827;margin-bottom:4px;">${fullName}</div>
                        <div style="font-size:14px;color:#6b7280;">${esc(program?.programName || 'Scholarship Program')}</div>
                    </div>
                    <span class="schol-pill ${statusCls}" style="flex-shrink:0;">${r.status}</span>
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
                selectedReasons.push(cb.value);
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
