@extends('Program_Management::survey._layout')

@section('survey_actions')
    <button type="button" class="schol-btn schol-btn-outline" id="btnExportAnalytics">Export Analytics CSV</button>
@endsection

@section('survey_content')
    <div class="survey-panel">
        <div class="survey-stats-row" id="analyticsStatsRow"></div>
        <div class="survey-toolbar survey-toolbar-filters">
            <select id="analyticsSurveyFilter" class="schol-input" aria-label="Select survey">
                <option value="">Select survey…</option>
            </select>
            <input type="date" id="analyticsDateFrom" class="schol-input" aria-label="From date">
            <input type="date" id="analyticsDateTo" class="schol-input" aria-label="To date">
            <select id="analyticsQuestionFilter" class="schol-input" aria-label="Filter by question">
                <option value="">All questions</option>
            </select>
        </div>
        <div id="analyticsQuestionsContainer" class="analytics-container">
            <p class="survey-empty-hint">Select a survey above to view response distribution, bar charts, and pie charts per question.</p>
        </div>
    </div>
@endsection
