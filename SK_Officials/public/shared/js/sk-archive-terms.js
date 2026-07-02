/**
 * SK Officials archive term filter — "Show Archive" dropdown (all archive modules).
 */
(function (global) {
    let TERMS = [];
    let ACTIVE_TERM_ID = '';
    let termsLoadPromise = null;

    function formatTermLabel(term) {
        const range = `${term.startYear}-${term.endYear}`;
        return term.isActive ? `Active Term (${range})` : `Term ${range}`;
    }

    function buildFallbackTerms() {
        const startYear = new Date().getFullYear();
        const endYear = startYear + 2;
        const id = `${startYear}-${endYear}`;

        return [{
            id,
            label: `Active Term (${id})`,
            startYear,
            endYear,
            isActive: true,
        }];
    }

    function normalizeApiTerms(apiTerms) {
        if (!Array.isArray(apiTerms) || apiTerms.length === 0) {
            return buildFallbackTerms();
        }

        return apiTerms.map((term) => {
            const startYear = Number(term.start_year ?? term.startYear);
            const endYear = Number(term.end_year ?? term.endYear);
            const id = String(term.id || `${startYear}-${endYear}`);
            const normalized = {
                id,
                startYear,
                endYear,
                isActive: Boolean(term.is_active ?? term.isActive),
            };

            return {
                ...normalized,
                label: formatTermLabel(normalized),
            };
        });
    }

    function applyTerms(apiTerms, activeTermId) {
        TERMS = normalizeApiTerms(apiTerms);
        ACTIVE_TERM_ID = activeTermId
            || TERMS.find((term) => term.isActive)?.id
            || TERMS[0]?.id
            || '';
    }

    function parseInlineTerms(select) {
        const raw = select?.dataset?.terms;
        if (!raw) {
            return null;
        }

        try {
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : null;
        } catch (error) {
            return null;
        }
    }

    function loadTerms() {
        if (termsLoadPromise) {
            return termsLoadPromise;
        }

        const select = document.getElementById('skArchiveSelect');
        const inlineTerms = parseInlineTerms(select);

        if (inlineTerms && inlineTerms.length > 0) {
            applyTerms(inlineTerms, select?.dataset?.activeTerm || '');
            termsLoadPromise = Promise.resolve();
            return termsLoadPromise;
        }

        const termsUrl = select?.dataset?.termsUrl || '/api/archive/terms';

        termsLoadPromise = fetch(termsUrl, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Failed to load archive terms.');
                }

                return response.json();
            })
            .then((payload) => {
                applyTerms(payload.terms || [], payload.active_term_id || '');
            })
            .catch(() => {
                applyTerms(buildFallbackTerms(), '');
            });

        return termsLoadPromise;
    }

    function getTerms() { return TERMS.slice(); }
    function getActiveTermId() { return ACTIVE_TERM_ID; }
    function getTermById(id) { return TERMS.find((term) => term.id === id) || null; }
    function isArchivedTerm(termId) { return termId !== ACTIVE_TERM_ID; }

    function parseToDate(value) {
        if (!value) return null;
        const d = value instanceof Date ? value : new Date(value);
        return Number.isNaN(d.getTime()) ? null : d;
    }

    function inferTermFromDate(dateInput) {
        const d = parseToDate(dateInput);
        if (!d || TERMS.length === 0) {
            return ACTIVE_TERM_ID;
        }

        const year = d.getFullYear();
        for (const term of TERMS) {
            if (year >= term.startYear && year <= term.endYear) {
                return term.id;
            }
        }

        const sorted = TERMS.slice().sort((a, b) => a.startYear - b.startYear);
        if (year < sorted[0].startYear) {
            return sorted[0].id;
        }

        return sorted[sorted.length - 1]?.id || ACTIVE_TERM_ID;
    }

    function resolveRecordTerm(record, dateKeys) {
        if (record && record.skTerm) return record.skTerm;
        const keys = dateKeys || ['_deletedTs', '_rejectedTs', 'submitted_at', 'rejected_at', 'deleted_at', 'archived_at'];
        for (const key of keys) {
            if (record && record[key]) {
                return inferTermFromDate(record[key]);
            }
        }
        return ACTIVE_TERM_ID;
    }

    function canRestoreRecord(record, dateKeys) {
        return !isArchivedTerm(resolveRecordTerm(record, dateKeys));
    }

    function filterByArchiveTerm(records, selectedTermId, dateKeys) {
        if (!selectedTermId) return records.slice();
        return records.filter((record) => resolveRecordTerm(record, dateKeys) === selectedTermId);
    }

    function mountShowArchiveFilter(onChange) {
        return loadTerms().then(() => {
            const select = document.getElementById('skArchiveSelect');
            if (!select) {
                return { getSelected: () => ACTIVE_TERM_ID };
            }

            if (!select.dataset.mounted) {
                select.dataset.mounted = '1';
                select.innerHTML = TERMS.map((term) =>
                    `<option value="${term.id}">${term.label}</option>`
                ).join('');
                select.addEventListener('change', () => {
                    if (typeof onChange === 'function') onChange(select.value);
                });
            } else {
                select.innerHTML = TERMS.map((term) =>
                    `<option value="${term.id}">${term.label}</option>`
                ).join('');
            }

            select.value = ACTIVE_TERM_ID;

            if (typeof onChange === 'function') {
                onChange(select.value);
            }

            return { getSelected: () => select.value };
        });
    }

    function escHtml(value) {
        if (value == null || value === '') return '—';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function profileField(label, value, iconClass) {
        const icon = iconClass ? `<i class="${iconClass}"></i> ` : '';
        return `<div class="profile-field"><label>${icon}${escHtml(label)}</label><p>${escHtml(value)}</p></div>`;
    }

    function profileRow(fieldsHtml) {
        return `<div class="profile-field-row">${fieldsHtml}</div>`;
    }

    function profileSection(title, iconClass, innerHtml) {
        return `<div class="profile-field-group"><div class="profile-field-group-label profile-field-group-label--bold"><i class="${iconClass}"></i> ${escHtml(title)}</div>${innerHtml}</div>`;
    }

    function profileMetaSection(title, iconClass, innerHtml) {
        return `<div class="profile-field-group record-view-meta--danger"><div class="profile-field-group-label profile-field-group-label--bold"><i class="${iconClass}"></i> ${escHtml(title)}</div>${innerHtml}</div>`;
    }

    global.SkArchive = {
        get TERMS() { return TERMS.slice(); },
        get ACTIVE_TERM_ID() { return ACTIVE_TERM_ID; },
        getTerms,
        getActiveTermId,
        getTermById,
        isArchivedTerm,
        inferTermFromDate,
        resolveRecordTerm,
        canRestoreRecord,
        filterByArchiveTerm,
        mountShowArchiveFilter,
        loadTerms,
    };

    global.SkRecordViewLayout = {
        escHtml,
        profileField,
        profileRow,
        profileSection,
        profileMetaSection,
    };
})(typeof window !== 'undefined' ? window : globalThis);
