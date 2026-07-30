<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $report->title }} - SK Officials Portal</title>

    @vite([
        'app/Modules/Layout/css/header.css',
        'app/Modules/Layout/css/sidebar.css',
        'app/Modules/Program_Accomplishment/Assets/css/program-accomplishment.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">{{ $report->title }}</h1>
                <p class="page-subtitle">
                    Status:
                    <span class="status-badge status-{{ strtolower($report->accomplishment_status) }}">{{ $report->accomplishment_status }}</span>
                </p>
            </div>
            <div class="page-header-right">
                @if($report->isEditable())
                <a href="{{ route('program-accomplishment.edit', $report->id) }}" class="btn primary-btn">Edit</a>
                @endif
            </div>
        </section>

        <div class="detail-grid">
            <div class="detail-card">
                <h3>Program Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Program</span>
                    <span class="detail-value">{{ $report->program?->program_name ?? $report->program?->activity_name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Description</span>
                    <span class="detail-value">{{ $report->description ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="detail-card">
                <h3>Objectives</h3>
                <p class="detail-text">{{ $report->objectives ?? 'No objectives provided.' }}</p>
            </div>

            <div class="detail-card">
                <h3>Implementation Summary</h3>
                <p class="detail-text">{{ $report->implementation_summary ?? 'No summary provided.' }}</p>
            </div>

            <div class="detail-card">
                <h3>Timeline & Venue</h3>
                <div class="detail-row">
                    <span class="detail-label">Date Started</span>
                    <span class="detail-value">{{ $report->date_started?->format('F d, Y') ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date Completed</span>
                    <span class="detail-value">{{ $report->date_completed?->format('F d, Y') ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Venue</span>
                    <span class="detail-value">{{ $report->venue ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="detail-card">
                <h3>Participants</h3>
                <p class="detail-value detail-value-lg">{{ number_format($report->participants_count) }}</p>
            </div>

            <div class="detail-card">
                <h3>Budget Summary</h3>
                <div class="detail-row">
                    <span class="detail-label">Budget Allocated</span>
                    <span class="detail-value">&#8369;{{ number_format($report->budget_allocated, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Actual Expense</span>
                    <span class="detail-value">&#8369;{{ number_format($report->actual_expense, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Remaining Budget</span>
                    <span class="detail-value">&#8369;{{ number_format($report->remaining_budget, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Budget Utilization</span>
                    <span class="detail-value">{{ number_format($report->budget_utilization_percent, 2) }}%</span>
                </div>
            </div>

            <div class="detail-card">
                <h3>Person Responsible</h3>
                <p class="detail-value">{{ $report->person_responsible ?? 'N/A' }}</p>
            </div>

            @if($report->lessons_learned)
            <div class="detail-card">
                <h3>Lessons Learned</h3>
                <p class="detail-text">{{ $report->lessons_learned }}</p>
            </div>
            @endif

            @if($report->recommendations)
            <div class="detail-card">
                <h3>Recommendations</h3>
                <p class="detail-text">{{ $report->recommendations }}</p>
            </div>
            @endif

            @if($report->image_path)
            <div class="detail-card detail-card-full">
                <h3>Photo</h3>
                <div class="photo-gallery" style="grid-template-columns:1fr;">
                    <div class="gallery-item">
                        <a href="{{ $report->image_path }}" target="_blank">
                            <img src="{{ $report->image_path }}" alt="{{ $report->image_caption ?? 'Photo' }}" style="max-height:300px;width:auto;">
                        </a>
                        @if($report->image_caption)
                        <p class="gallery-caption">{{ $report->image_caption }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @if($report->file_path)
            <div class="detail-card detail-card-full">
                <h3>Document</h3>
                <div class="document-list">
                    <div class="document-item">
                        <span class="file-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        </span>
                        <a href="{{ $report->file_path }}" target="_blank" class="file-link">{{ $report->file_name }}</a>
                    </div>
                </div>
            </div>
            @endif

            @if($report->remarks)
            <div class="detail-card">
                <h3>Remarks</h3>
                <p class="detail-text">{{ $report->remarks }}</p>
            </div>
            @endif
        </div>

        <div class="detail-actions">
            @if($report->isEditable())
            <button type="button" class="btn primary-btn" id="submitReportBtn">Submit for Approval</button>
            <button type="button" class="btn-cancel" id="deleteReportBtn">Delete</button>
            @endif
        </div>
    </div>
</main>

@vite([
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
    'app/Modules/Program_Accomplishment/Assets/js/program-accomplishment.js',
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
<script>
    window.__REPORT__ = {!! json_encode($report->toArray()) !!};
</script>
</body>
</html>
