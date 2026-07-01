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
        <i class="fas fa-arrow-left"></i>
        <span>Back to All Barangays</span>
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
                        <i class="fas fa-user"></i>
                        Submitted by: {{ $barangayData['submitted_by'] }}
                        @if(!empty($barangayData['submitted_by_role']))
                            <span class="bm-submitted-role">({{ $barangayData['submitted_by_role'] }})</span>
                        @endif
                    </p>
                @endif
            </div>
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

    <section class="bm-card bm-table-card">
        <div class="bm-table-header">
            <div class="bm-table-header-title">
                <h3><i class="fas fa-file-invoice-dollar"></i> Submitted ABYIP Reports</h3>
                <p>Review ABYIP submissions from this barangay</p>
            </div>
        </div>

        <div class="bm-table-filters">
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

        <div class="bm-table-wrap">
            <table class="bm-table bm-data-table bm-data-table--abyip" id="abyipTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Calendar Year</th>
                        <th>Date Submitted</th>
                        <th>Time Submitted</th>
                        <th>Submitted By</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barangayData['abyip']['reports'] ?? [] as $report)
                    <tr class="bm-abyip-row" data-year="{{ $report['fiscal_year'] ?? '' }}" data-status="{{ $report['status'] ?? 'pending' }}" data-id="{{ $report['id'] }}">
                        <td class="bm-cell-strong" data-label="Title">{{ $report['name'] ?? 'N/A' }}</td>
                        <td data-label="Calendar Year">{{ $report['fiscal_year'] ?? '—' }}</td>
                        <td data-label="Date Submitted">{{ !empty($report['date_submitted']) ? date('M d, Y', strtotime($report['date_submitted'])) : '—' }}</td>
                        <td data-label="Time Submitted">{{ !empty($report['date_submitted']) ? date('h:i A', strtotime($report['date_submitted'])) : '—' }}</td>
                        <td data-label="Submitted By">{{ $report['submitted_by'] ?? '—' }}</td>
                        <td data-label="Role">{{ $report['submitted_by_role'] ?? '—' }}</td>
                        <td data-label="Status">
                            <span class="bm-status-pill bm-status-{{ $report['status'] ?? 'pending' }}">
                                {{ ucfirst($report['status'] ?? 'pending') }}
                            </span>
                        </td>
                        <td data-label="Actions">
                            <div class="bm-row-actions">
                                @php $reportStatus = strtolower($report['status'] ?? 'pending'); @endphp
                                @if(in_array($reportStatus, ['pending', 'approved'], true))
                                <div class="bm-actions-menu" data-report-id="{{ $report['id'] }}" data-report-status="{{ $reportStatus }}">
                                    <button type="button" class="bm-actions-toggle" aria-label="More actions" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <div class="bm-actions-dropdown" hidden>
                                        <button type="button" class="bm-actions-item bm-actions-view" data-abyip-view="{{ $report['id'] }}">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        @if($reportStatus === 'pending')
                                            <button type="button" class="bm-actions-item bm-actions-approve" data-abyip-approve="{{ $report['id'] }}">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button type="button" class="bm-actions-item bm-actions-reject" data-abyip-reject="{{ $report['id'] }}">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @elseif($reportStatus === 'approved')
                                            <button type="button" class="bm-actions-item bm-actions-revoke" data-abyip-revoke="{{ $report['id'] }}">
                                                <i class="fas fa-undo"></i> Revoke
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                @else
                                <button type="button" class="bm-view-btn" data-abyip-view="{{ $report['id'] }}">View</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="bm-empty-row-tr">
                        <td colspan="8" class="bm-empty-row">
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
                    <span class="meta-label">Calendar Year</span>
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
                    <span class="meta-label">Role</span>
                    <span class="meta-value" id="modalSubmittedRole">-</span>
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
            <div class="modal-actions modal-actions-view-only" id="modalActions">
                <button type="button" class="action-btn cancel-btn" onclick="closeViewModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="approveModal" class="view-modal bm-confirm-modal">
    <div class="view-modal-content bm-confirm-modal-content">
        <div class="view-modal-header">
            <h3 class="view-modal-title">Approve ABYIP Submission</h3>
            <div class="view-modal-controls">
                <button type="button" class="view-modal-control-btn" onclick="closeApproveModal()" title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="view-modal-body">
            <p class="form-help">Are you sure you want to approve this ABYIP submission? This action will mark the report as <strong>Approved</strong>.</p>
            <div class="form-actions">
                <button type="button" class="form-btn cancel-btn" onclick="closeApproveModal()">Cancel</button>
                <button type="button" class="form-btn submit-btn approve-submit-btn" onclick="confirmApproval()">Confirm Approval</button>
            </div>
        </div>
    </div>
