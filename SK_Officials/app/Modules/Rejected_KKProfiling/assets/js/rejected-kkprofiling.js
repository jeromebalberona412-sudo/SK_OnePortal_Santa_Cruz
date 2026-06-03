// Rejected KK Profiling Module

document.addEventListener('DOMContentLoaded', function () {
    initRejectedKK();
});

const rejectedKKRecords = [
    {
        id: 'rkk-001',
        respondentNumber: '011',
        firstName: 'Benito',
        middleName: 'Cruz',
        lastName: 'Aquino',
        suffix: 'Jr.',
        sex: 'Male',
        age: 22,
        purokZone: 'Zone 2',
        barangay: 'VILLA GRACIA',
        youthClassification: 'Out of School Youth',
        workStatus: 'Unemployed',
        educationalBackground: 'High School Graduate',
        registeredSKVoter: 'No',
        rejectionReason: 'Incomplete requirements submitted',
        rejectedDate: 'Apr 20, 2026',
        rejectedTime: '10:20 AM',
        _rejectedTs: new Date('2026-04-20T10:20:00'),
        skTerm: '2025-2027',
    },
    {
        id: 'rkk-002',
        respondentNumber: '012',
        firstName: 'Carla',
        middleName: 'Reyes',
        lastName: 'Bautista',
        suffix: '',
        sex: 'Female',
        age: 19,
        purokZone: 'Zone 4',
        barangay: 'LUPANG PANGAKO',
        youthClassification: 'In School Youth',
        workStatus: 'Student',
        educationalBackground: 'College Level',
        registeredSKVoter: 'Yes',
        rejectionReason: 'Age does not meet eligibility criteria',
        rejectedDate: 'Apr 08, 2026',
        rejectedTime: '01:55 PM',
        _rejectedTs: new Date('2026-04-08T13:55:00'),
        skTerm: '2025-2027',
    },
    {
        id: 'rkk-003',
        respondentNumber: '013',
        firstName: 'Dante',
        middleName: '',
        lastName: 'Flores',
        suffix: '',
        sex: 'Male',
        age: 25,
        purokZone: 'Zone 7',
        barangay: 'BIGAYANVILLA ROSA',
        youthClassification: 'Working Youth',
        workStatus: 'Employed',
        educationalBackground: 'College Graduate',
        registeredSKVoter: 'Yes',
        rejectionReason: 'Duplicate submission detected',
        rejectedDate: 'Apr 14, 2026',
        rejectedTime: '04:10 PM',
        _rejectedTs: new Date('2026-04-14T16:10:00'),
        skTerm: '2025-2027',
    },
    {
        id: 'rkk-004',
        respondentNumber: '008',
        firstName: 'Elena',
        middleName: 'G.',
        lastName: 'Castro',
        suffix: '',
        sex: 'Female',
        age: 20,
        purokZone: 'Zone 1',
        barangay: 'POBLACION II',
        youthClassification: 'In School Youth',
        workStatus: 'Student',
        educationalBackground: 'College Level',
        registeredSKVoter: 'Yes',
        rejectionReason: 'Incomplete supporting documents',
        rejectedDate: 'Nov 02, 2024',
        rejectedTime: '03:15 PM',
        _rejectedTs: new Date('2024-11-02T15:15:00'),
        skTerm: '2022-2025',
    },
];

rejectedKKRecords.forEach(r => {
    if (!r.skTerm) r.skTerm = window.SkArchive ? SkArchive.inferTermFromDate(r._rejectedTs) : '2025-2027';
});

let rkkArchiveTerm = '2025-2027';
let rkkFiltered = [];
let rkkCurrentPage = 1;
const rkkPerPage = 10;
let rkkPendingRestoreId = null;
let rkkActiveFilter = 'all';

function rkkNow() { return new Date('2026-04-20T12:00:00'); }

function rkkIsToday(ts) {
    const n = rkkNow();
    return ts.getFullYear() === n.getFullYear() && ts.getMonth() === n.getMonth() && ts.getDate() === n.getDate();
}

function rkkIsThisWeek(ts) {
    const n = rkkNow();
    const startOfWeek = new Date(n);
    startOfWeek.setDate(n.getDate() - n.getDay());
    startOfWeek.setHours(0, 0, 0, 0);
    return ts >= startOfWeek;
}

