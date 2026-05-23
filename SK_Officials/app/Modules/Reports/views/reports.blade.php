<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK Reports - SK Officials Portal</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Reports/assets/css/reports.css',
        'app/Modules/schedule_programs/assets/css/sk-report-editor.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>
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
        <button type="button" class="reports-tab" data-reports-tab="make">Make Reports</button>
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

    {{-- Make Reports (MS Word style) --}}
    <section class="reports-panel reports-panel--hidden" id="reportsPanelMake">
        <div class="word-editor-meta">
            <select id="reportTypeSelect" class="reports-select scholar-report-select" aria-label="Report type">
                <option value="activity">Activity Report</option>
                <option value="resolution">SK Resolution</option>
                <option value="minutes">Meeting Minutes</option>
                <option value="financial">Financial Report</option>
                <option value="scholarship">Scholarship Program Report</option>
                <option value="custom">Custom Document</option>
            </select>
            <input type="text" id="reportTitleInput" class="reports-title-input scholar-report-title-input" placeholder="Document title..." maxlength="200">
            <button type="button" class="reports-btn reports-btn-save" id="reportSaveBtn">Save Report</button>
        </div>

        @include('schedule_programs::partials.word-report-shell', [
            'shellId' => 'reportWordShell',
            'editorId' => 'reportEditor',
            'pageId' => 'reportPage',
            'paperSelectId' => 'reportPaperSelect',
            'imageInputId' => 'reportImageInput',
            'cropBtnId' => 'reportCropBtn',
            'deleteImgBtnId' => 'reportDeleteImgBtn',
            'placeholder' => 'Start typing your SK report here, or click Generate for a template...',
            'showGenerate' => true,
            'generateId' => 'reportGenerateBtn',
            'showPrint' => true,
            'printId' => 'reportPrintBtn',
        ])
    </section>
</div>
</main>

<div class="reports-toast" id="reportsToast" hidden></div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/schedule_programs/assets/js/sk-report-editor.js',
    'app/Modules/Reports/assets/js/reports.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
