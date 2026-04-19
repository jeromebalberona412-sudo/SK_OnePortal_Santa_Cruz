// Rejected KK Profiling Module JS

document.addEventListener('DOMContentLoaded', function () {
    initRejectedKK();
});

// Sample data — replace with real API/localStorage data
const rejectedKKData = [];

let rkkCurrentPage = 1;
const rkkPerPage = 10;
let rkkFiltered = [...rejectedKKData];

function initRejectedKK() {
    renderTable();
    bindSearch();
    bindPurokFilter();
    bindModalClose();
}

function renderTable() {
    const tbody = document.getElementById('rejectedKKTableBody');
    const info  = document.getElementById('rejectedKKPaginationInfo');
    if (!tbody) return;

    const start = (rkkCurrentPage - 1) * rkkPerPage;
    const end   = start + rkkPerPage;
    const page  = rkkFiltered.slice(start, end);

    if (rkkFiltered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="empty-state">No rejected KK Profiling records found.</td></tr>`;
        if (info) info.textContent = 'No records found';
        renderPagination(0);
        return;
    }

    tbody.innerHTML = page.map(r => `
        <tr>
            <td>${r.lastName}, ${r.firstName} ${r.middleName || ''} ${r.suffix || ''}</td>
            <td>${r.age}</td>
            <td>${r.purokZone}</td>
            <td>${r.registeredVoter}</td>
            <td><span class="rejection-reason-cell" title="${r.rejectionReason}">${r.rejectionReason}</span></td>
            <td><span class="rejected-at-badge">${r.rejectedAt}</span></td>
            <td>
                <button class="btn-view-rkk" onclick="openRkkViewModal('${r.id}')">View</button>
            </td>
        </tr>
    `).join('');

    if (info) {
        info.textContent = `Showing ${start + 1}–${Math.min(end, rkkFiltered.length)} of ${rkkFiltered.length} records`;
    }

    renderPagination(rkkFiltered.length);
}

function renderPagination(total) {
    const pages = Math.ceil(total / rkkPerPage);
    const nums  = document.getElementById('rejectedKKPageNumbers');
    const prev  = document.getElementById('rejectedKKPrevBtn');
    const next  = document.getElementById('rejectedKKNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button class="pagination-btn ${i + 1 === rkkCurrentPage ? 'active' : ''}"
                    onclick="rkkGoToPage(${i + 1})">${i + 1}</button>
        `).join('');
    }

    if (prev) prev.disabled = rkkCurrentPage === 1;
    if (next) next.disabled = rkkCurrentPage === pages || pages === 0;

    if (prev) prev.onclick = () => rkkGoToPage(rkkCurrentPage - 1);
    if (next) next.onclick = () => rkkGoToPage(rkkCurrentPage + 1);
}

function rkkGoToPage(page) {
    rkkCurrentPage = page;
    renderTable();
}

function bindSearch() {
    const input = document.getElementById('rejectedKKSearch');
    if (!input) return;
    input.addEventListener('input', applyFilters);
}

function bindPurokFilter() {
    const select = document.getElementById('rejectedKKPurokFilter');
    if (!select) return;
    select.addEventListener('change', applyFilters);
}

function applyFilters() {
    const q     = (document.getElementById('rejectedKKSearch')?.value || '').toLowerCase();
    const purok = document.getElementById('rejectedKKPurokFilter')?.value || '';

    rkkFiltered = rejectedKKData.filter(r => {
        const matchSearch = `${r.firstName} ${r.middleName} ${r.lastName}`.toLowerCase().includes(q);
        const matchPurok  = purok === '' || r.purokZone === purok;
        return matchSearch && matchPurok;
    });

    rkkCurrentPage = 1;
    renderTable();
}

function openRkkViewModal(id) {
    const record = rejectedKKData.find(r => r.id === id);
    if (!record) return;

    const fields = {
        rkkViewRespondentNumber: record.respondentNumber || '—',
        rkkViewDate: record.date || '—',
        rkkViewLastName: record.lastName,
        rkkViewFirstName: record.firstName,
        rkkViewMiddleName: record.middleName || '—',
        rkkViewSuffix: record.suffix || '—',
        rkkViewRegion: record.region || '—',
        rkkViewProvince: record.province || '—',
        rkkViewCity: record.city || '—',
        rkkViewBarangay: record.barangay || '—',
        rkkViewPurokZone: record.purokZone || '—',
        rkkViewSex: record.sex || '—',
        rkkViewAge: record.age,
        rkkViewBirthday: record.birthday || '—',
        rkkViewEmail: record.email || '—',
        rkkViewContact: record.contactNumber || '—',
        rkkViewCivilStatus: record.civilStatus || '—',
        rkkViewYouthClassification: record.youthClassification || '—',
        rkkViewAgeGroup: record.ageGroup || '—',
        rkkViewWorkStatus: record.workStatus || '—',
        rkkViewEducation: record.educationalBackground || '—',
        rkkViewSKVoter: record.registeredSKVoter || '—',
        rkkViewNationalVoter: record.registeredNationalVoter || '—',
        rkkViewVotingHistory: record.votingHistory || '—',
        rkkViewRejectedAt: record.rejectedAt || '—',
        rkkViewRejectionReason: record.rejectionReason || '—',
    };

    Object.entries(fields).forEach(([id, val]) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    });

    document.getElementById('rejectedKKViewModal').style.display = 'flex';
}

function bindModalClose() {
    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.rkk-modal-backdrop').forEach(m => m.style.display = 'none');
        });
    });

    const reEvalBtn = document.getElementById('rkkReEvaluateBtn');
    if (reEvalBtn) {
        reEvalBtn.addEventListener('click', () => {
            alert('Re-evaluate functionality will be connected to the backend.');
        });
    }
}

window.openRkkViewModal = openRkkViewModal;
window.rkkGoToPage = rkkGoToPage;
