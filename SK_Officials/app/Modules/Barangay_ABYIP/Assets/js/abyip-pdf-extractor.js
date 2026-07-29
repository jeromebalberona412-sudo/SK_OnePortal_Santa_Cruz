// ABYIP — document totals, records (database), modals
// Create flow: Upload Word/PDF → save via API.
(function () {
'use strict';

const DEFAULT_RECORD_TITLE = 'ABYIP CY 2026';

let abyipRecords = [];
let abyipModalMode = 'create';
let recordPendingDeleteId = null;
let resubmitRecordId = null;
let pendingPdfData = null; // Store PDF data temporarily
let pendingPdfExtractedText = null; // Text extracted from PDF for program auto-detection
let pendingIsImported = false; // Track if pending record is an imported Word doc
let pendingPdfUploadFile = null;

let filterSearchText = '';
let filterYear = '';
let searchDebounceTimer = null;
let abyipSubmissionStatus = {
    can_submit: false,
    fiscal_year: new Date().getFullYear(),
    message: 'ABYIP submission is not open yet. Please wait for SK Federation to create the ABYIP schedule.',
    schedule: null,
};

const abyipCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

async function abyipApiFetch(url, options = {}) {
    const { headers: extraHeaders, body, ...rest } = options;
    const headers = {
        'X-CSRF-TOKEN': abyipCsrfToken(),
        'Accept': 'application/json',
        ...extraHeaders,
    };

    if (body && !(body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }

    const res = await fetch(url, {
        ...rest,
        headers,
        body: body && !(body instanceof FormData) ? JSON.stringify(body) : body,
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok) {
        const message = data.message || Object.values(data.errors || {}).flat()[0] || 'Request failed.';
        throw new Error(message);
    }

    return data;
}

function mapRecordFromApi(record) {
    return {
        id: record.id,
        title: record.title,
        dateCreated: record.date_created,
        status: record.status || 'pending',
        rejectionReason: record.rejection_reason || '',
        documentHtml: record.document_html || '',
        pdfData: record.pdf_data || null,
        isPdf: record.source_type === 'pdf',
        isImported: record.source_type === 'word',
        calendarYear: record.calendar_year,
    };
}

function formatStatusBadge(status) {
    const normalized = String(status || 'pending').toLowerCase();
    const label = normalized.charAt(0).toUpperCase() + normalized.slice(1);
    const className = normalized === 'approved'
        ? 'status-badge status-approved'
        : normalized === 'rejected'
          ? 'status-badge status-rejected'
          : 'status-badge status-pending';

    return '<span class="' + className + '">' + escapeHtml(label) + '</span>';
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}

function parseAmount(text) {
    if (!text) return 0;
    return parseFloat(String(text).replace(/,/g, '')) || 0;
}

function updateTotals() {
    const table = document.getElementById('abyipModalTable');
    if (!table) return;

    const rows = table.querySelectorAll('tbody tr');
    let totalMOOE = 0;
    let totalCO = 0;
    let grandTotal = 0;

    rows.forEach((row) => {
        if (row.classList.contains('total-row')) return;
        const mooeCell = row.querySelector('td:nth-child(7)');
        const coCell = row.querySelector('td:nth-child(8)');
        const totalCell = row.querySelector('td:nth-child(9)');
        if (!mooeCell || !coCell || !totalCell) return;
        if (!mooeCell.classList.contains('number')) return;

        const mooe = parseAmount(mooeCell.textContent);
        const co = parseAmount(coCell.textContent);
        const total = parseAmount(totalCell.textContent);

        totalMOOE += mooe;
        totalCO += co;
        grandTotal += total;
    });

    const totalRow = table.querySelector('.total-row');
    if (totalRow) {
        const mooeTotalEl = totalRow.querySelector('.abyip-mooe-total');
        const coTotalEl = totalRow.querySelector('.abyip-co-total');
        const grandTotalEl = totalRow.querySelector('.abyip-grand-total');
        if (mooeTotalEl) mooeTotalEl.innerHTML = '<strong>' + formatCurrency(totalMOOE) + '</strong>';
        if (coTotalEl) coTotalEl.innerHTML = '<strong>' + formatCurrency(totalCO) + '</strong>';
        if (grandTotalEl) grandTotalEl.innerHTML = '<strong>' + formatCurrency(grandTotal) + '</strong>';
    }
}

function addCalculationListeners() {
    document.addEventListener(
        'input',
        function (e) {
            if (!e.target.matches('#abyipModalTable td.number[contenteditable="true"]')) return;
            const row = e.target.closest('tr');
            if (!row || row.classList.contains('total-row')) return;

            const mooeCell = row.querySelector('td:nth-child(7)');
            const coCell = row.querySelector('td:nth-child(8)');
            const totalCell = row.querySelector('td:nth-child(9)');
            if (!mooeCell || !coCell || !totalCell) return;

            if (e.target === mooeCell || e.target === coCell) {
                const mooe = parseAmount(mooeCell.textContent);
                const co = parseAmount(coCell.textContent);
                totalCell.textContent = formatCurrency(mooe + co);
            }
            updateTotals();
        },
        true
    );
}

function addNumericValidation() {
    document.addEventListener(
        'input',
        function (e) {
            if (!e.target.matches('#abyipModalTable td.number[contenteditable="true"]')) return;
            let value = e.target.textContent.replace(/[^\d.]/g, '');
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            if (parts.length === 2 && parts[1].length > 2) {
                value = parts[0] + '.' + parts[1].substring(0, 2);
            }
            e.target.textContent = value;
        },
        true
    );
}

function formatDateDisplay(iso) {
    if (!iso) return '—';
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return '—';
    return d.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/** 12-hour clock, e.g. 3:45 PM (not 24h) */
function formatTimeCreated12(iso) {
    if (!iso) return '—';
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return '—';
    return d.toLocaleTimeString(undefined, {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s == null ? '' : String(s);
    return div.innerHTML;
}

function escapeAttr(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;');
}

function getRecordSearchHaystack(record) {
    if (!record) return '';
    const d = record.dateCreated ? new Date(record.dateCreated) : null;
    const dateStr = d && !Number.isNaN(d.getTime()) ? formatDateDisplay(record.dateCreated) : '';
    const timeStr = d && !Number.isNaN(d.getTime()) ? formatTimeCreated12(record.dateCreated) : '';
    const iso = record.dateCreated ? String(record.dateCreated) : '';
    const localeDate = d && !Number.isNaN(d.getTime()) ? d.toLocaleDateString() : '';
    const parts = [
        record.title,
        dateStr,
        timeStr,
        localeDate,
        iso,
        d && !Number.isNaN(d.getTime()) ? String(d.getMonth() + 1) : '',
        d && !Number.isNaN(d.getTime()) ? String(d.getFullYear()) : ''
    ];
    return parts
        .filter(Boolean)
        .join(' ')
        .toLowerCase();
}

function recordMatchesFilters(record) {
    // Year filter
    if (filterYear) {
        const recordYear = record.calendarYear
            ? String(record.calendarYear)
            : (record.dateCreated
                ? String(new Date(record.dateCreated).getFullYear())
                : '');
        if (recordYear !== filterYear) {
            return false;
        }
    }
    
    // Search text filter
    const q = filterSearchText.trim().toLowerCase();
    if (q) {
        const haystack = getRecordSearchHaystack(record);
        if (haystack.indexOf(q) === -1) {
            return false;
        }
    }
    
    return true;
}

function getFilteredRecords() {
    return abyipRecords.filter(recordMatchesFilters);
}

function renderScheduleRestrictionNotice() {
    const notice = document.getElementById('abyipScheduleNotice');
    const messageEl = document.getElementById('abyipScheduleNoticeMessage');
    if (!notice || !messageEl) {
        return;
    }

    if (abyipSubmissionStatus.can_submit) {
        notice.hidden = true;
        return;
    }

    const message = abyipSubmissionStatus.message
        || 'No ABYIP submission schedule has been set by SK Federation. Please contact SK Federation.';

    messageEl.textContent = message;
    notice.hidden = false;
}

function renderRecordsTable() {
    const tbody = document.getElementById('recordsTableBody');
    if (!tbody) return;

    // Update create button label based on record status
    const createBtn = document.getElementById('addAbyipBtn');
    if (createBtn) {
        const rejectedRecord = abyipRecords.find(function (record) {
            return String(record.status || '').toLowerCase() === 'rejected';
        });
        const blockingRecord = getBlockingRecordForYear();

        if (!abyipSubmissionStatus.can_submit) {
            createBtn.textContent = 'Upload ABYIP';
            createBtn.disabled = false;
            createBtn.title = abyipSubmissionStatus.message || 'ABYIP submission is not available.';
        } else if (rejectedRecord) {
            createBtn.textContent = 'Upload ABYIP';
            createBtn.disabled = false;
            createBtn.title = 'Upload a corrected ABYIP PDF for review';
        } else if (blockingRecord) {
            createBtn.textContent = 'Create New ABYIP';
            createBtn.disabled = false;
            createBtn.title = 'Only one ABYIP submission is allowed per calendar year';
        } else {
            createBtn.textContent = 'Upload ABYIP';
            createBtn.disabled = false;
            createBtn.title = 'Upload your ABYIP PDF document';
        }
    }

    renderScheduleRestrictionNotice();

    if (abyipRecords.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="5" class="abyip-records-empty">No ABYIP records yet. Upload a Word or PDF document to get started.</td></tr>';
        return;
    }

    const filtered = getFilteredRecords();
    if (filtered.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="5" class="abyip-records-empty">No records match your search.</td></tr>';
        return;
    }

    tbody.innerHTML = filtered
        .map((record) => {
            const status = String(record.status || 'pending').toLowerCase();
            const isApproved = status === 'approved';
            const isRejected = status === 'rejected';
            const rejectionNote = isRejected && record.rejectionReason
                ? '<div class="abyip-rejection-note">' + escapeHtml(record.rejectionReason) + '</div>'
                : '';

            return (
                '<tr data-record-id="' +
                record.id +
                '">' +
                '<td class="abyip-records-title">' +
                escapeHtml(formatRecordTitle(record)) +
                rejectionNote +
                '</td>' +
                '<td class="abyip-records-date">' +
                formatDateDisplay(record.dateCreated) +
                '</td>' +
                '<td class="abyip-records-time">' +
                formatTimeCreated12(record.dateCreated) +
                '</td>' +
                '<td class="abyip-records-status">' +
                formatStatusBadge(record.status) +
                '</td>' +
                '<td class="abyip-records-actions">' +
                '<div class="action-buttons-cell">' +
                '<button type="button" class="btn-action-view" data-action="view" data-id="' +
                record.id +
                '">View</button>' +
                (isRejected
                    ? '<button type="button" class="btn-action-resubmit" data-action="resubmit" data-id="' +
                      record.id +
                      '">Resubmit</button>'
                    : '') +
                '<button type="button" class="btn-action-delete' +
                (isApproved ? ' is-disabled' : '') +
                '" data-action="delete" data-id="' +
                record.id +
                '"' +
                (isApproved ? ' disabled title="Approved ABYIP cannot be deleted"' : '') +
                '>Delete</button>' +
                '</div></td></tr>'
            );
        })
        .join('');
}

function getDefaultDocumentHtml() {
    const tpl = document.getElementById('abyipDefaultDocumentTemplate');
    if (!tpl || !tpl.content) return '';
    const clone = tpl.content.cloneNode(true);
    const wrap = document.createElement('div');
    wrap.appendChild(clone);
    return wrap.innerHTML;
}

function setFormRootHtml(html) {
    const mount = document.getElementById('abyipModalContentMount');
    if (!mount) return;
    mount.innerHTML = html || '';
    updateTotals();
}

function setMountContentEditable(editable) {
    const mount = document.getElementById('abyipModalContentMount');
    if (!mount) return;
    mount.querySelectorAll('[contenteditable]').forEach((el) => {
        el.setAttribute('contenteditable', editable ? 'true' : 'false');
        if (!editable) el.classList.add('abyip-readonly-cell');
        else el.classList.remove('abyip-readonly-cell');
    });
}

function setMainModalFooterMode(mode) {
    const footer = document.getElementById('abyipModalFooter');
    const printBtn = document.getElementById('abyipModalPrint');
    const saveBtn = document.getElementById('abyipModalSave');
    const cancelBtn = document.getElementById('abyipModalCancel');

    if (mode === 'view') {
        footer?.classList.add('abyip-modal-footer-view');
        footer?.classList.remove('abyip-modal-footer-import', 'abyip-modal-footer-pdf-view');
        if (printBtn) printBtn.style.display = 'inline-block';
        if (saveBtn) saveBtn.style.display = 'none';
        if (cancelBtn) cancelBtn.style.display = 'none';
    } else if (mode === 'import') {
        footer?.classList.add('abyip-modal-footer-import');
        footer?.classList.remove('abyip-modal-footer-view', 'abyip-modal-footer-pdf-view');
        if (printBtn) printBtn.style.display = 'none';
        if (saveBtn) {
            saveBtn.style.display = 'inline-block';
            saveBtn.textContent = 'Save Imported ABYIP';
        }
        if (cancelBtn) cancelBtn.style.display = 'inline-block';
    } else if (mode === 'pdf-view') {
        footer?.classList.add('abyip-modal-footer-pdf-view');
        footer?.classList.remove('abyip-modal-footer-view', 'abyip-modal-footer-import');
        if (printBtn) printBtn.style.display = 'none';
        if (saveBtn) {
            saveBtn.style.display = 'inline-block';
            saveBtn.textContent = 'Save PDF';
        }
        if (cancelBtn) cancelBtn.style.display = 'inline-block';
    } else {
        footer?.classList.remove('abyip-modal-footer-view', 'abyip-modal-footer-import', 'abyip-modal-footer-pdf-view');
        if (printBtn) printBtn.style.display = 'none';
        if (saveBtn) {
            saveBtn.style.display = 'inline-block';
            saveBtn.textContent = 'Save ABYIP';
        }
        if (cancelBtn) cancelBtn.style.display = 'inline-block';
    }
}

async function openAbyipModal(mode, recordId) {
    abyipModalMode = mode;

    const modal = document.getElementById('abyipModal');
    const titleEl = document.getElementById('abyipModalTitle');
    const header = document.getElementById('abyipModalHeader');

    if (!modal || !titleEl) return;

    header.classList.remove('edit-mode', 'view-mode');
    if (mode === 'view') {
        header.classList.add('view-mode');
        titleEl.textContent = 'View Annual Barangay Youth Investment Program (ABYIP)';
    } else {
        titleEl.textContent = 'Create Annual Barangay Youth Investment Program (ABYIP)';
    }

    if (mode === 'create') {
        setFormRootHtml(getDefaultDocumentHtml());
        setMainModalFooterMode('edit');
        setMountContentEditable(true);
    } else {
        let record = abyipRecords.find((r) => r.id === recordId);
        if (!record) return;

        try {
            const response = await abyipApiFetch(`/api/abyip/${recordId}`);
            record = mapRecordFromApi(response.data);
        } catch (error) {
            showNotification(error.message || 'Failed to load ABYIP record.', 'error');
            return;
        }

        if (record.isPdf && record.pdfData) {
            renderStoredPdf(record.pdfData, record.title);
            setMainModalFooterMode('view');
        } else {
            const html = record.documentHtml && record.documentHtml.length > 0 ? record.documentHtml : getDefaultDocumentHtml();
            setFormRootHtml(html);
            setMainModalFooterMode('view');
            setMountContentEditable(false);
        }
    }

    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    requestAnimationFrame(() => updateTotals());
}

function closeAbyipModal() {
    const modal = document.getElementById('abyipModal');
    if (modal) {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
    }
    document.body.style.overflow = '';
    resubmitRecordId = null;
    setMainModalFooterMode('edit');
}

async function saveAbyip() {
    if (resubmitRecordId) {
        return saveResubmitAbyip();
    }

    if (abyipModalMode !== 'create' && abyipModalMode !== 'import' && abyipModalMode !== 'pdf-view') {
        return;
    }

    const isPdf = abyipModalMode === 'pdf-view';

    if (isPdf && !pendingPdfData) {
        showNotification('No PDF data to save.', 'error');
        return;
    }

    if (isPdf && (!pendingPdfExtractedText || !String(pendingPdfExtractedText).trim())) {
        showNotification('Could not read program data from this PDF. Please re-upload the file.', 'error');
        return;
    }

    let documentHtml = null;
    if (!isPdf) {
        const mount = document.getElementById('abyipModalContentMount');
        const modalTable = document.getElementById('abyipModalTable');
        const tbody = modalTable?.querySelector('tbody');
        if (tbody) normalizeImportedTableRows(tbody);
        documentHtml = mount ? mount.innerHTML : '';
        if (!documentHtml) {
            showNotification('Document content is required.', 'error');
            return;
        }
    }

    const calendarYear = abyipSubmissionStatus.fiscal_year || new Date().getFullYear();
    const saveBtn = document.getElementById('abyipModalSave');
    if (saveBtn) saveBtn.disabled = true;

    if (typeof window.showLoading === 'function') {
        window.showLoading('Saving ABYIP');
    }

    try {
        await abyipApiFetch('/api/abyip', {
            method: 'POST',
            body: {
                title: DEFAULT_RECORD_TITLE,
                source_type: isPdf ? 'pdf' : 'word',
                calendar_year: calendarYear,
                document_html: isPdf ? null : documentHtml,
                pdf_data: isPdf ? pendingPdfData : null,
                extracted_text: isPdf ? pendingPdfExtractedText : null,
            },
        });

        pendingPdfData = null;
        pendingPdfExtractedText = null;
        pendingIsImported = false;
        closeAbyipModal();
        await loadRecords();
        renderRecordsTable();
        showNotification('ABYIP record saved.', 'success');
    } catch (error) {
        showNotification(error.message || 'Failed to save ABYIP record.', 'error');
    } finally {
        if (typeof window.hideLoading === 'function') {
            window.hideLoading();
        }
        if (saveBtn) saveBtn.disabled = false;
    }
}

async function saveResubmitAbyip() {
    if (!resubmitRecordId) return;

    if (!pendingPdfData) {
        showNotification('No PDF data to resubmit.', 'error');
        return;
    }

    if (!pendingPdfExtractedText || !String(pendingPdfExtractedText).trim()) {
        showNotification('Could not read program data from this PDF. Please re-upload the file.', 'error');
        return;
    }

    const saveBtn = document.getElementById('abyipModalSave');
    if (saveBtn) saveBtn.disabled = true;

    if (typeof window.showLoading === 'function') {
        window.showLoading('Resubmitting ABYIP');
    }

    try {
        await abyipApiFetch('/api/abyip/' + resubmitRecordId + '/resubmit', {
            method: 'POST',
            body: {
                title: DEFAULT_RECORD_TITLE,
                source_type: 'pdf',
                pdf_data: pendingPdfData,
                extracted_text: pendingPdfExtractedText,
            },
        });

        pendingPdfData = null;
        pendingPdfExtractedText = null;
        resubmitRecordId = null;
        closeAbyipModal();
        await loadRecords();
        renderRecordsTable();
        showNotification('ABYIP resubmitted for federation review.', 'success');
    } catch (error) {
        showNotification(error.message || 'Failed to resubmit ABYIP record.', 'error');
    } finally {
        if (typeof window.hideLoading === 'function') {
            window.hideLoading();
        }
        if (saveBtn) saveBtn.disabled = false;
    }
}

function openResubmitFlow(recordId) {
    if (!abyipSubmissionStatus.can_submit) {
        showNotification(abyipSubmissionStatus.message || 'ABYIP submission is not available.', 'error');
        return;
    }

    const record = abyipRecords.find(function (r) {
        return String(r.id) === String(recordId);
    });

    if (!record || String(record.status || '').toLowerCase() !== 'rejected') {
        showNotification('Only rejected ABYIP records can be resubmitted.', 'error');
        return;
    }

    resubmitRecordId = recordId;
    openImportPdfFilePicker();
}

function printAbyipDocument() {
    // Hide PDF notice message before printing
    const pdfNotice = document.querySelector('.pdf-viewer-notice');
    if (pdfNotice) {
        pdfNotice.style.display = 'none';
    }
    
    document.body.classList.add('abyip-printing');
    window.print();
    setTimeout(function () {
        document.body.classList.remove('abyip-printing');
        
        // Restore PDF notice message after printing
        if (pdfNotice) {
            pdfNotice.style.display = '';
        }
    }, 500);
}

function formatRecordTitle(record) {
    const title = String(record.title || 'ABYIP').trim();
    const year = record.calendarYear;

    if (!year) {
        return title;
    }

    if (/\bCY\s*\d{4}\b/i.test(title)) {
        return title;
    }

    return `${title} CY ${year}`;
}

function populateYearFilter(years) {
    const yearFilter = document.getElementById('abyipYearFilter');
    if (!yearFilter) {
        return;
    }

    const currentVal = yearFilter.value;
    yearFilter.innerHTML = '<option value="">All Years</option>';

    (years || []).forEach(function (year) {
        const opt = document.createElement('option');
        opt.value = String(year);
        opt.textContent = 'CY ' + year;
        if (String(year) === currentVal) {
            opt.selected = true;
        }
        yearFilter.appendChild(opt);
    });
}

async function loadRecords() {
    try {
        const response = await abyipApiFetch('/api/abyip');
        abyipRecords = (response.data || []).map(mapRecordFromApi);
        populateYearFilter(response.years || []);
        if (response.submission) {
            abyipSubmissionStatus = {
                can_submit: Boolean(response.submission.can_submit),
                fiscal_year: Number(response.submission.fiscal_year) || new Date().getFullYear(),
                message: response.submission.message || null,
                schedule: response.submission.schedule || null,
            };
        }
    } catch (e) {
        abyipRecords = [];
        populateYearFilter([]);
    }
}

function showNotification(message, type) {
    const existing = document.querySelector('.abyip-toast');
    if (existing) existing.remove();

    const n = document.createElement('div');
    n.className = 'abyip-toast abyip-toast-' + (type || 'info') + ' abyip-toast-show';

    const icon = type === 'error' ? '✕' : '✓';
    n.innerHTML = '<span class="abyip-toast-icon">' + icon + '</span> ' + escapeHtml(message);

    document.body.appendChild(n);

    setTimeout(() => {
        n.classList.remove('abyip-toast-show');
        n.classList.add('abyip-toast-hide');
        setTimeout(() => n.remove(), 300);
    }, 3200);
}

function openDeleteModal(id) {
    const record = abyipRecords.find(function (r) { return String(r.id) === String(id); });
    if (record && String(record.status || '').toLowerCase() === 'approved') {
        showNotification('Approved ABYIP records cannot be deleted.', 'error');
        return;
    }
    recordPendingDeleteId = id;
    const m = document.getElementById('deleteConfirmModal');
    if (m) {
        m.classList.add('active');
        m.setAttribute('aria-hidden', 'false');
    }
}

function closeDeleteModal() {
    recordPendingDeleteId = null;
    const m = document.getElementById('deleteConfirmModal');
    if (m) {
        m.classList.remove('active');
        m.setAttribute('aria-hidden', 'true');
    }
}

function exportToWord() {
    const modalContent = document.getElementById('abyipModalContentMount');
    if (!modalContent) return;

    // Check if this is a PDF document
    const isPdfDocument = modalContent.querySelector('.pdf-viewer-container') !== null;
    
    if (isPdfDocument) {
        // Export PDF as images in Word document
        exportPdfToWord();
        return;
    }

    // Create a temporary container for the content
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = modalContent.innerHTML;

    // Apply centering to header elements
    const headerElements = tempDiv.querySelectorAll('.abyip-doc-header, .abyip-doc-header-text, .abyip-doc-line, .abyip-doc-barangay, .abyip-doc-sk, .abyip-doc-title-block, .abyip-doc-h1, .abyip-doc-h2');
    headerElements.forEach(el => {
        el.style.textAlign = 'center';
    });

    // Apply centering to footer elements
    const footerElements = tempDiv.querySelectorAll('.abyip-doc-footer, .signature-blocks, .signature-left, .signature-right');
    footerElements.forEach(el => {
        el.style.textAlign = 'center';
    });

    // Remove interactive elements
    const editableElements = tempDiv.querySelectorAll('[contenteditable="true"]');
    editableElements.forEach(el => {
        el.removeAttribute('contenteditable');
        el.style.backgroundColor = 'transparent';
        el.style.boxShadow = 'none';
    });

    // Create Word document content
    const wordContent = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="utf-8">
            <title>ABYIP Document</title>
            <!--[if gte mso 9]>
            <xml>
                <w:WordDocument>
                    <w:View>Print</w:View>
                    <w:Zoom>90</w:Zoom>
                </w:WordDocument>
            </xml>
            <![endif]-->
            <style>
                @page {
                    margin: 0.5in;
                    size: A4;
                }
                body {
                    font-family: 'Times New Roman', serif;
                    font-size: 9pt;
                    line-height: 1.1;
                    margin: 0;
                    padding: 0;
                    display: flex;
                    justify-content: center;
                }
                .table-wrapper {
                    margin: 0 auto;
                    width: auto;
                    display: flex;
                    justify-content: center;
                }
                table {
                    border-collapse: collapse;
                    width: auto;
                    margin: 5px auto;
                    font-size: 8pt;
                }
                th, td {
                    border: 1px solid #000;
                    padding: 4px;
                    text-align: left;
                    vertical-align: top;
                    font-size: 8pt;
                }
                th {
                    background-color: #f0f0f0;
                    font-weight: bold;
                    text-align: center;
                }
                .number {
                    text-align: right !important;
                    font-family: 'Courier New', monospace;
                }
                .total-row td {
                    background-color: #d0d0d0 !important;
                    font-weight: bold;
                }
                .section-header td {
                    background-color: #d0d0d0 !important;
                    font-weight: bold;
                    text-align: center;
                }
                .subsection-header td {
                    background-color: #e8e8e8 !important;
                    font-weight: bold;
                    text-align: center;
                }
                .category-header td {
                    background-color: #f5f5f5 !important;
                    font-weight: bold;
                }
                h1, h2 {
                    text-align: center;
                    font-weight: bold;
                }
                h1 {
                    font-size: 12pt;
                }
                h2 {
                    font-size: 10pt;
                }
                .document-footer {
                    margin-top: 20px;
                }
                .signature-blocks {
                    display: flex;
                    justify-content: space-around;
                    margin-top: 30px;
                    text-align: center;
                }
                .signature-left, .signature-right {
                    width: 45%;
                    text-align: center;
                }
                .signature-name {
                    font-weight: bold;
                    margin: 20px 0 5px 0;
                    border-bottom: 1px solid #000;
                    padding-bottom: 2px;
                    min-height: 20px;
                    display: inline-block;
                    width: 200px;
                }
                .abyip-doc-header {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .abyip-doc-header-text {
                    text-align: center;
                }
                .abyip-doc-line {
                    text-align: center;
                    margin: 2px 0;
                }
                .abyip-doc-barangay,
                .abyip-doc-sk {
                    text-align: center;
                    margin: 5px 0;
                    font-weight: bold;
                }
                .abyip-doc-title-block {
                    text-align: center;
                    margin: 15px 0;
                }
                .abyip-doc-h1,
                .abyip-doc-h2 {
                    text-align: center;
                    font-weight: bold;
                    margin: 5px 0;
                }
                .abyip-doc-footer {
                    text-align: center;
                    margin-top: 40px;
                }
            </style>
        </head>
        <body>
            ${tempDiv.innerHTML}
        </body>
        </html>
    `;

    // Create blob and download
    const blob = new Blob([wordContent], { type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `ABYIP_${new Date().toISOString().split('T')[0]}.doc`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);

    showNotification('Document exported to MS Word successfully!', 'success');
}

function exportPdfToWord() {
    // Get all PDF canvas elements
    const canvases = document.querySelectorAll('.pdf-page-canvas');
    
    if (canvases.length === 0) {
        showNotification('No PDF pages found to export.', 'error');
        return;
    }
    
    // Convert canvases to images
    let imagesHtml = '';
    canvases.forEach((canvas, index) => {
        const imageData = canvas.toDataURL('image/png');
        imagesHtml += `
            <div style="page-break-after: always; text-align: center; margin-bottom: 20px;">
                <img src="${imageData}" style="max-width: 100%; height: auto;" />
            </div>
        `;
    });
    
    // Create Word document with images
    const wordContent = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="utf-8">
            <title>ABYIP PDF Document</title>
            <!--[if gte mso 9]>
            <xml>
                <w:WordDocument>
                    <w:View>Print</w:View>
                    <w:Zoom>90</w:Zoom>
                </w:WordDocument>
            </xml>
            <![endif]-->
            <style>
                @page {
                    margin: 0.5in;
                    size: A4;
                }
                body {
                    margin: 0;
                    padding: 0;
                }
                img {
                    max-width: 100%;
                    height: auto;
                    display: block;
                    margin: 0 auto;
                }
            </style>
        </head>
        <body>
            ${imagesHtml}
        </body>
        </html>
    `;
    
    // Create blob and download
    const blob = new Blob([wordContent], { type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `ABYIP_PDF_${new Date().toISOString().split('T')[0]}.doc`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    
    showNotification('PDF exported to MS Word successfully!', 'success');
}

async function confirmDeleteRecord() {
    if (recordPendingDeleteId == null) return;

    const confirmBtn = document.getElementById('deleteConfirmBtn');
    const defaultHtml = confirmBtn ? confirmBtn.innerHTML : 'Delete';

    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="abyip-delete-spinner"></span> Deleting...';
    }

    try {
        await abyipApiFetch(`/api/abyip/${recordPendingDeleteId}`, { method: 'DELETE' });
        await loadRecords();
        renderRecordsTable();
        closeDeleteModal();
        showNotification('ABYIP deleted successfully.', 'success');
    } catch (error) {
        closeDeleteModal();
        showNotification(error.message || 'Failed to delete ABYIP record.', 'error');
    } finally {
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = defaultHtml;
        }
    }
}

function setUploadModalPreviewMode(isPreviewing) {
    const modal = document.getElementById('createOptionsModal');
    const modalBox = document.getElementById('abyipUploadModalBox');
    const uploadInner = document.getElementById('abyipUploadInner');
    const uploadHeader = document.getElementById('abyipUploadModalHeader');
    const uploadFooter = document.getElementById('abyipUploadFooter');
    const previewStage = document.getElementById('abyipPdfPreviewStage');
    const continueBtn = document.getElementById('abyipPdfUploadContinueBtn');

    if (modal) {
        modal.classList.toggle('is-immersive-preview', isPreviewing);
    }
    if (modalBox) {
        modalBox.classList.toggle('is-immersive-preview', isPreviewing);
    }
    if (uploadInner) {
        uploadInner.hidden = isPreviewing;
    }
    if (uploadHeader) {
        uploadHeader.hidden = isPreviewing;
    }
    if (uploadFooter) {
        uploadFooter.hidden = isPreviewing;
    }
    if (previewStage) {
        previewStage.hidden = !isPreviewing;
    }
    if (continueBtn && isPreviewing) {
        continueBtn.disabled = !pendingPdfUploadFile;
    }
}

function resetPdfUploadModal() {
    pendingPdfUploadFile = null;
    const fileInput = document.getElementById('pdfFileInput');
    if (fileInput) {
        fileInput.value = '';
    }

    const pagesContainer = document.getElementById('abyipUploadPdfPages');
    const continueBtn = document.getElementById('abyipPdfUploadContinueBtn');

    setUploadModalPreviewMode(false);

    if (pagesContainer) {
        pagesContainer.innerHTML = '';
    }
    if (continueBtn) {
        continueBtn.disabled = true;
    }
}

function getBlockingRecordForYear() {
    const calendarYear = Number(abyipSubmissionStatus.fiscal_year) || new Date().getFullYear();

    return abyipRecords.find(function (record) {
        const recordYear = Number(record.fiscal_year || record.calendar_year) || calendarYear;
        if (recordYear !== calendarYear) {
            return false;
        }

        const status = String(record.status || '').toLowerCase();
        return status === 'pending' || status === 'approved';
    }) || null;
}

function notifyExistingAbyipBlocked() {
    const calendarYear = Number(abyipSubmissionStatus.fiscal_year) || new Date().getFullYear();
    const existing = getBlockingRecordForYear();
    const status = existing ? String(existing.status || 'pending').toLowerCase() : 'pending';

    if (status === 'rejected') {
        showNotification(
            'Your ABYIP submission for CY ' + calendarYear + ' was rejected. Please use Upload ABYIP to submit a corrected file.',
            'error',
        );
        return;
    }

    showNotification(
        'Only one ABYIP submission is allowed per calendar year (CY ' + calendarYear + '). '
        + 'Delete the existing record or wait for the current submission to be reviewed before creating another.',
        'error',
    );
}

function openCreateOptionsModal() {
    if (!abyipSubmissionStatus.can_submit) {
        showNotification(abyipSubmissionStatus.message || 'ABYIP submission is not available.', 'error');
        return;
    }

    const rejectedRecord = abyipRecords.find(function (record) {
        return String(record.status || '').toLowerCase() === 'rejected';
    });

    if (rejectedRecord) {
        openResubmitFlow(rejectedRecord.id);
        return;
    }

    if (getBlockingRecordForYear()) {
        notifyExistingAbyipBlocked();
        return;
    }

    resetPdfUploadModal();
    const m = document.getElementById('createOptionsModal');
    if (m) {
        m.classList.add('active');
        m.setAttribute('aria-hidden', 'false');
    }
}

function closeCreateOptionsModal() {
    const m = document.getElementById('createOptionsModal');
    if (m) {
        m.classList.remove('active');
        m.setAttribute('aria-hidden', 'true');
    }
    resetPdfUploadModal();
}

function openImportPdfFilePicker() {
    document.getElementById('pdfFileInput')?.click();
}

function renderUploadModalPdfPreview(file) {
    const pagesContainer = document.getElementById('abyipUploadPdfPages');
    if (!pagesContainer || typeof pdfjsLib === 'undefined') {
        return;
    }

    setUploadModalPreviewMode(true);
    pagesContainer.innerHTML = '<p class="abyip-pdf-loading">Loading PDF preview...</p>';

    const reader = new FileReader();
    reader.onload = function (event) {
        const loadingTask = pdfjsLib.getDocument({ data: event.target.result });
        loadingTask.promise.then(function (pdf) {
            pagesContainer.innerHTML = '';
            renderAllPdfPages(pdf, pagesContainer);
        }).catch(function () {
            pagesContainer.innerHTML = '<div class="pdf-error">Unable to preview this PDF.</div>';
        });
    };
    reader.onerror = function () {
        pagesContainer.innerHTML = '<div class="pdf-error">Unable to read this PDF.</div>';
    };
    reader.readAsArrayBuffer(file);
}

function handlePdfFileChosen(event) {
    const fileInput = event.target;
    const file = fileInput?.files?.[0];
    if (!file) {
        return;
    }

    const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
    if (!isPdf) {
        showNotification('Please select a PDF file.', 'error');
        fileInput.value = '';
        return;
    }

    if (file.size > 15 * 1024 * 1024) {
        showNotification('PDF must be 15MB or smaller.', 'error');
        fileInput.value = '';
        return;
    }

    pendingPdfUploadFile = file;

    const continueBtn = document.getElementById('abyipPdfUploadContinueBtn');

    setUploadModalPreviewMode(true);

    if (continueBtn) {
        continueBtn.disabled = true;
    }

    renderUploadModalPdfPreview(file);

    if (continueBtn) {
        continueBtn.disabled = false;
    }
}

function continuePdfUpload() {
    if (!pendingPdfUploadFile) {
        return;
    }

    const file = pendingPdfUploadFile;
    const continueBtn = document.getElementById('abyipPdfUploadContinueBtn');
    if (continueBtn) {
        continueBtn.disabled = true;
    }

    if (typeof window.showLoading === 'function') {
        window.showLoading(resubmitRecordId ? 'Resubmitting ABYIP' : 'Saving ABYIP');
    }

    prepareAndUploadPdfFile(file)
        .then(function () {
            const wasResubmit = Boolean(resubmitRecordId);
            resubmitRecordId = null;
            closeCreateOptionsModal();
            return loadRecords().then(function () {
                renderRecordsTable();
                showNotification(
                    wasResubmit ? 'ABYIP resubmitted for federation review.' : 'ABYIP record saved.',
                    'success'
                );
            });
        })
        .catch(function (error) {
            showNotification(error.message || 'Failed to save ABYIP record.', 'error');
        })
        .finally(function () {
            if (typeof window.hideLoading === 'function') {
                window.hideLoading();
            }
            if (continueBtn) {
                continueBtn.disabled = false;
            }
        });
}

function prepareAndUploadPdfFile(file) {
    return new Promise(function (resolve, reject) {
        if (typeof pdfjsLib === 'undefined') {
            reject(new Error('PDF library is not available.'));
            return;
        }

        const reader = new FileReader();

        reader.onload = function (event) {
            const arrayBuffer = event.target.result;
            const base64String = btoa(
                new Uint8Array(arrayBuffer).reduce(function (data, byte) {
                    return data + String.fromCharCode(byte);
                }, '')
            );

            pdfjsLib.getDocument({ data: arrayBuffer }).promise
                .then(function (pdf) {
                    return extractPdfTextForPrograms(pdf).then(function (extractedText) {
                        if (!extractedText || !String(extractedText).trim()) {
                            throw new Error('Could not read program data from this PDF. Please re-upload the file.');
                        }

                        if (resubmitRecordId) {
                            return abyipApiFetch('/api/abyip/' + resubmitRecordId + '/resubmit', {
                                method: 'POST',
                                body: {
                                    title: DEFAULT_RECORD_TITLE,
                                    source_type: 'pdf',
                                    pdf_data: base64String,
                                    extracted_text: extractedText,
                                },
                            });
                        }

                        const calendarYear = abyipSubmissionStatus.fiscal_year || new Date().getFullYear();
                        return abyipApiFetch('/api/abyip', {
                            method: 'POST',
                            body: {
                                title: DEFAULT_RECORD_TITLE,
                                source_type: 'pdf',
                                calendar_year: calendarYear,
                                document_html: null,
                                pdf_data: base64String,
                                extracted_text: extractedText,
                            },
                        });
                    });
                })
                .then(function () {
                    pendingPdfData = null;
                    pendingPdfExtractedText = null;
                    pendingPdfUploadFile = null;
                    resolve();
                })
                .catch(function (error) {
                    reject(error instanceof Error ? error : new Error('Failed to upload ABYIP PDF.'));
                });
        };

        reader.onerror = function () {
            reject(new Error('Error reading file. Please try again.'));
        };

        reader.readAsArrayBuffer(file);
    });
}

function openImportWordFilePicker() {
    closeCreateOptionsModal();
    const fileInput = document.getElementById('wordFileInput');
    if (fileInput) {
        fileInput.click();
    }
}

function handleWordImport(event) {
    const fileInput = event.target;
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        return;
    }

    const file = fileInput.files[0];
    const reader = new FileReader();

    reader.onload = function(e) {
        try {
            const content = e.target.result;
            // Parse HTML content from Word document
            const parser = new DOMParser();
            const doc = parser.parseFromString(content, 'text/html');
            
            // Extract table content
            const tables = doc.querySelectorAll('table');
            if (tables.length === 0) {
                showNotification('No table found in the Word document.', 'error');
                fileInput.value = ''; // Reset file input
                return;
            }

            // Use the first table found
            const importedTable = tables[0];
            
            // Open ABYIP modal in preview mode
            openAbyipModalWithImport(importedTable);
            
            // Reset file input
            fileInput.value = '';
            
        } catch (error) {
            console.error('Import error:', error);
            showNotification('Error importing Word document. Please try again.', 'error');
            fileInput.value = ''; // Reset file input
        }
    };

    reader.onerror = function() {
        showNotification('Error reading file. Please try again.', 'error');
        fileInput.value = ''; // Reset file input
    };

    reader.readAsText(file);
}

function normalizeImportedTableRows(tbody) {
    if (!tbody) return;

    const categoryPattern = /\b[A-J]\.\s|Equitable Access to Quality Education|Environmental Protection|Disaster Risk Reduction|Youth Employment and Livelihood|^Health$|Anti-Drug and Peace and Order|Gender Sensitivity|Feeding Program for KK Members|Sports Development|Other Programs/i;

    tbody.querySelectorAll('tr').forEach((row) => {
        const text = (row.textContent || '').trim();
        const upper = text.toUpperCase();

        row.classList.remove('section-header', 'subsection-header', 'category-header', 'total-row');

        if (/^TOTAL\b/.test(upper) || upper.includes('TOTAL EXPENDITURE')) {
            row.classList.add('total-row');
        } else if (upper.includes('SK YOUTH DEVELOPMENT')) {
            row.classList.add('subsection-header');
        } else if (upper.includes('EXPENDITURE') || upper.includes('RECEIPTS')) {
            row.classList.add('section-header');
        } else if (categoryPattern.test(text)) {
            const cells = row.querySelectorAll('td');
            if (cells.length <= 3 || cells[1]?.getAttribute('colspan')) {
                row.classList.add('category-header');
            }
        }

        row.querySelectorAll('td').forEach((cell, index) => {
            if (index >= 6 && index <= 8) {
                cell.classList.add('number');
            }
        });
    });
}

function openAbyipModalWithImport(importedTable) {
    abyipModalMode = 'import';

    const modal = document.getElementById('abyipModal');
    const titleEl = document.getElementById('abyipModalTitle');
    const header = document.getElementById('abyipModalHeader');

    if (!modal || !titleEl) return;

    header.classList.remove('edit-mode', 'view-mode');
    header.classList.add('import-mode');
    titleEl.textContent = 'Preview Imported ABYIP Document';

    // Load default template first
    setFormRootHtml(getDefaultDocumentHtml());
    
    // Replace table content with imported data
    setTimeout(() => {
        const modalTable = document.getElementById('abyipModalTable');
        if (modalTable && importedTable) {
            const tbody = modalTable.querySelector('tbody');
            const importedTbody = importedTable.querySelector('tbody');
            
            if (tbody && importedTbody) {
                tbody.innerHTML = importedTbody.innerHTML;
                normalizeImportedTableRows(tbody);

                tbody.querySelectorAll('td').forEach(cell => {
                    if (!cell.hasAttribute('contenteditable')) {
                        cell.setAttribute('contenteditable', 'true');
                    }
                });
                
                updateTotals();
            }
        }
        
        // Show import preview footer
        setMainModalFooterMode('import');
        setMountContentEditable(true);
        
        showNotification('Word document imported successfully! You can now edit the content.', 'success');
    }, 100);

    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    requestAnimationFrame(() => updateTotals());
}

const ABYIP_COLUMN_DEFAULTS = {
    ppas: [0, 0.20],
    description: [0.20, 0.34],
    expected: [0.34, 0.42],
    performance: [0.42, 0.50],
    period: [0.50, 0.62],
    mooe: [0.62, 0.70],
    co: [0.70, 0.74],
    total: [0.74, 0.82],
    person: [0.82, 1.05],
};

let abyipColumnBounds = null;

function resetAbyipColumnBounds() {
    abyipColumnBounds = null;
}

function midpoint(left, right) {
    return (left + right) / 2;
}

function detectAbyipColumnBounds(pageRows) {
    if (abyipColumnBounds) {
        return abyipColumnBounds;
    }

    for (let i = 0; i < pageRows.length; i++) {
        const entry = pageRows[i];
        const width = entry.width;
        const parts = entry.row.parts;
        const markers = {};

        parts.forEach(function (part) {
            const ratio = part.x / width;
            const text = String(part.text || '').trim().toLowerCase();

            if (text === 'mooe') {
                markers.mooe = ratio;
            } else if (text === 'co') {
                markers.co = ratio;
            } else if (text === 'total') {
                markers.total = ratio;
            } else if (/^performance$/i.test(text) || text.indexOf('indicator') !== -1) {
                markers.performance = markers.performance === undefined ? ratio : Math.min(markers.performance, ratio);
            } else if (text.indexOf('period') !== -1 || text.indexOf('implementation') !== -1) {
                markers.period = markers.period === undefined ? ratio : Math.min(markers.period, ratio);
            } else if (text.indexOf('person') !== -1 || text.indexOf('responsible') !== -1) {
                markers.person = markers.person === undefined ? ratio : Math.min(markers.person, ratio);
            } else if (text === 'description') {
                markers.description = ratio;
            } else if (text.indexOf('expected') !== -1) {
                markers.expected = ratio;
            } else if (/^ppas$/i.test(text) || text.indexOf('programs') !== -1) {
                markers.ppas = markers.ppas === undefined ? ratio : Math.min(markers.ppas, ratio);
            }
        });

        if (markers.mooe === undefined || markers.total === undefined) {
            continue;
        }

        const co = markers.co !== undefined ? markers.co : markers.mooe + 0.04;
        const person = markers.person !== undefined ? markers.person : 0.84;
        const performance = markers.performance !== undefined ? markers.performance : 0.44;
        const period = markers.period !== undefined ? markers.period : 0.54;
        const expected = markers.expected !== undefined ? markers.expected : 0.38;
        const description = markers.description !== undefined ? markers.description : 0.22;
        const ppas = markers.ppas !== undefined ? markers.ppas : 0.05;

        abyipColumnBounds = {
            ppas: [0, midpoint(ppas, description)],
            description: [midpoint(ppas, description), midpoint(description, expected)],
            expected: [midpoint(description, expected), midpoint(expected, performance)],
            performance: [midpoint(expected, performance), midpoint(performance, period)],
            period: [midpoint(performance, period), midpoint(period, markers.mooe)],
            mooe: [midpoint(period, markers.mooe), midpoint(markers.mooe, co)],
            co: [midpoint(markers.mooe, co), midpoint(co, markers.total)],
            total: [midpoint(co, markers.total), midpoint(markers.total, person)],
            person: [midpoint(markers.total, person), 1.05],
        };

        return abyipColumnBounds;
    }

    abyipColumnBounds = ABYIP_COLUMN_DEFAULTS;
    return abyipColumnBounds;
}

function collectColumnText(parts, width, startRatio, endRatio) {
    const start = width * startRatio;
    const end = width * endRatio;

    return parts
        .filter(function (part) { return part.x >= start && part.x < end; })
        .map(function (part) { return part.text; })
        .join(' ')
        .replace(/\s+/g, ' ')
        .trim();
}

function mergeNumericParts(parts) {
    const sorted = parts.slice().sort(function (a, b) { return a.x - b.x; });
    const merged = [];
    let buffer = '';
    let bufferX = 0;

    sorted.forEach(function (part) {
        const text = String(part.text || '').replace(/\s/g, '');
        if (!text) {
            return;
        }

        const isNumericFragment = /^[\d,.\-]+$/.test(text) || (buffer && /^[\d,.\-]+$/.test(text));
        if (isNumericFragment) {
            if (!buffer) {
                bufferX = part.x;
            }
            buffer += text;
            return;
        }

        if (buffer) {
            merged.push({ x: bufferX, text: buffer });
            buffer = '';
        }

        merged.push({ x: part.x, text: text });
    });

    if (buffer) {
        merged.push({ x: bufferX, text: buffer });
    }

    return merged;
}

function normalizeAmountToken(text) {
    let value = String(text || '').replace(/\s/g, '').replace(/,/g, '');
    if (/^[\d]+\.\d{2}$/.test(value)) {
        return value;
    }

    if (/^[\d]+$/.test(value) && value.length >= 3) {
        return value.slice(0, -2) + '.' + value.slice(-2);
    }

    return '';
}

function extractColumnAmounts(parts, width, startRatio, endRatio) {
    const start = width * startRatio;
    const end = width * endRatio;

    return mergeNumericParts(parts)
        .filter(function (part) { return part.x >= start && part.x < end; })
        .map(function (part) { return normalizeAmountToken(part.text); })
        .filter(Boolean);
}

function extractBudgetAmountsFromParts(parts, width) {
    const budgetStart = width * 0.55;

    return mergeNumericParts(parts)
        .filter(function (part) { return part.x >= budgetStart; })
        .map(function (part) { return normalizeAmountToken(part.text); })
        .filter(Boolean);
}

function assignBudgetColumns(amounts) {
    if (!amounts || !amounts.length) {
        return {
            mooe: '',
            co: '',
            total: '',
            mooeAmounts: [],
            coAmounts: [],
            totalAmounts: [],
        };
    }

    if (amounts.length >= 3) {
        const mooe = amounts[amounts.length - 3];
        const co = amounts[amounts.length - 2];
        const total = amounts[amounts.length - 1];

        return {
            mooe: mooe,
            co: co,
            total: total,
            mooeAmounts: [mooe],
            coAmounts: co ? [co] : [],
            totalAmounts: [total],
        };
    }

    if (amounts.length === 2) {
        return {
            mooe: amounts[0],
            co: '',
            total: amounts[1],
            mooeAmounts: [amounts[0]],
            coAmounts: [],
            totalAmounts: [amounts[1]],
        };
    }

    const only = amounts[amounts.length - 1];

    return {
        mooe: only,
        co: '',
        total: only,
        mooeAmounts: [only],
        coAmounts: [],
        totalAmounts: [only],
    };
}

function appendBlockField(block, key, value) {
    const text = String(value || '').trim();
    if (!text) {
        return;
    }

    if (!block[key]) {
        block[key] = text;
        return;
    }

    if (!block[key].includes(text)) {
        block[key] += '\n' + text;
    }
}

function appendBlockAmounts(block, key, amounts) {
    if (!amounts || !amounts.length) {
        return;
    }

    const line = amounts.join('\n');
    if (!block[key]) {
        block[key] = line;
        return;
    }

    block[key] += '\n' + line;
}

function parseRowColumns(row, width, bounds) {
    const columns = bounds || abyipColumnBounds || ABYIP_COLUMN_DEFAULTS;
    let mooeAmounts = extractColumnAmounts(row.parts, width, columns.mooe[0], columns.mooe[1]);
    let coAmounts = extractColumnAmounts(row.parts, width, columns.co[0], columns.co[1]);
    let totalAmounts = extractColumnAmounts(row.parts, width, columns.total[0], columns.total[1]);

    let mooe = mooeAmounts[0] || '';
    let co = coAmounts[0] || '';
    let total = totalAmounts[0] || '';

    if (!mooe && !co && !total) {
        const fallback = assignBudgetColumns(extractBudgetAmountsFromParts(row.parts, width));
        mooe = fallback.mooe;
        co = fallback.co;
        total = fallback.total;
        mooeAmounts = fallback.mooeAmounts;
        coAmounts = fallback.coAmounts;
        totalAmounts = fallback.totalAmounts;
    }

    if (!total && mooe && co) {
        total = String((parseFloat(mooe) || 0) + (parseFloat(co) || 0));
        totalAmounts = [total];
    } else if (!total && mooe && !co) {
        total = mooe;
        totalAmounts = [total];
    }

    const fullLine = row.parts.map(function (part) { return part.text; }).join(' ').replace(/\s+/g, ' ').trim();
    let person = extractPersonResponsibleValue(row.parts, width, columns.person[0]);
    if (!person) {
        person = extractPersonFromFullLine(fullLine);
    }

    return {
        ppas: collectColumnText(row.parts, width, columns.ppas[0], columns.ppas[1]),
        description: collectColumnText(row.parts, width, columns.description[0], columns.description[1]),
        expected: collectColumnText(row.parts, width, columns.expected[0], columns.expected[1]),
        performance: collectColumnText(row.parts, width, columns.performance[0], columns.performance[1]),
        period: collectColumnText(row.parts, width, columns.period[0], columns.period[1]),
        mooeAmounts: mooeAmounts,
        coAmounts: coAmounts,
        totalAmounts: total ? [total] : totalAmounts,
        mooe: mooe,
        co: co,
        total: total,
        person: person,
        fullLine: fullLine,
    };
}

function mergeRowIntoBlock(block, cols) {
    appendBlockField(block, 'ppas', cols.ppas);
    appendBlockField(block, 'description', cols.description);
    appendBlockField(block, 'expected', cols.expected);
    appendBlockField(block, 'performance', cols.performance);
    appendBlockField(block, 'period', cols.period);
    appendBlockAmounts(block, 'mooe', cols.mooeAmounts);
    appendBlockAmounts(block, 'co', cols.coAmounts);
    appendBlockAmounts(block, 'total', cols.totalAmounts);

    if (cols.person) {
        block.person = cols.person;
    }
}

function isYouthProgramHeader(ppas, fullLine) {
    const source = ppas || fullLine;
    return /^([A-J])\.\s/i.test(source);
}

function extractYouthLetter(ppas, fullLine) {
    const source = ppas || fullLine;
    const match = source.match(/^([A-J])\.\s/i);
    return match ? match[1].toUpperCase() : '';
}

function startsWithBulletChar(text) {
    if (!text) {
        return false;
    }

    const first = text.charAt(0);
    return first === '-' || first === '\u2022' || first === '\u25CF' || first === '\uF0B7' || first === '\u00B7';
}

function isGeneralExpenditurePrimaryRow(ppas) {
    const text = String(ppas || '').trim();
    if (!text) {
        return false;
    }

    if (startsWithBulletChar(text)) {
        return false;
    }

    if (/^(A|B|C|D|E|F|G|H|I|J)\.\s/i.test(text)) {
        return false;
    }

    if (/^(Support|Training|Clean|Payroll|Tree|Distribution|150\s|Barangay|Livelihood|Food|Medicines|Educational)/i.test(text)) {
        return false;
    }

    return text.length <= 90;
}

function flushGeneralBlock(block, lines) {
    if (!block || !block.ppas) {
        return;
    }

    lines.push(buildStructuredTagRow('@ABYIP_ROW@', {
        PPAS: block.ppas,
        DESC: block.description || '',
        EXP: block.expected || '',
        PERF: block.performance || '',
        PERIOD: block.period || '',
        MOOE: block.mooe || '',
        CO: block.co || '',
        TOTAL: block.total || '',
        PERSON: block.person || '',
    }));
}

function flushYouthBlock(block, lines) {
    if (!block || !block.letter) {
        return;
    }

    lines.push(buildStructuredTagRow('@YOUTH_ROW@', {
        LETTER: block.letter,
        PPAS: block.ppas || '',
        DESC: block.description || '',
        EXP: block.expected || '',
        PERF: block.performance || '',
        PERIOD: block.period || '',
        MOOE: block.mooe || '',
        CO: block.co || '',
        TOTAL: block.total || '',
        PERSON: block.person || '',
    }));
}

function createEmptyBlock(letter) {
    return {
        letter: letter || '',
        ppas: '',
        description: '',
        expected: '',
        performance: '',
        period: '',
        mooe: '',
        co: '',
        total: '',
        person: '',
    };
}

function extractAmountFromText(text) {
    const source = String(text || '');

    const decimalMatches = source.match(/[\d,]+\.\d{2}/g);
    if (decimalMatches && decimalMatches.length) {
        return decimalMatches[decimalMatches.length - 1].replace(/,/g, '');
    }

    const groupedMatches = source.match(/\d{1,3}(?:,\d{3})+/g);
    if (groupedMatches && groupedMatches.length) {
        return groupedMatches[groupedMatches.length - 1].replace(/,/g, '');
    }

    return '';
}

function extractPersonColumn(parts, width, startRatio) {
    const threshold = width * (startRatio !== undefined ? startRatio : 0.82);
    const personParts = parts
        .filter(function (part) {
            return part.x >= threshold && !/^\d[\d,.-]*$/.test(part.text);
        })
        .map(function (part) { return part.text; });

    if (personParts.length) {
        return personParts.join(' ').replace(/\s+/g, ' ').trim();
    }

    return collectColumnText(parts, width, startRatio !== undefined ? startRatio : 0.82, 1.05);
}

function extractPersonFromFullLine(fullLine) {
    const source = String(fullLine || '').replace(/\s+/g, ' ').trim();
    if (!source) {
        return '';
    }

    const patterns = [
        /Sangguniang\s*Kabataan\s*Council\s*\/\s*BADAC/i,
        /Sangguniang\s*Kabataan\s*Council\s*\/\s*ALS/i,
        /SK\s*Chairman\s*\/\s*SK\s*Treasurer/i,
        /Sangguniang\s*Kabataan\s*Council/i,
        /Sangguniang\s*Kabataan\s*Counci[l]?/i,
        /SK\s*Treasurer/i,
        /SK\s*Chairman/i,
        /SK\s*Chairperson/i,
    ];

    for (let i = 0; i < patterns.length; i++) {
        const match = source.match(patterns[i]);
        if (match) {
            return normalizePersonColumnText(match[0]);
        }
    }

    return '';
}

function extractPersonResponsibleValue(parts, width, startRatio) {
    const threshold = width * (startRatio !== undefined ? startRatio : 0.82);
    const raw = parts
        .filter(function (part) { return part.x >= threshold; })
        .map(function (part) { return part.text; })
        .join(' ')
        .replace(/\s+/g, ' ')
        .trim();

    const fromColumn = extractPersonFromFullLine(raw);
    if (fromColumn) {
        return fromColumn;
    }

    const fallback = normalizePersonColumnText(extractPersonColumn(parts, width, startRatio));
    if (fallback && /^(SK|Sangguniang)/i.test(fallback)) {
        return fallback;
    }

    const fullLine = parts.map(function (part) { return part.text; }).join(' ').replace(/\s+/g, ' ').trim();

    return extractPersonFromFullLine(fullLine);
}

function normalizePersonColumnText(value) {
    if (!value) {
        return '';
    }

    let text = String(value).replace(/\bPerson\s*Responsible\b:?/gi, '').replace(/\s+/g, ' ').trim();
    const replacements = [
        [/SangguniangKabataanCouncil/gi, 'Sangguniang Kabataan Council'],
        [/SKTreasurer/gi, 'SK Treasurer'],
        [/SKChairman\/SKTreasurer/gi, 'SK Chairman/SK Treasurer'],
        [/SKChairman/gi, 'SK Chairman'],
        [/SKChairperson/gi, 'SK Chairperson'],
        [/SangguniangKabataanCouncil\/ALS/gi, 'Sangguniang Kabataan Council/ALS'],
        [/SangguniangKabataanCouncil\/BADAC/gi, 'Sangguniang Kabataan Council/BADAC'],
        [/^Council$/i, 'Sangguniang Kabataan Council'],
        [/^Kabataan Council$/i, 'Sangguniang Kabataan Council'],
    ];

    replacements.forEach(function (entry) {
        text = text.replace(entry[0], entry[1]);
    });

    if (/^(January|February|March|April|May|June|July|August|September|October|November|December)\b/i.test(text)) {
        return '';
    }

    if (/^\d[\d,.-]*$/.test(text)) {
        return '';
    }

    if (/\b(payment|professional|rendered|payroll|months|charge|incurred|transport|services|nominally|without|given)\b/i.test(text)) {
        return '';
    }

    return text;
}

function isAbyipHeaderRow(line) {
    return /^(Code|PPAs|Description|Expected|Performance|Period|Budget|Person|MOOE|CO|Total|I\.\s*Receipts|II\.\s*Expenditure|GENERAL ADMINISTRATION|Maintenance and Other|Capital Outlay|SK\s+YOUTH\s+DEVELOPMENT|Barangay\s+Estimated\s+Budget|Sangguniang\s+Kabataan\s+Fund|10%\s+of\s+the\s+General\s+Fund)/i.test(line);
}

function isAbyipBudgetHeaderRow(line) {
    return /Barangay\s+Estimated\s+Budget|Sangguniang\s+Kabataan\s+Fund|10%\s+of\s+the\s+General\s+Fund/i.test(line);
}

function hasStructuredTableData(ppas, description, expected, performance, period, mooe, co, total, person) {
    const hasMoney = Boolean(mooe || co || total);
    const hasMeta = Boolean(description || expected || performance || period || person);
    const hasPpas = Boolean(ppas);

    return hasMoney || (hasPpas && hasMeta) || (hasMeta && hasPpas);
}

function buildStructuredTagRow(tag, fields) {
    return tag + Object.keys(fields).map(function (key) {
        return key + ':' + (fields[key] || '');
    }).join('|');
}

function appendAbyipFooterMetadata(lines) {
    let grandTotal = '';

    for (let i = lines.length - 1; i >= 0; i--) {
        const line = lines[i];
        if (/^TOTAL\b/i.test(line) && !line.startsWith('@')) {
            const amounts = line.match(/[\d,]+\.\d{2}/g);
            if (amounts && amounts.length) {
                grandTotal = amounts[amounts.length - 1];
                break;
            }
        }
    }

    if (grandTotal) {
        lines.push('@ABYIP_GRAND_TOTAL@' + grandTotal);
    }

    const preparedIdx = lines.findIndex(function (line) {
        return /Prepared\s+by/i.test(line);
    });

    if (preparedIdx < 0) {
        return;
    }

    const blockLines = lines.slice(preparedIdx, Math.min(preparedIdx + 8, lines.length));
    const blockText = blockLines.join('\n');
    const names = [];
    const nameRegex = /HON\.?\s*([A-Z][A-Za-z.\s]+?)(?=\s+HON\.|\s+SK\s+Chair|\s+Barangay\s+Chair|\n|$)/gi;
    let nameMatch;

    while ((nameMatch = nameRegex.exec(blockText)) !== null) {
        names.push(('HON. ' + nameMatch[1].trim()).replace(/\s+/g, ' '));
    }

    if (names.length < 2) {
        blockLines.forEach(function (blockLine) {
            if (!/HON\./i.test(blockLine)) {
                return;
            }

            blockLine.split(/\s{2,}|\t/).forEach(function (part) {
                part = part.trim();
                if (part && /HON\./i.test(part) && names.indexOf(part) < 0) {
                    names.push(part.replace(/\s+/g, ' '));
                }
            });
        });
    }

    let preparedPos = '';
    let approvedPos = '';

    blockLines.forEach(function (blockLine) {
        if (/SK\s+Chair(?:person|man)?/i.test(blockLine)) {
            preparedPos = 'SK Chairperson';
        }
        if (/Barangay\s+Chair(?:man|person)?/i.test(blockLine)) {
            approvedPos = 'Barangay Chairman';
        }
    });

    const fields = {};
    if (names[0]) {
        fields.PREPARED_NAME = names[0];
    }
    if (preparedPos) {
        fields.PREPARED_POS = preparedPos;
    }
    if (names[1]) {
        fields.APPROVED_NAME = names[1];
    }
    if (approvedPos) {
        fields.APPROVED_POS = approvedPos;
    }

    if (Object.keys(fields).length) {
        lines.push(buildStructuredTagRow('@ABYIP_SIGNATURE@', fields));
    }
}

async function extractPdfTextForPrograms(pdfDoc) {
    resetAbyipColumnBounds();
    const lines = [];
    let inYouthSection = false;
    let inExpenditureSection = false;
    let inReceiptsSection = true;
    let generalBlock = null;
    let youthBlock = null;
    const pageRows = [];

    for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
        const page = await pdfDoc.getPage(pageNum);
        const viewport = page.getViewport({ scale: 1 });
        const textContent = await page.getTextContent();
        const width = viewport.width;
        const rowMap = new Map();

        textContent.items.forEach(function (item) {
            const text = (item.str || '').trim();
            if (!text) {
                return;
            }

            const x = item.transform[4];
            const y = Math.round(item.transform[5]);
            const rowKey = pageNum + ':' + y;
            const bucket = rowMap.get(rowKey) || { y: y, parts: [] };
            bucket.parts.push({ x: x, text: text });
            rowMap.set(rowKey, bucket);
        });

        Array.from(rowMap.values())
            .sort(function (a, b) { return b.y - a.y; })
            .forEach(function (row) {
                row.parts.sort(function (a, b) { return a.x - b.x; });
                pageRows.push({ width: width, row: row });
            });
    }

    const columnBounds = detectAbyipColumnBounds(pageRows);

    pageRows.forEach(function (entry) {
        const width = entry.width;
        const row = entry.row;
        row.parts.sort(function (a, b) { return a.x - b.x; });
        const cols = parseRowColumns(row, width, columnBounds);
        const fullLine = cols.fullLine;

        if (!fullLine) {
            return;
        }

        if (/I\.\s*RECEIPTS/i.test(fullLine)) {
            inReceiptsSection = true;
            inExpenditureSection = false;
        }

        if (/II\.\s*EXPENDITURE/i.test(fullLine)) {
            inExpenditureSection = true;
            inReceiptsSection = false;
        }

        if (/Barangay\s+Estimated\s+Budget/i.test(fullLine)) {
            const amount = extractAmountFromText(fullLine);
            if (amount) {
                lines.push('@ABYIP_HEADER@BARANGAY_BUDGET:' + amount);
            }
            lines.push(fullLine);
            return;
        }

        if (/Sangguniang\s+Kabataan\s+Fund/i.test(fullLine)) {
            const pctMatch = fullLine.match(/(\d+(?:\.\d+)?)\s*%/);
            const amount = extractAmountFromText(fullLine);
            let headerTag = '@ABYIP_HEADER@';
            if (pctMatch) {
                headerTag += 'SK_FUND_PERCENT:' + pctMatch[1];
            }
            if (amount) {
                headerTag += (pctMatch ? '|' : '') + 'SK_FUND_AMOUNT:' + amount;
            }
            if (headerTag !== '@ABYIP_HEADER@') {
                lines.push(headerTag);
            }
            lines.push(fullLine);
            return;
        }

        if (isAbyipBudgetHeaderRow(fullLine)) {
            lines.push(fullLine);
            return;
        }

        if (/SK\s+YOUTH\s+DEVELOPMENT/i.test(fullLine)) {
            flushGeneralBlock(generalBlock, lines);
            generalBlock = null;
            inYouthSection = true;
            inExpenditureSection = true;
            lines.push(fullLine);
            return;
        }

        if (/Prepared\s+by/i.test(fullLine) || /Approved\s+by/i.test(fullLine)) {
            flushGeneralBlock(generalBlock, lines);
            generalBlock = null;
            flushYouthBlock(youthBlock, lines);
            youthBlock = null;
            lines.push(fullLine);
            return;
        }

        if (inYouthSection && /^(TOTAL|Prepared\s+by|Approved\s+by)\b/i.test(fullLine)) {
            flushYouthBlock(youthBlock, lines);
            youthBlock = null;
            inYouthSection = false;
            if (/^TOTAL\b/i.test(fullLine)) {
                const totalAmounts = fullLine.match(/[\d,]+\.\d{2}/g);
                if (totalAmounts && totalAmounts.length) {
                    lines.push('@ABYIP_GRAND_TOTAL@' + totalAmounts[totalAmounts.length - 1]);
                }
                lines.push(fullLine);
            } else if (!/Prepared\s+by/i.test(fullLine) && !/Approved\s+by/i.test(fullLine)) {
                lines.push(fullLine);
            }
            return;
        }

        if (inReceiptsSection && !inExpenditureSection) {
            lines.push(fullLine);
            return;
        }

        if (inYouthSection) {
            const letter = extractYouthLetter(cols.ppas, fullLine);
            if (letter) {
                flushYouthBlock(youthBlock, lines);
                youthBlock = createEmptyBlock(letter);
            }

            if (youthBlock) {
                mergeRowIntoBlock(youthBlock, cols);
            }

            lines.push(fullLine);
            return;
        }

        if (inExpenditureSection && !inReceiptsSection && !isAbyipHeaderRow(fullLine) && !isAbyipBudgetHeaderRow(fullLine)) {
            const isPrimary = isGeneralExpenditurePrimaryRow(cols.ppas);
            const hasData = hasStructuredTableData(
                cols.ppas,
                cols.description,
                cols.expected,
                cols.performance,
                cols.period,
                cols.mooe,
                cols.co,
                cols.total,
                cols.person
            );

            if (isPrimary) {
                flushGeneralBlock(generalBlock, lines);
                generalBlock = createEmptyBlock('');
                mergeRowIntoBlock(generalBlock, cols);
            } else if (generalBlock && hasData) {
                mergeRowIntoBlock(generalBlock, cols);
            }
        }

        lines.push(fullLine);
    });

    flushGeneralBlock(generalBlock, lines);
    flushYouthBlock(youthBlock, lines);
    appendAbyipFooterMetadata(lines);

    return lines.join('\n');
}

function openImportPdfFilePickerLegacy() {
    document.getElementById('pdfFileInput')?.click();
}

function handlePdfImport(event) {
    const file = event.target?.files?.[0];
    if (!file) {
        return;
    }
    processPdfImportFile(file);
    if (event.target) {
        event.target.value = '';
    }
}

function processPdfImportFile(file) {
    // Show loading notification
    showNotification('Loading PDF document...', 'info');
    
    const reader = new FileReader();

    reader.onload = function(e) {
        try {
            const arrayBuffer = e.target.result;
            
            // Store the PDF data as base64 for later retrieval
            const base64String = btoa(
                new Uint8Array(arrayBuffer).reduce((data, byte) => data + String.fromCharCode(byte), '')
            );
            
            // Use PDF.js to render PDF
            const loadingTask = pdfjsLib.getDocument({data: arrayBuffer});
            
            loadingTask.promise.then(async function(pdf) {
                pendingPdfData = base64String;

                try {
                    pendingPdfExtractedText = await extractPdfTextForPrograms(pdf);
                } catch (extractError) {
                    console.error('PDF text extraction error:', extractError);
                    pendingPdfExtractedText = null;
                }

                openAbyipModalWithPdfPreview(pdf, file.name);
            }).catch(function(error) {
                console.error('PDF loading error:', error);
                showNotification('Error loading PDF document. Please try again.', 'error');
            });
            
        } catch (error) {
            console.error('PDF import error:', error);
            showNotification('Error importing PDF document. Please try again.', 'error');
        }
    };

    reader.onerror = function() {
        showNotification('Error reading file. Please try again.', 'error');
    };

    reader.readAsArrayBuffer(file);
}

function openAbyipModalWithPdfPreview(pdfDoc, filename) {
    abyipModalMode = 'pdf-view';

    const modal = document.getElementById('abyipModal');
    const titleEl = document.getElementById('abyipModalTitle');
    const header = document.getElementById('abyipModalHeader');

    if (!modal || !titleEl) return;

    header.classList.remove('edit-mode', 'import-mode');
    header.classList.add('view-mode');
    titleEl.textContent = resubmitRecordId
        ? 'Resubmit ABYIP PDF: ' + filename
        : 'PDF Preview: ' + filename;

    // Create PDF viewer container
    const mount = document.getElementById('abyipModalContentMount');
    if (mount) {
        mount.innerHTML = `
            <div class="pdf-viewer-container">
                <div class="pdf-viewer-header">
                    <p class="pdf-viewer-notice">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        PDF documents are displayed in view-only mode and cannot be edited. Scroll to view all pages.
                    </p>
                </div>
                <div class="pdf-viewer-canvas-container" id="pdfCanvasContainer">
                    <div class="pdf-pages-wrapper">
                        <!-- All pages will be rendered here -->
                    </div>
                </div>
            </div>
        `;
        
        // Render all pages
        renderAllPdfPages(pdfDoc);
    }

    // Set footer to PDF view mode (only Save and Cancel buttons)
    setMainModalFooterMode('pdf-view');
    
    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    showNotification('PDF loaded successfully! Scroll to view all pages.', 'success');
}

function renderAllPdfPages(pdfDoc, containerEl) {
    const container = containerEl || document.querySelector('.pdf-pages-wrapper');
    if (!container) return;
    
    const totalPages = pdfDoc.numPages;
    // Use higher scale for better quality while maintaining aspect ratio
    const scale = 2.0;
    
    // Render each page sequentially
    let renderPromise = Promise.resolve();
    
    for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
        renderPromise = renderPromise.then(() => {
            return pdfDoc.getPage(pageNum).then(function(page) {
                const viewport = page.getViewport({scale: scale});
                
                // Create canvas for this page
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                canvas.className = 'pdf-page-canvas';
                
                // Store original dimensions as data attributes for print reference
                canvas.setAttribute('data-original-width', viewport.width);
                canvas.setAttribute('data-original-height', viewport.height);
                
                // Add page number label
                const pageWrapper = document.createElement('div');
                pageWrapper.className = 'pdf-page-wrapper';
                
                const pageLabel = document.createElement('div');
                pageLabel.className = 'pdf-page-label';
                pageLabel.textContent = `Page ${pageNum} of ${totalPages}`;
                
                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };
                
                return page.render(renderContext).promise.then(function() {
                    pageWrapper.appendChild(pageLabel);
                    pageWrapper.appendChild(canvas);
                    container.appendChild(pageWrapper);
                });
            });
        });
    }
}

function renderStoredPdf(base64Data, filename) {
    // Convert base64 back to array buffer
    const binaryString = atob(base64Data);
    const len = binaryString.length;
    const bytes = new Uint8Array(len);
    for (let i = 0; i < len; i++) {
        bytes[i] = binaryString.charCodeAt(i);
    }
    
    const arrayBuffer = bytes.buffer;
    
    // Create PDF viewer container
    const mount = document.getElementById('abyipModalContentMount');
    if (mount) {
        mount.innerHTML = `
            <div class="pdf-viewer-container">
                <div class="pdf-viewer-header">
                    <p class="pdf-viewer-notice">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        PDF documents are displayed in view-only mode and cannot be edited. Scroll to view all pages.
                    </p>
                </div>
                <div class="pdf-viewer-canvas-container" id="pdfCanvasContainer">
                    <div class="pdf-pages-wrapper">
                        <!-- All pages will be rendered here -->
                    </div>
                </div>
            </div>
        `;
        
        // Use PDF.js to render the stored PDF
        const loadingTask = pdfjsLib.getDocument({data: arrayBuffer});
        
        loadingTask.promise.then(function(pdf) {
            renderAllPdfPages(pdf);
        }).catch(function(error) {
            console.error('Error rendering stored PDF:', error);
            mount.innerHTML = '<div class="pdf-error">Error loading PDF document.</div>';
        });
    }
}

document.addEventListener('DOMContentLoaded', async function () {
    await loadRecords();
    renderRecordsTable();

    addCalculationListeners();
    addNumericValidation();

    const printBtn = document.getElementById('abyipModalPrint');
    if (printBtn) printBtn.style.display = 'none';

    document.getElementById('addAbyipBtn')?.addEventListener('click', openCreateOptionsModal);

    document.getElementById('abyipPdfUploadZone')?.addEventListener('click', openImportPdfFilePicker);
    document.getElementById('abyipPdfUploadZone')?.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            openImportPdfFilePicker();
        }
    });
    document.getElementById('abyipPdfUploadContinueBtn')?.addEventListener('click', continuePdfUpload);
    document.getElementById('createOptionsCancelBtn')?.addEventListener('click', closeCreateOptionsModal);
    document.getElementById('abyipUploadFooterCancelBtn')?.addEventListener('click', closeCreateOptionsModal);
    document.getElementById('createOptionsClose')?.addEventListener('click', closeCreateOptionsModal);
    document.getElementById('createOptionsModal')?.addEventListener('click', function (e) {
        if (e.target === e.currentTarget) closeCreateOptionsModal();
    });

    document.getElementById('pdfFileInput')?.addEventListener('change', handlePdfFileChosen);

    document.getElementById('abyipModalClose')?.addEventListener('click', closeAbyipModal);
    document.getElementById('abyipModalCancel')?.addEventListener('click', closeAbyipModal);
    document.getElementById('abyipModalSave')?.addEventListener('click', saveAbyip);
    document.getElementById('abyipModalPrint')?.addEventListener('click', printAbyipDocument);

    document.getElementById('recordsTableBody')?.addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const id = parseInt(btn.getAttribute('data-id'), 10);
        const action = btn.getAttribute('data-action');
        if (action === 'view') openAbyipModal('view', id);
        else if (action === 'resubmit') openResubmitFlow(id);
        else if (action === 'delete') {
            if (btn.disabled || btn.classList.contains('is-disabled')) return;
            openDeleteModal(id);
        }
    });


    const searchInput = document.getElementById('abyipRecordsSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(function () {
                filterSearchText = searchInput.value || '';
                renderRecordsTable();
            }, 200);
        });
    }

    // Year filter
    const yearFilter = document.getElementById('abyipYearFilter');
    if (yearFilter) {
        yearFilter.addEventListener('change', function () {
            filterYear = yearFilter.value || '';
            renderRecordsTable();
        });
    }

    document.getElementById('deleteCancelBtn')?.addEventListener('click', closeDeleteModal);
    document.getElementById('deleteConfirmBtn')?.addEventListener('click', confirmDeleteRecord);

    document.getElementById('abyipModal')?.addEventListener('click', function (e) {
        if (e.target === e.currentTarget) closeAbyipModal();
    });

    document.getElementById('deleteConfirmModal')?.addEventListener('click', function (e) {
        if (e.target === e.currentTarget) closeDeleteModal();
    });

    const maxBtn = document.getElementById('abyipModalMaximize');
    const modalOverlay = document.getElementById('abyipModal');
    const modalContainer = modalOverlay?.querySelector('.modal-container');
    if (maxBtn && modalOverlay && modalContainer) {
        maxBtn.addEventListener('click', function () {
            const isMax = modalOverlay.classList.toggle('modal-maximized');
            modalContainer.classList.toggle('modal-maximized', isMax);
            maxBtn.textContent = isMax ? '⧉' : '□';
        });
    }

    updateTotals();
});

})();
