// Deleted ABYIP Module

document.addEventListener('DOMContentLoaded', function () {
    initDeletedAbyip();
});

const deletedAbyipRecords = [
    {
        id: 'da-001',
        title: 'ABYIP CY 2024',
        dateCreated: 'Jan 15, 2025',
        timeCreated: '10:30 AM',
        status: 'Draft',
        remarks: 'Superseded by 2025 version',
        deletedDate: 'Mar 20, 2026',
        deletedTime: '11:15 AM',
    },
    {
        id: 'da-002',
        title: 'ABYIP CY 2023',
        dateCreated: 'Feb 02, 2024',
        timeCreated: '02:15 PM',
        status: 'Approved',
        remarks: 'Archived after audit',
        deletedDate: 'Apr 01, 2026',
        deletedTime: '03:45 PM',
    },
];

let daFiltered = [...deletedAbyipRecords];
let daCurrentPage = 1;
const daPerPage = 10;
let daPendingRestoreId = null;

function initDeletedAbyip() {
    renderTable();
    bindSearch();
    bindRestoreModal();
    bindViewModal();
}

// ── Render ────────────────────────────────────────────────────────────────────
function renderTable() {
    const tbody = document.getElementById('deletedAbyipTableBody');
    const info  = document.getElementById('deletedAbyipPaginationInfo');
    if (!tbody) return;

    const start = (daCurrentPage - 1) * daPerPage;
    const end   = start + daPerPage;
    const page  = daFiltered.slice(start, end);

    if (daFiltered.length === 0) {
        tbody.innerHTML = `<tr class="empty-state-row"><td colspan="8">No deleted ABYIP records found.</td></tr>`;
        if (info) info.textContent = 'No records found';
        renderPagination(0);
        return;
    }

    tbody.innerHTML = page.map(r => `
        <tr>
            <td style="font-weight:600;color:#111827;text-align:left;">${r.title}</td>
            <td>${r.dateCreated}</td>
            <td>${r.timeCreated}</td>
            <td><span class="status-pill ${r.status.toLowerCase()}">${r.status}</span></td>
            <td style="text-align:left;font-size:12px;color:#6b7280;">${r.remarks || '—'}</td>
            <td><span class="deleted-at-badge">${r.deletedDate}</span></td>
            <td><span class="deleted-time-badge">${r.deletedTime}</span></td>
            <td>
                <div class="action-btns">
                    <button class="btn-view-action" data-id="${r.id}">View</button>
                    <button class="btn-restore-action" data-id="${r.id}">Restore</button>
                </div>
            </td>
        </tr>
    `).join('');

    if (info) {
        info.textContent = `Showing ${start + 1}–${Math.min(end, daFiltered.length)} of ${daFiltered.length} records`;
    }

    renderPagination(daFiltered.length);

    tbody.querySelectorAll('.btn-restore-action').forEach(btn => {
        btn.addEventListener('click', function () { openRestoreModal(this.dataset.id); });
    });
    tbody.querySelectorAll('.btn-view-action').forEach(btn => {
        btn.addEventListener('click', function () { openViewModal(this.dataset.id); });
    });
}

function renderPagination(total) {
    const pages = Math.ceil(total / daPerPage);
    const nums  = document.getElementById('deletedAbyipPageNumbers');
    const prev  = document.getElementById('deletedAbyipPrevBtn');
    const next  = document.getElementById('deletedAbyipNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button class="pagination-btn ${i + 1 === daCurrentPage ? 'active' : ''}">${i + 1}</button>
        `).join('');
        nums.querySelectorAll('.pagination-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => { daCurrentPage = i + 1; renderTable(); });
        });
    }
    if (prev) { prev.disabled = daCurrentPage === 1; prev.onclick = () => { daCurrentPage--; renderTable(); }; }
    if (next) { next.disabled = daCurrentPage >= pages || pages === 0; next.onclick = () => { daCurrentPage++; renderTable(); }; }
}

// ── Search ────────────────────────────────────────────────────────────────────
function bindSearch() {
    const input = document.getElementById('deletedAbyipSearch');
    if (!input) return;
    input.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        daFiltered = deletedAbyipRecords.filter(r =>
            r.title.toLowerCase().includes(q) ||
            (r.remarks || '').toLowerCase().includes(q)
        );
        daCurrentPage = 1;
        renderTable();
    });
}

// ── View modal ────────────────────────────────────────────────────────────────
function openViewModal(id) {
    const r = deletedAbyipRecords.find(x => x.id === id);
    if (!r) return;
    const body = document.getElementById('daViewModalBody');
    if (body) {
        body.innerHTML = `
            <div class="view-detail-grid">
                <div class="view-detail-row"><span class="view-detail-label">Title</span><span class="view-detail-value">${r.title}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Date Created</span><span class="view-detail-value">${r.dateCreated}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Time Created</span><span class="view-detail-value">${r.timeCreated}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Status</span><span class="view-detail-value">${r.status}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Remarks</span><span class="view-detail-value">${r.remarks || '—'}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Deleted Date</span><span class="view-detail-value">${r.deletedDate}</span></div>
                <div class="view-detail-row"><span class="view-detail-label">Deleted Time</span><span class="view-detail-value">${r.deletedTime}</span></div>
            </div>`;
    }
    const modal = document.getElementById('daViewModal');
    if (modal) modal.style.display = 'flex';
}

function bindViewModal() {
    const modal    = document.getElementById('daViewModal');
    const box      = document.getElementById('daViewModalBox');
    const closeBtn = document.getElementById('daViewModalClose');
    const toggleBtn = document.getElementById('daViewModalToggle');

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
    const record = deletedAbyipRecords.find(r => r.id === id);
    if (!record) return;
    daPendingRestoreId = id;
    const nameEl = document.getElementById('daRestoreName');
    if (nameEl) nameEl.textContent = record.title;
    const modal = document.getElementById('daRestoreModal');
    if (modal) modal.style.display = 'flex';
}

function closeRestoreModal() {
    daPendingRestoreId = null;
    const modal = document.getElementById('daRestoreModal');
    if (modal) modal.style.display = 'none';
}

function bindRestoreModal() {
    const cancelBtn  = document.getElementById('daRestoreCancelBtn');
    const confirmBtn = document.getElementById('daRestoreConfirmBtn');
    const modal      = document.getElementById('daRestoreModal');

    if (cancelBtn)  cancelBtn.addEventListener('click', closeRestoreModal);
    if (modal)      modal.addEventListener('click', e => { if (e.target === modal) closeRestoreModal(); });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!daPendingRestoreId) return;
            const idx = deletedAbyipRecords.findIndex(r => r.id === daPendingRestoreId);
            if (idx !== -1) deletedAbyipRecords.splice(idx, 1);
            daFiltered = [...deletedAbyipRecords];
            closeRestoreModal();
            daCurrentPage = 1;
            renderTable();
            showToast('daToast', 'ABYIP record restored successfully.');
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
