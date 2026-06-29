@extends('layout::app')

@section('title', ($barangayData['name'] ?? 'Barangay') . ' - Barangay Monitoring')

@push('main-class')
    bm-main
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ url('/modules/barangay-monitoring/css/barangay-monitoring.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ url('/modules/barangay-abyip/css/barangay_abyip.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="bm-container bm-show-page" id="bmShowApp"
     data-barangay-slug="{{ $barangayData['slug'] }}"
     data-barangay-name="{{ $barangayData['name'] }}">

    <a class="bm-back-link" href="{{ route('barangay-monitoring') }}">
        <i class="fas fa-arrow-left"></i> Back to All Barangays
    </a>

    <header class="bm-show-hero">
        <div class="bm-show-hero-brand">
            <div class="bm-show-hero-logo">
                @if(!empty($barangayData['logo_url']))
                    <img src="{{ $barangayData['logo_url'] }}" alt="{{ $barangayData['name'] }} logo" onerror="this.hidden=true;this.nextElementSibling.hidden=false;">
                @endif
                <span class="bm-show-hero-logo-fallback" @if(!empty($barangayData['logo_url'])) hidden @endif>{{ strtoupper(mb_substr($barangayData['name'], 0, 1)) }}</span>
            </div>
            <div class="bm-show-hero-main">
                <h1 class="bm-show-title">{{ $barangayData['name'] }}</h1>
                <p class="bm-show-subtitle">
                    <i class="fas fa-map-marker-alt"></i>
                    {{ $barangayData['name'] }}, {{ $barangayData['municipality'] }}
                </p>
                @if(!empty($barangayData['submitted_by']))
                    <p class="bm-show-submitted-by">
                        <i class="fas fa-user"></i> Submitted by: {{ $barangayData['submitted_by'] }}
                    </p>
                @endif
            </div>
        </div>
        <div class="bm-show-hero-status">
            <span class="bm-show-status-label">ABYIP Status</span>
            <span class="bm-compliance-badge bm-compliance-{{ $barangayData['compliance_status'] }}">
                @if($barangayData['compliance_status'] === 'compliant')
                    <i class="fas fa-check-circle"></i> Compliant
                @elseif($barangayData['compliance_status'] === 'pending')
                    <i class="fas fa-clock"></i> Pending
                @else
                    <i class="fas fa-times-circle"></i> Non-Compliant
                @endif
            </span>
        </div>
    </header>

    @if(!empty($barangayData['warnings']))
        @foreach($barangayData['warnings'] as $warning)
        <div class="bm-alert bm-alert-{{ $warning['type'] }}" role="alert">
            <div class="bm-alert-icon">
                <i class="fas fa-{{ $warning['type'] === 'critical' ? 'exclamation-triangle' : 'exclamation-circle' }}"></i>
            </div>
            <div class="bm-alert-content">
                <strong>{{ $warning['title'] }}</strong>
                <p>{{ $warning['message'] }}</p>
            </div>
        </div>
        @endforeach
    @endif

    @if(!empty($barangayData['abyip_schedule']))
        @php $schedule = $barangayData['abyip_schedule']; @endphp
        <section class="bm-card bm-schedule-banner">
            <div class="bm-card-head">
                <h3><i class="fas fa-calendar-alt"></i> {{ $schedule['title'] ?? 'ABYIP Submission' }}</h3>
                <span class="bm-schedule-status">{{ $schedule['status_label'] ?? '—' }}</span>
            </div>
            <div class="bm-schedule-grid">
                <div class="bm-schedule-item">
                    <p class="bm-schedule-label">Start</p>
                    <p class="bm-schedule-value">{{ $schedule['date_start'] ?? '—' }}</p>
                </div>
                <div class="bm-schedule-item">
                    <p class="bm-schedule-label">Deadline</p>
                    <p class="bm-schedule-value">{{ $schedule['deadline'] ?? '—' }}</p>
                </div>
                <div class="bm-schedule-item">
                    <p class="bm-schedule-label">Original Deadline</p>
                    <p class="bm-schedule-value">{{ $schedule['original_deadline'] ?? '—' }}</p>
                </div>
            </div>
        </section>
    @endif

    <div class="bm-tab-bar" role="tablist">
        <button type="button" class="bm-tab-btn active" data-tab="abyip" id="tab-abyip" role="tab" aria-selected="true">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Barangay ABYIP</span>
        </button>
        <button type="button" class="bm-tab-btn" data-tab="accomplishment" id="tab-accomplishment" role="tab" aria-selected="false">
            <i class="fas fa-trophy"></i>
            <span>Accomplishment</span>
        </button>
    </div>

    <div id="section-abyip" class="bm-tab-panel" role="tabpanel">
        <section class="bm-card bm-table-card">
            <div class="bm-table-toolbar">
                <div class="bm-table-toolbar-title">
                    <h3><i class="fas fa-file-invoice-dollar"></i> Submitted ABYIP Reports</h3>
                    <p>Review ABYIP submissions from this barangay</p>
                </div>
                <div class="bm-table-toolbar-filters">
                    <div class="bm-search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="search" id="abyipSearchInput" placeholder="Search reports..." aria-label="Search ABYIP reports">
                    </div>
                    <select id="abyipFilterYear" aria-label="Filter by year">
                        <option value="all">All Years</option>
                        @foreach(collect($barangayData['abyip']['reports'] ?? [])->pluck('fiscal_year')->unique()->sortDesc() as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                    <select id="abyipFilterStatus" aria-label="Filter by status">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>
            <div class="bm-table-wrap">
                <table class="bm-table bm-data-table bm-data-table--abyip" id="abyipTable">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Fiscal Year</th>
                            <th>Date Submitted</th>
                            <th>Time Submitted</th>
                            <th>Submitted By</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($barangayData['abyip']['reports'] ?? [] as $report)
                        <tr class="bm-abyip-row" data-year="{{ $report['fiscal_year'] ?? '' }}" data-status="{{ $report['status'] ?? 'pending' }}" data-id="{{ $report['id'] }}">
                            <td class="bm-cell-strong" data-label="Title">{{ $report['name'] ?? 'N/A' }}</td>
                            <td data-label="Fiscal Year">{{ $report['fiscal_year'] ?? '—' }}</td>
                            <td data-label="Date Submitted">{{ !empty($report['date_submitted']) ? date('M d, Y', strtotime($report['date_submitted'])) : '—' }}</td>
                            <td data-label="Time Submitted">{{ !empty($report['date_submitted']) ? date('h:i A', strtotime($report['date_submitted'])) : '—' }}</td>
                            <td data-label="Submitted By">{{ $report['submitted_by'] ?? '—' }}</td>
                            <td data-label="Status">
                                <span class="bm-status-pill bm-status-{{ $report['status'] ?? 'pending' }}">
                                    {{ ucfirst($report['status'] ?? 'pending') }}
                                </span>
                            </td>
                            <td data-label="Actions">
                                <button type="button" class="bm-view-btn" data-abyip-view="{{ $report['id'] }}">View</button>
                            </td>
                        </tr>
                        @empty
                        <tr class="bm-empty-row-tr">
                            <td colspan="7" class="bm-empty-row">
                                <i class="fas fa-inbox"></i>
                                <span>No ABYIP reports submitted yet</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div id="section-accomplishment" class="bm-tab-panel" role="tabpanel" hidden>
        <section class="bm-card bm-table-card">
            <div class="bm-table-toolbar">
                <div class="bm-table-toolbar-title">
                    <h3><i class="fas fa-trophy"></i> Approved Accomplishments</h3>
                    <p>Approved accomplishment reports from this barangay</p>
                </div>
                <div class="bm-table-toolbar-filters">
                    <div class="bm-search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="search" id="accomplishmentSearchInput" placeholder="Search programs..." aria-label="Search accomplishments">
                    </div>
                    <select id="accomplishmentFilterTerm" aria-label="Filter by term">
                        <option value="all">All Terms</option>
                        @foreach($barangayData['accomplishment_terms'] ?? [] as $term)
                            <option value="{{ $term }}">{{ $term }}</option>
                        @endforeach
                    </select>
                    <select id="accomplishmentFilterYear" aria-label="Filter by year">
                        <option value="all">All Years</option>
                        @foreach($barangayData['accomplishment_years'] ?? [] as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="bm-table-wrap">
                <table class="bm-table bm-data-table" id="accomplishmentTable">
                    <thead>
                        <tr>
                            <th>Program</th>
                            <th>Activity</th>
                            <th>Code</th>
                            <th>Term</th>
                            <th>Year</th>
                            <th>Date Approved</th>
                            <th>Report</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($barangayData['accomplishments'] ?? [] as $item)
                        <tr data-term="{{ $item['term'] ?? '' }}" data-year="{{ $item['year'] ?? '' }}">
                            <td class="bm-cell-strong" data-label="Program">{{ $item['title'] ?? 'N/A' }}</td>
                            <td data-label="Activity">{{ $item['activity'] ?? '—' }}</td>
                            <td data-label="Code">{{ $item['program_code'] ?? '—' }}</td>
                            <td data-label="Term">{{ $item['term'] ?? '—' }}</td>
                            <td data-label="Year">{{ $item['year'] ?? '—' }}</td>
                            <td data-label="Date Approved">{{ !empty($item['start_date']) ? date('M d, Y', strtotime($item['start_date'])) : '—' }}</td>
                            <td data-label="Report">
                                <a href="{{ $item['file_url'] ?? '#' }}" target="_blank" rel="noopener" class="bm-link-btn">
                                    <i class="fas fa-file-pdf"></i> View PDF
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr class="bm-empty-row-tr">
                            <td colspan="7" class="bm-empty-row">
                                <i class="fas fa-inbox"></i>
                                <span>No approved accomplishments yet</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<div id="viewModal" class="view-modal">
    <div class="view-modal-content">
        <div class="view-modal-header">
            <h3 class="view-modal-title">
                <i class="fas fa-file-invoice-dollar"></i>
                ABYIP Submission Details
            </h3>
            <div class="view-modal-controls">
                <button type="button" class="view-modal-control-btn" onclick="toggleFullscreen()" title="Toggle Fullscreen" id="fullscreenBtn">
                    <i class="fas fa-expand"></i>
                </button>
                <button type="button" class="view-modal-control-btn" onclick="closeViewModal()" title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="view-modal-body">
            <div class="submission-meta-grid">
                <div class="meta-item">
                    <span class="meta-label">Barangay</span>
                    <span class="meta-value" id="modalBarangay">{{ $barangayData['name'] }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Fiscal Year</span>
                    <span class="meta-value" id="modalFiscalYear">-</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Date Submitted</span>
                    <span class="meta-value" id="modalDateSubmitted">-</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Submitted Time</span>
                    <span class="meta-value" id="modalSubmittedTime">-</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Submitted By</span>
                    <span class="meta-value" id="modalSubmittedBy">-</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Status</span>
                    <span class="meta-value">
                        <span class="status-badge status-pending" id="modalStatus">Pending</span>
                    </span>
                </div>
                <div class="meta-item meta-item-full">
                    <span class="meta-label">Title</span>
                    <span class="meta-value" id="modalTitle">-</span>
                </div>
            </div>

            <div id="modalRejectionReason" class="modal-rejection-notice" style="display:none;"></div>

            <div class="submission-preview-section">
                <h4 class="preview-section-title">Uploaded ABYIP Document</h4>
                <div id="abyipPreviewMount" class="abyip-preview-mount">
                    <p class="preview-loading">Select View to load document preview.</p>
                </div>
            </div>
        </div>

        <div class="view-modal-footer" id="viewModalFooter">
            <div class="rejection-form" id="rejectForm" style="display:none;">
                <h4 class="form-title">Reject Submission</h4>
                <div class="form-group">
                    <label>Rejection Reason <span class="required">*</span></label>
                    <textarea id="abyipRejectReason" class="form-control" rows="4" maxlength="1000" placeholder="Provide a reason for rejection..."></textarea>
                    <span class="error-message" id="rejectReasonError"></span>
                </div>
                <div class="form-actions">
                    <button type="button" class="form-btn cancel-btn" onclick="hideRejectForm()">Cancel</button>
                    <button type="button" class="form-btn submit-btn reject-submit-btn" onclick="submitRejection()">Submit Rejection</button>
                </div>
            </div>

            <div class="modal-actions" id="modalActions">
                <button type="button" class="action-btn reject-btn" onclick="showRejectForm()">Reject</button>
                <button type="button" class="action-btn approve-btn" onclick="submitApproval()">Approve</button>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="toast">
    <div class="toast-content">
        <span class="toast-message"></span>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.barangayMonitoringShowConfig = {
            showUrl: @json(url('/api/barangay-abyip/__ID__')),
            approveUrl: @json(url('/api/barangay-abyip/__ID__/approve')),
            rejectUrl: @json(url('/api/barangay-abyip/__ID__/reject')),
            csrfToken: @json(csrf_token()),
            abyipReports: @json($barangayData['abyip']['reports'] ?? []),
        };
    </script>
    <script src="{{ url('/modules/barangay-monitoring/js/barangay-monitoring-show.js') }}?v={{ time() }}"></script>
@endpush
