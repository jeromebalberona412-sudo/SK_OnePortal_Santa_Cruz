import 'datatables.net-dt/css/dataTables.dataTables.css';

const auditEvents = [
    {
        timestamp: '2026-03-12 08:14:22',
        actor: 'admin.roxas',
        event: 'Reset official password',
        outcome: 'success',
        ipAddress: '10.40.2.18',
        device: 'Chrome / Linux',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-12 08:02:01',
        actor: 'admin.roxas',
        event: 'Federation account sync',
        outcome: 'success',
        ipAddress: '10.40.2.18',
        device: 'Chrome / Linux',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-12 07:38:19',
        actor: 'admin.delacruz',
        event: 'Failed role assignment',
        outcome: 'failure',
        ipAddress: '10.40.2.23',
        device: 'Edge / Windows',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-12 07:11:54',
        actor: 'admin.reyes',
        event: 'Created federation account',
        outcome: 'success',
        ipAddress: '10.40.2.9',
        device: 'Firefox / Linux',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-12 06:48:40',
        actor: 'admin.klein',
        event: 'Audit export requested',
        outcome: 'warning',
        ipAddress: '10.40.2.31',
        device: 'Safari / macOS',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-12 06:12:03',
        actor: 'admin.santos',
        event: 'Bulk status update',
        outcome: 'success',
        ipAddress: '10.40.2.42',
        device: 'Chrome / Windows',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-11 23:57:02',
        actor: 'admin.romero',
        event: 'Account import validation error',
        outcome: 'failure',
        ipAddress: '10.40.2.65',
        device: 'Edge / Windows',
        actionLabel: 'Open',
    },
];

window.auditEvents = auditEvents;

function renderAuditRows(tableElement) {
    const body = tableElement.querySelector('tbody');
    if (!body) {
        return;
    }

    body.innerHTML = auditEvents
        .map((event) => {
            const outcomeClassByState = {
                success: 'audit-outcome--success',
                warning: 'audit-outcome--warning',
                failure: 'audit-outcome--failure',
            };

            const outcomeClass = outcomeClassByState[event.outcome] || 'audit-outcome--warning';
            const channelLabel = event.device.split('/')[0].trim();

            return `
                <tr>
                    <td>${event.timestamp}</td>
                    <td>${event.actor}</td>
                    <td>${event.event}</td>
                    <td><span class="audit-outcome ${outcomeClass}">${event.outcome.toUpperCase()}</span></td>
                    <td>${event.ipAddress}</td>
                    <td>${channelLabel}</td>
                    <td><a class="audit-action-link" href="#">${event.actionLabel}</a></td>
                </tr>
            `;
        })
        .join('');
}

function initAuditDataTable() {
    const tableElement = document.getElementById('auditActivityTable');
    if (!tableElement) {
        return;
    }

    renderAuditRows(tableElement);

    if (!window.DataTable || tableElement.dataset.enhanced === 'true') {
        return;
    }

    new window.DataTable(tableElement, {
        pageLength: 5,
        lengthChange: false,
        searching: false,
        info: false,
        order: [[0, 'desc']],
        pagingType: 'simple_numbers',
        columnDefs: [{ orderable: false, targets: 6 }],
    });

    tableElement.dataset.enhanced = 'true';
}

function canBootAuditTable() {
    return typeof window.DataTable !== 'undefined';
}

function tryBootAuditTable() {
    if (!document.getElementById('auditActivityTable') || !canBootAuditTable()) {
        return;
    }

    initAuditDataTable();
}

window.addEventListener('sk:frontend-deps-ready', tryBootAuditTable);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tryBootAuditTable);
} else {
    tryBootAuditTable();
}
