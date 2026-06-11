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
                <td colspan="6" class="audit-empty-cell">No recent audit activity yet.</td>
            </tr>
        `;
        return;
    }

    body.innerHTML = rows.map((event) => `
            <tr>
                <td>${event.date || '-'}</td>
                <td>${event.time || '-'}</td>
                <td class="audit-cell-email">${event.email || event.actor || '-'}</td>
                <td>${event.event || '-'}</td>
                <td>${event.ipAddress || '-'}</td>
                <td>${event.device || 'Unknown'}</td>
            </tr>
        `).join('');
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
