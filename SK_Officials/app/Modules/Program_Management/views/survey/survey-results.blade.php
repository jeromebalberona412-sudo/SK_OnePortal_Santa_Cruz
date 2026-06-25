@extends('Program_Management::survey._layout')

@section('survey_actions')
    <button type="button" class="schol-btn schol-btn-save" id="btnExportResults">Export Response CSV</button>
@endsection

@section('survey_content')
    <div class="survey-panel">
        <div class="survey-toolbar survey-toolbar-filters">
            <select id="responsesYearFilter" class="schol-input" aria-label="Filter by year">
                <option value="">All Years</option>
            </select>
            <select id="responsesTermFilter" class="schol-input" aria-label="Filter by SK term">
                <option value="">All Terms</option>
            </select>
            <input type="date" id="resultsDateFrom" class="schol-input" title="From date" aria-label="From date">
            <span class="date-separator">to</span>
            <input type="date" id="resultsDateTo" class="schol-input" title="To date" aria-label="To date">
            <input type="search" id="resultsSearch" class="schol-input survey-search" placeholder="Search full name or barangay…" aria-label="Search respondents">
        </div>
        <div class="saf-forms-table-card survey-table-card">
            <div class="saf-table-wrap">
                <table class="saf-forms-table survey-data-table">
                    <thead>
                        <tr>
                            <th class="survey-col-title">Full Name</th>
                            <th>Barangay</th>
                            <th>Answer</th>
                            <th>Date Submitted</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="surveyResultsTableBody">
                        <tr><td colspan="5" class="saf-table-empty">Loading responses…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
