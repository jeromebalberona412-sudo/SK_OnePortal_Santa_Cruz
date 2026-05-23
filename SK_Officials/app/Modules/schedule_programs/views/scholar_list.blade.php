<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholar List - SK Officials Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/schedule_programs/assets/css/scholarship_application_form.css',
        'app/Modules/schedule_programs/assets/css/scholar_list.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="sl-page-container schol-page-container">

        @include('schedule_programs::partials.scholarship-page-top', [
            'activeTab' => 'list',
            'pageTitle' => 'Scholarship List',
            'pageSubtitle' => 'Passed scholars approved for SK scholarship programs.',
        ])

        <div class="schol-page-toolbar">
            <button type="button" id="slExportCsvBtn" class="sl-btn sl-btn-green">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export to CSV
            </button>
        </div>

        <!-- ── Scholar Table ── -->
        <div class="sl-table-card">
            <div class="sl-table-wrapper">
                <table class="sl-table">
                    <thead id="slTableHead">
                        <tr>
                            <th>FULL NAME
                                <div class="sl-col-hint">LN, FN, MN, Suffix</div>
                            </th>
                            <th>School</th>
                            <th>Year / Level</th>
                            <th>Program / Strand</th>
                            <th>Purpose</th>
                            <th>Date Approved</th>
                            <th>Status</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="slTableBody"></tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- ── Scholar View Modal ── -->
<div class="sl-modal-overlay" id="slViewModal" style="display:none;">
    <div class="sl-modal-box" id="slViewBox">
        <div class="sl-modal-header">
            <h3>Application Details — PDF View</h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="sl-modal-close" id="slViewMaximize" title="Maximize">□</button>
                <button type="button" class="sl-modal-close" id="slViewClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="sl-modal-body" id="slViewBody"></div>
    </div>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/schedule_programs/assets/js/scholar_list.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
