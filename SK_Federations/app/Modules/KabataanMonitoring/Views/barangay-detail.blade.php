@extends('layout::app')

@section('title', $barangay . ' - Kabataan Monitoring - SK OnePortal')

@push('main-class')
    km-main
@endpush

@push('main-attributes')
    data-detail-base="{{ url('/kabataan-monitoring') }}"
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ url('/modules/kabataan-monitoring/css/kabataan-monitoring.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ url('/modules/kabataan-monitoring/css/kk-questionnaire-view.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="km-container km-barangay-detail-page">

    <section class="km-brgy-hero">
        <div class="km-brgy-hero-logo">
            @if(!empty($barangayLogoUrl))
                <img src="{{ $barangayLogoUrl }}" alt="{{ $barangay }} logo" onerror="this.hidden=true;this.nextElementSibling.hidden=false;">
            @endif
            <span class="km-brgy-hero-logo-fallback" @if(!empty($barangayLogoUrl)) hidden @endif>{{ strtoupper(mb_substr($barangay, 0, 1)) }}</span>
        </div>
        <div class="km-brgy-hero-copy">
            <p class="km-brgy-hero-eyebrow">{{ $barangay }}</p>
            <h1 class="km-brgy-hero-title">KKK Profiling Masterlist</h1>
            <p class="km-brgy-hero-subtitle">Youth profiling records for {{ $barangay }}</p>
        </div>
    </section>

    <section class="km-masterlist-top">
        <div class="km-masterlist-topbar">
            <div class="km-filter-row-inner km-filter-row-inner--compact">
                <select id="km-brgy-year-filter" class="km-select" aria-label="Filter by year">
                    <option value="all">All Years</option>
                    @foreach($registrationYears as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>

                <div class="km-search-group">
                    <input type="text" id="km-brgy-search" class="km-search-input" placeholder="Search by name, respondent #, purok...">
                    <button type="button" class="km-search-btn" onclick="performBarangaySearch()">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
        </div>
    </section>

    <div class="km-table-card">
        <div class="km-table-wrapper">
            <table class="km-table km-table--masterlist">
                <thead>
                    <tr>
                        <th>Respondent #</th>
                        <th>
                            FULLNAME
                            <div class="table-col-hint">LN, FN, MN, Suffix</div>
                        </th>
                        <th>Age</th>
                        <th>Barangay</th>
                        <th>Purok/Zone</th>
                        <th>Registered Voter</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="km-table-tbody">
                    <tr class="km-loading-row">
                        <td colspan="7">Loading records…</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p id="km-empty" class="km-empty km-empty--in-card" hidden>No profiles match your current filters.</p>

        <div class="km-table-footer pagination-footer" aria-label="Table pagination">
            <div class="pagination-footer-nav">
                <button type="button" class="pagination-arrow" id="km-prev-btn" disabled aria-label="Previous page">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="pagination-page-label">Page</span>
                <input type="number" class="pagination-page-input" id="km-page-input" value="1" min="1" aria-label="Current page">
                <span class="pagination-page-of">of <span id="km-total-pages">1</span></span>
                <button type="button" class="pagination-arrow" id="km-next-btn" disabled aria-label="Next page">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="pagination-footer-right">
                <select id="km-rows-per-page" class="pagination-rows-select" aria-label="Rows per page">
                    <option value="10" selected>10 rows</option>
                    <option value="25">25 rows</option>
                    <option value="50">50 rows</option>
                </select>
                <span class="pagination-record-count" id="km-pagination-text">0 records</span>
            </div>
        </div>
    </div>
</div>

<div class="km-kkp-modal" id="kmKKPModal">
    <div class="km-kkp-modal-overlay" onclick="closeKKPModal()"></div>
    <div class="km-kkp-modal-content">
        <div class="km-kkp-modal-header">
            <h2><i class="fas fa-file-alt"></i> KK Survey Questionnaire</h2>
            <button type="button" class="km-kkp-modal-close" onclick="closeKKPModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="km-kkp-modal-body kk-qs-body" id="kmKKPFormContainer">
            <p class="km-kkp-loading">Loading questionnaire...</p>
        </div>
        <div class="km-kkp-modal-footer">
            <button type="button" class="km-kkp-btn-close" onclick="closeKKPModal()">Close</button>
            <button type="button" class="km-kkp-btn-print" onclick="printKKPForm()"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.kmPageMode = 'barangay-detail';
        window.kmBarangay = @json($barangay);
        window.kmConfig = {
            dataUrl: @json(route('api.kabataan-monitoring.index')),
            questionnaireUrl: @json(url('/api/kabataan-monitoring/__ID__/questionnaire')),
        };
    </script>
    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script src="{{ url('/modules/kabataan-monitoring/js/kabataan-monitoring.js') }}?v={{ time() }}"></script>
@endpush
