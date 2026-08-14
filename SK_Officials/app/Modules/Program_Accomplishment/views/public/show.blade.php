<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report['program']['program_name'] ?? $report['title'] ?? 'Program Accomplishment' }}</title>
    @vite(['app/Modules/Program_Accomplishment/Assets/css/program-accomplishment.css'])
</head>
<body>
<main class="page-container" style="max-width: 920px; margin: 24px auto; padding: 0 16px;">
    <p>Program Accomplishment</p>
    <h1>{{ $report['program']['program_name'] ?? $report['title'] }}</h1>
    <p>{{ $report['program']['barangay'] ?? '' }} · Completed {{ $report['actual_completion_date'] ?? $report['program']['end_date'] ?? '' }}</p>

    <section class="pa-form-section">
        <h2 class="pa-section-title">Program Information</h2>
        <p>{{ $report['program']['description'] ?? '' }}</p>
        <p>Category: {{ $report['program']['category'] ?? $report['program']['program_type'] ?? '—' }}</p>
    </section>

    <section class="pa-form-section">
        <h2 class="pa-section-title">Accomplishment</h2>
        <p>{{ $report['implementation_summary'] ?? '' }}</p>
        @if (!empty($report['actual_result']))
            <p><strong>Actual result:</strong> {{ $report['actual_result'] }}</p>
        @endif
        <p>Beneficiaries — Target: {{ $report['target_beneficiaries'] ?? '—' }} · Actual: {{ $report['participants_count'] ?? '—' }}</p>
    </section>

    <section class="pa-form-section">
        <h2 class="pa-section-title">Financial Information</h2>
        <p>Approved Budget: ₱{{ number_format((float) ($report['approved_budget'] ?? 0), 2) }}</p>
        <p>Actual Expenditure: ₱{{ number_format((float) ($report['actual_expense'] ?? 0), 2) }}</p>
        <p>Remaining: ₱{{ number_format((float) ($report['remaining_budget'] ?? 0), 2) }}</p>
    </section>

    @if (!empty($report['images']))
        <section class="pa-form-section">
            <h2 class="pa-section-title">Photo Gallery</h2>
            <div class="pa-gallery">
                @foreach ($report['images'] as $image)
                    <div class="pa-gallery-item">
                        <img src="{{ $image['secure_url'] }}" alt="{{ $image['display_name'] ?? 'Photo' }}" loading="lazy">
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if (!empty($report['documents']))
        <section class="pa-form-section">
            <h2 class="pa-section-title">Public Supporting Documents</h2>
            <ul>
                @foreach ($report['documents'] as $document)
                    <li>
                        @if (!empty($document['public_url']))
                            <a href="{{ $document['public_url'] }}" target="_blank" rel="noopener">{{ $document['original_name'] }}</a>
                        @else
                            {{ $document['original_name'] }}
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</main>
</body>
</html>
