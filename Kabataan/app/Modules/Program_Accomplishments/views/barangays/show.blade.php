@extends('program_accomplishments::layout')

@section('title', $barangay->name . ' Accomplishments — SK OnePortal Kabataan')

@push('styles')
    @vite([
        'app/Modules/Program_Accomplishments/assets/css/barangay-accomplishments.css',
    ])
@endpush

@push('scripts')
    @vite([
        'app/Modules/Program_Accomplishments/assets/js/barangay-accomplishments.js',
    ])
@endpush

@section('content')
@vite([
    'app/Modules/Program_Accomplishments/assets/css/barangay-accomplishments.css',
])
<div class="barangay-accomplishments-page kabataan-page-section barangay-accomplishments-offset">
    <section class="accomplishments-detail-hero">
        <div class="container accomplishments-shell">
            <a href="{{ route('program_accomplishments.barangays') }}" class="accomplishments-back-link">← Back to all barangays</a>

            <div class="accomplishments-detail-header">
                <div class="accomplishments-header-text">
                    <span class="accomplishments-eyebrow">Barangay Accomplishments</span>
                    <h1>{{ $barangay->name }}</h1>
                    @if ($accomplishment)
                        <p class="accomplishments-detail-subtitle">Calendar Year {{ $accomplishment->year }}</p>
                    @endif
                </div>

                @if ($accomplishment)
                    <button type="button" class="accomplishments-print-btn" onclick="window.print()">Print</button>
                @endif
            </div>

            @if ($accomplishment && ($accomplishment->status ?? '') === 'pending')
                <div class="accomplishments-pending-banner" role="note" aria-label="Accomplishments status">
                    <svg class="accomplishments-pending-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span class="accomplishments-pending-text">This Accomplishments is pending review and may be updated.</span>
                </div>
            @endif

            @if ($accomplishment === null)
                <div class="no-doc accomplishments-empty-state">
                    <h2>No Accomplishments uploaded yet</h2>
                    <p>
                        {{ $barangay->name }} has not published an Accomplishments document for public viewing yet.
                        Check back later or contact your barangay SK officials.
                    </p>
                </div>
            @else
                <div class="accomplishments-budget-cards">
                    <article class="accomplishments-budget-pill">
                        <span>Estimated Budget</span>
                        <strong>₱{{ number_format((float) $accomplishment->estimated_budget, 2) }}</strong>
                    </article>
                    <article class="accomplishments-budget-pill">
                        <span>SK Fund (10%)</span>
                        <strong>₱{{ number_format((float) $accomplishment->sk_fund, 2) }}</strong>
                    </article>
                    <article class="accomplishments-budget-pill">
                        <span>Total Expenditure</span>
                        <strong>₱{{ number_format((float) $accomplishment->total_expenditure, 2) }}</strong>
                    </article>
                </div>

                <div class="accomplishments-table-wrap" tabindex="0" aria-label="PPA table, scroll horizontally to view all columns">
                    <table class="accomplishments-table">
                        <thead>
                            <tr>
                                <th class="accomplishments-col-ppa">PPA</th>
                                <th class="accomplishments-col-description">Description</th>
                                <th class="accomplishments-col-expected">Expected Result</th>
                                <th class="accomplishments-col-indicator">Performance Indicator</th>
                                <th class="accomplishments-col-period">Period</th>
                                <th class="accomplishments-col-mooe">MOOE</th>
                                <th class="accomplishments-col-co">CO</th>
                                <th class="accomplishments-col-total">Total</th>
                                <th class="accomplishments-col-person">Person Responsible</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($accomplishment->items as $item)
                                @if ($item->row_type === 'section')
                                    <tr class="accomplishments-row-section">
                                        <td colspan="9">{{ $item->label }}</td>
                                    </tr>
                                @elseif ($item->row_type === 'subsection')
                                    <tr class="accomplishments-row-subsection">
                                        <td colspan="9">{{ $item->label }}</td>
                                    </tr>
                                @else
                                    <tr class="accomplishments-row-item">
                                        <td class="accomplishments-col-ppa accomplishments-cell">{{ $item->ppa ?: '—' }}</td>
                                        <td class="accomplishments-col-description accomplishments-cell accomplishments-longtext">{{ $item->description ?: '—' }}</td>
                                        <td class="accomplishments-col-expected accomplishments-cell accomplishments-longtext">{{ $item->expected_result ?: '—' }}</td>
                                        <td class="accomplishments-col-indicator accomplishments-cell accomplishments-longtext">{{ $item->performance_indicator ?: '—' }}</td>
                                        <td class="accomplishments-col-period accomplishments-cell">{{ $item->period ?: '—' }}</td>
                                        <td class="accomplishments-col-mooe accomplishments-cell accomplishments-num">₱{{ number_format((float) $item->mooe, 2) }}</td>
                                        <td class="accomplishments-col-co accomplishments-cell accomplishments-num">₱{{ number_format((float) $item->co, 2) }}</td>
                                        <td class="accomplishments-col-total accomplishments-cell accomplishments-num">₱{{ number_format((float) $item->total, 2) }}</td>
                                        <td class="accomplishments-col-person accomplishments-cell">{{ $item->person_responsible ?: '—' }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    <div class="accomplishments-scroll-hint" aria-hidden="true">
                        <span>Scroll horizontally →</span>
                    </div>
                </div>

                <div class="accomplishments-signatories">
                    <article>
                        <span>{{ $accomplishment->chairperson_title }}</span>
                        <strong>{{ $accomplishment->chairperson_name ?: 'Not yet on file' }}</strong>
                        <small>Prepared by</small>
                    </article>
                    <article>
                        <span>{{ $accomplishment->approved_by_title }}</span>
                        <strong>{{ $accomplishment->approved_by_name ?: 'Not yet on file' }}</strong>
                        <small>Approved by</small>
                    </article>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