function rkkIsThisMonth(ts) {
    const n = rkkNow();
    return ts.getFullYear() === n.getFullYear() && ts.getMonth() === n.getMonth();
}

function rkkApplyFilter(records, filter) {
    if (filter === 'today') return records.filter(r => rkkIsToday(r._rejectedTs));
    if (filter === 'week') return records.filter(r => rkkIsThisWeek(r._rejectedTs));
    if (filter === 'month') return records.filter(r => rkkIsThisMonth(r._rejectedTs));
    return records;
}

function rkkApplyAllFilters() {
    const byDate = rkkApplyFilter(rejectedKKRecords, rkkActiveFilter);
    return window.SkArchive
        ? SkArchive.filterByArchiveTerm(byDate, rkkArchiveTerm, ['_rejectedTs'])
        : byDate;
}

function initRejectedKK() {
    if (window.SkArchive) {
        SkArchive.mountShowArchiveFilter((termId) => {
            rkkArchiveTerm = termId;
            rkkFiltered = rkkApplyAllFilters();
            rkkCurrentPage = 1;
            renderTable();
        });
    } else {
        rkkFiltered = rkkApplyAllFilters();
    }
    renderStats();
    renderTable();
    bindSearch();
    bindFilterTabs();
    bindRestoreModal();
    bindViewModal();
    bindRkkKkFormModal();
}

function renderStats() {
    const row = document.getElementById('rkkStatsRow');
    if (!row) return;
    const total = rejectedKKRecords.length;
    const month = rejectedKKRecords.filter(r => rkkIsThisMonth(r._rejectedTs)).length;
    const today = rejectedKKRecords.filter(r => rkkIsToday(r._rejectedTs)).length;

    row.innerHTML = `
        <div class="stat-card stat-card-red">
            <div class="stat-card-top">
                <span class="stat-card-value">${total}</span>
                <div class="stat-card-icon stat-icon-red">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                </div>
            </div>
            <span class="stat-card-label">Total Rejected</span>
        </div>
        <div class="stat-card stat-card-orange">
            <div class="stat-card-top">
                <span class="stat-card-value">${month}</span>
                <div class="stat-card-icon stat-icon-orange">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="10" x2="21" y2="10"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="16" y1="2" x2="16" y2="6"></line></svg>
                </div>
            </div>
            <span class="stat-card-label">This Month</span>
        </div>
        <div class="stat-card stat-card-blue">
            <div class="stat-card-top">
                <span class="stat-card-value">${today}</span>
                <div class="stat-card-icon stat-icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>
            <span class="stat-card-label">Today</span>
        </div>`;
}

function bindFilterTabs() {
    document.querySelectorAll('.filter-tab').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            rkkActiveFilter = this.dataset.filter;
            const labels = { all: 'All Rejected Records', today: 'Rejected Today', week: 'Rejected This Week', month: 'Rejected This Month' };
            const label = document.getElementById('rkkSectionLabel');
            if (label) label.textContent = labels[rkkActiveFilter] || 'Rejected Records';
            rkkFiltered = rkkApplyAllFilters();
            rkkCurrentPage = 1;
            renderTable();
        });
    });
}

