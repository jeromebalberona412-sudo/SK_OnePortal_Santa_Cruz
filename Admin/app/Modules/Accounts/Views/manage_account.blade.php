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
    @vite([
        'app/Modules/Accounts/assets/css/manage_account.css',
        'app/Modules/Accounts/assets/css/edit_account_base.css',
        'app/Modules/Accounts/assets/css/add_sk_fed.css',
        'app/Modules/Accounts/assets/css/add_sk_officials.css',
        'app/Modules/Accounts/assets/css/view_account.css'
    ])
@endsection

@section('content')
<!-- Include Header -->
@include('layout::layouts.header')

<!-- Include Sidebar -->
@include('layout::layouts.sidebar')
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
                                        >
                                            View
                                        </button>
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
                                        >
                                            Edit
                                        </button>
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

<!-- Include Add SK Federation Modal -->
@include('accounts::add_sk_fed')
<!-- Include Add SK Officials Modal -->
@include('accounts::add_sk_officials')
<!-- Include Edit SK Federation Modal -->
@include('accounts::edit_sk_fed')
<!-- Include Edit SK Officials Modal -->
@include('accounts::edit_sk_officials')
<!-- Include View Account Modal -->
@include('accounts::view_account')
@endsection

@section('scripts')
    @vite([
        'app/Modules/Accounts/assets/js/manage_account.js'
    ])
@endsection
