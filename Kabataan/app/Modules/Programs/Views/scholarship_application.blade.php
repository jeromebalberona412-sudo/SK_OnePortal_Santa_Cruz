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

        <ol class="gf-step-progress" id="applicationStepProgress" aria-label="Application steps">
            <li class="gf-step-item is-active" data-step-item="1"><span class="gf-step-num">1</span><span class="gf-step-label">Personal Information</span></li>
            <li class="gf-step-item" data-step-item="2"><span class="gf-step-num">2</span><span class="gf-step-label">Upload Requirements</span></li>
            <li class="gf-step-item" data-step-item="3"><span class="gf-step-num">3</span><span class="gf-step-label">Review Application</span></li>
            <li class="gf-step-item" data-step-item="4"><span class="gf-step-num">4</span><span class="gf-step-label">Confirmation</span></li>
        </ol>

        <form id="scholarshipApplicationForm" class="gf-form" novalidate>
            {{-- Step 1: Personal Information --}}
            <div class="gf-step-panel is-active" data-step="1" id="stepPersonal">
                <div class="gf-card">
                    <div class="gf-kk-notice">
                        <p class="gf-kk-notice-text">Your KK Profiling information has been included in this scholarship application. The information below is automatically retrieved from your KK Profile and cannot be edited here.</p>
                    </div>
                    <div class="gf-kk-section">
                        <div class="gf-section-header">
                            <h2 class="gf-section-title">Personal Information</h2>
                            <span class="gf-badge gf-badge-autofill">Auto-Filled from KK Profile</span>
                        </div>
                        <div class="gf-kk-fields" id="kkProfileFieldsContainer"></div>
                    </div>
                </div>
            </div>

            {{-- Step 2: Upload Requirements --}}
            <div class="gf-step-panel" data-step="2" id="stepUpload" hidden>
                <div id="customQuestionsContainer"></div>
            </div>

            {{-- Step 3: Review Application --}}
            <div class="gf-step-panel" data-step="3" id="stepReview" hidden>
                <div class="gf-card">
                    <h2 class="gf-section-title">Review Application</h2>
                    <p class="gf-review-intro">Please review all information before proceeding to confirmation.</p>
                    <div id="reviewStatusList" class="gf-review-status-list"></div>
                    <div id="reviewStepContainer" class="gf-review-content"></div>
                </div>
            </div>

            {{-- Step 4: Confirmation --}}
            <div class="gf-step-panel" data-step="4" id="stepConfirm" hidden>
                <div class="gf-card">
                    <h2 class="gf-section-title">Confirmation</h2>
                    <p class="gf-review-intro">Please confirm the following before submitting your application.</p>
                    <div class="gf-confirm-checklist">
                        <label class="gf-confirm-item">
                            <input type="checkbox" id="confirmInfoTrue" name="confirm_info_true">
                            <span class="gf-confirm-box"></span>
                            <span>I confirm that all information provided is true and correct.</span>
                        </label>
                        <label class="gf-confirm-item">
                            <input type="checkbox" id="confirmDocsValid" name="confirm_docs_valid">
                            <span class="gf-confirm-box"></span>
                            <span>I confirm that all uploaded documents are clear and valid.</span>
                        </label>
                        <label class="gf-confirm-item">
                            <input type="checkbox" id="confirmFalseInfo" name="confirm_false_info">
                            <span class="gf-confirm-box"></span>
                            <span>I understand that false information may result in rejection.</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="gf-actions gf-step-actions">
                <button type="button" class="gf-btn gf-btn-cancel" id="cancelBtn">Cancel</button>
                <div class="gf-step-nav">
                    <button type="button" class="gf-btn gf-btn-secondary" id="prevStepBtn" hidden>Back</button>
                    <button type="button" class="gf-btn gf-btn-primary" id="nextStepBtn">Continue</button>
                </div>
            </div>
        </form>
    </div>

    <div id="pdfPreviewModal" class="gf-pdf-modal" hidden>
        <div class="gf-pdf-modal-overlay"></div>
        <div class="gf-pdf-modal-container">
            <div class="gf-pdf-modal-header">
                <h3 id="pdfPreviewTitle">PDF Preview</h3>
                <div class="gf-pdf-modal-actions">
                    <button type="button" class="gf-pdf-close-btn" id="pdfPreviewClose" aria-label="Close preview">×</button>
                </div>
            </div>
            <div class="gf-pdf-modal-body" id="pdfPreviewPages"></div>
        </div>
    </div>

    <div id="confirmSubmitModal" class="gf-confirm-modal" hidden>
        <div class="gf-confirm-modal-overlay" data-close-confirm-modal></div>
        <div class="gf-confirm-modal-card" role="dialog" aria-modal="true" aria-labelledby="confirmSubmitTitle">
            <h2 id="confirmSubmitTitle" class="gf-confirm-modal-title">Confirm Application Submission</h2>
            <p class="gf-confirm-modal-text">Please review your information carefully. After submission, editing may no longer be allowed.</p>
            <div class="gf-confirm-modal-actions">
                <button type="button" class="gf-btn gf-btn-secondary" id="backToReviewBtn">Back to Review</button>
                <button type="button" class="gf-btn gf-btn-submit" id="confirmSubmitBtn">Submit Application</button>
            </div>
        </div>
    </div>

    <div id="successModal" class="gf-success-modal" hidden>
        <div class="gf-success-card">
            <div class="gf-success-icon">🎉</div>
            <h2 class="gf-success-title">Application Submitted Successfully</h2>
            <div class="gf-success-details">
                <div class="gf-success-detail-row">
                    <span class="gf-success-detail-label">Reference Number:</span>
                    <strong id="successReferenceNumber">—</strong>
                </div>
                <div class="gf-success-detail-row">
                    <span class="gf-success-detail-label">Status:</span>
                    <span class="gf-status-badge gf-status-pending" id="successStatusBadge">Pending Review</span>
                </div>
            </div>
            <p class="gf-success-message">You will be notified once your application has been processed.</p>
            <div class="gf-success-actions">
                <button type="button" class="gf-btn gf-btn-primary" id="goToDashboardBtn">Go to Dashboard</button>
            </div>
        </div>
    </div>

    <script>
        window.__scheduleProgramId = @json($scheduleProgramId);
        window.__scheduleProgram = @json($program);
        window.__kkFieldLabels = @json($kkFieldLabels);
        window.__dashboardUrl = @json(route('dashboard'));
    </script>
</body>
</html>
