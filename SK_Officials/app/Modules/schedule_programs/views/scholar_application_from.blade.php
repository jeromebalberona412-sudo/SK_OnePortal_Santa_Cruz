<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Published Scholarship Forms - SK Officials Portal</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/schedule_programs/assets/css/scholarship_application_form.css',
        'app/Modules/schedule_programs/assets/css/sports_requests.css',
        'app/Modules/schedule_programs/assets/css/scholar_application_from.css',
        'app/Modules/schedule_programs/assets/css/scholar_report.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="schol-page-container saf-page-wrap">
    <a href="/schedule-programs" class="schol-back-top">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Back to Schedule Programs
    </a>

    @include('schedule_programs::partials.scholarship-tabs', ['activeTab' => 'form'])

    <section class="saf-page-header-row">
        <div class="saf-page-header-text">
            <h1 class="schol-page-title">Published Scholarship Forms</h1>
            <p class="schol-page-subtitle">Create Google Form–style questionnaires published for Kabataan scholarship applicants.</p>
        </div>
        <div class="saf-page-header-actions">
            <a href="{{ url('/reports/ckeditor?source=scholarship') }}" class="schol-btn saf-report-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Make Report
            </a>
            <button type="button" class="schol-btn schol-btn-save saf-open-form-btn" id="safOpenFormBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Make Scholar Application Form
            </button>
        </div>
    </section>

    <div class="saf-forms-table-card">
            <div class="saf-table-wrap">
                <table class="saf-forms-table">
                    <thead>
                        <tr>
                            <th>Form</th>
                            <th>Date</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="safFormsTableBody">
                        <tr>
                            <td colspan="3" class="saf-table-empty">No forms yet. Click <strong>Make Scholar Application Form</strong> to create one.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</main>

@include('schedule_programs::partials.scholar-form-builder-modal', ['formId' => $formId ?? ''])
<div class="sports-modal-overlay" id="safPreviewModal" style="display:none;">
    <div class="sports-modal-box" style="max-width:560px;">
        <div class="sports-modal-header">
            <h3>Form Preview</h3>
            <button type="button" class="sports-modal-close" id="safPreviewClose">&times;</button>
        </div>
        <div class="sports-modal-body" id="safPreviewBody"></div>
    </div>
</div>

<div class="sports-toast" id="safToast" style="display:none;"></div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/schedule_programs/assets/js/spfb-form-builder.js',
    'app/Modules/schedule_programs/assets/js/scholar_application_from.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
