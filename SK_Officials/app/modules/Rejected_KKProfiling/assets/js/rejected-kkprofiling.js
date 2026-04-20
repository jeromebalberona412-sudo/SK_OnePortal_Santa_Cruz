// Rejected KK Profiling Module
// Shows KK Profiling requests that were rejected.
// Restore moves them back to the active KK Profiling list.

document.addEventListener('DOMContentLoaded', function () {
    initRejectedKK();
});

// ── Rejected KK records (mirrors kkprofiling-requests.js structure) ───────────
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
        rejectedAt: 'Apr 05, 2026',
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
        rejectedAt: 'Apr 08, 2026',
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
        rejectedAt: 'Apr 14, 2026',
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
        tbody.innerHTML = `<tr class="empty-state-row"><td colspan="8">No rejected KK Profiling records found.</td></tr>`;
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
            <td><span class="rejected-at-badge">${r.rejectedAt}</span></td>
            <td>
                <button class="btn-restore-action" data-id="${r.id}">↩ Restore</button>
            </td>
        </tr>`;
    }).join('');

    if (info) {
        info.textContent = `Showing ${start + 1}–${Math.min(end, rkkFiltered.length)} of ${rkkFiltered.length} records`;
    }

    renderPagination(rkkFiltered.length);

    tbody.querySelectorAll('.btn-restore-action').forEach(btn => {
        btn.addEventListener('click', function () {
            openRestoreModal(this.dataset.id);
        });
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
