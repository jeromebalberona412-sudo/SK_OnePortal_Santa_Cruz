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

<!-- Delete Confirmation Modal -->
<div id="deleteAccountModal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:360px;border-radius:14px;overflow:hidden;">
        <div style="background:linear-gradient(135deg,#dc2626,#b91c1c);padding:13px 18px;text-align:center;">
            <h3 style="margin:0;font-size:15px;font-weight:700;color:#ffffff;">Delete Account</h3>
        </div>
        <div style="padding:1.75rem 1.5rem 1.25rem;background:#ffffff;text-align:center;">
            <div style="width:52px;height:52px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6"/><path d="M14 11v6"/>
                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
            </div>
            <p style="margin:0 0 6px;font-size:15px;font-weight:700;color:#1e293b;">Are you sure?</p>
            <p style="margin:0;font-size:13px;color:#64748b;line-height:1.55;">
                You are about to delete <strong id="deleteAccountName" style="color:#1e293b;"></strong>. This action cannot be undone.
            </p>
        </div>
        <div style="padding:0 1.5rem 1.5rem;background:#ffffff;display:flex;gap:0.6rem;">
            <button type="button" onclick="closeDeleteModal()"
                style="flex:1;padding:9px 0;border-radius:8px;border:1.5px solid #e2e8f0;background:#ffffff;color:#475569;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;">
                Cancel
            </button>
            <button type="button" id="deleteConfirmBtn" onclick="confirmDeleteAccount()"
                style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:9px 0;border-radius:8px;border:none;background:linear-gradient(135deg,#dc2626,#b91c1c);color:#ffffff;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;">
                Delete
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @vite(['app/Modules/Accounts/assets/js/account.js'])
@endsection

<!-- Global success toast (green — add) -->
<div id="accountToast" role="status" aria-live="polite">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <path d="M20 6L9 17l-5-5"/>
    </svg>
    <span id="accountToastMsg">Account successfully created!</span>
</div>

<!-- Edit toast (yellow) -->
<div id="accountToastEdit" role="status" aria-live="polite">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
    </svg>
    <span id="accountToastEditMsg">Account updated successfully!</span>
</div>

<!-- Delete toast (red) -->
<div id="accountToastDelete" role="status" aria-live="polite">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <polyline points="3 6 5 6 21 6"/>
        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
    </svg>
    <span id="accountToastDeleteMsg">Account deleted successfully!</span>
</div>
