@extends('layout::app')

@section('title', 'Barangay Monitoring - SK Federation')

@push('main-class')
    bm-main
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ url('/modules/barangay-monitoring/css/barangay-monitoring.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="bm-container">
            <section class="bm-kpi-grid" aria-label="Monitoring summary">
                <article class="bm-kpi-card">
                    <div class="bm-kpi-icon bm-kpi-icon--blue"><i class="fas fa-map-marked-alt"></i></div>
                    <div class="bm-kpi-body">
                        <div class="bm-kpi-label">Total Barangay</div>
                        <div class="bm-kpi-value">{{ $stats['total_barangays'] }}</div>
                    </div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-icon bm-kpi-icon--indigo"><i class="fas fa-file-invoice-dollar"></i></div>
                    <div class="bm-kpi-body">
                        <div class="bm-kpi-label">ABYIP Submitted</div>
                        <div class="bm-kpi-value">{{ $stats['abyip_submitted_count'] }}</div>
                    </div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-icon bm-kpi-icon--green"><i class="fas fa-check-circle"></i></div>
                    <div class="bm-kpi-body">
                        <div class="bm-kpi-label">Compliance Rate</div>
                        <div class="bm-kpi-value">{{ $stats['compliance_rate'] }}%</div>
                    </div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-icon bm-kpi-icon--green"><i class="fas fa-thumbs-up"></i></div>
                    <div class="bm-kpi-body">
                        <div class="bm-kpi-label">Total Approved</div>
                        <div class="bm-kpi-value">{{ $stats['approved_count'] }}</div>
                    </div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-icon bm-kpi-icon--rose"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="bm-kpi-body">
                        <div class="bm-kpi-label">Non-Compliance Rate</div>
                        <div class="bm-kpi-value">{{ $stats['non_compliance_rate'] }}%</div>
                    </div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-icon bm-kpi-icon--amber"><i class="fas fa-times-circle"></i></div>
                    <div class="bm-kpi-body">
                        <div class="bm-kpi-label">Total Not Submitted</div>
                        <div class="bm-kpi-value">{{ $stats['not_submitted_count'] }}</div>
                    </div>
                </article>
            </section>

            <section class="bm-card bm-schedule-summary-card" id="abyipScheduleSection" aria-label="ABYIP submission schedule">
                <div class="bm-card-head bm-card-head-flex">
                    <div>
                        <h3><i class="fas fa-calendar-check"></i> ABYIP Submission Schedule</h3>
                        <p class="bm-schedule-summary-note">Set when barangay SK Officials may upload ABYIP documents.</p>
                    </div>
                    <div class="bm-schedule-actions">
                        @if(!empty($abyipSchedule))
                        <button type="button" class="bm-btn-schedule secondary" id="btnViewSchedule">
                            <i class="fas fa-eye"></i> View Schedule
                        </button>
                        <button type="button" class="bm-btn-schedule secondary" id="btnCreateSchedule">
                            <i class="fas fa-edit"></i> Edit Schedule
                        </button>
                        @elseif(!empty($canCreateAbyipSchedule))
                        <button type="button" class="bm-btn-schedule" id="btnCreateSchedule">
                            <i class="fas fa-plus"></i> Create Schedule
                        </button>
                        @else
                        <button type="button" class="bm-btn-schedule secondary" id="btnViewSchedule" disabled title="A schedule for this calendar year already exists.">
                            <i class="fas fa-eye"></i> View Schedule
                        </button>
                        @endif
                    </div>
                </div>
                <div class="bm-card-body">
                    @if(!empty($abyipSchedule))
                        <div class="bm-schedule-compact">
                            <span class="bm-schedule-status">{{ $abyipSchedule['status_label'] }}</span>
                            <p class="bm-schedule-compact-title">{{ $abyipSchedule['title'] }} · CY {{ $abyipSchedule['fiscal_year'] }}</p>
                            <p class="bm-schedule-compact-dates">
                                Open: {{ $abyipSchedule['date_start'] }} — Deadline: {{ $abyipSchedule['deadline'] }}
                            </p>
                        </div>
                    @else
                        <p class="bm-empty">No ABYIP submission schedule yet. Create one before barangay SK Officials can upload ABYIP.</p>
                    @endif
                </div>
            </section>

            <section class="bm-card" aria-label="All barangays list">
                <div class="bm-card-head">
                    <h3>All Barangays</h3>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <select id="bmFilterStatus" onchange="bmFilterBarangays()" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;color:#475569;background:#fff;cursor:pointer;">
                            <option value="all">All Status</option>
                            <option value="approved">Approved</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                            <option value="not_submitted">Not Submitted</option>
                        </select>
                        <select id="bmFilterBarangay" onchange="bmFilterBarangays()" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;color:#475569;background:#fff;cursor:pointer;">
                            <option value="all">All Barangays</option>
                            <option value="Alipit">Alipit</option>
                            <option value="Bagumbayan">Bagumbayan</option>
                            <option value="Calios">Calios</option>
                            <option value="Duhat">Duhat</option>
                            <option value="Gatid">Gatid</option>
                            <option value="Jasaan">Jasaan</option>
                            <option value="Labuin">Labuin</option>
                            <option value="Malinao">Malinao</option>
                            <option value="Oogong">Oogong</option>
                            <option value="Pagsawitan">Pagsawitan</option>
                            <option value="Palasan">Palasan</option>
                            <option value="Patimbao">Patimbao</option>
                            <option value="Poblacion I">Poblacion I</option>
                            <option value="Poblacion II">Poblacion II</option>
                            <option value="Poblacion III">Poblacion III</option>
                            <option value="Poblacion IV">Poblacion IV</option>
                            <option value="Poblacion V">Poblacion V</option>
                            <option value="San Jose">San Jose</option>
                            <option value="San Juan">San Juan</option>
                            <option value="San Pablo Norte">San Pablo Norte</option>
                            <option value="San Pablo Sur">San Pablo Sur</option>
                            <option value="Santisima Cruz">Santisima Cruz</option>
                            <option value="Santo Angel Central">Santo Angel Central</option>
                            <option value="Santo Angel Norte">Santo Angel Norte</option>
                            <option value="Santo Angel Sur">Santo Angel Sur</option>
                        </select>
                    </div>
                </div>
                <div class="bm-card-body">
                    <div class="bm-list-grid" id="bm-list-grid">
                        @foreach ($barangays as $barangay)
                            <a
                                href="{{ route('barangay-monitoring.show', ['barangay' => $barangay['slug']]) }}"
                                class="bm-list-item"
                                data-status="{{ $barangay['status'] }}"
                                data-name="{{ strtolower($barangay['name']) }}"
                                data-barangay="{{ strtolower($barangay['name']) }}"
                            >
                                <div class="bm-list-head">
                                    <div class="bm-list-brand">
                                        <div class="bm-list-logo">
                                            @if(!empty($barangay['logo_url']))
                                                <img src="{{ $barangay['logo_url'] }}" alt="" onerror="this.hidden=true;this.nextElementSibling.hidden=false;">
                                            @endif
                                            <span class="bm-list-logo-fallback" @if(!empty($barangay['logo_url'])) hidden @endif>{{ strtoupper(mb_substr($barangay['name'], 0, 1)) }}</span>
                                        </div>
                                        <h4>{{ $barangay['name'] }}</h4>
                                    </div>
                                    @php
                                        $statusLabel = match ($barangay['status'] ?? 'not_submitted') {
                                            'approved' => 'Approved',
                                            'pending' => 'Pending',
                                            'rejected' => 'Rejected',
                                            default => 'Not Submitted',
                                        };
                                    @endphp
                                    <span class="bm-status {{ $barangay['status'] }}">{{ $statusLabel }}</span>
                                </div>
                                @if(!empty($barangay['submitted_by']))
                                    <div class="bm-list-meta">
                                        <span><i class="fas fa-user"></i> Submitted by: {{ $barangay['submitted_by'] }}@if(!empty($barangay['submitted_by_role'])) <span class="bm-submitted-role">({{ $barangay['submitted_by_role'] }})</span>@endif</span>
                                    </div>
                                @endif
                                <div class="bm-list-foot">
                                    <span class="bm-link-cta">View full details <i class="fas fa-arrow-right"></i></span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <p class="bm-empty" id="bm-empty" hidden>No barangays match the current search/filter.</p>
                </div>
            </section>
        </div>

<div id="scheduleViewModal" class="bm-modal" hidden>
    <div class="bm-modal-backdrop" data-schedule-view-close></div>
    <div class="bm-modal-dialog bm-modal-lg">
        <div class="bm-modal-header">
            <h3>ABYIP Submission Schedule</h3>
            <button type="button" class="bm-modal-close" data-schedule-view-close>&times;</button>
        </div>
        <div class="bm-modal-body" id="scheduleViewModalBody">
            @if(!empty($abyipSchedule))
                <div class="bm-schedule-current">
                    <div class="bm-schedule-current-head">
                        <h4>{{ $abyipSchedule['title'] }}</h4>
                        <span class="bm-schedule-status">{{ $abyipSchedule['status_label'] }}</span>
                    </div>
                    <div class="bm-schedule-grid">
                        <div>
                            <p class="bm-schedule-label">Calendar Year</p>
                            <p class="bm-schedule-value">{{ $abyipSchedule['fiscal_year'] }}</p>
                        </div>
                        <div>
                            <p class="bm-schedule-label">Start</p>
                            <p class="bm-schedule-value">{{ $abyipSchedule['date_start'] }}</p>
                        </div>
                        <div>
                            <p class="bm-schedule-label">Deadline</p>
                            <p class="bm-schedule-value">{{ $abyipSchedule['deadline'] }}</p>
                        </div>
                        <div>
                            <p class="bm-schedule-label">Original Deadline</p>
                            <p class="bm-schedule-value">{{ $abyipSchedule['original_deadline'] }}</p>
                        </div>
                        <div>
                            <p class="bm-schedule-label">Created By</p>
                            <p class="bm-schedule-value">{{ $abyipSchedule['created_by'] }}@if(!empty($abyipSchedule['created_by_role'])) <span class="bm-schedule-role">({{ $abyipSchedule['created_by_role'] }})</span>@endif</p>
                        </div>
                    </div>
                    @if(!empty($abyipSchedule['histories']))
                        <div class="bm-schedule-history">
                            <h5>Schedule History</h5>
                            <div class="bm-schedule-history-list">
                                @foreach($abyipSchedule['histories'] as $history)
                                    <div class="bm-schedule-history-item">
                                        <div class="bm-schedule-history-top">
                                            <strong>{{ $history['action_label'] }}</strong>
                                            <span>{{ $history['created_at'] }}</span>
                                        </div>
                                        @if($history['old_deadline'] || $history['new_deadline'])
                                            <p>Deadline: {{ $history['old_deadline'] ?? '—' }} → {{ $history['new_deadline'] ?? '—' }}</p>
                                        @endif
                                        <p class="bm-schedule-history-meta">By {{ $history['updated_by'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <p class="bm-empty">No schedule has been created yet.</p>
            @endif
        </div>
        <div class="bm-modal-footer bm-schedule-view-footer">
            @if(!empty($abyipSchedule))
            <button type="button" class="bm-btn-schedule secondary" id="btnEditSchedule" data-id="{{ $abyipSchedule['id'] }}">
                <i class="fas fa-edit"></i> Edit Schedule
            </button>
            <button type="button" class="bm-btn-schedule danger" id="btnCancelSchedule" data-id="{{ $abyipSchedule['id'] }}">
                <i class="fas fa-ban"></i> Cancel Schedule
            </button>
            <button type="button" class="bm-btn-schedule danger" id="btnDeleteSchedule" data-id="{{ $abyipSchedule['id'] }}">
                <i class="fas fa-trash"></i> Delete Schedule
            </button>
            @endif
        </div>
    </div>
</div>

<div id="scheduleModal" class="bm-modal" hidden>
    <div class="bm-modal-backdrop" data-schedule-close></div>
    <div class="bm-modal-dialog bm-modal-lg">
        <div class="bm-modal-header">
            <h3 id="scheduleModalTitle">Create ABYIP Schedule</h3>
            <button type="button" class="bm-modal-close" data-schedule-close>&times;</button>
        </div>
        <div class="bm-modal-body">
            <input type="hidden" id="scheduleId">
            <div class="bm-form-grid">
                <label>Calendar Year
                    <input type="text" id="scheduleFiscalYear" value="{{ date('Y') }}" readonly>
                </label>
                <label>Title
                    <input type="text" id="scheduleTitle" value="ABYIP Submission" maxlength="50">
                </label>
                <label>Start Date
                    <input type="date" id="scheduleDateStart">
                    <span class="bm-field-hint" id="scheduleDateStartHint">Must be between today and December 31, {{ date('Y') }}.</span>
                    <span class="bm-field-error" id="scheduleDateStartError" hidden></span>
                </label>
                <label>Deadline
                    <input type="date" id="scheduleDeadline">
                    <span class="bm-field-hint" id="scheduleDeadlineHint">Must be on or after start date and on or before Dec 31, {{ date('Y') }}.</span>
                    <span class="bm-field-error" id="scheduleDeadlineError" hidden></span>
                </label>
            </div>
        </div>
        <div class="bm-modal-footer">
            <button type="button" class="bm-btn-secondary" data-schedule-close>Cancel</button>
            <button type="button" class="bm-btn-primary" id="scheduleSaveBtn">Save Schedule</button>
        </div>
    </div>
</div>

<div id="deleteScheduleModal" class="bm-modal" hidden>
    <div class="bm-modal-backdrop" data-delete-schedule-close></div>
    <div class="bm-modal-dialog bm-cancel-schedule-dialog">
        <div class="bm-modal-header bm-cancel-schedule-header">
            <div class="bm-cancel-schedule-icon" aria-hidden="true">
                <i class="fas fa-trash"></i>
            </div>
            <div>
                <h3>Delete ABYIP Schedule?</h3>
                <p class="bm-cancel-schedule-subtitle">This permanently removes the schedule record for this calendar year.</p>
            </div>
            <button type="button" class="bm-modal-close" data-delete-schedule-close>&times;</button>
        </div>
        <div class="bm-modal-body bm-cancel-schedule-body">
            <p>Are you sure you want to delete this ABYIP submission schedule? Barangay SK Officials will not be able to upload until a new schedule is created.</p>
        </div>
        <div class="bm-modal-footer bm-cancel-schedule-footer">
            <button type="button" class="bm-btn-secondary" data-delete-schedule-close>No, Keep Schedule</button>
            <button type="button" class="bm-btn-danger-solid" id="confirmDeleteScheduleBtn">Yes, Delete Schedule</button>
        </div>
    </div>
</div>

<div id="cancelScheduleModal" class="bm-modal" hidden>
    <div class="bm-modal-backdrop" data-cancel-schedule-close></div>
    <div class="bm-modal-dialog bm-cancel-schedule-dialog">
        <div class="bm-modal-header bm-cancel-schedule-header">
            <div class="bm-cancel-schedule-icon" aria-hidden="true">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h3>Cancel ABYIP Schedule?</h3>
                <p class="bm-cancel-schedule-subtitle">This will stop barangay SK Officials from submitting ABYIP documents for the current schedule period.</p>
            </div>
            <button type="button" class="bm-modal-close" data-cancel-schedule-close>&times;</button>
        </div>
        <div class="bm-modal-body bm-cancel-schedule-body">
            <p>Are you sure you want to cancel the current ABYIP submission schedule? This action cannot be undone.</p>
        </div>
        <div class="bm-modal-footer bm-cancel-schedule-footer">
            <button type="button" class="bm-btn-secondary" data-cancel-schedule-close>No, Keep Schedule</button>
            <button type="button" class="bm-btn-danger-solid" id="confirmCancelScheduleBtn">Yes, Cancel Schedule</button>
        </div>
    </div>
</div>

<div id="extendModal" class="bm-modal" hidden>
    <div class="bm-modal-backdrop" data-schedule-close></div>
    <div class="bm-modal-dialog">
        <div class="bm-modal-header">
            <h3>Extend ABYIP Deadline</h3>
            <button type="button" class="bm-modal-close" data-schedule-close>&times;</button>
        </div>
        <div class="bm-modal-body">
            <input type="hidden" id="extendScheduleId">
            <label>New Deadline
                <input type="date" id="extendNewDeadline">
            </label>
        </div>
        <div class="bm-modal-footer">
            <button type="button" class="bm-btn-secondary" data-schedule-close>Cancel</button>
            <button type="button" class="bm-btn-primary" id="extendSaveBtn">Extend Deadline</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.barangayMonitoringScheduleConfig = {
        listUrl: @json(route('api.barangay-monitoring.schedules')),
        storeUrl: @json(route('api.barangay-monitoring.schedules.store')),
        updateUrl: @json(url('/api/barangay-monitoring/abyip-schedules')),
        csrfToken: @json(csrf_token()),
        currentSchedule: @json($abyipSchedule),
        canCreateSchedule: @json($canCreateAbyipSchedule ?? true),
    };
</script>
<script src="{{ url('/shared/js/loading.js') }}"></script>
<script src="{{ url('/modules/barangay-monitoring/js/barangay-monitoring.js') }}"></script>
<script src="{{ url('/modules/barangay-monitoring/js/barangay-monitoring-schedule.js') }}?v={{ time() }}"></script>
@endpush
