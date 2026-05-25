<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK Reports - SK Officials Portal</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Reports/assets/css/reports.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body class="reports-body" data-reports-make-url="{{ route('reports.ckeditor') }}">
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="reports-page">
    <section class="reports-page-header">
        <div class="reports-page-header-inner">
            <div class="reports-page-header-text">
                <h1 class="reports-page-title">SK Reports</h1>
                <p class="reports-page-subtitle">Create official SK documents, resolutions, and activity reports.</p>
            </div>
            <div class="reports-header-actions">
                <div class="reports-search-wrap">
                    <label for="reportsSearchInput" class="reports-sr-only">Search reports</label>
                    <svg class="reports-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input type="search" id="reportsSearchInput" class="reports-search" placeholder="Search reports..." autocomplete="off">
                </div>
                <a href="{{ route('reports.ckeditor') }}" class="reports-btn reports-btn-make">Make Report</a>
            </div>
        </div>
    </section>

    <section class="reports-panel" id="reportsPanelList">
        <div class="reports-table-wrap">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Paper</th>
                        <th>Date</th>
                        <th>Time Created</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="reportsTableBody">
                    <tr>
                        <td colspan="6" class="reports-table-empty">No reports yet. Click <strong>Make Report</strong> to create one.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>
</main>

<div class="reports-toast" id="reportsToast" hidden></div>

<div class="reports-modal-backdrop" id="reportsViewModal" hidden>
    <div class="reports-modal reports-modal-lg" role="dialog" aria-labelledby="reportsViewTitle" aria-modal="true">
        <div class="reports-modal-head">
            <h2 id="reportsViewTitle" class="reports-modal-title">Report</h2>
            <button type="button" class="reports-modal-close" data-reports-close aria-label="Close">&times;</button>
        </div>
        <div class="reports-modal-meta" id="reportsViewMeta"></div>
        <div class="reports-modal-body reports-view-body" id="reportsViewBody"></div>
        <div class="reports-modal-foot">
            <a href="#" class="reports-btn reports-btn-edit" id="reportsViewEditBtn">Edit</a>
            <button type="button" class="reports-btn reports-btn-outline" data-reports-close>Close</button>
        </div>
    </div>
</div>

<div class="reports-modal-backdrop" id="reportsDeleteModal" hidden>
    <div class="reports-modal reports-modal-sm" role="alertdialog" aria-labelledby="reportsDeleteTitle" aria-modal="true">
        <div class="reports-delete-inner">
            <div class="reports-delete-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
            </div>
            <h2 id="reportsDeleteTitle" class="reports-delete-title">Delete this report?</h2>
            <p class="reports-delete-message" id="reportsDeleteMessage">This action cannot be undone.</p>
            <p class="reports-delete-report-name" id="reportsDeleteReportName"></p>
            <div class="reports-delete-actions">
                <button type="button" class="reports-btn reports-btn-outline" data-reports-close>Cancel</button>
                <button type="button" class="reports-btn reports-btn-danger" id="reportsConfirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Reports/assets/js/reports.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
