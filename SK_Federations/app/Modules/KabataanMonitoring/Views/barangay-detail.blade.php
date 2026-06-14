@extends('layout::app')

@section('title', '{{ $barangay }} - Kabataan Monitoring - SK OnePortal')

@push('main-class')
    km-main
@endpush

@push('main-attributes')
    data-detail-base="{{ url('/kabataan-monitoring') }}"
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ url('/modules/kabataan-monitoring/css/kabataan-monitoring.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="km-container">

            {{-- Back Button & Header --}}
            <div class="km-detail-header">
                <a href="{{ route('kabataan-monitoring') }}" class="km-back-link">
                    <i class="fas fa-arrow-left"></i> Back to Kabataan Monitoring
                </a>
                <div class="km-brgy-title-section">
                    <h1><i class="fas fa-map-marker-alt"></i> {{ $barangay }}</h1>
                    <p>KKK Profiling Masterlist</p>
                </div>
            </div>

            {{-- Summary Cards for Barangay --}}
            <section class="km-brgy-summary-section">
                <div class="km-summary-grid" aria-label="Barangay summary statistics">
                    <article class="km-summary-card km-summary-total">
                        <div class="km-summary-icon"><i class="fas fa-users"></i></div>
                        <div class="km-summary-body">
                            <div class="km-summary-label">Total Kabataan</div>
                            <div class="km-summary-value" id="km-brgy-total">0</div>
                            <div class="km-summary-note">Registered youth profiles</div>
                        </div>
                    </article>
                    <article class="km-summary-card km-summary-active">
                        <div class="km-summary-icon"><i class="fas fa-user-check"></i></div>
                        <div class="km-summary-body">
                            <div class="km-summary-label">Participation Rate</div>
                            <div class="km-summary-value" id="km-brgy-rate">0%</div>
                            <div class="km-summary-note">Active vs total registered</div>
                        </div>
                    </article>
                    <article class="km-summary-card km-summary-active">
                        <div class="km-summary-icon"><i class="fas fa-user-check"></i></div>
                        <div class="km-summary-body">
                            <div class="km-summary-label">Active</div>
                            <div class="km-summary-value" id="km-brgy-active">0</div>
                            <div class="km-summary-note">High & moderate engagement</div>
                        </div>
                    </article>
                    <article class="km-summary-card km-summary-inactive">
                        <div class="km-summary-icon"><i class="fas fa-user-times"></i></div>
                        <div class="km-summary-body">
                            <div class="km-summary-label">Inactive</div>
                            <div class="km-summary-value" id="km-brgy-inactive">0</div>
                            <div class="km-summary-note">Needs follow-up & intervention</div>
                        </div>
                    </article>
                </div>
            </section>

            {{-- Masterlist Table --}}
            <section class="km-masterlist-top">
                <div class="km-masterlist-topbar">
                    <div>
                        <h2><i class="fas fa-list-alt" style="color:#213F99;margin-right:8px;"></i>KKK Profiling Masterlist</h2>
                        <p>Youth profiling records for {{ $barangay }}</p>
                    </div>
                    <div class="km-masterlist-actions">
                        <button class="km-export-btn" onclick="exportBarangayCSV()">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                    </div>
                </div>
                
                {{-- Filters Row --}}
                <div class="km-filter-row" style="padding:14px 24px;border-top:1px solid #f1f5f9;">
                    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;flex:1;">
                        {{-- Year Filter --}}
                        <select id="km-brgy-year-filter" style="padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:14px;min-width:140px;">
                            <option value="all">All Years</option>
                            <option value="2026">2026</option>
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                        </select>
                        
                        {{-- Time Period Filter --}}
                        <select id="km-period-filter" style="padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:14px;min-width:140px;">
                            <option value="all">All</option>
                            <option value="recent">Recent</option>
                            <option value="month">This Month</option>
                        </select>
                        
                        {{-- Search Box --}}
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input type="text" id="km-brgy-search" placeholder="Search by name, barangay..." style="padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:14px;min-width:250px;">
                            <button class="km-search-btn" onclick="performBarangaySearch()" style="padding:8px 16px;background:linear-gradient(135deg,#213F99,#d0242b);color:#fff;border:none;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Table --}}
            <div class="km-table-wrap">
                <table class="km-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Name</th><th>Age</th><th>Sex</th>
                            <th>Civil Status</th><th>Education</th><th>Work Status</th>
                            <th>Classification</th><th>Status</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="km-table-tbody"></tbody>
                </table>
            </div>
            <p id="km-empty" class="km-empty" hidden>No profiles match your current filters.</p>

            {{-- Pagination --}}
            <div class="km-pagination-wrapper">
                <div class="km-pagination-info">
                    <span id="km-pagination-text">Showing 0 of 0 records</span>
                </div>
                <div class="km-pagination-controls">
                    <button class="km-pagination-btn" id="km-prev-btn" onclick="previousPage()" disabled>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <div class="km-pagination-numbers" id="km-pagination-numbers"></div>
                    <button class="km-pagination-btn" id="km-next-btn" onclick="nextPage()" disabled>
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

        </div>
@endsection

@push('scripts')
{{-- KK Profiling Form Modal --}}
    <div class="km-kkp-modal" id="kmKKPModal">
        <div class="km-kkp-modal-overlay" onclick="closeKKPModal()"></div>
        <div class="km-kkp-modal-content">
            <div class="km-kkp-modal-header">
                <h2><i class="fas fa-file-alt"></i> KK Survey Questionnaire</h2>
                <button class="km-kkp-modal-close" onclick="closeKKPModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="km-kkp-modal-body">
                <form id="kmKKPForm" class="km-kkp-form">
                    {{-- Form Header --}}
                    <div class="km-kkp-form-header">
                        <div class="km-kkp-header-info">
                            <div class="km-kkp-header-field">
                                <label>Respondent #:</label>
                                <input type="text" id="kmKKPRespondent" readonly>
                            </div>
                            <div class="km-kkp-header-field">
                                <label>Date:</label>
                                <input type="text" id="kmKKPDate" readonly>
                            </div>
                        </div>
                    </div>

                    {{-- Notice Box --}}
                    <div class="km-kkp-notice">
                        <p class="km-kkp-notice-title">TO THE RESPONDENT:</p>
                        <p>We are currently conducting a study that focuses on assessing the demographic information of the Katipunan ng Kabataan. We would like to ask your participation by taking time to answer this questionnaire. Please read the questions carefully and answer them accurately.</p>
                        <p class="km-kkp-notice-confidential">REST ASSURED THAT ALL INFORMATION GATHERED FROM THIS STUDY WILL BE TREATED WITH UTMOST CONFIDENTIALITY.</p>
                    </div>

                    {{-- I. PROFILE --}}
                    <div class="km-kkp-section-title">I. PROFILE</div>

                    {{-- Name --}}
                    <div class="km-kkp-field-group">
                        <label class="km-kkp-field-label">Name of Respondent:</label>
                        <div class="km-kkp-name-row">
                            <input type="text" id="kmKKPLastName" placeholder="Last Name" class="km-kkp-input">
                            <input type="text" id="kmKKPFirstName" placeholder="First Name" class="km-kkp-input">
                            <input type="text" id="kmKKPMiddleName" placeholder="Middle Name" class="km-kkp-input">
                            <select id="kmKKPSuffix" class="km-kkp-input km-kkp-input-sm">
                                <option value="">Suffix</option>
                                <option>Jr.</option><option>Sr.</option>
                                <option>II</option><option>III</option><option>IV</option><option>V</option>
                            </select>
                        </div>
                    </div>

                    {{-- Location --}}
                    <div class="km-kkp-field-group">
                        <label class="km-kkp-field-label">Location:</label>
                        <div class="km-kkp-location-row">
                            <input type="text" value="Region IV-A (CALABARZON)" readonly class="km-kkp-input km-kkp-input-readonly">
                            <input type="text" value="Laguna" readonly class="km-kkp-input km-kkp-input-readonly">
                            <input type="text" value="Santa Cruz" readonly class="km-kkp-input km-kkp-input-readonly">
                            <input type="text" id="kmKKPBarangay" readonly class="km-kkp-input km-kkp-input-readonly">
                            <input type="text" id="kmKKPPurok" placeholder="Purok/Zone" class="km-kkp-input">
                        </div>
                    </div>

                    {{-- Personal Info --}}
                    <div class="km-kkp-field-group">
                        <div class="km-kkp-personal-row">
                            <div class="km-kkp-personal-col">
                                <label>Sex Assigned by Birth:</label>
                                <div class="km-kkp-checkbox-group">
                                    <label><input type="radio" name="kmKKPSex" value="Male"> Male</label>
                                    <label><input type="radio" name="kmKKPSex" value="Female"> Female</label>
                                </div>
                            </div>
                            <div class="km-kkp-personal-col">
                                <label>Age: *</label>
                                <input type="number" id="kmKKPAge" min="15" max="30" class="km-kkp-input km-kkp-input-sm">
                            </div>
                            <div class="km-kkp-personal-col">
                                <label>Birthday:</label>
                                <input type="date" id="kmKKPBirthday" class="km-kkp-input">
                            </div>
                        </div>
                        <div class="km-kkp-personal-row">
                            <div class="km-kkp-personal-col">
                                <label>E-mail address:</label>
                                <input type="email" id="kmKKPEmail" class="km-kkp-input">
                            </div>
                            <div class="km-kkp-personal-col">
                                <label>Contact #:</label>
                                <input type="text" id="kmKKPContact" class="km-kkp-input">
                            </div>
                        </div>
                    </div>

                    {{-- II. DEMOGRAPHIC CHARACTERISTICS --}}
                    <div class="km-kkp-section-title">II. DEMOGRAPHIC CHARACTERISTICS</div>
                    <p class="km-kkp-instruction">Please put a Check mark (✓) next to the word or Phrase that matches your response.</p>

                    <div class="km-kkp-demo-grid">
                        {{-- Left Column --}}
                        <div class="km-kkp-demo-col">
                            <div class="km-kkp-demo-block">
                                <div class="km-kkp-demo-block-title">Civil Status</div>
                                <div class="km-kkp-checkbox-group">
                                    <label><input type="checkbox" name="kmKKPCivilStatus" value="Single"> Single</label>
                                    <label><input type="checkbox" name="kmKKPCivilStatus" value="Married"> Married</label>
                                    <label><input type="checkbox" name="kmKKPCivilStatus" value="Widowed"> Widowed</label>
                                    <label><input type="checkbox" name="kmKKPCivilStatus" value="Divorced"> Divorced</label>
                                    <label><input type="checkbox" name="kmKKPCivilStatus" value="Separated"> Separated</label>
                                    <label><input type="checkbox" name="kmKKPCivilStatus" value="Annulled"> Annulled</label>
                                </div>
                            </div>

                            <div class="km-kkp-demo-block">
                                <div class="km-kkp-demo-block-title">Youth Age Group</div>
                                <div class="km-kkp-checkbox-group">
                                    <label><input type="checkbox" name="kmKKPYouthAge" value="Child Youth (15-17 yrs old)"> Child Youth (15-17 yrs old)</label>
                                    <label><input type="checkbox" name="kmKKPYouthAge" value="Core Youth (18-24 yrs old)"> Core Youth (18-24 yrs old)</label>
                                    <label><input type="checkbox" name="kmKKPYouthAge" value="Young Adult (15-30 yrs old)"> Young Adult (15-30 yrs old)</label>
                                </div>
                            </div>

                            <div class="km-kkp-demo-block">
                                <div class="km-kkp-demo-block-title">Educational Background</div>
                                <div class="km-kkp-checkbox-group">
                                    <label><input type="checkbox" name="kmKKPEducation" value="High School Level"> High School Level</label>
                                    <label><input type="checkbox" name="kmKKPEducation" value="High School Grad"> High School Grad</label>
                                    <label><input type="checkbox" name="kmKKPEducation" value="College Level"> College Level</label>
                                    <label><input type="checkbox" name="kmKKPEducation" value="College Grad"> College Grad</label>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column --}}
                        <div class="km-kkp-demo-col">
                            <div class="km-kkp-demo-block">
                                <div class="km-kkp-demo-block-title">Youth Classification</div>
                                <div class="km-kkp-checkbox-group">
                                    <label><input type="checkbox" name="kmKKPYouthClass" value="In School Youth"> In School Youth</label>
                                    <label><input type="checkbox" name="kmKKPYouthClass" value="Out of School Youth"> Out of School Youth</label>
                                    <label><input type="checkbox" name="kmKKPYouthClass" value="Working Youth"> Working Youth</label>
                                </div>
                            </div>

                            <div class="km-kkp-demo-block">
                                <div class="km-kkp-demo-block-title">Work Status</div>
                                <div class="km-kkp-checkbox-group">
                                    <label><input type="checkbox" name="kmKKPWorkStatus" value="Employed"> Employed</label>
                                    <label><input type="checkbox" name="kmKKPWorkStatus" value="Unemployed"> Unemployed</label>
                                    <label><input type="checkbox" name="kmKKPWorkStatus" value="Self-Employed"> Self-Employed</label>
                                </div>
                            </div>

                            <div class="km-kkp-demo-block">
                                <div class="km-kkp-demo-block-title">SK Voter Status</div>
                                <div class="km-kkp-checkbox-group">
                                    <label><input type="checkbox" name="kmKKPSKVoter" value="Yes"> Registered SK Voter</label>
                                    <label><input type="checkbox" name="kmKKPSKVoted" value="Yes"> Voted in Last SK Election</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="km-kkp-footer">
                        <div class="km-kkp-footer-field">
                            <label>FB Account:</label>
                            <input type="text" id="kmKKPFacebook" class="km-kkp-input">
                        </div>
                        <div class="km-kkp-footer-field">
                            <label>Willing to join the group chat?</label>
                            <div class="km-kkp-checkbox-group">
                                <label><input type="radio" name="kmKKPGroupChat" value="Yes"> Yes</label>
                                <label><input type="radio" name="kmKKPGroupChat" value="No"> No</label>
                            </div>
                        </div>
                    </div>

                    <div class="km-kkp-thankyou">Thank you for your participation!</div>
                </form>
            </div>
            <div class="km-kkp-modal-footer">
                <button type="button" class="km-kkp-btn-close" onclick="closeKKPModal()">Close</button>
                <button type="button" class="km-kkp-btn-print" onclick="printKKPForm()"><i class="fas fa-print"></i> Print</button>
            </div>
        </div>
    </div>

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script src="{{ url('/modules/kabataan-monitoring/js/kabataan-monitoring.js') }}?v={{ time() }}"></script>
@endpush
