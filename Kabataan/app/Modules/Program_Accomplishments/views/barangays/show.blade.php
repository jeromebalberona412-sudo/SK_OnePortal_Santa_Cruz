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
<div class="barangay-accomplishments-page kabataan-page-section barangay-accomplishments-offset">
    <section class="accomplishments-detail-hero">
        <div class="container accomplishments-shell">
            <a href="{{ route('program_accomplishments.barangays') }}" class="accomplishments-back-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                Back to all barangays
            </a>

            <div class="accomplishments-detail-header">
                <div class="accomplishments-header-text">
                    <p class="accomplishments-kicker">Barangay Accomplishments</p>
                    <div class="accomplishments-detail-title-row">
                        @if (!empty($logoUrl))
                            <img src="{{ $logoUrl }}" alt="" class="accomplishments-detail-logo" loading="lazy">
                        @endif
                        <h1>{{ $barangay->name }}</h1>
                    </div>
                    @if ($accomplishment)
                        <p class="accomplishments-detail-subtitle">Calendar Year {{ $accomplishment->year }}</p>
                    @elseif ($programReports->isNotEmpty())
                        <p class="accomplishments-detail-subtitle">Published program accomplishments</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="accomplishments-detail-body">
        <div class="container accomplishments-shell">
            @if ($accomplishment && ($accomplishment->status ?? '') === 'pending')
                <div class="accomplishments-pending-banner" role="note" aria-label="Accomplishments status">
                    <svg class="accomplishments-pending-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span class="accomplishments-pending-text">This Accomplishments is pending review and may be updated.</span>
                </div>
            @endif

            @if ($accomplishment === null && $programReports->isEmpty())
                <div class="no-doc accomplishments-empty-state">
                    <h2>No Accomplishment uploaded yet</h2>
                    <p>
                        {{ $barangay->name }} has not published an Accomplishments document for public viewing yet.
                        Check back later or contact your barangay SK officials.
                    </p>
                </div>
            @endif

            @if ($programReports->isNotEmpty())
                <section class="program-report-grid" aria-label="Published program accomplishments">
                    <h2 class="program-report-heading">Program Accomplishments</h2>
                    @foreach ($programReports as $report)
                        @php
                            $programName = $report->program?->program_name ?? $report->title;
                            $completedDate = optional($report->actual_completion_date ?: $report->program?->end_date)->format('M d, Y');
                            $photos = $report->images ?? collect();
                            $docs = $report->documents ?? collect();
                        @endphp
                        <article class="program-report-card">
                            <div class="program-report-card-top">
                                <h3>{{ $programName }}</h3>
                                <span class="program-report-status">Completed</span>
                            </div>
                            <p class="program-report-meta">
                                {{ $barangay->name }}
                                @if ($completedDate)
                                    · {{ $completedDate }}
                                @endif
                            </p>
                            @if ($report->implementation_summary)
                                <p class="program-report-summary">{{ $report->implementation_summary }}</p>
                            @endif

                            <dl class="program-report-stats">
                                <div>
                                    <dt>Target beneficiaries</dt>
                                    <dd>{{ $report->target_beneficiaries ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt>Actual beneficiaries</dt>
                                    <dd>{{ $report->participants_count ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt>Approved budget</dt>
                                    <dd>₱{{ number_format($report->plannedBudget(), 2) }}</dd>
                                </div>
                                <div>
                                    <dt>Actual expenditure</dt>
                                    <dd>₱{{ number_format((float) $report->actual_expense, 2) }}</dd>
                                </div>
                                <div>
                                    <dt>Remaining</dt>
                                    <dd>₱{{ number_format($report->remainingBudget(), 2) }}</dd>
                                </div>
                            </dl>

                            @if ($report->actual_result)
                                <div class="program-report-block">
                                    <h4>Actual result</h4>
                                    <p>{{ $report->actual_result }}</p>
                                </div>
                            @endif

                            @if ($photos->isNotEmpty())
                                <div class="program-report-block">
                                    <h4>Photos</h4>
                                    <div class="program-report-photos">
                                        @foreach ($photos as $index => $image)
                                            <button
                                                type="button"
                                                class="program-report-photo"
                                                data-photo-src="{{ $image->secure_url ?: $image->image_url }}"
                                                data-photo-alt="{{ $image->display_name ?: 'Program photo' }}"
                                                aria-label="Preview {{ $image->display_name ?: 'photo' }}"
                                            >
                                                <img src="{{ $image->secure_url ?: $image->image_url }}" alt="{{ $image->display_name ?: 'Program photo' }}" loading="lazy">
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if ($docs->isNotEmpty())
                                <div class="program-report-block">
                                    <h4>Supporting documents</h4>
                                    <ul class="program-report-docs">
                                        @foreach ($docs as $document)
                                            <li>
                                                @if ($document->visibility === 'public' && $document->stored_path)
                                                    <a href="{{ asset('storage/'.$document->stored_path) }}" target="_blank" rel="noopener">{{ $document->original_name }}</a>
                                                @else
                                                    <span>{{ $document->original_name }}</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </section>
            @endif

            @if ($accomplishment)
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

<div class="program-photo-lightbox" id="programPhotoLightbox" hidden>
    <button type="button" class="program-photo-lightbox-close" id="programPhotoLightboxClose" aria-label="Close photo preview">×</button>
    <img src="" alt="" id="programPhotoLightboxImage">
    <p id="programPhotoLightboxCaption"></p>
</div>
@endsection
