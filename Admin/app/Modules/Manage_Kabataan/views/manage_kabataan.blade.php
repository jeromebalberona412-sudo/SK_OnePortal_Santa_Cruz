@extends('layouts.app')

@section('title', 'Manage Kabataan')

@section('head')
    @vite(['app/Modules/Manage_Kabataan/assets/css/manage_kabataan.css'])
@endsection

@section('content')
@include('layout::header')
@include('layout::sidebar')

<div id="mainContent" class="main-content-modern mk-page">

    {{-- Page Header --}}
    <div class="mk-page-header">
        <div class="mk-header-left">
            <h1 class="mk-page-title">Manage Kabataan</h1>
            <p class="mk-page-subtitle">View and manage registered Kabataan (KK) member records.</p>
        </div>
        <div class="mk-header-right">
            <div class="mk-search-wrap">
                <svg class="mk-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" id="mkSearch" class="mk-search-input" placeholder="Search by name, KK number, email…" autocomplete="off">
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="mk-stats-row" id="mkStatsRow"></div>

    {{-- Filter Bar --}}
    <div class="mk-filter-bar" id="mkFilterBar">
        <div class="mk-filter-group">
            <label class="mk-filter-label" for="mkFilterBarangay">Barangay</label>
            <select id="mkFilterBarangay" class="mk-filter-select">
                <option value="">All Barangays</option>
                @foreach($barangays as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mk-filter-group mk-filter-group-age">
            <label class="mk-filter-label">Age Range</label>
            <div class="mk-age-range">
                <input type="number" id="mkAgeMin" class="mk-filter-input" placeholder="Min" min="15" max="30">
                <span class="mk-age-sep">–</span>
                <input type="number" id="mkAgeMax" class="mk-filter-input" placeholder="Max" min="15" max="30">
            </div>
        </div>
        <div class="mk-filter-group">
            <label class="mk-filter-label" for="mkFilterGender">Gender</label>
            <select id="mkFilterGender" class="mk-filter-select">
                <option value="">All</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
        </div>
        <div class="mk-filter-group">
            <label class="mk-filter-label" for="mkFilterVoter">Voter Status</label>
            <select id="mkFilterVoter" class="mk-filter-select">
                <option value="">All</option>
                <option value="Registered">Registered</option>
                <option value="Not Registered">Not Registered</option>
            </select>
        </div>
        <div class="mk-filter-group">
            <label class="mk-filter-label" for="mkFilterAccount">Account Status</label>
            <select id="mkFilterAccount" class="mk-filter-select">
                <option value="">All</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>
        <div class="mk-filter-group">
            <label class="mk-filter-label" for="mkFilterVerification">Verification</label>
            <select id="mkFilterVerification" class="mk-filter-select">
                <option value="">All</option>
                <option value="Verified">Verified</option>
                <option value="Unverified">Unverified</option>
            </select>
        </div>
        <div class="mk-filter-group mk-filter-reset-group">
            <button type="button" id="mkResetFilters" class="mk-btn-reset">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                    <path d="M3 3v5h5"/>
                </svg>
                Reset Filters
            </button>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="mk-table-card">
        {{-- Loading overlay --}}
        <div class="mk-table-loading" id="mkTableLoading" style="display:none;">
            <div class="mk-spinner"></div>
            <span>Loading records…</span>
        </div>

        <div class="mk-table-wrapper">
            <table class="mk-table" id="mkTable">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>KK Number</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Barangay</th>
                        <th>Contact Number</th>
                        <th>Email</th>
                        <th>Youth Classification</th>
                        <th>Voter Status</th>
                        <th>Account Status</th>
                        <th>Verification</th>
                        <th class="mk-col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="mkTableBody">
                    @forelse($kabataan as $k)
                        @php
                            $fullName = trim(collect([
                                $k->first_name,
                                $k->middle_name ? strtoupper(substr($k->middle_name,0,1)).'.' : null,
                                $k->last_name,
                                $k->suffix ?? null,
                            ])->filter()->implode(' '));
                        @endphp
                        <tr
                            data-id="{{ $k->id }}"
                            data-first-name="{{ $k->first_name }}"
                            data-last-name="{{ $k->last_name }}"
                            data-middle-name="{{ $k->middle_name ?? '' }}"
                            data-suffix="{{ $k->suffix ?? '' }}"
                            data-kk-number="{{ $k->kk_number }}"
                            data-age="{{ $k->age }}"
                            data-gender="{{ $k->gender }}"
                            data-birthday="{{ $k->birthday ?? '' }}"
                            data-barangay-id="{{ $k->barangay_id }}"
                            data-barangay="{{ $k->barangay?->name ?? '' }}"
                            data-contact="{{ $k->contact_number ?? '' }}"
                            data-email="{{ $k->email ?? '' }}"
                            data-youth-classification="{{ $k->youth_classification ?? '' }}"
                            data-educational-background="{{ $k->educational_background ?? '' }}"
                            data-work-status="{{ $k->work_status ?? '' }}"
                            data-civil-status="{{ $k->civil_status ?? '' }}"
                            data-sk-voter="{{ $k->sk_voter ?? '' }}"
                            data-national-voter="{{ $k->national_voter ?? '' }}"
                            data-voter-status="{{ $k->national_voter ?? '' }}"
                            data-account-status="{{ $k->account_status ?? '' }}"
                            data-verification-status="{{ $k->verification_status ?? '' }}"
                            data-purok="{{ $k->purok_zone ?? '' }}"
                        >
                            <td class="mk-name-cell">{{ $fullName ?: 'N/A' }}</td>
                            <td>{{ $k->kk_number ?? '—' }}</td>
                            <td>{{ $k->age ?? '—' }}</td>
                            <td>{{ $k->gender ?? '—' }}</td>
                            <td>{{ $k->barangay?->name ?? '—' }}</td>
                            <td>{{ $k->contact_number ?? '—' }}</td>
                            <td class="mk-email-cell">{{ $k->email ?? '—' }}</td>
                            <td>{{ $k->youth_classification ?? '—' }}</td>
                            <td>
                                @if(($k->national_voter ?? '') === 'Registered')
                                    <span class="mk-badge mk-badge-green">Registered</span>
                                @elseif(($k->national_voter ?? '') === 'Not Registered')
                                    <span class="mk-badge mk-badge-gray">Not Registered</span>
                                @else
                                    <span class="mk-badge mk-badge-gray">—</span>
                                @endif
                            </td>
                            <td>
                                @if(($k->account_status ?? '') === 'Active')
                                    <span class="mk-badge mk-badge-green">Active</span>
                                @elseif(($k->account_status ?? '') === 'Inactive')
                                    <span class="mk-badge mk-badge-red">Inactive</span>
                                @else
                                    <span class="mk-badge mk-badge-gray">—</span>
                                @endif
                            </td>
                            <td>
                                @if(($k->verification_status ?? '') === 'Verified')
                                    <span class="mk-badge mk-badge-blue">Verified</span>
                                @elseif(($k->verification_status ?? '') === 'Unverified')
                                    <span class="mk-badge mk-badge-yellow">Unverified</span>
                                @else
                                    <span class="mk-badge mk-badge-gray">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="mk-action-btns">
                                    <button type="button" class="mk-btn-view" data-id="{{ $k->id }}" aria-label="View {{ $fullName }}">View</button>
                                    <button type="button" class="mk-btn-recover" data-id="{{ $k->id }}" aria-label="Recover Account {{ $fullName }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="width:13px;height:13px;flex-shrink:0;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                        Recover Account
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        {{-- Sample data rows shown when no DB records exist --}}
                        <tr
                            data-id="1"
                            data-first-name="Juan"
                            data-last-name="Dela Cruz"
                            data-middle-name="Santos"
                            data-suffix=""
                            data-kk-number="KK-2024-00001"
                            data-age="19"
                            data-gender="Male"
                            data-birthday="2005-03-15"
                            data-barangay-id=""
                            data-barangay="Poblacion I"
                            data-contact="09171234567"
                            data-email="juan.delacruz@example.com"
                            data-youth-classification="In-School Youth"
                            data-educational-background="College Level"
                            data-work-status="Student"
                            data-civil-status="Single"
                            data-sk-voter="Yes"
                            data-national-voter="Registered"
                            data-voter-status="Registered"
                            data-account-status="Active"
                            data-verification-status="Verified"
                            data-purok="Purok 2"
                        >
                            <td class="mk-name-cell">Juan S. Dela Cruz</td>
                            <td>KK-2024-00001</td>
                            <td>19</td>
                            <td>Male</td>
                            <td>Poblacion I</td>
                            <td>09171234567</td>
                            <td class="mk-email-cell">juan.delacruz@example.com</td>
                            <td>In-School Youth</td>
                            <td><span class="mk-badge mk-badge-green">Registered</span></td>
                            <td><span class="mk-badge mk-badge-green">Active</span></td>
                            <td><span class="mk-badge mk-badge-blue">Verified</span></td>
                            <td>
                                <div class="mk-action-btns">
                                    <button type="button" class="mk-btn-view" data-id="1" aria-label="View Juan Dela Cruz">View</button>
                                    <button type="button" class="mk-btn-recover" data-id="1" aria-label="Recover Account Juan Dela Cruz">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="width:13px;height:13px;flex-shrink:0;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                        Recover Account
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr
                            data-id="2"
                            data-first-name="Maria"
                            data-last-name="Reyes"
                            data-middle-name="Bautista"
                            data-suffix=""
                            data-kk-number="KK-2024-00002"
                            data-age="22"
                            data-gender="Female"
                            data-birthday="2002-07-28"
                            data-barangay-id=""
                            data-barangay="San Juan"
                            data-contact="09281234567"
                            data-email="maria.reyes@example.com"
                            data-youth-classification="Working Youth"
                            data-educational-background="College Graduate"
                            data-work-status="Employed"
                            data-civil-status="Single"
                            data-sk-voter="Yes"
                            data-national-voter="Registered"
                            data-voter-status="Registered"
                            data-account-status="Active"
                            data-verification-status="Verified"
                            data-purok="Purok 5"
                        >
                            <td class="mk-name-cell">Maria B. Reyes</td>
                            <td>KK-2024-00002</td>
                            <td>22</td>
                            <td>Female</td>
                            <td>San Juan</td>
                            <td>09281234567</td>
                            <td class="mk-email-cell">maria.reyes@example.com</td>
                            <td>Working Youth</td>
                            <td><span class="mk-badge mk-badge-green">Registered</span></td>
                            <td><span class="mk-badge mk-badge-green">Active</span></td>
                            <td><span class="mk-badge mk-badge-blue">Verified</span></td>
                            <td>
                                <div class="mk-action-btns">
                                    <button type="button" class="mk-btn-view" data-id="2" aria-label="View Maria Reyes">View</button>
                                    <button type="button" class="mk-btn-recover" data-id="2" aria-label="Recover Account Maria Reyes">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="width:13px;height:13px;flex-shrink:0;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                        Recover Account
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mk-pagination">
            <span class="mk-pagination-info" id="mkPaginationInfo">No records</span>
            <div class="mk-pagination-controls">
                <button type="button" id="mkPrevBtn" class="mk-page-btn" disabled>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    Previous
                </button>
                <div id="mkPageNumbers" class="mk-page-numbers"></div>
                <button type="button" id="mkNextBtn" class="mk-page-btn" disabled>
                    Next
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                </button>
            </div>
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════
     VIEW MODAL
═══════════════════════════════════════════════════════════ --}}
<div class="mk-modal-backdrop" id="mkViewModal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="mkViewModalTitle">
    <div class="mk-modal-box mk-view-modal-box" id="mkViewModalBox">
        <div class="mk-modal-header">
            <h2 class="mk-modal-title" id="mkViewModalTitle">Kabataan Details</h2>
            <div class="mk-modal-controls">
                <button type="button" class="mk-modal-toggle-btn" id="mkViewToggleBtn" title="Maximize" aria-label="Maximize modal">□</button>
                <button type="button" class="mk-modal-close-btn" id="mkViewCloseBtn" aria-label="Close modal">&times;</button>
            </div>
        </div>
        <div class="mk-view-body" id="mkViewBody"></div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     EDIT MODAL
