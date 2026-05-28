<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sport Application History - SK Officials Portal</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/schedule_programs/assets/css/scholarship_application_form.css',
        'app/Modules/schedule_programs/assets/css/sports_requests.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body data-sports-page="history">
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="sports-page-container schol-page-container">

    @include('schedule_programs::partials.sports-page-top', [
        'activeTab' => 'history',
        'pageTitle' => 'Sport Application History',
        'pageSubtitle' => 'View sports programs you have created and their application form details.',
    ])

    <div class="sports-table-card">
        <div class="sports-table-wrap">
            <table class="sports-table sports-history-table">
                <thead>
                    <tr>
                        <th>Program Name</th>
                        <th>Committee Head</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Questions</th>
                        <th>Date Created</th>
                        <th>Time Created</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="createdProgramsTableBody">
                    <tr class="sports-empty-row">
                        <td colspan="10" style="text-align:center;padding:32px;color:#6b7280;font-size:13px;">No sports programs created yet.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
</main>

@include('schedule_programs::partials.sports-program-view-modal')

<div class="sports-toast" id="sportsToast" style="display:none;">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="sportsToastMsg"></span>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/schedule_programs/assets/js/sports_requests.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
