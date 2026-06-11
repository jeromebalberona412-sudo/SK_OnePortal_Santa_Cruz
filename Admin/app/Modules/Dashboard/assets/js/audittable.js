let dashboardAuditEvents = Array.isArray(window.__DASHBOARD_AUDIT__) ? window.__DASHBOARD_AUDIT__ : [];

window.auditEvents = dashboardAuditEvents;

function renderAuditRows(events = dashboardAuditEvents) {
    const body = document.getElementById('auditActivityBody');
    if (!body) {
        return;
    }

    const rows = Array.isArray(events) ? events.slice(0, 10) : [];

    if (!rows.length) {
        body.innerHTML = `
            <tr>
                <td colspan="7" class="audit-empty-cell">No recent audit activity yet.</td>
            </tr>
        `;
        return;
    }

    body.innerHTML = rows.map((event) => {
        const outcomeClassByState = {
            success: 'audit-outcome--success',
            warning: 'audit-outcome--warning',
            failure: 'audit-outcome--failure',
        };
        const outcomeClass = outcomeClassByState[event.outcome] || 'audit-outcome--warning';

        return `
            <tr>
                <td>${event.date || '-'}</td>
                <td>${event.time || '-'}</td>
                <td>${event.actor || 'System'}</td>
                <td>${event.event || '-'}</td>
                <td><span class="audit-outcome ${outcomeClass}">${String(event.outcome || 'success').toUpperCase()}</span></td>
                <td>${event.ipAddress || '-'}</td>
                <td>${event.device || 'Unknown'}</td>
            </tr>
        `;
    }).join('');
}

window.refreshDashboardAuditTable = function refreshDashboardAuditTable(events) {
    dashboardAuditEvents = Array.isArray(events) ? events : [];
    window.auditEvents = dashboardAuditEvents;
    renderAuditRows(dashboardAuditEvents);
};

function initAuditTable() {
    renderAuditRows(dashboardAuditEvents);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAuditTable);
} else {
    initAuditTable();
}
