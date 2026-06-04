@extends('Program_Management::survey._layout')

@section('survey_actions')
    <button type="button" class="schol-btn schol-btn-save" id="btnCreateSurvey">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Create Survey Form
    </button>
@endsection

@section('survey_content')
    <div class="survey-panel">
        <div class="survey-toolbar">
            <input type="search" id="formsSearch" class="schol-input survey-search" placeholder="Search by title or activity…" aria-label="Search surveys">
        </div>
        <div class="saf-forms-table-card survey-table-card">
            <div class="saf-table-wrap">
                <table class="saf-forms-table survey-data-table">
                    <thead>
                        <tr>
                            <th class="survey-col-title">Survey Title</th>
                            <th>Activity</th>
                            <th>Questions</th>
                            <th>Responses</th>
                            <th>Status</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="surveyFormsTableBody">
                        <tr><td colspan="6" class="saf-table-empty">Loading surveys…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
