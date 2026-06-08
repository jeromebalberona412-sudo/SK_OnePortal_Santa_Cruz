// ABYIP — document totals, records (database), modals
// Create flow: Upload Word/PDF → save via API.
(function () {
'use strict';

const DEFAULT_RECORD_TITLE = 'ABYIP CY 2026';

let abyipRecords = [];
let abyipModalMode = 'create';
let recordPendingDeleteId = null;
let pendingPdfData = null; // Store PDF data temporarily
let pendingPdfExtractedText = null; // Text extracted from PDF for program auto-detection
let pendingIsImported = false; // Track if pending record is an imported Word doc

let filterSearchText = '';
let filterYear = '';
let searchDebounceTimer = null;

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
        documentHtml: record.document_html || '',
        pdfData: record.pdf_data || null,
        isPdf: record.source_type === 'pdf',
        isImported: record.source_type === 'word',
        calendarYear: record.calendar_year,
    };
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

function renderRecordsTable() {
    const tbody = document.getElementById('recordsTableBody');
    if (!tbody) return;

    // DO NOT disable the Create button - keep it always enabled
    const createBtn = document.getElementById('addAbyipBtn');
    if (createBtn) {
        createBtn.disabled = false;
        createBtn.title = '';
    }

    if (abyipRecords.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="4" class="abyip-records-empty">No ABYIP records yet. Upload a Word or PDF document to get started.</td></tr>';
        return;
    }

    const filtered = getFilteredRecords();
    if (filtered.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="4" class="abyip-records-empty">No records match your search.</td></tr>';
        return;
    }

    tbody.innerHTML = filtered
        .map((record) => {
            return (
                '<tr data-record-id="' +
                record.id +
                '">' +
                '<td class="abyip-records-title">' +
                escapeHtml(record.title || '') +
                '</td>' +
                '<td class="abyip-records-date">' +
                formatDateDisplay(record.dateCreated) +
                '</td>' +
                '<td class="abyip-records-time">' +
                formatTimeCreated12(record.dateCreated) +
                '</td>' +
                '<td class="abyip-records-actions">' +
                '<div class="action-buttons-cell">' +
                '<button type="button" class="btn-action-view" data-action="view" data-id="' +
                record.id +
                '">View</button>' +
                '<button type="button" class="btn-action-delete" data-action="delete" data-id="' +
                record.id +
                '">Delete</button>' +
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
    const exportBtn = document.getElementById('abyipModalExportWord');
    const printBtn = document.getElementById('abyipModalPrint');
    const saveBtn = document.getElementById('abyipModalSave');
    const cancelBtn = document.getElementById('abyipModalCancel');

    if (mode === 'view') {
        footer?.classList.add('abyip-modal-footer-view');
        footer?.classList.remove('abyip-modal-footer-import', 'abyip-modal-footer-pdf-view');
        // Show only Print and Export buttons in view mode (no cancel button)
        if (exportBtn) exportBtn.style.display = 'inline-block';
        if (printBtn) printBtn.style.display = 'inline-block';
        if (saveBtn) saveBtn.style.display = 'none';
        if (cancelBtn) cancelBtn.style.display = 'none';
    } else if (mode === 'import') {
        footer?.classList.add('abyip-modal-footer-import');
        footer?.classList.remove('abyip-modal-footer-view', 'abyip-modal-footer-pdf-view');
        // Show Save and Cancel buttons for import mode
        if (exportBtn) exportBtn.style.display = 'none';
        if (printBtn) printBtn.style.display = 'none';
        if (saveBtn) {
            saveBtn.style.display = 'inline-block';
            saveBtn.textContent = 'Save Imported ABYIP';
        }
        if (cancelBtn) cancelBtn.style.display = 'inline-block';
    } else if (mode === 'pdf-view') {
        footer?.classList.add('abyip-modal-footer-pdf-view');
        footer?.classList.remove('abyip-modal-footer-view', 'abyip-modal-footer-import');
        // Show only Save and Cancel buttons for PDF view mode (no Print or Export)
        if (exportBtn) exportBtn.style.display = 'none';
        if (printBtn) printBtn.style.display = 'none';
        if (saveBtn) {
            saveBtn.style.display = 'inline-block';
            saveBtn.textContent = 'Save PDF';
        }
        if (cancelBtn) cancelBtn.style.display = 'inline-block';
    } else {
        footer?.classList.remove('abyip-modal-footer-view', 'abyip-modal-footer-import', 'abyip-modal-footer-pdf-view');
        // Show Save and Cancel buttons in create/edit modes
        if (exportBtn) exportBtn.style.display = 'none';
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
    setMainModalFooterMode('edit');
}

async function saveAbyip() {
    if (abyipModalMode !== 'create' && abyipModalMode !== 'import' && abyipModalMode !== 'pdf-view') {
        return;
    }

    const isPdf = abyipModalMode === 'pdf-view';

    if (isPdf && !pendingPdfData) {
        showNotification('No PDF data to save.', 'error');
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

    const calendarYear = new Date().getFullYear();
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

async function loadRecords() {
    try {
        const response = await abyipApiFetch('/api/abyip');
        abyipRecords = (response.data || []).map(mapRecordFromApi);
    } catch (e) {
        abyipRecords = [];
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

function openCreateOptionsModal() {
    // Enforce: only 1 ABYIP record allowed at a time
    if (abyipRecords.length >= 1) {
        showNotification('Cannot create a new ABYIP. An existing record is already present. Please delete it first before creating a new one.', 'error');
        return;
    }
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

async function extractPdfTextForPrograms(pdfDoc) {
    const lines = [];
    const programLines = [];

    for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
        const page = await pdfDoc.getPage(pageNum);
        const viewport = page.getViewport({ scale: 1 });
        const textContent = await page.getTextContent();
        const leftColumnLimit = viewport.width * 0.38;
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
            bucket.parts.push({ x: x, text: text, isLeft: x <= leftColumnLimit });
            rowMap.set(rowKey, bucket);
        });

        Array.from(rowMap.values())
            .sort(function (a, b) { return b.y - a.y; })
            .forEach(function (row) {
                row.parts.sort(function (a, b) { return a.x - b.x; });
                const fullLine = row.parts.map(function (part) { return part.text; }).join(' ').replace(/\s+/g, ' ').trim();
                const leftLine = row.parts
                    .filter(function (part) { return part.isLeft; })
                    .map(function (part) { return part.text; })
                    .join(' ')
                    .replace(/\s+/g, ' ')
                    .trim();

                if (fullLine) {
                    lines.push(fullLine);
                }

                if (leftLine && /^[A-J]\.\s/i.test(leftLine)) {
                    programLines.push(leftLine);
                } else if (fullLine && /^[A-J]\.\s/i.test(fullLine)) {
                    programLines.push(fullLine);
                }
            });
    }

    const sectionIndex = lines.findIndex(function (line) {
        return /SK\s+YOUTH\s+DEVELOPMENT/i.test(line);
    });

    const output = [];
    if (sectionIndex >= 0) {
        output.push(lines[sectionIndex]);
    }
    programLines.forEach(function (line) {
        if (!output.includes(line)) {
            output.push(line);
        }
    });

    if (output.length === 0) {
        return lines.join('\n');
    }

    return output.join('\n');
}

function openImportPdfFilePicker() {
    closeCreateOptionsModal();
    const fileInput = document.getElementById('pdfFileInput');
    if (fileInput) {
        fileInput.click();
    }
}

function handlePdfImport(event) {
    const fileInput = event.target;
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        return;
    }

    const file = fileInput.files[0];
    
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
                fileInput.value = '';
            }).catch(function(error) {
                console.error('PDF loading error:', error);
                showNotification('Error loading PDF document. Please try again.', 'error');
                fileInput.value = '';
            });
            
        } catch (error) {
            console.error('PDF import error:', error);
            showNotification('Error importing PDF document. Please try again.', 'error');
            fileInput.value = '';
        }
    };

    reader.onerror = function() {
        showNotification('Error reading file. Please try again.', 'error');
        fileInput.value = '';
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
    titleEl.textContent = 'PDF Preview: ' + filename;

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

function renderAllPdfPages(pdfDoc) {
    const container = document.querySelector('.pdf-pages-wrapper');
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

    document.getElementById('addAbyipBtn')?.addEventListener('click', function() {
        // Check if an ABYIP record already exists for the current year (2026)
        const currentYear = new Date().getFullYear();
        const existingRecordForYear = abyipRecords.some((record) => {
            const recordYear = record.calendarYear
                || (record.dateCreated ? new Date(record.dateCreated).getFullYear() : null);
            return recordYear === currentYear;
        });

        if (existingRecordForYear) {
            showNotification('Cannot create another ABYIP for this year. Delete existing record first.', 'error');
            return;
        }
        
        openCreateOptionsModal();
    });

    document.getElementById('selectImportBtn')?.addEventListener('click', openImportWordFilePicker);
    document.getElementById('selectImportPdfBtn')?.addEventListener('click', openImportPdfFilePicker);
    document.getElementById('createOptionsClose')?.addEventListener('click', closeCreateOptionsModal);
    document.getElementById('createOptionsModal')?.addEventListener('click', function (e) {
        if (e.target === e.currentTarget) closeCreateOptionsModal();
    });

    // File input change listener for Word import
    document.getElementById('wordFileInput')?.addEventListener('change', handleWordImport);
    
    // File input change listener for PDF import
    document.getElementById('pdfFileInput')?.addEventListener('change', handlePdfImport);

    document.getElementById('abyipModalClose')?.addEventListener('click', closeAbyipModal);
    document.getElementById('abyipModalCancel')?.addEventListener('click', closeAbyipModal);
    document.getElementById('abyipModalSave')?.addEventListener('click', saveAbyip);
    document.getElementById('abyipModalPrint')?.addEventListener('click', printAbyipDocument);
    document.getElementById('abyipModalExportWord')?.addEventListener('click', exportToWord);

    document.getElementById('recordsTableBody')?.addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const id = parseInt(btn.getAttribute('data-id'), 10);
        const action = btn.getAttribute('data-action');
        if (action === 'view') openAbyipModal('view', id);
        else if (action === 'delete') openDeleteModal(id);
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
