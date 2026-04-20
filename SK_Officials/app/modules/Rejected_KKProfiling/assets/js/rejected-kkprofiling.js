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
        rejectedDate: 'Apr 05, 2026',
        rejectedTime: '10:20 AM',
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
    },
];

let rkkFiltered = [...rejectedKKRecords];
let rkkCurrentPage = 1;
const rkkPerPage = 10;
let rkkPendingRestoreId = null;

function initRejectedKK() {
    renderTable();
    bindSearch();
    bindRestoreModal();
    bindViewModal();
}

// ── Render ────────────────────────────────────────────────────────────────────
function renderTable() {
    const tbody = document.getElementById('rejectedKKTableBody');
    const info  = document.getElementById('rejectedKKPaginationInfo');
    if (!tbody) return;

    const start = (rkkCurrentPage - 1) * rkkPerPage;
    const end   = start + rkkPerPage;
    const page  = rkkFiltered.slice(start, end);

    if (rkkFiltered.length === 0) {
        tbody.innerHTML = `<tr class="empty-state-row"><td colspan="9">No rejected KK Profiling records found.</td></tr>`;
        if (info) info.textContent = 'No records found';
        renderPagination(0);
        return;
    }

    tbody.innerHTML = page.map(r => {
        const fullName = `${r.lastName}, ${r.firstName}${r.middleName ? ' ' + r.middleName : ''}${r.suffix ? ' ' + r.suffix : ''}`;
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
                    <button class="btn-restore-action" data-id="${r.id}">Restore</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) {
        info.textContent = `Showing ${start + 1}–${Math.min(end, rkkFiltered.length)} of ${rkkFiltered.length} records`;
    }

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
    const nums  = document.getElementById('rejectedKKPageNumbers');
    const prev  = document.getElementById('rejectedKKPrevBtn');
    const next  = document.getElementById('rejectedKKNextBtn');

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

// ── Search ────────────────────────────────────────────────────────────────────
function bindSearch() {
    const input = document.getElementById('rejectedKKSearch');
    if (!input) return;
    input.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        rkkFiltered = rejectedKKRecords.filter(r =>
            `${r.firstName} ${r.middleName || ''} ${r.lastName}`.toLowerCase().includes(q) ||
            (r.purokZone || '').toLowerCase().includes(q) ||
            (r.rejectionReason || '').toLowerCase().includes(q)
        );
        rkkCurrentPage = 1;
        renderTable();
    });
}

// ── View modal ────────────────────────────────────────────────────────────────
function openViewModal(id) {
    const r = rejectedKKRecords.find(x => x.id === id);
    if (!r) return;
    const body = document.getElementById('rkkViewModalBody');
    if (body) {
        body.innerHTML = `
            <div class="view-detail-grid">
                <div class="view-detail-row"><span class="view-detail-label">Full Name</span><span class="view-detail-value">${r.lastName}, ${r.firstName} ${r.middleName || ''} ${r.suffix || ''}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Age</span><span class="view-detail-value">${r.age || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Sex</span><span class="view-detail-value">${r.sex || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Purok / Zone</span><span class="view-detail-value">${r.purokZone || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Barangay</span><span class="view-detail-value">${r.barangay || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Youth Classification</span><span class="view-detail-value">${r.youthClassification || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Work Status</span><span class="view-detail-value">${r.workStatus || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Education</span><span class="view-detail-value">${r.educationalBackground || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Registered SK Voter</span><span class="view-detail-value">${r.registeredSKVoter || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Rejection Reason</span><span class="view-detail-value" style="color:#dc2626;">${r.rejectionReason || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Rejected Date</span><span class="view-detail-value">${r.rejectedDate}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Rejected Time</span><span class="view-detail-value">${r.rejectedTime}</span></div>
            </div>`;
    }
    const modal = document.getElementById('rkkViewModal');
    if (modal) modal.style.display = 'flex';
}

function bindViewModal() {
    const modal    = document.getElementById('rkkViewModal');
    const box      = document.getElementById('rkkViewModalBox');
    const closeBtn = document.getElementById('rkkViewModalClose');
    const toggleBtn = document.getElementById('rkkViewModalToggle');

    const close = () => {
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('view-modal-maximized');
        }
        if (box) box.classList.remove('view-modal-maximized');
        if (toggleBtn) toggleBtn.textContent = '□';
    };

    if (closeBtn) closeBtn.addEventListener('click', close);
    if (modal)    modal.addEventListener('click', e => { if (e.target === modal) close(); });

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

// ── Restore modal ─────────────────────────────────────────────────────────────
function openRestoreModal(id) {
    const record = rejectedKKRecords.find(r => r.id === id);
    if (!record) return;
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
    const cancelBtn  = document.getElementById('rkkRestoreCancelBtn');
    const confirmBtn = document.getElementById('rkkRestoreConfirmBtn');
    const modal      = document.getElementById('rkkRestoreModal');

    if (cancelBtn)  cancelBtn.addEventListener('click', closeRestoreModal);
    if (modal)      modal.addEventListener('click', e => { if (e.target === modal) closeRestoreModal(); });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!rkkPendingRestoreId) return;
            const idx = rejectedKKRecords.findIndex(r => r.id === rkkPendingRestoreId);
            if (idx !== -1) rejectedKKRecords.splice(idx, 1);
            rkkFiltered = [...rejectedKKRecords];
            closeRestoreModal();
            rkkCurrentPage = 1;
            renderTable();
            showToast('rkkToast', 'Record restored to KK Profiling.');
        });
    }
}

// ── Toast ─────────────────────────────────────────────────────────────────────
function showToast(toastId, message) {
    const toast = document.getElementById(toastId);
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}
