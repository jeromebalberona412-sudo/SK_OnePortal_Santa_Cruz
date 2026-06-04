@extends('Program_Management::survey._layout')

@section('survey_actions')
    <button type="button" class="schol-btn schol-btn-outline" id="btnExportResults">Export Results CSV</button>
@endsection

@section('survey_content')
    <div class="survey-panel">
        <div class="survey-toolbar survey-toolbar-filters">
            <select id="resultsSurveyFilter" class="schol-input" aria-label="Filter by survey">
                <option value="">All Surveys</option>
            </select>
            <input type="date" id="resultsDateFrom" class="schol-input" title="From date" aria-label="From date">
            <input type="date" id="resultsDateTo" class="schol-input" title="To date" aria-label="To date">
            <input type="search" id="resultsSearch" class="schol-input survey-search" placeholder="Search respondent or barangay…" aria-label="Search respondents">
        </div>
        <div class="saf-forms-table-card survey-table-card">
            <div class="saf-table-wrap">
                <table class="saf-forms-table survey-data-table">
                    <thead>
                        <tr>
                            <th class="survey-col-title">Respondent</th>
                            <th>Survey</th>
                            <th>Barangay</th>
                            <th>Answer</th>
                            <th>Date Submitted</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="surveyResultsTableBody">
                        <tr><td colspan="6" class="saf-table-empty">Loading responses…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