function renderTable() {
    const tbody = document.getElementById('rejectedKKTableBody');
    const info = document.getElementById('rejectedKKPaginationInfo');
    if (!tbody) return;

    const start = (rkkCurrentPage - 1) * rkkPerPage;
    const end = start + rkkPerPage;
    const page = rkkFiltered.slice(start, end);

    if (rkkFiltered.length === 0) {
        tbody.innerHTML = `<tr class="empty-state-row"><td colspan="9">No rejected KK Profiling records found.</td></tr>`;
        if (info) info.textContent = 'No records found';
        renderPagination(0);
        return;
    }

    tbody.innerHTML = page.map(r => {
        const fullName = `${r.lastName}, ${r.firstName}${r.middleName ? ' ' + r.middleName : ''}${r.suffix ? ' ' + r.suffix : ''}`;
        const canRestore = window.SkArchive ? SkArchive.canRestoreRecord(r, ['_rejectedTs']) : true;
        const restoreBtn = canRestore
            ? `<button class="btn-restore-action" data-id="${r.id}">Restore</button>`
            : `<button type="button" class="btn-restore-action is-disabled" disabled title="Past term — view only">Restore</button>`;
        return `
        <tr>
            <td style="font-weight:600;color:#111827;">${fullName}</td>
            <td>${r.age || '—'}</td>
            <td>${r.sex || '—'}</td>
            <td>${r.purokZone || '—'}</td>
            <td>${r.youthClassification || '—'}</td>
            <td><span class="rejection-reason-cell" title="${r.rejectionReason}">${r.rejectionReason}</span></td>
            <td><span class="deleted-at-badge">${r.rejectedDate}</span></td>
            <td><span class="deleted-time-badge">${r.rejectedTime}</span></td>
            <td>
                <div class="action-btns">
                    <button class="btn-view-action" data-id="${r.id}">View</button>
                    ${restoreBtn}
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) info.textContent = `Showing ${start + 1}–${Math.min(end, rkkFiltered.length)} of ${rkkFiltered.length} records`;

    renderPagination(rkkFiltered.length);

    tbody.querySelectorAll('.btn-restore-action').forEach(btn => {
        btn.addEventListener('click', function () { openRestoreModal(this.dataset.id); });
    });
    tbody.querySelectorAll('.btn-view-action').forEach(btn => {
        btn.addEventListener('click', function () { openViewModal(this.dataset.id); });
    });
}

function renderPagination(total) {
    const pages = Math.ceil(total / rkkPerPage);
    const nums = document.getElementById('rejectedKKPageNumbers');
    const prev = document.getElementById('rejectedKKPrevBtn');
    const next = document.getElementById('rejectedKKNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button class="pagination-btn ${i + 1 === rkkCurrentPage ? 'active' : ''}">${i + 1}</button>
        `).join('');
        nums.querySelectorAll('.pagination-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => { rkkCurrentPage = i + 1; renderTable(); });
        });
    }
    if (prev) { prev.disabled = rkkCurrentPage === 1; prev.onclick = () => { rkkCurrentPage--; renderTable(); }; }
    if (next) { next.disabled = rkkCurrentPage >= pages || pages === 0; next.onclick = () => { rkkCurrentPage++; renderTable(); }; }
}

function bindSearch() {
    const input = document.getElementById('rejectedKKSearch');
    if (!input) return;
    input.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        const base = rkkApplyAllFilters();
        rkkFiltered = base.filter(r =>
            `${r.firstName} ${r.middleName || ''} ${r.lastName}`.toLowerCase().includes(q) ||
            (r.purokZone || '').toLowerCase().includes(q) ||
            (r.rejectionReason || '').toLowerCase().includes(q)
        );
        rkkCurrentPage = 1;
        renderTable();
    });
}

function normalizeEducation(ed) {
    const map = {
        'High School Graduate': 'High School Grad',
        'Elementary Graduate': 'Elementary Grad',
        'College Graduate': 'College Grad',
    };
    return map[ed] || ed;
}

