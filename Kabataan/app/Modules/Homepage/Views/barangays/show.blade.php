@extends('homepage::layout')

@section('title', $barangay->name . ' ABYIP — SK OnePortal Kabataan')

@section('content')
<div class="barangay-abyip-page kabataan-page-section">
    <section class="abyip-detail-hero">
        <div class="container abyip-shell">
            <a href="{{ route('homepage.barangays') }}" class="abyip-back-link">← Back to all barangays</a>

            <div class="abyip-detail-header">
                <div class="abyip-header-text">
                    <span class="abyip-eyebrow">Barangay ABYIP</span>
                    <h1>{{ $barangay->name }}</h1>
                    @if ($abyip)
                        <p class="abyip-detail-subtitle">Calendar Year {{ $abyip->year }}</p>
                    @endif
                </div>

                @if ($abyip)
                    <button type="button" class="abyip-print-btn" onclick="window.print()">Print</button>
                @endif
            </div>

            @if ($abyip && ($abyip->status ?? '') === 'pending')
                <div class="abyip-pending-banner" role="note" aria-label="ABYIP status">
                    <svg class="abyip-pending-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span class="abyip-pending-text">This ABYIP is pending review and may be updated.</span>
                </div>
            @endif

            @if ($abyip === null)
                <div class="no-doc abyip-empty-state">
                    <h2>No ABYIP uploaded yet</h2>
                    <p>
                        {{ $barangay->name }} has not published an ABYIP document for public viewing yet.
                        Check back later or contact your barangay SK officials.
                    </p>
                </div>
            @else
                <div class="abyip-budget-cards">
                    <article class="abyip-budget-pill">
                        <span>Estimated Budget</span>
                        <strong>₱{{ number_format((float) $abyip->estimated_budget, 2) }}</strong>
                    </article>
                    <article class="abyip-budget-pill">
                        <span>SK Fund (10%)</span>
                        <strong>₱{{ number_format((float) $abyip->sk_fund, 2) }}</strong>
                    </article>
                    <article class="abyip-budget-pill">
                        <span>Total Expenditure</span>
                        <strong>₱{{ number_format((float) $abyip->total_expenditure, 2) }}</strong>
                    </article>
                </div>

                <div class="abyip-table-wrap" tabindex="0" aria-label="PPA table, scroll horizontally to view all columns">
                    <table class="abyip-table">
                        <thead>
                            <tr>
                                <th class="abyip-col-ppa">PPA</th>
                                <th class="abyip-col-description">Description</th>
                                <th class="abyip-col-expected">Expected Result</th>
                                <th class="abyip-col-indicator">Performance Indicator</th>
                                <th class="abyip-col-period">Period</th>
                                <th class="abyip-col-mooe">MOOE</th>
                                <th class="abyip-col-co">CO</th>
                                <th class="abyip-col-total">Total</th>
                                <th class="abyip-col-person">Person Responsible</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($abyip->items as $item)
                                @if ($item->row_type === 'section')
                                    <tr class="abyip-row-section">
                                        <td colspan="9">{{ $item->label }}</td>
                                    </tr>
                                @elseif ($item->row_type === 'subsection')
                                    <tr class="abyip-row-subsection">
                                        <td colspan="9">{{ $item->label }}</td>
                                    </tr>
                                @else
                                    <tr class="abyip-row-item">
                                        <td class="abyip-col-ppa abyip-cell">{{ $item->ppa ?: '—' }}</td>
                                        <td class="abyip-col-description abyip-cell abyip-longtext">{{ $item->description ?: '—' }}</td>
                                        <td class="abyip-col-expected abyip-cell abyip-longtext">{{ $item->expected_result ?: '—' }}</td>
                                        <td class="abyip-col-indicator abyip-cell abyip-longtext">{{ $item->performance_indicator ?: '—' }}</td>
                                        <td class="abyip-col-period abyip-cell">{{ $item->period ?: '—' }}</td>
                                        <td class="abyip-col-mooe abyip-cell abyip-num">₱{{ number_format((float) $item->mooe, 2) }}</td>
                                        <td class="abyip-col-co abyip-cell abyip-num">₱{{ number_format((float) $item->co, 2) }}</td>
                                        <td class="abyip-col-total abyip-cell abyip-num">₱{{ number_format((float) $item->total, 2) }}</td>
                                        <td class="abyip-col-person abyip-cell">{{ $item->person_responsible ?: '—' }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    <div class="abyip-scroll-hint" aria-hidden="true">
                        <span>Scroll horizontally →</span>
                    </div>
                </div>

                <div class="abyip-signatories">
                    <article>
                        <span>{{ $abyip->chairperson_title }}</span>
                        <strong>{{ $abyip->chairperson_name ?: 'Not yet on file' }}</strong>
                        <small>Prepared by</small>
                    </article>
                    <article>
                        <span>{{ $abyip->approved_by_title }}</span>
                        <strong>{{ $abyip->approved_by_name ?: 'Not yet on file' }}</strong>
                        <small>Approved by</small>
                    </article>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