═══════════════════════════════════════════════════════════ --}}
<div class="mk-modal-backdrop" id="mkEditModal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="mkEditModalTitle">
    <div class="mk-modal-box mk-edit-modal-box" id="mkEditModalBox">
        <div class="mk-modal-header">
            <h2 class="mk-modal-title" id="mkEditModalTitle">Edit Kabataan Record</h2>
            <div class="mk-modal-controls">
                <button type="button" class="mk-modal-toggle-btn" id="mkEditToggleBtn" title="Maximize" aria-label="Maximize modal">□</button>
                <button type="button" class="mk-modal-close-btn" id="mkEditCloseBtn" aria-label="Close modal">&times;</button>
            </div>
        </div>
        <div class="mk-edit-body">
            <form id="mkEditForm" novalidate>
                @csrf
                <input type="hidden" id="mkEditId" name="id">

                {{-- Section: Personal Information --}}
                <div class="mk-form-section">
                    <div class="mk-form-section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a8.38 8.38 0 0 1 13 0"/></svg>
                        <span>Personal Information</span>
                    </div>
                    <div class="mk-form-grid">
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditFirstName">First Name <span class="mk-required">*</span></label>
                            <input type="text" id="mkEditFirstName" name="first_name" class="mk-form-input" required>
                            <span class="mk-field-error" id="errFirstName"></span>
                        </div>
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditMiddleName">Middle Name</label>
                            <input type="text" id="mkEditMiddleName" name="middle_name" class="mk-form-input">
                        </div>
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditLastName">Last Name <span class="mk-required">*</span></label>
                            <input type="text" id="mkEditLastName" name="last_name" class="mk-form-input" required>
                            <span class="mk-field-error" id="errLastName"></span>
                        </div>
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditSuffix">Suffix</label>
                            <input type="text" id="mkEditSuffix" name="suffix" class="mk-form-input" placeholder="Jr., Sr., III…">
                        </div>
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditBirthday">Birthday <span class="mk-required">*</span></label>
                            <input type="date" id="mkEditBirthday" name="birthday" class="mk-form-input" required>
                            <span class="mk-field-error" id="errBirthday"></span>
                        </div>
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditAge">Age <span class="mk-required">*</span></label>
                            <input type="number" id="mkEditAge" name="age" class="mk-form-input" min="15" max="30" required readonly>
                            <span class="mk-field-error" id="errAge"></span>
                        </div>
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditGender">Gender <span class="mk-required">*</span></label>
                            <select id="mkEditGender" name="gender" class="mk-form-input" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            <span class="mk-field-error" id="errGender"></span>
                        </div>
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditCivilStatus">Civil Status</label>
                            <select id="mkEditCivilStatus" name="civil_status" class="mk-form-input">
                                <option value="">Select</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Separated">Separated</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Section: Contact & Location --}}
                <div class="mk-form-section">
                    <div class="mk-form-section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span>Contact & Location</span>
                    </div>
                    <div class="mk-form-grid">
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditContact">Contact Number <span class="mk-required">*</span></label>
                            <input type="text" id="mkEditContact" name="contact_number" class="mk-form-input" required>
                            <span class="mk-field-error" id="errContact"></span>
                        </div>
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditEmail">Email Address <span class="mk-required">*</span></label>
                            <input type="email" id="mkEditEmail" name="email" class="mk-form-input" required>
                            <span class="mk-field-error" id="errEmail"></span>
                        </div>
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditBarangay">Barangay <span class="mk-required">*</span></label>
                            <select id="mkEditBarangay" name="barangay_id" class="mk-form-input" required>
                                <option value="">Select Barangay</option>
                                @foreach($barangays as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                            <span class="mk-field-error" id="errBarangay"></span>
                        </div>
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditPurok">Purok / Zone</label>
                            <input type="text" id="mkEditPurok" name="purok_zone" class="mk-form-input">
                        </div>
                    </div>
                </div>

                {{-- Section: KK Information --}}
                <div class="mk-form-section">
                    <div class="mk-form-section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z" stroke-linejoin="round" stroke-linecap="round"/><path d="M6 12v5c0 1.657 2.686 3 6 3s6-1.343 6-3v-5" stroke-linecap="round"/></svg>
                        <span>KK Information</span>
                    </div>
                    <div class="mk-form-grid">
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditKkNumber">KK Number <span class="mk-required">*</span></label>
                            <input type="text" id="mkEditKkNumber" name="kk_number" class="mk-form-input" required>
                            <span class="mk-field-error" id="errKkNumber"></span>
                        </div>
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditYouthClassification">Youth Classification <span class="mk-required">*</span></label>
                            <select id="mkEditYouthClassification" name="youth_classification" class="mk-form-input" required>
                                <option value="">Select</option>
                                <option value="In-School Youth">In-School Youth</option>
                                <option value="Out-of-School Youth">Out-of-School Youth</option>
                                <option value="Working Youth">Working Youth</option>
                                <option value="Youth with Special Needs">Youth with Special Needs</option>
                                <option value="Youth with Disabilities">Youth with Disabilities</option>
                            </select>
                            <span class="mk-field-error" id="errYouthClassification"></span>
                        </div>
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditEducationalBackground">Educational Background</label>
                            <select id="mkEditEducationalBackground" name="educational_background" class="mk-form-input">
                                <option value="">Select</option>
                                <option value="Elementary Level">Elementary Level</option>
                                <option value="Elementary Graduate">Elementary Graduate</option>
                                <option value="High School Level">High School Level</option>
                                <option value="High School Graduate">High School Graduate</option>
                                <option value="Vocational/Technical">Vocational/Technical</option>
                                <option value="College Level">College Level</option>
                                <option value="College Graduate">College Graduate</option>
                                <option value="Post Graduate">Post Graduate</option>
                            </select>
                        </div>
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditWorkStatus">Work Status</label>
                            <select id="mkEditWorkStatus" name="work_status" class="mk-form-input">
                                <option value="">Select</option>
                                <option value="Employed">Employed</option>
                                <option value="Unemployed">Unemployed</option>
                                <option value="Self-Employed">Self-Employed</option>
                                <option value="Student">Student</option>
                            </select>
                        </div>
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditSkVoter">SK Voter</label>
                            <select id="mkEditSkVoter" name="sk_voter" class="mk-form-input">
                                <option value="">Select</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditNationalVoter">National Voter Status</label>
                            <select id="mkEditNationalVoter" name="national_voter" class="mk-form-input">
                                <option value="">Select</option>
                                <option value="Registered">Registered</option>
                                <option value="Not Registered">Not Registered</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Section: Account Status --}}
                <div class="mk-form-section">
                    <div class="mk-form-section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <span>Account Status</span>
                    </div>
                    <div class="mk-form-grid">
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditAccountStatus">Account Status <span class="mk-required">*</span></label>
                            <select id="mkEditAccountStatus" name="account_status" class="mk-form-input" required>
                                <option value="">Select</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                            <span class="mk-field-error" id="errAccountStatus"></span>
                        </div>
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="mkEditVerificationStatus">Verification Status <span class="mk-required">*</span></label>
                            <select id="mkEditVerificationStatus" name="verification_status" class="mk-form-input" required>
                                <option value="">Select</option>
                                <option value="Verified">Verified</option>
                                <option value="Unverified">Unverified</option>
                            </select>
                            <span class="mk-field-error" id="errVerificationStatus"></span>
                        </div>
                    </div>
                </div>

                {{-- Form Footer --}}
                <div class="mk-form-footer">
                    <button type="button" class="mk-btn-cancel" id="mkEditCancelBtn">Cancel</button>
                    <button type="submit" class="mk-btn-save" id="mkEditSaveBtn">
                        <span id="mkEditSaveBtnText">Save Changes</span>
                        <span id="mkEditSaveBtnSpinner" class="mk-btn-spinner" style="display:none;"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Toast --}}
