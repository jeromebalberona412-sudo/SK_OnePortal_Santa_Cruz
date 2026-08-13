const ACCOMPLISHMENT_FIELDS = [
    'progress_percent',
    'accomplishment_status',
    'target_date',
    'completed_at',
    'submitted_at',
    'approved_at',
    'rejected_at',
];

export function preserveAccomplishmentFields(existing, incoming) {
    const preserved = { ...incoming };

    ACCOMPLISHMENT_FIELDS.forEach((field) => {
        if (existing && Object.prototype.hasOwnProperty.call(existing, field)) {
            preserved[field] = existing[field];
        }
    });

    return preserved;
}

export function formatAccomplishment(row) {
    return {
        progress_percent: row?.progress_percent ?? 0,
        accomplishment_status: row?.accomplishment_status ?? 'Not Started',
        target_date: row?.target_date ?? null,
        completed_at: row?.completed_at ?? null,
        submitted_at: row?.submitted_at ?? null,
        approved_at: row?.approved_at ?? null,
        rejected_at: row?.rejected_at ?? null,
    };
}
