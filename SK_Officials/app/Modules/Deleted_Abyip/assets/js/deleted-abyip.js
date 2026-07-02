document.addEventListener('DOMContentLoaded', () => initDeletedAbyip());

const deletedAbyipRecords = [
    {
        id: 'da-001',
        title: 'Youth Sports Development 2026',
        category: 'Sports',
        deletedDate: 'Apr 18, 2026',
        deletedTime: '10:30 AM',
        _deletedTs: new Date('2026-04-18T10:30:00'),
    },
    {
        id: 'da-002',
        title: 'SK Livelihood Training 2024',
        category: 'Livelihood',
        deletedDate: 'Sep 05, 2024',
        deletedTime: '02:15 PM',
        _deletedTs: new Date('2024-09-05T14:15:00'),
    },
];

let daArchiveTerm = '';
let daActiveFilter = 'all';
let daFiltered = [];

function daApplyDateFilter(records, filter) {
    const n = new Date();
    if (filter === 'today') {
        return records.filter((record) => {
            const timestamp = record._deletedTs;
            return timestamp
                && timestamp.getFullYear() === n.getFullYear()
                && timestamp.getMonth() === n.getMonth()
                && timestamp.getDate() === n.getDate();
        });
    }
    return records;
}

function daApplyAllFilters() {
    const byDate = daActiveFilter === 'all'
        ? deletedAbyipRecords.slice()
        : daApplyDateFilter(deletedAbyipRecords, daActiveFilter);

    return window.SkArchive
        ? SkArchive.filterByArchiveTerm(byDate, daArchiveTerm, ['_deletedTs'])
        : byDate;
}

function initDeletedAbyip() {
    bindDaSearch();
    bindDaFilterTabs();
    bindDaView();

    const boot = () => {
        daFiltered = daApplyAllFilters();
        renderDaTable();
    };

    if (window.SkArchive) {
        SkArchive.mountShowArchiveFilter((termId) => {
            daArchiveTerm = termId;
            daFiltered = daApplyAllFilters();
            renderDaTable();
        }).then(boot);
        return;
    }

    boot();
}

function renderDaTable() {
    const tbody = document.getElementById('daTableBody');
    if (!tbody) return;
    if (!daFiltered.length) {
        tbody.innerHTML = '<tr class="empty-state-row"><td colspan="5">No deleted ABYIP records for this term.</td></tr>';
        return;
    }
    tbody.innerHTML = daFiltered.map((record) => {
        const canRestore = window.SkArchive ? SkArchive.canRestoreRecord(record, ['_deletedTs']) : true;
        const restoreBtn = canRestore
            ? `<button type="button" class="btn-restore-action" data-id="${record.id}">Restore</button>`
            : '<button type="button" class="btn-restore-action is-disabled" disabled title="Past term — view only">Restore</button>';
        return `<tr>
            <td style="font-weight:600;">${record.title}</td>
            <td>${record.category}</td>
            <td><span class="deleted-at-badge">${record.deletedDate}</span></td>
            <td><span class="deleted-time-badge">${record.deletedTime}</span></td>
            <td><div class="action-btns">
                <button type="button" class="btn-view-action" data-id="${record.id}">View</button>
                ${restoreBtn}
            </div></td>
        </tr>`;
    }).join('');
    tbody.querySelectorAll('.btn-view-action').forEach((btn) => {
        btn.addEventListener('click', () => openDaView(btn.dataset.id));
    });
}

function openDaView(id) {
    const record = deletedAbyipRecords.find((item) => item.id === id);
    const body = document.getElementById('daViewBody');
    const modal = document.getElementById('daViewModal');
    if (!record || !body || !modal) return;
    body.innerHTML = `<p><strong>${record.title}</strong> (${record.category})</p><p>Deleted: ${record.deletedDate} ${record.deletedTime}</p>`;
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
        const query = input.value.toLowerCase();
        daFiltered = daApplyAllFilters().filter((record) =>
            (record.title || '').toLowerCase().includes(query)
            || (record.category || '').toLowerCase().includes(query)
        );
        renderDaTable();
    });
}

function bindDaFilterTabs() {
    document.querySelectorAll('.filter-tab').forEach((btn) => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach((button) => button.classList.remove('active'));
            this.classList.add('active');
            daActiveFilter = this.dataset.filter;
            daFiltered = daApplyAllFilters();
            renderDaTable();
        });
    });
}
