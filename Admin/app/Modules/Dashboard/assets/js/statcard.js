const dashboardMetrics = {
    totalUsers: { value: '0', status: 'System Stable', delta: '+2.7%', statusTone: 'healthy' },
    federationAccounts: { value: '0', status: 'Within Quota', delta: '+1.2%', statusTone: 'healthy' },
    officialAccounts: { value: '0', status: 'Active Review', delta: '+3.1%', statusTone: 'warning' },
    currentActiveAccounts: { value: '0', status: 'Online Now', delta: '+5.4%', statusTone: 'healthy' },
    kabataanAccounts: { value: '0', status: 'Registered Youth', delta: '+4.8%', statusTone: 'healthy' },
    totalBarangay: { value: '0', status: 'Locations', delta: '0%', statusTone: 'healthy' },
    deletedSkFederation: { value: '0', status: 'Archived', delta: '0%', statusTone: 'critical' },
    deletedSkOfficials: { value: '0', status: 'Archived', delta: '0%', statusTone: 'critical' },
    archivedData: { value: '0', status: 'Archived', delta: '0%', statusTone: 'critical' },
    skFederationRecords: { value: '0', status: 'Archived', delta: '0%', statusTone: 'critical' },
    skOfficialsRecords: { value: '0', status: 'Archived', delta: '0%', statusTone: 'critical' },
    skOfficialsArchive: { value: '0', status: 'Archived', delta: '0%', statusTone: 'critical' },
    deletedKabataan: { value: '0', status: 'Archived', delta: '0%', statusTone: 'critical' },
    rejectedKkProfiling: { value: '0', status: 'Rejected', delta: '0%', statusTone: 'critical' },
    rejectedScholarships: { value: '0', status: 'Rejected', delta: '0%', statusTone: 'critical' },
};

window.dashboardMetrics = dashboardMetrics;