</div>

<div id="rejectModal" class="view-modal bm-reject-modal">
    <div class="view-modal-content bm-reject-modal-content">
        <div class="view-modal-header">
            <h3 class="view-modal-title">Reject ABYIP Submission</h3>
            <div class="view-modal-controls">
                <button type="button" class="view-modal-control-btn" onclick="closeRejectModal()" title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="view-modal-body">
            <div class="rejection-form" id="rejectForm">
                <p class="form-help">Select a rejection reason below.</p>
                <div class="form-group">
                    <label>Rejection Reason <span class="required">*</span></label>
                    <div class="revoke-reason-options">
                        <label class="revoke-check"><input type="checkbox" id="rejectReasonWrongPdf"> Wrong PDF file</label>
                        <label class="revoke-check"><input type="checkbox" id="rejectReasonOther"> Other</label>
                    </div>
                </div>
                <div class="form-group" id="rejectReasonFieldWrap" style="display:none;">
                    <label for="abyipRejectReason">Other Reason <span class="required">*</span></label>
                    <textarea id="abyipRejectReason" class="form-control revoke-reason-input" rows="3" maxlength="100" placeholder="Explain the rejection reason..."></textarea>
                    <span class="error-message" id="rejectReasonError"></span>
                </div>
                <div class="form-actions">
                    <button type="button" class="form-btn cancel-btn" onclick="closeRejectModal()">Cancel</button>
                    <button type="button" class="form-btn submit-btn reject-submit-btn" onclick="submitRejection()">Submit Rejection</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="revokeModal" class="view-modal bm-revoke-modal">
    <div class="view-modal-content bm-revoke-modal-content">
        <div class="view-modal-header">
            <h3 class="view-modal-title">Revoke ABYIP Approval</h3>
            <div class="view-modal-controls">
                <button type="button" class="view-modal-control-btn" onclick="closeRevokeModal()" title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="view-modal-body">
            <div class="rejection-form" id="revokeForm">
                <p class="form-help">Type <strong>Confirm</strong> below to continue. The submission will return to <strong>Pending</strong> status.</p>
                <div class="form-group">
                    <label for="abyipRevokeConfirm">Confirmation <span class="required">*</span></label>
                    <input type="text" id="abyipRevokeConfirm" class="form-control" maxlength="20" placeholder="Confirm">
                    <span class="error-message" id="revokeConfirmError"></span>
                </div>
                <div class="form-group">
                    <label>Reason</label>
                    <div class="revoke-reason-options">
                        <label class="revoke-check"><input type="checkbox" id="revokeReasonAccidental"> Accidentally approved</label>
                        <label class="revoke-check"><input type="checkbox" id="revokeReasonOther"> Other</label>
                    </div>
                </div>
                <div class="form-group" id="revokeReasonFieldWrap" style="display:none;">
                    <label for="abyipRevokeReason">Revoke Reason <span class="required">*</span></label>
                    <textarea id="abyipRevokeReason" class="form-control revoke-reason-input" rows="3" maxlength="100" placeholder="Explain why this approval is being revoked..."></textarea>
                    <span class="error-message" id="revokeReasonError"></span>
                </div>
                <div class="form-actions">
                    <button type="button" class="form-btn cancel-btn" onclick="closeRevokeModal()">Cancel</button>
                    <button type="button" class="form-btn submit-btn revoke-submit-btn" onclick="submitRevocation()">Revoke Approval</button>
                </div>
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
            revokeUrl: @json(url('/api/barangay-abyip/__ID__/revoke')),
            csrfToken: @json(csrf_token()),
            abyipReports: @json($barangayData['abyip']['reports'] ?? []),
        };
    </script>
    <script src="{{ url('/modules/barangay-monitoring/js/barangay-monitoring-show.js') }}?v={{ time() }}"></script>
@endpush
