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
                <span class="sl-status-badge sl-status-open">Open</span>
            </div>
            <div class="sl-card-body">
                <h3 class="sl-program-name">Scholarship Assistance Program 2026</h3>
                <p class="sl-program-description">Educational assistance program for qualified Kabataan members of Barangay Santa Cruz.</p>
                
                <div class="sl-info-grid">
                    <div class="sl-info-item">
                        <span class="sl-info-label">Application Period:</span>
                        <span class="sl-info-value">June 1, 2026 - June 30, 2026</span>
                    </div>
                    <div class="sl-info-item">
                        <span class="sl-info-label">Available Slots:</span>
                        <span class="sl-info-value">100</span>
                    </div>
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

        <!-- Application Status Card -->
        <div class="sl-card sl-card-status">
            <div class="sl-card-header">
                <h2 class="sl-card-title">Application Status</h2>
            </div>
            <div class="sl-card-body">
                <div class="sl-status-content" id="applicationStatusContent">
                    <div class="sl-status-item sl-status-not-applied">
                        <div class="sl-status-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        </div>
                        <div class="sl-status-text">
                            <h3 class="sl-status-heading">Not Yet Applied</h3>
                            <p class="sl-status-desc">You have not submitted an application for this scholarship program yet.</p>
                        </div>
                    </div>
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
</body>
</html>
