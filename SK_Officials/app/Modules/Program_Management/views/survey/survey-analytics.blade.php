@extends('Program_Management::survey._layout')

@section('survey_actions')
    <button type="button" class="schol-btn schol-btn-save" id="btnExportAnalytics">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Export Analytics CSV
    </button>
@endsection

@section('survey_content')
    <div class="survey-panel survey-analytics-panel">
        <div class="analytics-stats-row" id="analyticsStatsRow"></div>

        <div class="analytics-toolbar-pro">
            <div class="analytics-toolbar-field analytics-toolbar-field--survey">
                <label class="analytics-toolbar-label" for="analyticsSurveyFilter">Survey</label>
                <select id="analyticsSurveyFilter" class="schol-input analytics-toolbar-input" aria-label="Select survey">
                    <option value="">Select survey…</option>
                </select>
            </div>
            <div class="analytics-toolbar-field analytics-toolbar-field--dates">
                <label class="analytics-toolbar-label">Date range</label>
                <div class="analytics-date-range">
                    <input type="date" id="analyticsDateFrom" class="schol-input analytics-toolbar-input" aria-label="From date" title="From date">
                    <span class="analytics-date-sep">to</span>
                    <input type="date" id="analyticsDateTo" class="schol-input analytics-toolbar-input" aria-label="To date" title="To date">
                </div>
            </div>
        </div>

        <div id="analyticsQuestionsContainer" class="analytics-container">
            <p class="survey-empty-hint">Select a survey above to view response distribution, bar charts, and pie charts per question.</p>
        </div>
    </div>
@endsection
