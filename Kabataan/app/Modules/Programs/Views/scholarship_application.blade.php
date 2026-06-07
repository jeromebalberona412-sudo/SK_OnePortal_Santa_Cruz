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
<body class="sch-app-body kabataan-app-page">
    @include('layout::kabataan-header', ['showSearch' => false, 'pageBadge' => null])

    <div class="gf-container">
        <!-- Back Button -->
        <div class="gf-back-button">
            <a href="{{ route('scholarship.apply') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                <span>Back</span>
            </a>
        </div>

        <!-- Header Section -->
        <div class="gf-header">
            <div class="gf-banner">
                <div class="gf-banner-content">
                    <h1 class="gf-title">Scholarship Assistance Program 2026</h1>
                    <p class="gf-description">Provide educational assistance to qualified Kabataan members of Barangay Santa Cruz.</p>
                </div>
            </div>
            <div class="gf-info-bar">
                <div class="gf-info-item">
                    <span class="gf-info-label">Application Period:</span>
                    <span class="gf-info-value">June 1, 2026 - June 30, 2026</span>
                </div>
                <div class="gf-info-item">
                    <span class="gf-info-label">Status:</span>
                    <span class="gf-status-badge gf-status-open">Open</span>
                </div>
            </div>
        </div>

        <!-- KK Profiling Integration Section -->
        <div class="gf-card">
            <div class="gf-kk-notice">
                <p class="gf-kk-notice-text">Your KK Profiling information has been included in this scholarship application. The information below is automatically retrieved from your KK Profile and cannot be edited here.</p>
            </div>

            <div class="gf-kk-section">
                <div class="gf-section-header">
                    <h2 class="gf-section-title">KK Profiling Information</h2>
                    <span class="gf-badge gf-badge-autofill">Auto-Filled from KK Profile</span>
                </div>
                <div class="gf-kk-fields" id="kkProfileFieldsContainer">
                    <!-- KK Profile fields will be dynamically populated here -->
                </div>
            </div>
        </div>

        <!-- Scholarship Application Form -->
        <form id="scholarshipApplicationForm" class="gf-form" novalidate>
            <!-- Educational Information Section -->
            <div class="gf-card">
                <h2 class="gf-section-title">Educational Information</h2>

                <div class="gf-question">
                    <label class="gf-question-label">
                        School Attended <span class="gf-required">*</span>
                    </label>
                    <div class="gf-input-wrapper">
                        <input type="text" name="school_attended" class="gf-input" placeholder="Your answer" required>
                    </div>
                </div>

                <div class="gf-question">
                    <label class="gf-question-label">
                        Year Level and Course <span class="gf-required">*</span>
                    </label>
                    <div class="gf-input-wrapper">
                        <input type="text" name="year_level_course" class="gf-input" placeholder="Your answer" required>
                    </div>
                </div>

                <div class="gf-question">
                    <label class="gf-question-label">
                        Current Academic Status <span class="gf-required">*</span>
                    </label>
                    <div class="gf-select-wrapper">
                        <select name="academic_status" class="gf-select" required>
                            <option value="">Choose</option>
                            <option value="regular">Regular Student</option>
                            <option value="irregular">Irregular Student</option>
                            <option value="graduating">Graduating Student</option>
                        </select>
                    </div>
                </div>

                <div class="gf-question">
                    <label class="gf-question-label">
                        General Weighted Average (GWA) <span class="gf-required">*</span>
                    </label>
                    <div class="gf-input-wrapper">
                        <input type="text" name="gwa" class="gf-input" placeholder="Your answer" required>
                    </div>
                </div>
            </div>

            <!-- Family Information Section -->
            <div class="gf-card">
                <h2 class="gf-section-title">Family Information</h2>

                <div class="gf-question">
                    <label class="gf-question-label">
                        Parent/Guardian Full Name <span class="gf-required">*</span>
                    </label>
                    <div class="gf-input-wrapper">
                        <input type="text" name="parent_guardian_name" class="gf-input" placeholder="Your answer" required>
                    </div>
                </div>

                <div class="gf-question">
                    <label class="gf-question-label">
                        Parent/Guardian Occupation <span class="gf-required">*</span>
                    </label>
                    <div class="gf-input-wrapper">
                        <input type="text" name="parent_guardian_occupation" class="gf-input" placeholder="Your answer" required>
                    </div>
                </div>

                <div class="gf-question">
                    <label class="gf-question-label">
                        Estimated Monthly Family Income <span class="gf-required">*</span>
                    </label>
                    <div class="gf-select-wrapper">
                        <select name="monthly_income" class="gf-select" required>
                            <option value="">Choose</option>
                            <option value="below_5000">Below ₱5,000</option>
                            <option value="5000_10000">₱5,001 - ₱10,000</option>
                            <option value="10001_20000">₱10,001 - ₱20,000</option>
                            <option value="above_20000">Above ₱20,000</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Requirements Upload Section -->
            <div class="gf-card">
                <h2 class="gf-section-title">Requirements Upload</h2>

                <div class="gf-question">
                    <label class="gf-question-label">
                        Upload Certificate of Registration (COR) - PDF Only <span class="gf-required">*</span>
                    </label>
                    <div class="gf-file-upload">
                        <input type="file" name="cor_file" class="gf-file-input" accept=".pdf" required>
                        <div class="gf-file-drop-zone">
                            <svg class="gf-file-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/>
                                <line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            <p class="gf-file-text">Drag and drop PDF files here or click to browse</p>
                            <p class="gf-file-hint">Accepted: PDF only</p>
                        </div>
                        <div class="gf-file-preview" id="corPreview"></div>
                    </div>
                </div>

                <div class="gf-question">
                    <label class="gf-question-label">
                        Upload Copy of Grades - PDF Only <span class="gf-required">*</span>
                    </label>
                    <div class="gf-file-upload">
                        <input type="file" name="grades_file" class="gf-file-input" accept=".pdf" required>
                        <div class="gf-file-drop-zone">
                            <svg class="gf-file-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/>
                                <line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            <p class="gf-file-text">Drag and drop PDF files here or click to browse</p>
                            <p class="gf-file-hint">Accepted: PDF only</p>
                        </div>
                        <div class="gf-file-preview" id="gradesPreview"></div>
                    </div>
                </div>

            </div>

            <!-- Application Actions -->
            <div class="gf-actions">
                <button type="button" class="gf-btn gf-btn-cancel" id="cancelBtn">Cancel</button>
                <button type="submit" class="gf-btn gf-btn-submit" id="submitBtn">
                    <span class="gf-btn-label">Submit Application</span>
                    <span class="gf-btn-spinner" hidden></span>
                </button>
            </div>
        </form>
    </div>

    <!-- Success Modal -->
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
                <span class="gf-status-badge gf-status-pending">Pending Review</span>
            </div>
            <p class="gf-success-message">Your scholarship application has been submitted successfully and is currently awaiting review by the SK Officials.</p>
            <button type="button" class="gf-btn gf-btn-primary" id="closeSuccessModal">Close</button>
        </div>
    </div>
</body>
</html>
