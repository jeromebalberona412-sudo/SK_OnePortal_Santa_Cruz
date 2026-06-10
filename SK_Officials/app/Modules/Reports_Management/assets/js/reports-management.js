(function () {
    'use strict';

    const STORAGE_KEY = 'sk_reports_management_uploads_v1';

    const PROGRAMS = [
        {
            code: 'A',
            name: 'Equitable Access to Quality Education',
            activities: [
                'Educational Assistance Program',
                'Tutorial and Review Sessions',
                'School Supplies Distribution',
            ],
        },
        {
            code: 'B',
            name: 'Environmental Protection',
            activities: [
                'Tree Planting Activity',
                'Clean-up Drive',
                'Environmental Awareness Seminar',
            ],
        },
        {
            code: 'C',
            name: 'Disaster Risk Reduction and Resiliency',
            activities: [
                'Disaster Preparedness Training',
                'Emergency Kit Distribution',
                'Community Evacuation Drill',
            ],
        },
        {
            code: 'D',
            name: 'Youth Employment and Livelihood',
            activities: [
                'Skills Training Workshop',
                'Livelihood Starter Kits Distribution',
                'Job Fair Orientation',
            ],
        },
        {
            code: 'E',
            name: 'Health',
            activities: [
                'Medical and Dental Mission',
                'Mental Health Awareness Forum',
                'Feeding Program',
            ],
        },
        {
            code: 'F',
            name: 'Anti-Drug Abuse',
            activities: [
                'Anti-Drug Symposium',
                'Peer Counseling Session',
                'Community Awareness Campaign',
            ],
        },
        {
            code: 'G',
            name: 'Gender Sensitivity',
            activities: [
                'Gender Sensitivity Seminar',
                'Women and Youth Empowerment Forum',
            ],
        },
        {
            code: 'H',
            name: 'Feeding Program',
            activities: [
                'Community Feeding Activity',
                'Nutrition Education Session',
            ],
        },
        {
            code: 'I',
            name: 'Sports Development',
            activities: [
                'Inter-Barangay Sports Tournament',
                'Sports Clinic and Training',
            ],
        },
        {
            code: 'J',
            name: 'Other Programs',
            activities: [
                'Katipunan ng Kabataan General Assembly',
                'Barangay Day Celebration',
                'Youth Week',
            ],
        },
    ];

    let reports = [];
    let selectedFile = null;

    document.addEventListener('DOMContentLoaded', initializeReportsManagement);

    function initializeReportsManagement() {
        reports = loadReports();
        populateProgramSelects();
        bindEvents();
        renderTable();
    }

    function loadReports() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (error) {
            console.error('Failed to load reports from localStorage', error);
            return [];
        }
    }

    function saveReports() {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(reports));
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

        if (searchInput) searchInput.addEventListener('input', renderTable);
        if (programFilter) programFilter.addEventListener('change', renderTable);

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
            alert('Please upload a Word (.doc, .docx) or PDF (.pdf) file only.');
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
        return (
            name.endsWith('.pdf') ||
            name.endsWith('.doc') ||
            name.endsWith('.docx') ||
            type.includes('pdf') ||
            type.includes('msword') ||
            type.includes('wordprocessingml')
        );
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

    function submitUpload() {
        const programSelect = document.getElementById('rmProgramSelect');
        const activitySelect = document.getElementById('rmActivitySelect');

        if (!programSelect?.value || !activitySelect?.value || !selectedFile) {
            return;
        }

        const program = PROGRAMS.find(function (item) {
            return item.code === programSelect.value;
        });

        const report = {
            id: 'rm-' + Date.now(),
            programCode: program.code,
            programName: program.name,
            activity: activitySelect.value,
            fileName: selectedFile.name,
            fileType: selectedFile.name.toLowerCase().endsWith('.pdf') ? 'pdf' : 'word',
            status: 'pending',
            uploadedAt: new Date().toISOString(),
        };

        reports.unshift(report);
        saveReports();
        closeUploadModal();
        renderTable();
    }

    function deleteReport(id) {
        if (!confirm('Delete this uploaded report?')) {
            return;
        }

        reports = reports.filter(function (report) {
            return report.id !== id;
        });
        saveReports();
        renderTable();
    }

    function getFilteredReports() {
        const search = (document.getElementById('rmSearchInput')?.value || '').trim().toLowerCase();
        const program = document.getElementById('rmProgramFilter')?.value || '';

        return reports.filter(function (report) {
            const haystack = [
                report.programName,
                report.activity,
                report.fileName,
            ].join(' ').toLowerCase();

            if (search && !haystack.includes(search)) {
                return false;
            }

            if (program && report.programCode !== program) {
                return false;
            }

            return true;
        });
    }

    function renderTable() {
        const tbody = document.getElementById('rmRecordsTableBody');
        if (!tbody) return;

        const filtered = getFilteredReports();

        if (filtered.length === 0) {
            tbody.innerHTML =
                '<tr class="rm-empty-row"><td colspan="5">No reports uploaded yet. Click <strong>Upload Report</strong> to submit a program or activity report.</td></tr>';
            return;
        }

        tbody.innerHTML = filtered.map(function (report) {
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
            return item.id === id;
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
                    '<div class="rm-preview-row"><dt>Reviewed by</dt><dd>SK Federations (pending verification)</dd></div>' +
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
})();
