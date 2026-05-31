const barangayDistribution = [
    { barangay: 'Aplaya', skFederationAssigned: true, skOfficialsAssigned: 12, accountCount: 48 },
    { barangay: 'Alipit', skFederationAssigned: true, skOfficialsAssigned: 12, accountCount: 45 },
    { barangay: 'Bagumbayan', skFederationAssigned: true, skOfficialsAssigned: 10, accountCount: 43 },
    { barangay: 'Bubukal', skFederationAssigned: false, skOfficialsAssigned: 8, accountCount: 31 },
    { barangay: 'Calios', skFederationAssigned: true, skOfficialsAssigned: 9, accountCount: 38 },
    { barangay: 'Duhat', skFederationAssigned: true, skOfficialsAssigned: 12, accountCount: 44 },
    { barangay: 'Gatid', skFederationAssigned: true, skOfficialsAssigned: 12, accountCount: 46 },
    { barangay: 'Labuin', skFederationAssigned: false, skOfficialsAssigned: 0, accountCount: 19 },
    { barangay: 'Malinao', skFederationAssigned: true, skOfficialsAssigned: 12, accountCount: 47 },
    { barangay: 'Poblacion 1', skFederationAssigned: true, skOfficialsAssigned: 11, accountCount: 41 },
    { barangay: 'Poblacion 2', skFederationAssigned: true, skOfficialsAssigned: 12, accountCount: 42 },
    { barangay: 'San Pablo Norte', skFederationAssigned: true, skOfficialsAssigned: 9, accountCount: 38 },
];

window.barangayDistribution = barangayDistribution;

window.dashboardConsole = function dashboardConsole() {
    return {
        barangayDistribution,

        federationBadge(row) {
            return row.skFederationAssigned ? 'pill--healthy' : 'pill--critical';
        },

        federationLabel(row) {
            return row.skFederationAssigned ? 'Assigned' : 'Missing';
        },

        overallGap(row) {
            return row.skFederationAssigned && row.skOfficialsAssigned > 0;
        },
    };
};

window.exportBarangayDistribution = function exportBarangayDistribution() {
    const headers = ['Barangay Name', 'Federation Assigned', 'Official Accounts', 'Total Accounts', 'Status'];
    const rows = barangayDistribution.map(row => [
        row.barangay,
        row.skFederationAssigned ? 'Assigned' : 'Missing',
        row.skOfficialsAssigned,
        row.accountCount,
        row.skFederationAssigned && row.skOfficialsAssigned > 0 ? 'Healthy' : 'RED FLAG'
    ]);

    let csvContent = headers.join(',') + '\n';
    rows.forEach(row => {
        csvContent += row.join(',') + '\n';
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
};
