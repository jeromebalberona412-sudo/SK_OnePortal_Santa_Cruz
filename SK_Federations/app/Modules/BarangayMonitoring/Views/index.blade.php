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
                    <div class="bm-kpi-label">Total Barangay</div>
                    <div class="bm-kpi-value">{{ $stats['total_barangays'] }}</div>
                    <div class="bm-kpi-note">Active barangays in monitoring</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label">Total Programs</div>
                    <div class="bm-kpi-value">{{ $stats['total_programs'] }}</div>
                    <div class="bm-kpi-note">Cross-barangay total</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label">Average Participation Rate</div>
                    <div class="bm-kpi-value">{{ $stats['average_participation_rate'] }}%</div>
                    <div class="bm-kpi-note">Across all barangays</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label">Compliance Rate</div>
                    <div class="bm-kpi-value">{{ $stats['compliance_rate'] }}%</div>
                    <div class="bm-kpi-note">Compliant barangays</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label">Non-Compliance Rate</div>
                    <div class="bm-kpi-value">{{ $stats['non_compliance_rate'] }}%</div>
                    <div class="bm-kpi-note">Non-compliant barangays</div>
                </article>
            </section>

            <section class="bm-card" id="abyipScheduleSection" aria-label="ABYIP submission schedule">
                <div class="bm-card-head bm-card-head-flex">
                    <h3><i class="fas fa-calendar-check"></i> ABYIP Submission Schedule</h3>
                    <div class="bm-schedule-actions">
                        <button type="button" class="bm-btn-schedule" id="btnCreateSchedule">
                            <i class="fas fa-plus"></i> Create Schedule
                        </button>
                        @if(!empty($abyipSchedule))
                        <button type="button" class="bm-btn-schedule secondary" id="btnEditSchedule" data-id="{{ $abyipSchedule['id'] }}">
                            <i class="fas fa-edit"></i> Edit Schedule
                        </button>
                        <button type="button" class="bm-btn-schedule secondary" id="btnExtendSchedule" data-id="{{ $abyipSchedule['id'] }}">
                            <i class="fas fa-clock"></i> Extend Deadline
                        </button>
                        <button type="button" class="bm-btn-schedule danger" id="btnCancelSchedule" data-id="{{ $abyipSchedule['id'] }}">
                            <i class="fas fa-ban"></i> Cancel Schedule
                        </button>
                        @endif
                    </div>
                </div>
                <div class="bm-card-body">
                    @if(!empty($abyipSchedule))
                        <div class="bm-schedule-current">
                            <div class="bm-schedule-current-head">
                                <h4>{{ $abyipSchedule['title'] }}</h4>
                                <span class="bm-schedule-status">{{ $abyipSchedule['status_label'] }}</span>
                            </div>
                            <div class="bm-schedule-grid">
                                <div>
                                    <p class="bm-schedule-label">Fiscal Year</p>
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
                                                <p class="bm-schedule-history-meta">By {{ $history['updated_by'] }}@if($history['reason']) — {{ $history['reason'] }}@endif</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="bm-empty">No ABYIP submission schedule yet. Create one to set the deadline for all barangays.</p>
                    @endif
                </div>
            </section>

            <section class="bm-card" aria-label="All barangays list">
                <div class="bm-card-head">
                    <h3>All Barangays</h3>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <select id="bmFilterStatus" onchange="bmFilterBarangays()" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;color:#475569;background:#fff;cursor:pointer;">
                            <option value="all">All Status</option>
                            <option value="compliant">Compliant</option>
                            <option value="pending">Pending</option>
                            <option value="non-compliant">Non-Compliant</option>
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
                                    <span class="bm-status {{ $barangay['status'] }}">{{ ucfirst(str_replace('-', ' ', $barangay['status'])) }}</span>
                                </div>
                                @if(!empty($barangay['submitted_by']))
                                    <div class="bm-list-meta">
                                        <span><i class="fas fa-user"></i> Submitted by: {{ $barangay['submitted_by'] }}</span>
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
                <label>Fiscal Year
                    <input type="number" id="scheduleFiscalYear" min="2020" max="2100" value="{{ date('Y') }}">
                </label>
                <label>Title
                    <input type="text" id="scheduleTitle" value="ABYIP Submission">
                </label>
                <label>Start Date
                    <input type="date" id="scheduleDateStart">
                </label>
                <label>Deadline
                    <input type="date" id="scheduleDeadline">
                </label>
            </div>
        </div>
        <div class="bm-modal-footer">
            <button type="button" class="bm-btn-secondary" data-schedule-close>Cancel</button>
            <button type="button" class="bm-btn-primary" id="scheduleSaveBtn">Save Schedule</button>
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
    };
</script>
<script src="{{ url('/shared/js/loading.js') }}"></script>
<script src="{{ url('/modules/barangay-monitoring/js/barangay-monitoring.js') }}"></script>
<script src="{{ url('/modules/barangay-monitoring/js/barangay-monitoring-schedule.js') }}"></script>
@endpush
