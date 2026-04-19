// ABYIP — document totals, records (localStorage), modals
// Create flow: Save ABYIP (document) → meta modal (editable Title default "ABYIP CY 2025", Remarks) → record saved to table.
// Table: Title, Date, Time (12h), Status, Remarks (read-only), Actions. View mode: Print ABYIP + header close only.

const ABYIP_STORAGE_KEY = 'abyip_records_v2';
const DEFAULT_RECORD_TITLE = 'ABYIP CY 2025';

let abyipRecords = [];
let abyipModalMode = 'create';
let currentEditId = null;
let recordPendingDeleteId = null;
let pendingCreateDocumentHtml = null;

let filterSearchText = '';
let searchDebounceTimer = null;

// Static status - all records are always Pending
const STATIC_STATUS = 'Pending';

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

function statusSelectClass(status) {
    const s = (status || 'Pending').toLowerCase();
    return 'abyip-row-status abyip-st-' + s.replace(/\s+/g, '-');
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
    const q = filterSearchText.trim().toLowerCase();
    if (!q) return true;
    return getRecordSearchHaystack(record).indexOf(q) !== -1;
}

function getFilteredRecords() {
    return abyipRecords.filter(recordMatchesFilters);
}

function buildStatusDisplay() {
    return '<span class="status-badge status-pending">Pending</span>';
}