function enrichRejectedKKFormData(r) {
    const age = parseInt(r.age, 10) || 18;
    let youthAgeGroup = 'Core Youth (18-24 yrs old)';
    if (age <= 17) youthAgeGroup = 'Child Youth (15-17 yrs old)';
    else if (age >= 25) youthAgeGroup = 'Young Adult (15-30 yrs old)';

    const youthClass = (r.youthClassification || '').replace(/In School/i, 'In school');

    const samples = {
        'rkk-001': {
            date: 'Apr 18, 2026',
            birthday: '04/15/2004',
            emailAddress: 'benito.aquino@email.com',
            contactNumber: '09187654321',
            civilStatus: 'Single',
            facebookAccount: 'benito.cruz.aquino',
        },
        'rkk-002': {
            date: 'Apr 05, 2026',
            birthday: '06/12/2006',
            emailAddress: 'carla.bautista@email.com',
            contactNumber: '09181234567',
            civilStatus: 'Single',
            facebookAccount: 'carla.reyes.bautista',
            workStatus: 'Currently looking for a Job',
        },
        'rkk-003': {
            date: 'Apr 12, 2026',
            birthday: '03/22/2001',
            emailAddress: 'dante.flores@email.com',
            contactNumber: '09291234567',
            civilStatus: 'Single',
            facebookAccount: 'dante.flores',
        },
        'rkk-004': {
            date: 'Nov 01, 2024',
            birthday: '08/30/2004',
            emailAddress: 'elena.castro@email.com',
            contactNumber: '09351234567',
            civilStatus: 'Single',
            facebookAccount: 'elena.g.castro',
            workStatus: 'Currently looking for a Job',
        },
    };

    const extra = samples[r.id] || {};
    return {
        respondentNumber: r.respondentNumber,
        date: r.rejectedDate || '—',
        firstName: r.firstName,
        middleName: r.middleName || '',
        lastName: r.lastName,
        suffix: r.suffix || 'None',
        region: 'Region IV-A (CALABARZON)',
        province: 'Laguna',
        city: 'Santa Cruz',
        barangay: r.barangay,
        purokZone: r.purokZone,
        sex: r.sex,
        age: r.age,
        emailAddress: `${(r.firstName || 'user').toLowerCase()}.${(r.lastName || 'youth').toLowerCase()}@email.com`,
        contactNumber: '09171234567',
        civilStatus: 'Single',
        youthAgeGroup,
        youthClassification: youthClass,
        workStatus: r.workStatus,
        educationalBackground: normalizeEducation(r.educationalBackground),
        registeredSKVoter: r.registeredSKVoter,
        registeredNationalVoter: r.registeredSKVoter === 'Yes' ? 'Yes' : 'No',
        votingHistory: 'No',
        votingFrequency: '',
        attendedKKAssembly: 'No',
        votingReason: 'There was no KK Assembly Meeting',
        facebookAccount: `${r.firstName} ${r.lastName}`,
        willingToJoinGroupChat: 'Yes',
        signature: [r.firstName, r.middleName, r.lastName, r.suffix].filter(Boolean).join(' '),
        ...extra,
    };
}

