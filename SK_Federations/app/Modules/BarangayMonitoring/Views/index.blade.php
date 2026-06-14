@extends('layout::app')

@section('title', 'Barangay Monitoring - SK Federation')

@push('styles')
    <link rel="stylesheet" href="{{ url('/modules/barangay-monitoring/css/barangay-monitoring.css') }}">
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

            <section class="bm-card" aria-label="All barangays list">
                <div class="bm-card-head">
                    <h3>All Barangays</h3>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <select id="bmFilterStatus" onchange="bmFilterBarangays()" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;color:#475569;background:#fff;cursor:pointer;">
                            <option value="all">All Status</option>
                            <option value="compliant">Compliant</option>
                            <option value="partial">Partial</option>
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
                                data-barangay="{{ strtolower($barangay['name']) }}"
                                data-date="{{ strtotime($barangay['last_update']) }}"
                            >
                                <div class="bm-list-head">
                                    <h4>{{ $barangay['name'] }}</h4>
                                    <span class="bm-status {{ $barangay['status'] }}">{{ ucfirst(str_replace('-', ' ', $barangay['status'])) }}</span>
                                </div>
                                <div class="bm-list-meta">
                                    <span><i class="fas fa-user"></i> SK Chairman: {{ $barangay['sk_chairman'] }}</span>
                                </div>
                                <div class="bm-list-meta">
                                    <span><i class="fas fa-layer-group"></i> Annual Programs: {{ $barangay['active_programs'] }}</span>
                                    <span><i class="fas fa-users"></i> Participation Rate: {{ $barangay['participation_rate'] }}%</span>
                                </div>
                                <div class="bm-list-meta">
                                    <span><i class="fas fa-file-alt"></i> Report Rate: {{ $barangay['report_rate'] }}%</span>
                                </div>
                                <div class="bm-list-foot">
                                    <span>Last Update: {{ $barangay['last_update'] }}</span>
                                    <span class="bm-link-cta">View full details <i class="fas fa-arrow-right"></i></span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <p class="bm-empty" id="bm-empty" hidden>No barangays match the current search/filter.</p>
                </div>
            </section>
        </div>
@endsection

@push('scripts')
<script src="{{ url('/shared/js/loading.js') }}"></script>
<script src="{{ url('/modules/barangay-monitoring/js/barangay-monitoring.js') }}"></script>
@endpush
