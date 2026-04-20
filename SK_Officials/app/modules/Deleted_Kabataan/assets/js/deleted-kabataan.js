// Deleted Kabataan Module
// Shares the same data source as kabataan.js (kabataan array).
// Records with status === 'deleted' are shown here.
// Restore moves them back to active.

document.addEventListener('DOMContentLoaded', function () {
    initDeletedKabataan();
});

// ── Shared data store (mirrors kabataan.js sample data, marked deleted) ──────
// In production this would come from an API / shared store.
// For UI-only: we seed a few deleted records from the same structure.
const deletedKabataanRecords = [
    {
        id: 'dk-001',
        respondentNumber: '009',
        firstName: 'Ramon',
        middleName: 'Jose',
        lastName: 'Villanueva',
        suffix: '',
        sex: 'Male',
        age: 20,
        barangay: 'DAMAYAN',
        purokZone: 'Zone 5',
        educationalBackground: 'College Level',
        youthClassification: 'In School Youth',
        workStatus: 'Student',
        civilStatus: 'Single',
        contactNumber: '09187654321',
        deletedAt: 'Apr 10, 2026',
    },
    {
        id: 'dk-002',
        respondentNumber: '010',
        firstName: 'Liza',
        middleName: 'Mae',
        lastName: 'Santos',
        suffix: '',
        sex: 'Female',
        age: 17,
        barangay: 'IMELDA',
        purokZone: 'Zone 3',
        educationalBackground: 'High School Level',
        youthClassification: 'In School Youth',
        workStatus: 'Student',
        civilStatus: 'Single',
        contactNumber: '09198765432',
        deletedAt: 'Apr 12, 2026',
    },
];

let dkFiltered = [...deletedKabataanRecords];
let dkCurrentPage = 1;
const dkPerPage = 10;
let dkPendingRestoreId = null;

function initDeletedKabataan() {
    renderTable();
    bindSearch();
    bindRestoreModal();
}

// ── Render ────────────────────────────────────────────────────────────────────
function renderTable() {
    const tbody = document.getElementById('deletedKabataanTableBody');
    const info  = document.getElementById('deletedKabataanPaginationInfo');
    if (!tbody) return;

    const start = (dkCurrentPage - 1) * dkPerPage;
    const end   = start + dkPerPage;
    const page  = dkFiltered.slice(start, end);

    if (dkFiltered.length === 0) {
        tbody.innerHTML = `<tr class="empty-state-row"><td colspan="7">No deleted Kabataan records found.</td></tr>`;
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
            <td>${r.barangay || '—'}</td>
            <td>${r.educationalBackground || '—'}</td>
            <td><span class="deleted-at-badge">${r.deletedAt}</span></td>
            <td>
                <button class="btn-restore-action" data-id="${r.id}">
                    ↩ Restore
                </button>
            </td>
        </tr>`;
    }).join('');

    if (info) {
        info.textContent = `Showing ${start + 1}–${Math.min(end, dkFiltered.length)} of ${dkFiltered.length} records`;
    }

    renderPagination(dkFiltered.length);

    // Wire restore buttons
    tbody.querySelectorAll('.btn-restore-action').forEach(btn => {
        btn.addEventListener('click', function () {
            openRestoreModal(this.dataset.id);
        });
    });
}

function renderPagination(total) {
    const pages = Math.ceil(total / dkPerPage);
    const nums  = document.getElementById('deletedKabataanPageNumbers');
    const prev  = document.getElementById('deletedKabataanPrevBtn');
    const next  = document.getElementById('deletedKabataanNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button class="pagination-btn ${i + 1 === dkCurrentPage ? 'active' : ''}">${i + 1}</button>
        `).join('');
        nums.querySelectorAll('.pagination-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => { dkCurrentPage = i + 1; renderTable(); });
        });
    }

    if (prev) { prev.disabled = dkCurrentPage === 1; prev.onclick = () => { dkCurrentPage--; renderTable(); }; }
    if (next) { next.disabled = dkCurrentPage >= pages || pages === 0; next.onclick = () => { dkCurrentPage++; renderTable(); }; }
}

// ── Search ────────────────────────────────────────────────────────────────────
function bindSearch() {
    const input = document.getElementById('deletedKabataanSearch');
    if (!input) return;
    input.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        dkFiltered = deletedKabataanRecords.filter(r =>
            `${r.firstName} ${r.middleName || ''} ${r.lastName}`.toLowerCase().includes(q) ||
            (r.barangay || '').toLowerCase().includes(q)
        );
        dkCurrentPage = 1;
        renderTable();
    });
}

// ── Restore modal ─────────────────────────────────────────────────────────────
function openRestoreModal(id) {
    const record = deletedKabataanRecords.find(r => r.id === id);
    if (!record) return;
    dkPendingRestoreId = id;
    const nameEl = document.getElementById('dkRestoreName');
    if (nameEl) nameEl.textContent = `${record.lastName}, ${record.firstName}${record.middleName ? ' ' + record.middleName : ''}`;
    const modal = document.getElementById('dkRestoreModal');
    if (modal) modal.style.display = 'flex';
}

function closeRestoreModal() {
    dkPendingRestoreId = null;
    const modal = document.getElementById('dkRestoreModal');
    if (modal) modal.style.display = 'none';
}

function bindRestoreModal() {
    const cancelBtn  = document.getElementById('dkRestoreCancelBtn');
    const confirmBtn = document.getElementById('dkRestoreConfirmBtn');
    const modal      = document.getElementById('dkRestoreModal');

    if (cancelBtn)  cancelBtn.addEventListener('click', closeRestoreModal);
    if (modal)      modal.addEventListener('click', e => { if (e.target === modal) closeRestoreModal(); });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!dkPendingRestoreId) return;
            // Remove from deleted list
            const idx = deletedKabataanRecords.findIndex(r => r.id === dkPendingRestoreId);
            if (idx !== -1) deletedKabataanRecords.splice(idx, 1);
            dkFiltered = [...deletedKabataanRecords];
            closeRestoreModal();
            dkCurrentPage = 1;
            renderTable();
            showToast('dkToast', 'Record restored to Kabataan list.');
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
