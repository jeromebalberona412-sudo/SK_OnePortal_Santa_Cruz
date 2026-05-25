/**
 * SK Officials archive term filter — "Show Archive" dropdown (all archive modules).
 */
(function (global) {
    const ACTIVE_TERM_ID = '2025-2027';

    const TERMS = [
        { id: '2025-2027', label: 'Active Term (2025-2027)', startYear: 2025, endYear: 2027, isActive: true },
        { id: '2022-2025', label: 'Term 2022-2025', startYear: 2022, endYear: 2025 },
        { id: '2019-2022', label: 'Term 2019-2022', startYear: 2019, endYear: 2022 },
    ];

    function getTerms() { return TERMS.slice(); }
    function getActiveTermId() { return ACTIVE_TERM_ID; }
    function getTermById(id) { return TERMS.find(t => t.id === id) || null; }
    function isArchivedTerm(termId) { return termId !== ACTIVE_TERM_ID; }

    function parseToDate(value) {
        if (!value) return null;
        const d = value instanceof Date ? value : new Date(value);
        return Number.isNaN(d.getTime()) ? null : d;
    }

    function inferTermFromDate(dateInput) {
        const d = parseToDate(dateInput);
        if (!d) return ACTIVE_TERM_ID;
        const y = d.getFullYear();
        for (const t of TERMS) {
            if (y >= t.startYear && y <= t.endYear) return t.id;
        }
        return y < 2025 ? '2019-2022' : ACTIVE_TERM_ID;
    }

    function resolveRecordTerm(record, dateKeys) {
        if (record && record.skTerm) return record.skTerm;
        const keys = dateKeys || ['_deletedTs', '_rejectedTs', 'submitted_at', 'rejected_at', 'deleted_at'];
        for (const k of keys) {
            if (record && record[k]) return inferTermFromDate(record[k]);
        }
        return ACTIVE_TERM_ID;
    }

    function canRestoreRecord(record, dateKeys) {
        return !isArchivedTerm(resolveRecordTerm(record, dateKeys));
    }

    function filterByArchiveTerm(records, selectedTermId, dateKeys) {
        if (!selectedTermId) return records.slice();
        return records.filter(r => resolveRecordTerm(r, dateKeys) === selectedTermId);
    }

    function mountShowArchiveFilter(onChange) {
        const select = document.getElementById('skArchiveSelect');
        if (!select) return { getSelected: () => ACTIVE_TERM_ID };

        if (!select.dataset.mounted) {
            select.dataset.mounted = '1';
            select.innerHTML = TERMS.map(t =>
                `<option value="${t.id}">${t.label}</option>`
            ).join('');
            select.value = ACTIVE_TERM_ID;
            select.addEventListener('change', () => {
                if (typeof onChange === 'function') onChange(select.value);
            });
        }

        if (typeof onChange === 'function') onChange(select.value);

        return { getSelected: () => select.value };
    }

    global.SkArchive = {
        TERMS,
        ACTIVE_TERM_ID,
        getTerms,
        getActiveTermId,
        getTermById,
        isArchivedTerm,
        inferTermFromDate,
        resolveRecordTerm,
        canRestoreRecord,
        filterByArchiveTerm,
        mountShowArchiveFilter,
    };
})(typeof window !== 'undefined' ? window : globalThis);
