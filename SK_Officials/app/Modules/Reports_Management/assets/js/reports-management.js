(function () {
    'use strict';

    const config = window.rmConfig || {};
    const PROGRAMS = Array.isArray(config.programs) ? config.programs : [];
    const abyipGate = config.abyipGate || null;

    let reports = [];
    let selectedFile = null;

    document.addEventListener('DOMContentLoaded', initializeReportsManagement);

    function initializeReportsManagement() {
        populateProgramSelects();
        bindEvents();
        applyPendingState();
        loadReports();
    }

    function applyPendingState() {
        const openBtn = document.getElementById('rmOpenUploadBtn');
        if (window.SkAbyipNotice?.isPending(abyipGate) && openBtn) {
            openBtn.disabled = true;
            openBtn.title = window.SkAbyipNotice.pendingMessage(abyipGate);
        }
    }

    async function loadReports() {
        try {
            const params = new URLSearchParams();
            const search = (document.getElementById('rmSearchInput')?.value || '').trim();
            const program = document.getElementById('rmProgramFilter')?.value || '';
            if (search) params.set('search', search);
            if (program) params.set('program', program);

            const url = config.listUrl + (params.toString() ? '?' + params.toString() : '');
            const response = await fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Failed to load reports');
            }

            const payload = await response.json();
            reports = payload.data || [];
            renderTable();
        } catch (error) {
            console.error(error);
            reports = [];
            renderTable();
        }
    }

    function populateProgramSelects() {
        const programSelect = document.getElementById('rmProgramSelect');
        const programFilter = document.getElementById('rmProgramFilter');

        PROGRAMS.forEach(function (program) {
            const label = program.code + '. ' + program.name;
            if (programSelect) {
                const option = document.createElement('option');
                option.value = program.code;
                option.textContent = label;
                programSelect.appendChild(option);
            }
            if (programFilter) {
                const filterOption = document.createElement('option');
                filterOption.value = program.code;
                filterOption.textContent = label;
                programFilter.appendChild(filterOption);
            }
        });
    }

    function bindEvents() {
        const openBtn = document.getElementById('rmOpenUploadBtn');
        const closeBtn = document.getElementById('rmCloseUploadBtn');
        const cancelBtn = document.getElementById('rmCancelUploadBtn');
        const submitBtn = document.getElementById('rmSubmitUploadBtn');
        const uploadZone = document.getElementById('rmUploadZone');
        const fileInput = document.getElementById('rmFileInput');
        const programSelect = document.getElementById('rmProgramSelect');
        const activitySelect = document.getElementById('rmActivitySelect');
        const searchInput = document.getElementById('rmSearchInput');
        const programFilter = document.getElementById('rmProgramFilter');
        const previewClose = document.getElementById('rmClosePreviewBtn');
        const previewCloseFooter = document.getElementById('rmClosePreviewFooterBtn');

        if (openBtn) openBtn.addEventListener('click', openUploadModal);
        if (closeBtn) closeBtn.addEventListener('click', closeUploadModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeUploadModal);
        if (submitBtn) submitBtn.addEventListener('click', submitUpload);
        if (previewClose) previewClose.addEventListener('click', closePreviewModal);
        if (previewCloseFooter) previewCloseFooter.addEventListener('click', closePreviewModal);

        if (programSelect) {
            programSelect.addEventListener('change', function () {
                populateActivities(programSelect.value);
                validateUploadForm();
            });
        }

        if (activitySelect) {
            activitySelect.addEventListener('change', validateUploadForm);
        }

        if (searchInput) searchInput.addEventListener('input', loadReports);
        if (programFilter) programFilter.addEventListener('change', loadReports);

        if (uploadZone && fileInput) {
            uploadZone.addEventListener('click', function () {
                fileInput.click();
            });

            uploadZone.addEventListener('dragover', function (event) {
                event.preventDefault();
                uploadZone.classList.add('rm-dragover');
            });

            uploadZone.addEventListener('dragleave', function () {
                uploadZone.classList.remove('rm-dragover');
            });

            uploadZone.addEventListener('drop', function (event) {
                event.preventDefault();
                uploadZone.classList.remove('rm-dragover');
                if (event.dataTransfer.files && event.dataTransfer.files[0]) {
                    handleFileSelection(event.dataTransfer.files[0]);
                }
            });

            fileInput.addEventListener('change', function () {
                if (fileInput.files && fileInput.files[0]) {
                    handleFileSelection(fileInput.files[0]);
                }
            });
        }

        document.getElementById('rmRecordsTableBody')?.addEventListener('click', function (event) {
            const viewBtn = event.target.closest('[data-rm-action="view"]');
            const deleteBtn = event.target.closest('[data-rm-action="delete"]');

            if (viewBtn) {
                openPreviewModal(viewBtn.dataset.id);
            }

            if (deleteBtn) {
                deleteReport(deleteBtn.dataset.id);
            }
        });
    }

    function populateActivities(programCode) {
        const activitySelect = document.getElementById('rmActivitySelect');
        if (!activitySelect) return;

        activitySelect.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = programCode ? 'Select activity' : 'Select program first';
        activitySelect.appendChild(placeholder);
        activitySelect.disabled = !programCode;

        const program = PROGRAMS.find(function (item) {
            return item.code === programCode;
        });

        if (!program) return;

        program.activities.forEach(function (activity) {
            const option = document.createElement('option');
            option.value = activity;
            option.textContent = activity;
            activitySelect.appendChild(option);
        });
    }

    function handleFileSelection(file) {
        if (!isAllowedFile(file)) {
            alert('Please upload a PDF (.pdf) file only.');
            return;
        }

        selectedFile = file;
        const fileNameEl = document.getElementById('rmSelectedFileName');
        if (fileNameEl) {
            fileNameEl.textContent = file.name;
        }
        validateUploadForm();
    }

    function isAllowedFile(file) {
        const name = (file.name || '').toLowerCase();
        const type = (file.type || '').toLowerCase();
        return name.endsWith('.pdf') || type.includes('pdf');
    }

    function validateUploadForm() {
        const programSelect = document.getElementById('rmProgramSelect');
        const activitySelect = document.getElementById('rmActivitySelect');
        const submitBtn = document.getElementById('rmSubmitUploadBtn');

        const isValid =
            programSelect?.value &&
            activitySelect?.value &&
            selectedFile;

        if (submitBtn) {
            submitBtn.disabled = !isValid;
        }
    }

    function openUploadModal() {
        selectedFile = null;
        const modal = document.getElementById('rmUploadModal');
        const fileNameEl = document.getElementById('rmSelectedFileName');
        const programSelect = document.getElementById('rmProgramSelect');
        const activitySelect = document.getElementById('rmActivitySelect');
        const fileInput = document.getElementById('rmFileInput');

        if (programSelect) programSelect.value = '';
        if (activitySelect) {
            activitySelect.innerHTML = '<option value="">Select program first</option>';
            activitySelect.disabled = true;
        }
        if (fileInput) fileInput.value = '';
        if (fileNameEl) fileNameEl.textContent = 'No file selected';
        validateUploadForm();

        if (modal) modal.hidden = false;
    }

    function closeUploadModal() {
        const modal = document.getElementById('rmUploadModal');
        if (modal) modal.hidden = true;
    }

    async function submitUpload() {
        const programSelect = document.getElementById('rmProgramSelect');
        const activitySelect = document.getElementById('rmActivitySelect');
        const submitBtn = document.getElementById('rmSubmitUploadBtn');

        if (!programSelect?.value || !activitySelect?.value || !selectedFile) {
            return;
        }

        const formData = new FormData();
        formData.append('program_code', programSelect.value);
        formData.append('activity_name', activitySelect.value);
        formData.append('report_file', selectedFile);

        if (submitBtn) submitBtn.disabled = true;

        try {
            const response = await fetch(config.storeUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: formData,
            });

            const payload = await response.json().catch(function () {
                return {};
            });

            if (!response.ok) {
                alert(payload.message || 'Failed to upload report.');
                return;
            }

            closeUploadModal();
            await loadReports();
        } catch (error) {
            console.error(error);
            alert('Failed to upload report.');
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    }

    async function deleteReport(id) {
        if (!confirm('Delete this uploaded report?')) {
            return;
        }

        try {
            const response = await fetch(config.destroyUrl.replace('__ID__', id), {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                const payload = await response.json().catch(function () {
                    return {};
                });
                alert(payload.message || 'Failed to delete report.');
                return;
            }

            await loadReports();
        } catch (error) {
            console.error(error);
            alert('Failed to delete report.');
        }
    }

    function renderTable() {
        const tbody = document.getElementById('rmRecordsTableBody');
        if (!tbody) return;

        if (reports.length === 0) {
            if (window.SkAbyipNotice?.isPending(abyipGate)) {
                tbody.innerHTML = '<tr class="rm-empty-row">' + window.SkAbyipNotice.renderEmptyRow(5, abyipGate) + '</tr>';
            } else {
                tbody.innerHTML =
                    '<tr class="rm-empty-row"><td colspan="5">No reports uploaded yet. Click <strong>Upload Report</strong> to submit a program or activity report.</td></tr>';
            }
            return;
        }

        tbody.innerHTML = reports.map(function (report) {
            return (
                '<tr>' +
                '<td>' + escapeHtml(report.programCode + '. ' + report.programName) + '</td>' +
                '<td>' + escapeHtml(report.activity) + '</td>' +
                '<td><span class="rm-file-type rm-file-type-' + report.fileType + '">' + escapeHtml(report.fileName) + '</span></td>' +
                '<td>' + formatDate(report.uploadedAt) + '</td>' +
                '<td class="rm-actions-col">' +
                    '<div class="rm-action-buttons">' +
                        '<button type="button" class="rm-btn-view" data-rm-action="view" data-id="' + report.id + '">View</button>' +
                        '<button type="button" class="rm-btn-delete" data-rm-action="delete" data-id="' + report.id + '">Delete</button>' +
                    '</div>' +
                '</td>' +
                '</tr>'
            );
        }).join('');
    }

    function openPreviewModal(id) {
        const report = reports.find(function (item) {
            return String(item.id) === String(id);
        });

        if (!report) return;

        const body = document.getElementById('rmPreviewBody');
        const modal = document.getElementById('rmPreviewModal');

        if (body) {
            body.innerHTML =
                '<dl class="rm-preview-detail">' +
                    '<div class="rm-preview-row"><dt>Program</dt><dd>' + escapeHtml(report.programCode + '. ' + report.programName) + '</dd></div>' +
                    '<div class="rm-preview-row"><dt>Activity</dt><dd>' + escapeHtml(report.activity) + '</dd></div>' +
                    '<div class="rm-preview-row"><dt>File</dt><dd>' + escapeHtml(report.fileName) + '</dd></div>' +
                    '<div class="rm-preview-row"><dt>Uploaded</dt><dd>' + formatDate(report.uploadedAt) + '</dd></div>' +
                    '<div class="rm-preview-row"><dt>Status</dt><dd>' + renderStatusBadge(report.status) + '</dd></div>' +
                    (report.downloadUrl ? '<div class="rm-preview-row"><dt>Download</dt><dd><a href="' + escapeHtml(report.downloadUrl) + '" target="_blank" rel="noopener">Open PDF</a></dd></div>' : '') +
                '</dl>';
        }

        if (modal) modal.hidden = false;
    }

    function closePreviewModal() {
        const modal = document.getElementById('rmPreviewModal');
        if (modal) modal.hidden = true;
    }

    function renderStatusBadge(status) {
        const normalized = String(status || 'pending').toLowerCase();
        const label = normalized.charAt(0).toUpperCase() + normalized.slice(1);
        const className = normalized === 'approved'
            ? 'rm-status-approved'
            : normalized === 'rejected'
                ? 'rm-status-rejected'
                : 'rm-status-pending';

        return '<span class="rm-status-badge ' + className + '">' + escapeHtml(label) + '</span>';
    }

    function formatDate(value) {
        if (!value) return '—';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return '—';

        return date.toLocaleDateString('en-PH', {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
        });
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }
})();
