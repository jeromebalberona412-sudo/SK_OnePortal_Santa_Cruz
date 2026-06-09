<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scholarship Application - SK OnePortal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite([
        'app/Modules/Layout/assets/css/kabataan-header.css',
        'app/Modules/Layout/assets/js/kabataan-header.js',
        'app/Modules/Dashboard/assets/css/chatbot.css',
        'app/Modules/Dashboard/assets/js/chatbot.js',
        'app/Modules/Dashboard/assets/css/notif.css',
        'app/Modules/Dashboard/assets/js/notif.js',
        'app/Modules/Programs/assets/css/scholarship_application.css',
        'app/Modules/Programs/assets/js/scholarship_application.js',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])
</head>
<body class="sch-app-body kabataan-app-page" data-skip-initial-loading>
    @include('dashboard::loading')
    @include('layout::kabataan-header', ['showSearch' => false, 'pageBadge' => null])

    <div class="gf-container">
        <div class="gf-back-button">
            <a href="{{ route('scholarship.apply', ['schedule' => $scheduleProgramId]) }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                <span>Back</span>
            </a>
        </div>

        <div class="gf-header">
            <div class="gf-banner">
                <div class="gf-banner-content">
                    <h1 class="gf-title" id="gfProgramTitle">{{ $program['program_name'] ?? 'Scholarship Application' }}</h1>
                    <p class="gf-description" id="gfProgramDescription">{{ $program['announcement'] ?? $program['program_type'] ?? '' }}</p>
                </div>
            </div>
            <div class="gf-info-bar">
                <div class="gf-info-item">
                    <span class="gf-info-label">Application Period:</span>
                    <span class="gf-info-value" id="gfApplicationPeriod">{{ ($program['start_date_display'] ?? '—') . ' - ' . ($program['end_date_display'] ?? '—') }}</span>
                </div>
                <div class="gf-info-item">
                    <span class="gf-info-label">Status:</span>
                    <span class="gf-status-badge gf-status-open" id="gfProgramStatus">{{ ($program['status'] ?? 'open') === 'open' ? 'Open' : 'Closed' }}</span>
                </div>
            </div>
        </div>

        <div class="gf-card">
            <div class="gf-kk-notice">
                <p class="gf-kk-notice-text">Your KK Profiling information has been included in this scholarship application. The information below is automatically retrieved from your KK Profile and cannot be edited here.</p>
            </div>
            <div class="gf-kk-section">
                <div class="gf-section-header">
                    <h2 class="gf-section-title">KK Profiling Information</h2>
                    <span class="gf-badge gf-badge-autofill">Auto-Filled from KK Profile</span>
                </div>
                <div class="gf-kk-fields" id="kkProfileFieldsContainer"></div>
            </div>
        </div>

        <form id="scholarshipApplicationForm" class="gf-form" novalidate>
            <div id="customQuestionsContainer"></div>

            <div class="gf-actions">
                <button type="button" class="gf-btn gf-btn-cancel" id="cancelBtn">Cancel</button>
                <button type="submit" class="gf-btn gf-btn-submit" id="submitBtn">Submit Application</button>
            </div>
        </form>
    </div>

    <div id="pdfPreviewModal" class="gf-pdf-modal" hidden>
        <div class="gf-pdf-modal-overlay"></div>
        <div class="gf-pdf-modal-container">
            <div class="gf-pdf-modal-header">
                <h3 id="pdfPreviewTitle">PDF Preview</h3>
                <div class="gf-pdf-modal-actions">
                    <button type="button" class="gf-pdf-zoom-btn" id="pdfPreviewZoomOut" title="Zoom out">−</button>
                    <button type="button" class="gf-pdf-zoom-btn" id="pdfPreviewZoomIn" title="Zoom in">+</button>
                    <a href="#" class="gf-pdf-download-btn" id="pdfPreviewDownload" download>Download PDF</a>
                    <button type="button" class="gf-pdf-close-btn" id="pdfPreviewClose" aria-label="Close preview">×</button>
                </div>
            </div>
            <div class="gf-pdf-modal-body" id="pdfPreviewPages"></div>
        </div>
    </div>

    <div id="successModal" class="gf-success-modal" hidden>
        <div class="gf-success-card">
            <div class="gf-success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <h2 class="gf-success-title">Application Submitted Successfully</h2>
            <div class="gf-success-status">
                <span class="gf-status-badge gf-status-pending">Pending</span>
            </div>
            <p class="gf-success-message">Your application has been submitted successfully and is currently awaiting review by the SK Officials.</p>
            <button type="button" class="gf-btn gf-btn-primary" id="closeSuccessModal">View Application Status</button>
        </div>
    </div>

    <script>
        window.__scheduleProgramId = @json($scheduleProgramId);
        window.__scheduleProgram = @json($program);
        window.__kkFieldLabels = @json($kkFieldLabels);
    </script>
</body>
</html>