function populateRkkKkQuestionnaire(r) {
    const d = enrichRejectedKKFormData(r);
    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val ?? '—';
    };
    const setCheck = (id, checked) => {
        const el = document.getElementById(id);
        if (!el) return;
        const text = el.textContent.replace(/^[☐☑]\s*/, '');
        el.textContent = (checked ? '☑ ' : '☐ ') + text;
        el.style.fontWeight = checked ? '700' : '400';
        el.style.color = checked ? '#1a1a1a' : '#6b7280';
    };

    // Populate rejection details
    setVal('rkkRejectionReason', r.rejectionReason);
    setVal('rkkRejectedDate', r.rejectedDate);
    setVal('rkkRejectedTime', r.rejectedTime);

    setVal('rkkKkViewRespondentNumber', d.respondentNumber);
    setVal('rkkKkViewDate', d.date);
    setVal('rkkKkViewLastName', d.lastName);
    setVal('rkkKkViewFirstName', d.firstName);
    setVal('rkkKkViewMiddleName', d.middleName || '—');
    setVal('rkkKkViewSuffix', d.suffix || 'None');
    setVal('rkkKkViewRegion', d.region);
    setVal('rkkKkViewProvince', d.province);
    setVal('rkkKkViewCity', d.city);
    setVal('rkkKkViewBarangay', d.barangay);
    setVal('rkkKkViewPurokZone', d.purokZone);
    setVal('rkkKkViewSexAssignedAtBirth', d.sex);
    setVal('rkkKkViewAge', d.age);
    setVal('rkkKkViewBirthday', d.birthday);
    setVal('rkkKkViewEmailAddress', d.emailAddress);
    setVal('rkkKkViewContactNumber', d.contactNumber);
    setVal('rkkKkViewFacebookAccount', d.facebookAccount);

    const csMap = { rkkKkViewCS_Single: 'Single', rkkKkViewCS_Married: 'Married', rkkKkViewCS_Widowed: 'Widowed', rkkKkViewCS_Divorced: 'Divorced', rkkKkViewCS_Separated: 'Separated', rkkKkViewCS_Annulled: 'Annulled', rkkKkViewCS_Unknown: 'Unknown', rkkKkViewCS_Livein: 'Live-in' };
    Object.entries(csMap).forEach(([id, val]) => setCheck(id, d.civilStatus === val));
    const yagMap = { rkkKkViewYAG_Child: 'Child Youth (15-17 yrs old)', rkkKkViewYAG_Core: 'Core Youth (18-24 yrs old)', rkkKkViewYAG_Young: 'Young Adult (15-30 yrs old)' };
    Object.entries(yagMap).forEach(([id, val]) => setCheck(id, d.youthAgeGroup === val));
    const ebMap = { rkkKkViewEB_ElemLevel: 'Elementary Level', rkkKkViewEB_ElemGrad: 'Elementary Grad', rkkKkViewEB_HSLevel: 'High School Level', rkkKkViewEB_HSGrad: 'High School Grad', rkkKkViewEB_VocGrad: 'Vocational Grad', rkkKkViewEB_ColLevel: 'College Level', rkkKkViewEB_ColGrad: 'College Grad', rkkKkViewEB_MasLevel: 'Masters Level', rkkKkViewEB_MasGrad: 'Masters Grad', rkkKkViewEB_DocLevel: 'Doctorate Level', rkkKkViewEB_DocGrad: 'Doctorate Graduate' };
    Object.entries(ebMap).forEach(([id, val]) => setCheck(id, d.educationalBackground === val));
    const ycMap = { rkkKkViewYC_ISY: 'In school Youth', rkkKkViewYC_OSY: 'Out of School Youth', rkkKkViewYC_Working: 'Working Youth', rkkKkViewYC_PWD: 'Person w/ Disability', rkkKkViewYC_CICL: 'Children in Conflict w/ Law', rkkKkViewYC_IP: 'Indigenous People' };
    Object.entries(ycMap).forEach(([id, val]) => setCheck(id, d.youthClassification === val));
    const wsMap = { rkkKkViewWS_Employed: 'Employed', rkkKkViewWS_Unemployed: 'Unemployed', rkkKkViewWS_SelfEmployed: 'Self-Employed', rkkKkViewWS_Looking: 'Currently looking for a Job', rkkKkViewWS_NotInterested: 'Not Interested Looking for a Job' };
    Object.entries(wsMap).forEach(([id, val]) => setCheck(id, d.workStatus === val));

    setCheck('rkkKkViewSKV_Yes', d.registeredSKVoter === 'Yes');
    setCheck('rkkKkViewSKV_No', d.registeredSKVoter === 'No');
    setCheck('rkkKkViewNV_Yes', d.registeredNationalVoter === 'Yes');
    setCheck('rkkKkViewNV_No', d.registeredNationalVoter === 'No');
    setCheck('rkkKkViewVH_Yes', d.votingHistory === 'Yes');
    setCheck('rkkKkViewVH_No', d.votingHistory === 'No');
    setCheck('rkkKkViewVF_12', d.votingFrequency === '1-2 Times');
    setCheck('rkkKkViewVF_34', d.votingFrequency === '3-4 Times');
    setCheck('rkkKkViewVF_5', d.votingFrequency === '5 and above');
    setCheck('rkkKkViewKK_Yes', d.attendedKKAssembly === 'Yes');
    setCheck('rkkKkViewKK_No', d.attendedKKAssembly === 'No');
    setCheck('rkkKkViewVR_NoKK', d.votingReason === 'There was no KK Assembly Meeting');
    setCheck('rkkKkViewVR_NotInt', d.votingReason === 'Not interested to Attend');
    setCheck('rkkKkViewGC_Yes', d.willingToJoinGroupChat === 'Yes');
    setCheck('rkkKkViewGC_No', d.willingToJoinGroupChat === 'No');

    const sigEl = document.getElementById('rkkKkViewSignature');
    const sigOverlay = document.getElementById('rkkKkViewSignatureOverlay');
    if (sigEl) {
        sigEl.textContent = d.signature || '—';
        if (sigOverlay) sigOverlay.style.display = d.signature ? 'none' : '';
    }
}

function openRkkKkFormModal(record) {
    populateRkkKkQuestionnaire(record);
    const modal = document.getElementById('rkkKkFormModal');
    const box = document.getElementById('rkkKkFormModalBox');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.remove('view-modal-maximized');
    }
    if (box) box.classList.remove('view-modal-maximized');
    const toggle = document.getElementById('rkkKkFormToggle');
    if (toggle) toggle.textContent = '□';
}

