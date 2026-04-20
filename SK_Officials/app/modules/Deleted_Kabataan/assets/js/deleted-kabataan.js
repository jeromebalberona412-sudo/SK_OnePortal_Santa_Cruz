// Deleted Kabataan Module

document.addEventListener('DOMContentLoaded', function () {
    initDeletedKabataan();
});

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
        deletedDate: 'Apr 10, 2026',
        deletedTime: '09:45 AM',
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
        deletedDate: 'Apr 12, 2026',
        deletedTime: '02:30 PM',
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
    bindViewModal();
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
        tbody.innerHTML = `<tr class="empty-state-row"><td colspan="8">No deleted Kabataan records found.</td></tr>`;
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
            <td><span class="deleted-at-badge">${r.deletedDate}</span></td>
            <td><span class="deleted-time-badge">${r.deletedTime}</span></td>
            <td>
                <div class="action-btns">
                    <button class="btn-view-action" data-id="${r.id}">View</button>
                    <button class="btn-restore-action" data-id="${r.id}">Restore</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) {
        info.textContent = `Showing ${start + 1}–${Math.min(end, dkFiltered.length)} of ${dkFiltered.length} records`;
    }

    renderPagination(dkFiltered.length);

    tbody.querySelectorAll('.btn-restore-action').forEach(btn => {
        btn.addEventListener('click', function () { openRestoreModal(this.dataset.id); });
    });
    tbody.querySelectorAll('.btn-view-action').forEach(btn => {
        btn.addEventListener('click', function () { openViewModal(this.dataset.id); });
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

// ── View modal ────────────────────────────────────────────────────────────────
function openViewModal(id) {
    const r = deletedKabataanRecords.find(x => x.id === id);
    if (!r) return;
    const body = document.getElementById('dkViewModalBody');
    if (body) {
        body.innerHTML = `
            <div class="view-detail-grid">
                <div class="view-detail-row"><span class="view-detail-label">Full Name</span><span class="view-detail-value">${r.lastName}, ${r.firstName} ${r.middleName || ''} ${r.suffix || ''}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Age</span><span class="view-detail-value">${r.age || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Sex</span><span class="view-detail-value">${r.sex || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Civil Status</span><span class="view-detail-value">${r.civilStatus || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Barangay</span><span class="view-detail-value">${r.barangay || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Purok / Zone</span><span class="view-detail-value">${r.purokZone || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Education</span><span class="view-detail-value">${r.educationalBackground || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Youth Classification</span><span class="view-detail-value">${r.youthClassification || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Work Status</span><span class="view-detail-value">${r.workStatus || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Contact Number</span><span class="view-detail-value">${r.contactNumber || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Deleted Date</span><span class="view-detail-value">${r.deletedDate}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Deleted Time</span><span class="view-detail-value">${r.deletedTime}</span></div>
            </div>`;
    }
    const modal = document.getElementById('dkViewModal');
    if (modal) modal.style.display = 'flex';
}

function bindViewModal() {
    const modal    = document.getElementById('dkViewModal');
    const box      = document.getElementById('dkViewModalBox');
    const closeBtn = document.getElementById('dkViewModalClose');
    const toggleBtn = document.getElementById('dkViewModalToggle');

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