<div id="mkToast" class="mk-toast" role="status" aria-live="polite">
    <span id="mkToastMsg"></span>
</div>

{{-- ═══════════════════════════════════════════════════════════
     RECOVER ACCOUNT MODAL
═══════════════════════════════════════════════════════════ --}}
<div class="mk-modal-backdrop" id="mkRecoverModal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="mkRecoverModalTitle">
    <div class="mk-modal-box mk-recover-modal-box" id="mkRecoverModalBox">

        {{-- Header --}}
        <div class="mk-modal-header mk-recover-header">
            <div class="mk-recover-header-inner">
                <div class="mk-recover-header-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div>
                    <h2 class="mk-modal-title" id="mkRecoverModalTitle">Recover Kabataan Account</h2>
                    <p class="mk-recover-subtitle">Assist Kabataan users in recovering access to their SK OnePortal account.</p>
                </div>
            </div>
            <div class="mk-modal-controls">
                <button type="button" class="mk-modal-close-btn" id="mkRecoverCloseBtn" aria-label="Close modal">&times;</button>
            </div>
        </div>

        <div class="mk-recover-body">
            <form id="mkRecoverForm" novalidate>
                @csrf
                <input type="hidden" id="mkRecoverId" name="id">

                {{-- Security Notice --}}
                <div class="mk-recover-notice">
                    <div class="mk-recover-notice-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>
                    <div class="mk-recover-notice-text">
                        <strong>Security Notice</strong>
                        <span>This feature is only for legitimate account recovery assistance. Admins must verify the identity of the Kabataan user before updating account credentials.</span>
                    </div>
                </div>

                {{-- Section: Account Information (Read-only) --}}
                <div class="mk-recover-section">
                    <div class="mk-recover-section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a8.38 8.38 0 0 1 13 0"/></svg>
                        <span>Account Information</span>
                        <span class="mk-recover-readonly-badge">Read Only</span>
                    </div>
                    <div class="mk-recover-info-grid">
                        <div class="mk-recover-info-item">
                            <span class="mk-recover-info-label">Full Name</span>
                            <span class="mk-recover-info-value" id="rInfoFullName">—</span>
                        </div>
                        <div class="mk-recover-info-item">
                            <span class="mk-recover-info-label">KK Number</span>
                            <span class="mk-recover-info-value" id="rInfoKkNumber">—</span>
                        </div>
                        <div class="mk-recover-info-item">
                            <span class="mk-recover-info-label">Current Email</span>
                            <span class="mk-recover-info-value" id="rInfoEmail">—</span>
                        </div>
                        <div class="mk-recover-info-item">
                            <span class="mk-recover-info-label">Barangay</span>
                            <span class="mk-recover-info-value" id="rInfoBarangay">—</span>
                        </div>
                        <div class="mk-recover-info-item">
                            <span class="mk-recover-info-label">Verification Status</span>
                            <span class="mk-recover-info-value" id="rInfoVerification">—</span>
                        </div>
                        <div class="mk-recover-info-item">
                            <span class="mk-recover-info-label">Account Status</span>
                            <span class="mk-recover-info-value" id="rInfoAccountStatus">—</span>
                        </div>
                    </div>
                </div>

                {{-- Section: Recovery Assistance (Email only) --}}
                <div class="mk-recover-section">
                    <div class="mk-recover-section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <span>Recovery Assistance</span>
                    </div>
                    <div class="mk-form-grid mk-recover-email-grid">
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="rNewEmail">New Email Address</label>
                            <input type="email" id="rNewEmail" name="new_email" class="mk-form-input" placeholder="Enter new email address">
                            <span class="mk-field-error" id="errRNewEmail"></span>
                        </div>
                        <div class="mk-form-group">
                            <label class="mk-form-label" for="rConfirmEmail">Confirm New Email Address</label>
                            <input type="email" id="rConfirmEmail" name="confirm_email" class="mk-form-input" placeholder="Confirm new email address">
                            <span class="mk-field-error" id="errRConfirmEmail"></span>
                        </div>
                    </div>
                </div>

                {{-- Form Footer --}}
                <div class="mk-form-footer mk-recover-footer">
                    <button type="button" class="mk-btn-cancel" id="mkRecoverCancelBtn">Close</button>
                    <button type="submit" class="mk-btn-recover-submit" id="mkRecoverSaveBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="width:15px;height:15px;flex-shrink:0;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span id="mkRecoverSaveText">Submit Recovery</span>
                        <span id="mkRecoverSaveSpinner" class="mk-btn-spinner" style="display:none;"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    @vite(['app/Modules/Manage_Kabataan/assets/js/manage_kabataan.js'])
@endsection
