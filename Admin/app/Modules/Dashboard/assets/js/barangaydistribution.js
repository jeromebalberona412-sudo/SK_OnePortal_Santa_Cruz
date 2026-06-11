let barangayDistribution = Array.isArray(window.__BARANGAY_DISTRIBUTION__)
    ? window.__BARANGAY_DISTRIBUTION__
    : [];

window.barangayDistribution = barangayDistribution;

function overallGap(row) {
    return (row.federationCount ?? 0) > 0 && (row.skOfficialsAssigned ?? 0) > 0;
}

function renderBarangayDistributionRows(rows = barangayDistribution) {
    const tbody = document.getElementById('barangayDistributionBody');
    if (!tbody) {
        return;
    }

    const data = Array.isArray(rows) ? rows : [];

    if (!data.length) {
        tbody.innerHTML = `<tr><td colspan="4" class="barangay-empty-cell">No barangay data available.</td></tr>`;
        return;
    }

    tbody.innerHTML = data.map((row) => `
            <tr>
                <td>${row.barangay || '-'}</td>
                <td>${row.federationCount ?? 0}</td>
                <td>${row.skOfficialsAssigned ?? 0}</td>
                <td>${row.accountCount ?? 0}</td>
            </tr>
        `).join('');
}

window.dashboardConsole = function dashboardConsole() {
    return {
        barangayDistribution,

        federationBadge(row) {
            return row.skFederationAssigned ? 'pill--healthy' : 'pill--critical';
        },

        federationLabel(row) {
            return String(row.federationCount ?? 0);
        },

        overallGap(row) {
            return overallGap(row);
        },
    };
};

window.refreshBarangayDistribution = function refreshBarangayDistribution(rows) {
    barangayDistribution = Array.isArray(rows) ? rows : [];
    window.barangayDistribution = barangayDistribution;
    renderBarangayDistributionRows(barangayDistribution);
};

window.exportBarangayDistribution = function exportBarangayDistribution() {
    const headers = ['Barangay', 'Federation Assigned', 'Official Accounts', 'Total Accounts'];
    const rows = barangayDistribution.map((row) => [
        row.barangay,
        row.federationCount ?? 0,
        row.skOfficialsAssigned ?? 0,
        row.accountCount ?? 0,
    ]);

    let csvContent = `${headers.join(',')}\n`;
    rows.forEach((row) => {
        csvContent += `${row.map((value) => `"${String(value ?? '').replace(/"/g, '""')}"`).join(',')}\n`;
    });

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'barangay_distribution.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => renderBarangayDistributionRows());
} else {
    renderBarangayDistributionRows();
}
