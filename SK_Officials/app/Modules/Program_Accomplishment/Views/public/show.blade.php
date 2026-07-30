<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report->title }} - SK Programs</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1f2937; background: #f9fafb; }
        .container { max-width: 1000px; margin: 0 auto; padding: 2rem 1rem; }
        h1 { font-size: 1.75rem; margin-bottom: 0.25rem; }
        .subtitle { color: #6b7280; margin-bottom: 2rem; }
        .status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; background: #d1fae5; color: #065f46; }
        .card { background: #fff; border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
        .card h2 { font-size: 1.15rem; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }
        .label { color: #6b7280; font-size: 0.85rem; }
        .value { font-weight: 600; font-size: 1.05rem; }
        .value-lg { font-size: 1.5rem; color: #2563eb; }
        .gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
        .gallery img { width: 100%; height: 180px; object-fit: cover; border-radius: 0.5rem; cursor: pointer; transition: transform 0.2s; }
        .gallery img:hover { transform: scale(1.03); }
        .files { list-style: none; }
        .files li { padding: 0.5rem 0; border-bottom: 1px solid #f3f4f6; }
        .files a { color: #2563eb; text-decoration: none; }
        .files a:hover { text-decoration: underline; }
        .text-block { white-space: pre-wrap; color: #374151; }
        @media (max-width: 768px) { .grid, .grid-3 { grid-template-columns: 1fr; } }
        .back-link { display: inline-block; margin-bottom: 1.5rem; color: #2563eb; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ url('/') }}" class="back-link">&larr; Back to Homepage</a>

        <h1>{{ $report->title }}</h1>
        <p class="subtitle">
            <span class="status-badge">{{ $report->accomplishment_status }}</span>
            &middot; Completed {{ $report->date_completed?->format('F d, Y') }}
        </p>

        <div class="card">
            <h2>Program Information</h2>
            <div class="grid">
                <div>
                    <p class="label">Program</p>
                    <p class="value">{{ $report->program?->program_name ?? $report->program?->activity_name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="label">Venue</p>
                    <p class="value">{{ $report->venue ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="label">Date Started</p>
                    <p class="value">{{ $report->date_started?->format('F d, Y') ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="label">Date Completed</p>
                    <p class="value">{{ $report->date_completed?->format('F d, Y') }}</p>
                </div>
            </div>
        </div>

        @if($report->objectives)
        <div class="card">
            <h2>Objectives</h2>
            <p class="text-block">{{ $report->objectives }}</p>
        </div>
        @endif

        @if($report->description)
        <div class="card">
            <h2>Description</h2>
            <p class="text-block">{{ $report->description }}</p>
        </div>
        @endif

        @if($report->implementation_summary)
        <div class="card">
            <h2>Implementation Summary</h2>
            <p class="text-block">{{ $report->implementation_summary }}</p>
        </div>
        @endif

        <div class="card">
            <h2>Budget Summary</h2>
            <div class="grid-3">
                <div>
                    <p class="label">Allocated</p>
                    <p class="value">&#8369;{{ number_format($report->budget_allocated, 2) }}</p>
                </div>
                <div>
                    <p class="label">Actual Expense</p>
                    <p class="value">&#8369;{{ number_format($report->actual_expense, 2) }}</p>
                </div>
                <div>
                    <p class="label">Remaining</p>
                    <p class="value">&#8369;{{ number_format($report->remaining_budget, 2) }}</p>
                </div>
            </div>
            <div style="margin-top:1rem;">
                <p class="label">Budget Utilization: {{ number_format($report->budget_utilization_percent, 2) }}%</p>
                <div style="background:#e5e7eb;border-radius:9999px;height:8px;margin-top:0.25rem;overflow:hidden;">
                    <div style="width:{{ min(100, $report->budget_utilization_percent) }}%;background:#2563eb;height:100%;border-radius:9999px;"></div>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Timeline</h2>
            <div class="grid">
                <div>
                    <p class="label">Participants</p>
                    <p class="value value-lg">{{ number_format($report->participants_count) }}</p>
                </div>
                <div>
                    <p class="label">Person Responsible</p>
                    <p class="value">{{ $report->person_responsible ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        @if($report->image_path)
        <div class="card">
            <h2>Photo</h2>
            <div class="gallery" style="grid-template-columns:1fr;">
                <div>
                    <a href="{{ $report->image_path }}" target="_blank">
                        <img src="{{ $report->image_path }}" alt="{{ $report->image_caption ?? 'Photo' }}" style="max-height:300px;width:auto;">
                    </a>
                    @if($report->image_caption)
                    <p style="font-size:0.85rem;color:#6b7280;margin-top:0.25rem;">{{ $report->image_caption }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if($report->file_path)
        <div class="card">
            <h2>Document</h2>
            <ul class="files">
                <li>
                    <a href="{{ $report->file_path }}" target="_blank">{{ $report->file_name }}</a>
                </li>
            </ul>
        </div>
        @endif

        @if($report->lessons_learned)
        <div class="card">
            <h2>Lessons Learned</h2>
            <p class="text-block">{{ $report->lessons_learned }}</p>
        </div>
        @endif

        @if($report->recommendations)
        <div class="card">
            <h2>Recommendations</h2>
            <p class="text-block">{{ $report->recommendations }}</p>
        </div>
        @endif

        @if($report->remarks)
        <div class="card">
            <h2>Remarks</h2>
            <p class="text-block">{{ $report->remarks }}</p>
        </div>
        @endif
    </div>
</body>
</html>