function bindRkkKkFormModal() {
    const modal = document.getElementById('rkkKkFormModal');
    const box = document.getElementById('rkkKkFormModalBox');
    const closeBtn = document.getElementById('rkkKkFormClose');
    const toggleBtn = document.getElementById('rkkKkFormToggle');

    const close = () => {
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('modal-maximized', 'view-modal-maximized');
        }
        if (box) box.classList.remove('modal-maximized', 'view-modal-maximized');
        if (toggleBtn) toggleBtn.textContent = '□';
    };

    if (closeBtn) closeBtn.addEventListener('click', close);
    if (modal) modal.addEventListener('click', e => { if (e.target === modal) close(); });

    if (toggleBtn && box && modal) {
        toggleBtn.addEventListener('click', e => {
            e.stopPropagation();
            const isMax = !modal.classList.contains('modal-maximized');
            modal.classList.toggle('modal-maximized', isMax);
            box.classList.toggle('modal-maximized', isMax);
            toggleBtn.textContent = isMax ? '⧉' : '□';
        });
    }
}

function openViewModal(id) {
    const r = rejectedKKRecords.find(x => x.id === id);
    if (!r) return;
    // Directly open the KK profiling form modal instead of the summary modal
    openRkkKkFormModal(r);
}

function bindViewModal() {
    const modal = document.getElementById('rkkViewModal');
    const box = document.getElementById('rkkViewModalBox');
    const closeBtn = document.getElementById('rkkViewModalClose');
    const toggleBtn = document.getElementById('rkkViewModalToggle');

    const close = () => {
        if (modal) { modal.style.display = 'none'; modal.classList.remove('view-modal-maximized'); }
        if (box) box.classList.remove('view-modal-maximized');
        if (toggleBtn) toggleBtn.textContent = '□';
    };

    if (closeBtn) closeBtn.addEventListener('click', close);
    if (modal) modal.addEventListener('click', e => { if (e.target === modal) close(); });

    if (toggleBtn && box) {
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isMax = !box.classList.contains('view-modal-maximized');
            modal.classList.toggle('view-modal-maximized', isMax);
            box.classList.toggle('view-modal-maximized', isMax);
            toggleBtn.textContent = isMax ? '⧉' : '□';
        });
    }
}

function openRestoreModal(id) {
    const record = rejectedKKRecords.find(r => r.id === id);
    if (!record) return;
    if (window.SkArchive && !SkArchive.canRestoreRecord(record, ['_rejectedTs'])) {
        alert('This record is from a past SK term and cannot be restored. View-only archive.');
        return;
    }
    rkkPendingRestoreId = id;
    const nameEl = document.getElementById('rkkRestoreName');
    if (nameEl) nameEl.textContent = `${record.lastName}, ${record.firstName}${record.middleName ? ' ' + record.middleName : ''}`;
    const modal = document.getElementById('rkkRestoreModal');
    if (modal) modal.style.display = 'flex';
}

function closeRestoreModal() {
    rkkPendingRestoreId = null;
    const modal = document.getElementById('rkkRestoreModal');
    if (modal) modal.style.display = 'none';
}

function bindRestoreModal() {
    const cancelBtn = document.getElementById('rkkRestoreCancelBtn');
    const confirmBtn = document.getElementById('rkkRestoreConfirmBtn');
    const modal = document.getElementById('rkkRestoreModal');

    if (cancelBtn) cancelBtn.addEventListener('click', closeRestoreModal);
    if (modal) modal.addEventListener('click', e => { if (e.target === modal) closeRestoreModal(); });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!rkkPendingRestoreId) return;
            const record = rejectedKKRecords.find(r => r.id === rkkPendingRestoreId);
            const name = record ? `${record.lastName}, ${record.firstName}` : 'Record';
            const idx = rejectedKKRecords.findIndex(r => r.id === rkkPendingRestoreId);
            if (idx !== -1) rejectedKKRecords.splice(idx, 1);
            rkkFiltered = rkkApplyAllFilters();
            closeRestoreModal();
            rkkCurrentPage = 1;
            renderStats();
            renderTable();
            showRestoreBanner('rkkRestoreBanner', 'rkkRestoreBannerText', `${name} has been restored to KK Profiling.`);
        });
    }
}

function showRestoreBanner(bannerId, textId, message) {
    const banner = document.getElementById(bannerId);
    const text = document.getElementById(textId);
    if (!banner || !text) return;
    text.textContent = message;
    banner.style.display = 'flex';
    banner.classList.add('show');
    setTimeout(() => {
        banner.classList.remove('show');
        setTimeout(() => { banner.style.display = 'none'; }, 400);
    }, 4000);
}

function showToast(toastId, message) {
    const toast = document.getElementById(toastId);
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}
