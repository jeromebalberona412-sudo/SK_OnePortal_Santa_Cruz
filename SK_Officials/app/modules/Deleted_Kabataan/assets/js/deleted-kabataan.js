// Deleted Kabataan Module JS

document.addEventListener('DOMContentLoaded', function () {
    initDeletedKabataan();
});

// Sample data — replace with real API/localStorage data
const deletedKabataanData = [];

let dkCurrentPage = 1;
const dkPerPage = 10;
let dkFiltered = [...deletedKabataanData];

function initDeletedKabataan() {
    renderTable();
    bindSearch();
    bindModalClose();
}

function renderTable() {
    const tbody = document.getElementById('deletedKabataanTableBody');
    const info  = document.getElementById('deletedKabataanPaginationInfo');
    if (!tbody) return;

    const start = (dkCurrentPage - 1) * dkPerPage;
    const end   = start + dkPerPage;
    const page  = dkFiltered.slice(start, end);

    if (dkFiltered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="empty-state">No deleted kabataan records found.</td></tr>`;
        if (info) info.textContent = 'No records found';
        renderPagination(0);
        return;
    }

    tbody.innerHTML = page.map(r => `
        <tr>
            <td>${r.lastName}, ${r.firstName} ${r.middleName || ''} ${r.suffix || ''}</td>
            <td>${r.age}</td>
            <td>${r.sex}</td>
            <td>${r.purokSitio}</td>
            <td>${r.highestEducation}</td>
            <td><span class="deleted-at-badge">${r.deletedAt}</span></td>
            <td>
                <button class="btn-view-dk" onclick="openDkViewModal('${r.id}')">View</button>
            </td>
        </tr>
    `).join('');

    if (info) {
        info.textContent = `Showing ${start + 1}–${Math.min(end, dkFiltered.length)} of ${dkFiltered.length} records`;
    }

    renderPagination(dkFiltered.length);
}

function renderPagination(total) {
    const pages = Math.ceil(total / dkPerPage);
    const nums  = document.getElementById('deletedKabataanPageNumbers');
    const prev  = document.getElementById('deletedKabataanPrevBtn');
    const next  = document.getElementById('deletedKabataanNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button class="pagination-btn ${i + 1 === dkCurrentPage ? 'active' : ''}"
                    onclick="dkGoToPage(${i + 1})">${i + 1}</button>
        `).join('');
    }

    if (prev) prev.disabled = dkCurrentPage === 1;
    if (next) next.disabled = dkCurrentPage === pages || pages === 0;

    if (prev) prev.onclick = () => dkGoToPage(dkCurrentPage - 1);
    if (next) next.onclick = () => dkGoToPage(dkCurrentPage + 1);
}

function dkGoToPage(page) {
    dkCurrentPage = page;
    renderTable();
}

function bindSearch() {
    const input = document.getElementById('deletedKabataanSearch');
    if (!input) return;
    input.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        dkFiltered = deletedKabataanData.filter(r =>
            `${r.firstName} ${r.middleName} ${r.lastName}`.toLowerCase().includes(q) ||
            (r.purokSitio || '').toLowerCase().includes(q)
        );
        dkCurrentPage = 1;
        renderTable();
    });
}

function openDkViewModal(id) {
    const record = deletedKabataanData.find(r => r.id === id);
    if (!record) return;

    const fields = {
        dkViewFirstName: record.firstName,
        dkViewMiddleName: record.middleName || '—',
        dkViewLastName: record.lastName,
        dkViewSuffix: record.suffix || '—',
        dkViewAge: record.age,
        dkViewDateOfBirth: record.dateOfBirth || '—',
        dkViewSex: record.sex,
        dkViewCivilStatus: record.civilStatus || '—',
        dkViewPurokSitio: record.purokSitio,
        dkViewBarangay: record.barangay || '—',
        dkViewHighestEducation: record.highestEducation,
        dkViewWorkStatus: record.workStatus || '—',
        dkViewYouthClassification: record.youthClassification || '—',
        dkViewRegisteredVoter: record.registeredVoter || '—',
        dkViewVotedLastElection: record.votedLastElection || '—',
        dkViewRecordId: record.id,
        dkViewDeletedAt: record.deletedAt,
    };

    Object.entries(fields).forEach(([id, val]) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    });

    document.getElementById('deletedKabataanViewModal').style.display = 'flex';
}

function bindModalClose() {
    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.dk-modal-backdrop').forEach(m => m.style.display = 'none');
        });
    });

    // Restore button placeholder
    const restoreBtn = document.getElementById('dkRestoreBtn');
    if (restoreBtn) {
        restoreBtn.addEventListener('click', () => {
            alert('Restore functionality will be connected to the backend.');
        });
    }
}

window.openDkViewModal = openDkViewModal;
window.dkGoToPage = dkGoToPage;
