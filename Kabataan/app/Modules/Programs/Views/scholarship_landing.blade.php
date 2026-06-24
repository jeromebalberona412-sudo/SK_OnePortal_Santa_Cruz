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
        'app/Modules/Programs/assets/css/scholarship_landing.css',
        'app/Modules/Programs/assets/js/scholarship_landing.js',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])
</head>
<body class="sl-body kabataan-app-page">
    @include('dashboard::loading')
    @include('layout::kabataan-header', ['showSearch' => false, 'pageBadge' => null])

    <div class="sl-container">
        <!-- Header -->
        <div class="sl-header">
            <div class="sl-back-link">
                <a href="{{ route('dashboard') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    <span>Back to Dashboard</span>
                </a>
            </div>
            <h1 class="sl-title">Scholarship Application</h1>
        </div>

        <!-- Program Information Card -->
        <div class="sl-card sl-card-program">
            <div class="sl-card-header">
                <h2 class="sl-card-title">Program Information</h2>
                <span class="sl-status-badge sl-status-open" id="slProgramStatusBadge">Open</span>
            </div>
            <div class="sl-card-body">
                <h3 class="sl-program-name" id="slProgramName">Loading program…</h3>
                <p class="sl-program-description" id="slProgramDescription">Please wait while program details are loaded.</p>
                
                <div class="sl-info-grid">
                    <div class="sl-info-item">
                        <span class="sl-info-label">Application Period:</span>
                        <span class="sl-info-value" id="slApplicationPeriod">—</span>
                    </div>
                    <div class="sl-info-item">
                        <span class="sl-info-label">Available Slots:</span>
                        <span class="sl-info-value" id="slAvailableSlots">—</span>
                    </div>
                    <div class="sl-info-item">
                        <span class="sl-info-label">Committee:</span>
                        <span class="sl-info-value" id="slCommittee">—</span>
                    </div>
                </div>

                <div class="sl-section">
                    <h4 class="sl-section-title">Announcement</h4>
                    <p class="sl-program-description" id="slAnnouncement">—</p>
                </div>

                <div class="sl-section">
                    <h4 class="sl-section-title">Eligibility Requirements</h4>
                    <ul class="sl-list">
                        <li>Must be a registered Kabataan member of Barangay Santa Cruz</li>
                        <li>Must be currently enrolled in an educational institution</li>
                        <li>Must maintain a GPA of 85% or higher</li>
                        <li>Must submit all required documents</li>
                    </ul>
                </div>

                <div class="sl-section">
                    <h4 class="sl-section-title">Terms and Conditions</h4>
                    <ul class="sl-list">
                        <li>Scholarship is valid for one academic year</li>
                        <li>Recipients must maintain good academic standing</li>
                        <li>False information may result in disqualification</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Previous Applications Card -->
        <div class="sl-card sl-card-history">
            <div class="sl-card-header">
                <h2 class="sl-card-title">Previous Applications</h2>
            </div>
            <div class="sl-card-body">
                <div class="sl-table-wrapper">
                    <table class="sl-table">
                        <thead>
                            <tr>
                                <th>Program Name</th>
                                <th>Application Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="previousApplicationsTable">
                            <tr class="sl-empty-row">
                                <td colspan="4">No previous applications found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Requirements Preview Card -->
        <div class="sl-card sl-card-requirements">
            <div class="sl-card-header">
                <h2 class="sl-card-title">Required Documents</h2>
            </div>
            <div class="sl-card-body">
                <ul class="sl-requirements-list">
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        <span>Certificate of Registration (COR)</span>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        <span>Copy of Grades</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- KK Profiling Preview Card -->
        <div class="sl-card sl-card-kk-profile">
            <div class="sl-card-header">
                <h2 class="sl-card-title">KK Profiling Data Included</h2>
                <span class="sl-badge sl-badge-autofill">Auto-Filled</span>
            </div>
            <div class="sl-card-body">
                <p class="sl-kk-notice">The following information will be automatically retrieved from your KK Profile:</p>
                <div class="sl-kk-fields">
                    <div class="sl-kk-field">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Full Name</span>
                    </div>
                    <div class="sl-kk-field">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Birthday</span>
                    </div>
                    <div class="sl-kk-field">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Age</span>
                    </div>
                    <div class="sl-kk-field">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Sex</span>
                    </div>
                    <div class="sl-kk-field">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Civil Status</span>
                    </div>
                    <div class="sl-kk-field">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Contact Number</span>
                    </div>
                    <div class="sl-kk-field">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Home Address</span>
                    </div>
                    <div class="sl-kk-field">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Current School</span>
                    </div>
                    <div class="sl-kk-field">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Year Level</span>
                    </div>
                    <div class="sl-kk-field">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Course / Strand</span>
                    </div>
                    <div class="sl-kk-field">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Barangay</span>
                    </div>
                    <div class="sl-kk-field">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>City/Municipality</span>
                    </div>
                    <div class="sl-kk-field">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Province</span>
                    </div>
                    <div class="sl-kk-field">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Region</span>
                    </div>
                </div>
                <p class="sl-kk-note">You do not need to enter the information above again because it will be automatically filled from your KK Profile.</p>
            </div>
        </div>

        <!-- Start Application Button -->
        <div class="sl-actions">
            <button class="sl-btn sl-btn-primary" id="startApplicationBtn">
                <span>Start Scholarship Application</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
        </div>
    </div>

    <!-- Mobile Drawer Backdrop -->
    <div id="programsDrawerBackdrop" class="programs-drawer-backdrop"></div>

    <!-- Mobile Drawer -->
    <aside class="programs-sidebar" id="programsDrawerSidebar">
        <div class="sidebar-card">
            <h2 class="sidebar-title">Programs in Your Barangay</h2>
            <p class="sidebar-subtitle">Available programs in Barangay Santa Cruz</p>
            
            <div class="program-categories">
                <a href="{{ route('scholarship.apply') }}" class="program-category">
                    <div class="category-icon">📚</div>
                    <div class="category-info">
                        <h3>Scholarship Programs</h3>
                        <p>Apply for educational assistance</p>
                    </div>
                </a>
                <a href="{{ route('sports.apply') }}" class="program-category">
                    <div class="category-icon">⚽</div>
                    <div class="category-info">
                        <h3>Sports Programs</h3>
                        <p>Join sports activities and competitions</p>
                    </div>
                </a>
            </div>
        </div>
    </aside>

    <div id="applicationViewModal" class="sl-view-modal" hidden>
        <div class="sl-view-modal-overlay"></div>
        <div class="sl-view-modal-container" id="applicationViewContainer">
            <div class="sl-view-modal-header">
                <div class="sl-view-modal-header-main">
                    <h2 id="applicationViewTitle">Application Details</h2>
                    <p id="applicationViewMeta" class="sl-view-modal-meta"></p>
                </div>
                <div class="sl-view-modal-header-actions">
                    <button type="button" class="sl-view-modal-icon-btn sl-view-modal-maximize-btn" id="applicationViewMaximize" title="Maximize" aria-label="Maximize">
                        <svg class="sl-modal-icon-maximize" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
                        <svg class="sl-modal-icon-restore" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" hidden><path d="M8 3v3a2 2 0 0 1-2 2H3"/><path d="M21 8h-3a2 2 0 0 1-2-2V3"/><path d="M3 16h3a2 2 0 0 1 2 2v3"/><path d="M16 21v-3a2 2 0 0 1 2-2h3"/></svg>
                    </button>
                    <button type="button" class="sl-view-modal-icon-btn sl-view-modal-close" id="applicationViewClose" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
            <div class="sl-view-modal-body">
                <section class="sl-view-section">
                    <h3 class="sl-view-section-title">Personal Information</h3>
                    <div id="applicationViewPersonalInfo" class="sl-view-personal-grid"></div>
                </section>
                <section class="sl-view-section">
                    <h3 class="sl-view-section-title">Application Answers</h3>
                    <div id="applicationViewAnswers" class="sl-view-answers"></div>
                </section>
                <div id="applicationViewCancelledInfo" class="sl-view-cancelled-info" hidden>
                    <h3>Cancellation Details</h3>
                    <p id="applicationViewCancelledType" class="sl-view-cancelled-type"></p>
                    <p id="applicationViewCancelledReason"></p>
                </div>
            </div>
        </div>
    </div>

    <div id="applicationCancelModal" class="sl-cancel-modal" hidden>
        <div class="sl-cancel-modal-overlay"></div>
        <div class="sl-cancel-modal-box" id="applicationCancelModalBox">
            <div class="sl-cancel-modal-header">
                <h3>Cancel Application</h3>
                <button type="button" class="sl-view-modal-icon-btn sl-view-modal-close" id="applicationCancelClose" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="sl-cancel-modal-body">
                <p class="sl-view-cancel-note">Please select a cancel type and provide a reason before confirming cancellation.</p>
                <label class="sl-view-cancel-label" for="applicationCancelType">Cancel Type</label>
                <select id="applicationCancelType" class="sl-view-cancel-select">
                    <option value="">Select cancel type</option>
                    <option value="Voluntary Withdrawal">Voluntary Withdrawal</option>
                    <option value="Incorrect Information">Incorrect Information</option>
                    <option value="Wrong Program Applied">Wrong Program Applied</option>
                    <option value="Other">Other</option>
                </select>
                <label class="sl-view-cancel-label" for="applicationCancelReason">Cancel Reason</label>
                <textarea id="applicationCancelReason" class="sl-view-cancel-input" rows="4" maxlength="500" placeholder="Type your reason for cancelling this application..."></textarea>
                <p id="applicationCancelCharCount" class="sl-view-cancel-char-count">0 / 500 characters</p>
                <p id="applicationCancelError" class="sl-view-cancel-error" hidden></p>
            </div>
            <div class="sl-cancel-modal-footer">
                <button type="button" class="sl-btn-action sl-btn-secondary" id="applicationCancelDismissBtn">Keep Application</button>
                <button type="button" class="sl-btn-action sl-btn-cancel" id="applicationCancelBtn">Confirm Cancel</button>
            </div>
        </div>
    </div>

    <script>
        window.__scheduleProgramId = @json($scheduleProgramId);
        window.__kkFieldLabels = @json($kkFieldLabels);
    </script>
</body>
</html>