function renderRecordsTable() {
    const tbody = document.getElementById('recordsTableBody');
    if (!tbody) return;

    if (abyipRecords.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="6" class="abyip-records-empty">No ABYIP records yet. Click &ldquo;Create ABYIP&rdquo; to add one.</td></tr>';
        return;
    }

    const filtered = getFilteredRecords();
    if (filtered.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="6" class="abyip-records-empty">No records match your search.</td></tr>';
        return;
    }

    tbody.innerHTML = filtered
        .map((record) => {
            const remarksText = record.statusRemarks || '';
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
                '<td class="abyip-records-status-cell">' +
                buildStatusDisplay() +
                '</td>' +
                '<td class="abyip-records-remarks-readonly">' +
                escapeHtml(remarksText) +
                '</td>' +
                '<td class="abyip-records-actions">' +
                '<div class="action-buttons-cell">' +
                '<button type="button" class="btn-action-view" data-action="view" data-id="' +
                record.id +
                '">View</button>' +
                '<button type="button" class="btn-action-edit" data-action="edit" data-id="' +
                record.id +
                '">Edit</button>' +
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
        // Show only Print and Export buttons in view mode (no cancel button)
        if (exportBtn) exportBtn.style.display = 'inline-block';
        if (printBtn) printBtn.style.display = 'inline-block';
        if (saveBtn) saveBtn.style.display = 'none';
        if (cancelBtn) cancelBtn.style.display = 'none';
    } else {
        footer?.classList.remove('abyip-modal-footer-view');
        // Show Save and Cancel buttons in create/edit modes
        if (exportBtn) exportBtn.style.display = 'none';
        if (printBtn) printBtn.style.display = 'none';
        if (saveBtn) saveBtn.style.display = 'inline-block';
        if (cancelBtn) cancelBtn.style.display = 'inline-block';
    }
}

function openAbyipModal(mode, recordId) {
    abyipModalMode = mode;
    currentEditId = recordId != null ? recordId : null;

    const modal = document.getElementById('abyipModal');
    const titleEl = document.getElementById('abyipModalTitle');
    const header = document.getElementById('abyipModalHeader');

    if (!modal || !titleEl) return;

    header.classList.remove('edit-mode', 'view-mode');
    if (mode === 'view') {
        header.classList.add('view-mode');
        titleEl.textContent = 'View Annual Barangay Youth Investment Program (ABYIP)';
    } else if (mode === 'edit') {
        header.classList.add('edit-mode');
        titleEl.textContent = 'Edit Annual Barangay Youth Investment Program (ABYIP)';
    } else {
        titleEl.textContent = 'Create Annual Barangay Youth Investment Program (ABYIP)';
    }

    if (mode === 'create') {
        setFormRootHtml(getDefaultDocumentHtml());
        setMainModalFooterMode('edit');
        setMountContentEditable(true);
    } else {
        const record = abyipRecords.find((r) => r.id === recordId);
        if (!record) return;
        const html = record.documentHtml && record.documentHtml.length > 0 ? record.documentHtml : getDefaultDocumentHtml();
        setFormRootHtml(html);

        if (mode === 'view') {
            setMainModalFooterMode('view');
            setMountContentEditable(false);
        } else {
            setMainModalFooterMode('edit');
            setMountContentEditable(true);
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

function openMetaModalForCreate() {
    const m = document.getElementById('abyipMetaModal');
    const titleIn = document.getElementById('abyipMetaTitleInput');
    const remIn = document.getElementById('abyipMetaRemarksInput');
    if (titleIn) titleIn.value = DEFAULT_RECORD_TITLE;
    if (remIn) remIn.value = '';
    if (m) {
        m.classList.add('active');
        m.setAttribute('aria-hidden', 'false');
    }
    if (titleIn) titleIn.focus();
}

function closeMetaModalOnly() {
    const m = document.getElementById('abyipMetaModal');
    if (m) {
        m.classList.remove('active');
        m.setAttribute('aria-hidden', 'true');
    }
}

function closeMetaModal() {
    closeMetaModalOnly();
    pendingCreateDocumentHtml = null;
}

function saveAbyip() {
    const mount = document.getElementById('abyipModalContentMount');
    const documentHtml = mount ? mount.innerHTML : '';

    if (abyipModalMode === 'edit' && currentEditId != null) {
        const record = abyipRecords.find((r) => r.id === currentEditId);
        if (record) {
            record.documentHtml = documentHtml;
        }
        persistRecords();
        renderRecordsTable();
        closeAbyipModal();
        showNotification('ABYIP updated successfully.', 'success');
        return;
    }

    if (abyipModalMode === 'create') {
        pendingCreateDocumentHtml = documentHtml;
        closeAbyipModal();
        openMetaModalForCreate();
    }
}

function confirmMetaSave() {
    if (pendingCreateDocumentHtml == null) {
        closeMetaModal();
        return;
    }

    const titleIn = document.getElementById('abyipMetaTitleInput');
    const remIn = document.getElementById('abyipMetaRemarksInput');
    const title = (titleIn && titleIn.value.trim()) || DEFAULT_RECORD_TITLE;
    const statusRemarks = (remIn && remIn.value.trim()) || '';

    const nextId = abyipRecords.length ? Math.max(...abyipRecords.map((r) => r.id)) + 1 : 1;
    abyipRecords.push({
        id: nextId,
        title,
        dateCreated: new Date().toISOString(),
        status: STATIC_STATUS,
        statusRemarks,
        documentHtml: pendingCreateDocumentHtml
    });

    pendingCreateDocumentHtml = null;
    closeMetaModalOnly();
    persistRecords();
    renderRecordsTable();
    showNotification('ABYIP record saved.', 'success');
}

function cancelMetaSave() {
    pendingCreateDocumentHtml = null;
    closeMetaModal();
}

function printAbyipDocument() {
    document.body.classList.add('abyip-printing');
    window.print();
    setTimeout(function () {
        document.body.classList.remove('abyip-printing');
    }, 500);
}

function loadRecords() {
    try {
        const raw = localStorage.getItem(ABYIP_STORAGE_KEY);
        if (raw) {
            abyipRecords = JSON.parse(raw);
            return;
        }
    } catch {
        abyipRecords = [];
    }

    abyipRecords = [
        {
            id: 1,
            title: DEFAULT_RECORD_TITLE,
            dateCreated: new Date().toISOString(),
            status: STATIC_STATUS,
            statusRemarks: '',
            documentHtml: ''
        }
    ];
    persistRecords();
}

function persistRecords() {
    localStorage.setItem(ABYIP_STORAGE_KEY, JSON.stringify(abyipRecords));
}

function updateRecordStatus(id, status) {
    const record = abyipRecords.find((r) => r.id === id);
    if (!record) return;
    record.status = status;
    persistRecords();
    renderRecordsTable();
}

function showNotification(message, type) {
    if (window.__abyipToast) return;
    const n = document.createElement('div');
    n.className = 'abyip-toast abyip-toast-' + (type || 'info');
    n.textContent = message;
    n.style.cssText =
        'position:fixed;bottom:24px;right:24px;padding:12px 18px;border-radius:8px;background:#111;color:#fff;z-index:2000;font-size:14px;box-shadow:0 8px 24px rgba(0,0,0,.2)';
    if (type === 'success') n.style.background = '#15803d';
    document.body.appendChild(n);
    window.__abyipToast = true;
    setTimeout(() => {
        n.remove();
        window.__abyipToast = false;
    }, 2800);
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

function confirmDeleteRecord() {
    if (recordPendingDeleteId == null) return;
    abyipRecords = abyipRecords.filter((r) => r.id !== recordPendingDeleteId);
    persistRecords();
    renderRecordsTable();
    closeDeleteModal();
    showNotification('Record deleted.', 'success');
}

document.addEventListener('DOMContentLoaded', function () {
    loadRecords();
    renderRecordsTable();

    addCalculationListeners();
    addNumericValidation();

    const printBtn = document.getElementById('abyipModalPrint');
    if (printBtn) printBtn.style.display = 'none';

    document.getElementById('addAbyipBtn')?.addEventListener('click', () => openAbyipModal('create', null));

    document.getElementById('abyipModalClose')?.addEventListener('click', closeAbyipModal);
    document.getElementById('abyipModalCancel')?.addEventListener('click', closeAbyipModal);
    document.getElementById('abyipModalSave')?.addEventListener('click', saveAbyip);
    document.getElementById('abyipModalPrint')?.addEventListener('click', printAbyipDocument);
    document.getElementById('abyipModalExportWord')?.addEventListener('click', exportToWord);

    document.getElementById('abyipMetaConfirm')?.addEventListener('click', confirmMetaSave);
    document.getElementById('abyipMetaCancel')?.addEventListener('click', cancelMetaSave);
    document.getElementById('abyipMetaModal')?.addEventListener('click', function (e) {
        if (e.target === e.currentTarget) cancelMetaSave();
    });

    document.getElementById('recordsTableBody')?.addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const id = parseInt(btn.getAttribute('data-id'), 10);
        const action = btn.getAttribute('data-action');
        if (action === 'view') openAbyipModal('view', id);
        else if (action === 'edit') openAbyipModal('edit', id);
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
