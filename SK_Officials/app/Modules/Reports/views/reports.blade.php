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
<body data-reports-make-url="{{ route('reports.make') }}">
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="reports-page">
    <section class="reports-page-header">
        <div>
            <h1 class="reports-page-title">SK Reports</h1>
            <p class="reports-page-subtitle">Create official SK documents, resolutions, and activity reports.</p>
        </div>
    </section>

    <nav class="reports-tab-bar" aria-label="Reports sections">
        <button type="button" class="reports-tab active" data-reports-tab="list">My Reports</button>
        <a href="{{ route('reports.make') }}" class="reports-tab reports-tab--link">Make Reports</a>
    </nav>

    {{-- My Reports --}}
    <section class="reports-panel" id="reportsPanelList">
        <div class="reports-list-toolbar">
            <input type="search" id="reportsSearchInput" class="reports-search" placeholder="Search reports..." aria-label="Search reports">
        </div>
        <div class="reports-table-wrap">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Paper</th>
                        <th>Date</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="reportsTableBody">
                    <tr>
                        <td colspan="5" class="reports-table-empty">No reports yet. Go to <strong>Make Reports</strong> to create one.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="reports-panel reports-panel--cta" id="reportsPanelMake">
        <p class="reports-cta-text">Create reports with CKEditor 5 — rich formatting, PDF/Word export, comments, and track changes.</p>
        <a href="{{ route('reports.make') }}" class="reports-btn reports-btn-primary">Open Make a Report</a>
    </section>
</div>
</main>

<div class="reports-toast" id="reportsToast" hidden></div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Reports/assets/js/reports.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
