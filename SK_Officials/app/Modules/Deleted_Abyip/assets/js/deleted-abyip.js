document.addEventListener('DOMContentLoaded', () => initDeletedAbyip());

const deletedAbyipRecords = [
    {
        id: 'da-001',
        title: 'Youth Sports Development 2026',
        category: 'Sports',
        deletedDate: 'Apr 18, 2026',
        deletedTime: '10:30 AM',
        _deletedTs: new Date('2026-04-18T10:30:00'),
        skTerm: '2025-2027',
    },
    {
        id: 'da-002',
        title: 'SK Livelihood Training 2024',
        category: 'Livelihood',
        deletedDate: 'Sep 05, 2024',
        deletedTime: '02:15 PM',
        _deletedTs: new Date('2024-09-05T14:15:00'),
        skTerm: '2022-2025',
    },
];

let daArchiveTerm = '2025-2027';
let daActiveFilter = 'all';
let daFiltered = [];

function daApplyDateFilter(records, filter) {
    const n = new Date('2026-04-20T12:00:00');
    if (filter === 'today') {
        return records.filter(r => {
            const t = r._deletedTs;
            return t.getFullYear() === n.getFullYear() && t.getMonth() === n.getMonth() && t.getDate() === n.getDate();
        });
    }
    return records;
}

function daApplyAllFilters() {
    const byDate = daActiveFilter === 'all' ? deletedAbyipRecords.slice() : daApplyDateFilter(deletedAbyipRecords, daActiveFilter);
    return window.SkArchive ? SkArchive.filterByArchiveTerm(byDate, daArchiveTerm, ['_deletedTs']) : byDate;
}

function initDeletedAbyip() {
    if (window.SkArchive) {
        SkArchive.mountShowArchiveFilter((termId) => {
            daArchiveTerm = termId;
            daFiltered = daApplyAllFilters();
            renderDaTable();
        });
    } else {
        daFiltered = daApplyAllFilters();
        renderDaTable();
    }
    bindDaSearch();
    bindDaFilterTabs();
    bindDaView();
}

function renderDaTable() {
    const tbody = document.getElementById('daTableBody');
    if (!tbody) return;
    if (!daFiltered.length) {
        tbody.innerHTML = '<tr class="empty-state-row"><td colspan="5">No deleted ABYIP records for this term.</td></tr>';
        return;
    }
    tbody.innerHTML = daFiltered.map(r => {
        const canRestore = window.SkArchive ? SkArchive.canRestoreRecord(r, ['_deletedTs']) : true;
        const restoreBtn = canRestore
            ? `<button type="button" class="btn-restore-action" data-id="${r.id}">Restore</button>`
            : `<button type="button" class="btn-restore-action is-disabled" disabled title="Past term — view only">Restore</button>`;
        return `<tr>
            <td style="font-weight:600;">${r.title}</td>
            <td>${r.category}</td>
            <td><span class="deleted-at-badge">${r.deletedDate}</span></td>
            <td><span class="deleted-time-badge">${r.deletedTime}</span></td>
            <td><div class="action-btns">
                <button type="button" class="btn-view-action" data-id="${r.id}">View</button>
                ${restoreBtn}
            </div></td>
        </tr>`;
    }).join('');
    tbody.querySelectorAll('.btn-view-action').forEach(btn => {
        btn.addEventListener('click', () => openDaView(btn.dataset.id));
    });
}

function openDaView(id) {
    const r = deletedAbyipRecords.find(x => x.id === id);
    const body = document.getElementById('daViewBody');
    const modal = document.getElementById('daViewModal');
    if (!r || !body || !modal) return;
    body.innerHTML = `<p><strong>${r.title}</strong> (${r.category})</p><p>Deleted: ${r.deletedDate} ${r.deletedTime}</p>`;
    modal.style.display = 'flex';
}

function bindDaView() {
    const close = document.getElementById('daViewClose');
    const modal = document.getElementById('daViewModal');
    if (close) close.addEventListener('click', () => { if (modal) modal.style.display = 'none'; });
}

function bindDaSearch() {
    const input = document.getElementById('daSearch');
    if (!input) return;
    input.addEventListener('input', () => {
        const q = input.value.toLowerCase();
        daFiltered = daApplyAllFilters().filter(r =>
            (r.title || '').toLowerCase().includes(q) || (r.category || '').toLowerCase().includes(q)
        );
        renderDaTable();
    });
}

function bindDaFilterTabs() {
    document.querySelectorAll('.filter-tab').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            daActiveFilter = this.dataset.filter;
            daFiltered = daApplyAllFilters();
            renderDaTable();
        });
    });
}
