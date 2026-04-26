@extends('layouts.app')

@section('title', 'Manage Account')

@php
    $isOfficials = ($accountType ?? 'sk_federation') === 'sk_officials';
    $pageTitle = $isOfficials ? 'Manage SK Officials Account' : 'Manage SK Federation Account';
    $pageSubtitle = $isOfficials
        ? 'Create or manage SK Officials member accounts'
        : 'Create or manage SK Federation member accounts';
    $addLabel = $isOfficials ? 'Add SK Officials' : 'Add SK Federation';
@endphp

@section('head')
    @vite(['app/Modules/Accounts/assets/css/account.css'])
@endsection

@section('content')
<!-- Include Header -->
@include('layout::header')

<!-- Include Sidebar -->
@include('layout::sidebar')
<div class="main-content-modern accounts-page" id="mainContent">
    <div class="manage-account-container">
        <!-- Page Header with Search and Add Account Button -->
        <div class="page-header-modern-with-button">
            <div class="page-header-left">
                <h1 class="page-title-modern" id="pageTitle">{{ $pageTitle }}</h1>
                <p class="page-subtitle-modern" id="pageSubtitle">{{ $pageSubtitle }}</p>
            </div>
            <div class="page-header-right">
                <form method="GET" action="{{ $isOfficials ? route('accounts.officials.index') : route('accounts.federation.index') }}" class="search-add-container">
                    <div class="filter-dropdown-container">
                        <select id="accountTypeFilter" class="filter-dropdown" name="account_type">
                            <option value="sk_federation" {{ $isOfficials ? '' : 'selected' }}>SK Federation</option>
                            <option value="sk_officials" {{ $isOfficials ? 'selected' : '' }}>SK Officials</option>
                        </select>
                    </div>
                    <div class="filter-dropdown-container">
                        <select id="barangayFilter" class="filter-dropdown" name="barangay_id">
                            <option value="">All Barangays</option>
                            @foreach($barangays as $barangay)
                                <option value="{{ $barangay->id }}" {{ request('barangay_id') == $barangay->id ? 'selected' : '' }}>{{ $barangay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="search-container">
                        <input type="text" id="searchInput" name="search" class="search-input" value="{{ request('search') }}" placeholder="Search accounts...">
                        <button type="submit" class="search-btn" id="searchBtn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                        </button>
                    </div>
                    <button type="button" class="btn-primary-modern btn-green" id="addAccountBtn" onclick="openAddAccountModal()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 4v16m8-8H4"/>
                        </svg>
                        <span id="addButtonText">{{ $addLabel }}</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card-modern">
            <div class="table-responsive">
                <table class="accounts-table">
                    <thead>
                        <tr>
                            <th class="full-name-header">
                                <div class="header-main">Full Name</div>
                                <div class="header-sub">(FN,MN LN,Suffix)</div>
                            </th>
                            <th>Email Address</th>
                            <th>Barangay</th>
                            <th>Position (SK Role)</th>
                            <th>Term End</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                            @php
                                $profile = $account->officialProfile;
                                $term = $profile?->latestTerm;
                                $middleName = trim((string) ($profile?->middle_name ?? ''));
                                $middleInitial = $middleName !== '' ? strtoupper(substr($middleName, 0, 1)).'.' : null;
                                $fullName = trim(collect([
                                    $profile?->first_name,
                                    $middleInitial,
                                    $profile?->last_name,
                                    $profile?->suffix,
                                ])->filter()->implode(' '));
                                $displayName = $fullName !== '' ? $fullName : ($account->name ?? 'N/A');
                            @endphp
                            <tr>
                                <td>{{ $displayName }}</td>
                                <td>{{ $account->email }}</td>
                                <td>{{ $account->barangay?->name ?? '-' }}</td>
                                <td>{{ $profile?->position ?? '-' }}</td>
                                <td>{{ $term?->term_end?->format('m/d/Y') ?? '-' }}</td>
                                <td>
                                    <span class="status-badge {{ strtolower($account->status) }}">{{ $account->status }}</span>
                                </td>
                                <td>
                                    <div class="action-buttons-container">
                                        <button
                                            type="button"
                                            class="btn-view-modern btn-view-account"
                                            data-account-id="{{ $account->id }}"
                                            data-first-name="{{ $profile?->first_name ?? '' }}"
                                            data-last-name="{{ $profile?->last_name ?? '' }}"
                                            data-middle-name="{{ $profile?->middle_name ?? '' }}"
                                            data-suffix="{{ $profile?->suffix ?? '' }}"
                                            data-date-of-birth="{{ $profile?->date_of_birth?->toDateString() ?? '' }}"
                                            data-age="{{ $profile?->age ?? '' }}"
                                            data-contact-number="{{ $profile?->contact_number ?? '' }}"
                                            data-email="{{ $account->email ?? '' }}"
                                            data-position="{{ $profile?->position ?? '' }}"
                                            data-barangay-id="{{ $account->barangay_id ?? '' }}"
                                            data-barangay-name="{{ $account->barangay?->name ?? '' }}"
                                            data-municipality="{{ $profile?->municipality ?? '' }}"
                                            data-province="{{ $profile?->province ?? '' }}"
                                            data-region="{{ $profile?->region ?? '' }}"
                                            data-status="{{ $account->status ?? '' }}"
                                            data-term-status="{{ $term?->status ?? 'ACTIVE' }}"
                                            data-term-start="{{ $term?->term_start?->toDateString() ?? '' }}"
                                            data-term-end="{{ $term?->term_end?->toDateString() ?? '' }}"
                                            data-email-verified-at="{{ $account->email_verified_at?->format('m/d/Y h:i A') ?? '' }}"
                                        >View</button>
                                        <button
                                            type="button"
                                            class="btn-edit-modern btn-edit-account"
                                            data-account-id="{{ $account->id }}"
                                            data-first-name="{{ $profile?->first_name ?? '' }}"
                                            data-last-name="{{ $profile?->last_name ?? '' }}"
                                            data-middle-name="{{ $profile?->middle_name ?? '' }}"
                                            data-suffix="{{ $profile?->suffix ?? '' }}"
                                            data-date-of-birth="{{ $profile?->date_of_birth?->toDateString() ?? '' }}"
                                            data-age="{{ $profile?->age ?? '' }}"
                                            data-contact-number="{{ $profile?->contact_number ?? '' }}"
                                            data-email="{{ $account->email ?? '' }}"
                                            data-position="{{ $profile?->position ?? '' }}"
                                            data-barangay-id="{{ $account->barangay_id ?? '' }}"
                                            data-status="{{ $account->status ?? '' }}"
                                            data-term-status="{{ $term?->status ?? 'ACTIVE' }}"
                                            data-term-start="{{ $term?->term_start?->toDateString() ?? '' }}"
                                            data-term-end="{{ $term?->term_end?->toDateString() ?? '' }}"
                                        >Edit</button>
                                        <button
                                            type="button"
                                            class="btn-delete-modern btn-delete-account"
                                            data-account-id="{{ $account->id }}"
                                            data-display-name="{{ $displayName }}"
                                        >Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No accounts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Custom Pagination -->
            <div class="pagination-container">
                <div class="pagination-wrapper">
                    <nav class="pagination-nav" aria-label="Table pagination">
                        <button type="button" class="pagination-btn pagination-btn-prev" id="prevBtn" disabled>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M15 18l-6-6 6-6"/>
                            </svg>
                            Previous
                        </button>
                        
                        <div class="pagination-numbers" id="paginationNumbers">
                            <!-- Page numbers will be dynamically generated here -->
                        </div>
                        
                        <button type="button" class="pagination-btn pagination-btn-next" id="nextBtn">
                            Next
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 18l6-6-6-6"/>
                            </svg>
                        </button>
                    </nav>
                    
                    <div class="pagination-info">
                        <span class="pagination-text" id="paginationInfo">Showing <strong>1-10</strong> of <strong>0</strong> accounts</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SK Federation: Add + Edit modals (shared form) -->
@include('accounts::form_sk_fed')
<!-- SK Officials: Add + Edit modals (shared form) -->
@include('accounts::form_sk_officials')
<!-- View Account Modal -->
@include('accounts::view_account')

<!-- Delete Confirmation Modal (UI-only) -->
<div id="deleteAccountModal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:420px;">
        <div class="modal-header" style="background:linear-gradient(180deg,#dc2626 0%,#b91c1c 100%);">
            <h3 class="modal-title">Delete Account</h3>
            <button type="button" class="modal-close-btn" onclick="closeDeleteModal()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body" style="text-align:center;padding:1.75rem 1.5rem 1rem;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1.5" style="margin-bottom:1rem;">
                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
            </svg>
            <p style="color:#1e293b;font-size:15px;font-weight:600;margin:0 0 0.5rem;">Are you sure?</p>
            <p style="color:#64748b;font-size:13px;margin:0 0 1.5rem;">
                You are about to delete <strong id="deleteAccountName"></strong>. This action cannot be undone.
            </p>
        </div>
        <div class="modal-footer" style="justify-content:center;gap:0.75rem;border-top:1px solid #fee2e2;background:#fff5f5;">
            <button type="button" class="btn-secondary-modern" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="btn-delete-confirm" id="deleteConfirmBtn" onclick="confirmDeleteAccount()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:4px;">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                </svg>
                Delete
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @vite(['app/Modules/Accounts/assets/js/account.js'])
@endsection
