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

@section('content')
<!-- Include Header -->
@include('layout::layouts.header')

<!-- Include Sidebar -->
@include('layout::layouts.sidebar')
<div class="main-content-modern" id="mainContent">
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
                            @if ($isOfficials)
                                <th colspan="2" class="table-group-header">Personal Information</th>
                                <th colspan="2" class="table-group-header">Location Information</th>
                                <th colspan="4" class="table-group-header">Term Information</th>
                            @else
                                <th colspan="2" class="table-group-header">Personal Information</th>
                                <th colspan="4" class="table-group-header">Location Information</th>
                                <th colspan="5" class="table-group-header">Term Information</th>
                            @endif
                        </tr>
                        <tr>
                            @if ($isOfficials)
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>SK Role</th>
                                <th>Barangay</th>
                                <th>Municipality</th>
                                <th>Term Start</th>
                                <th>Term End</th>
                                <th>Status</th>
                                <th>Actions</th>
                            @else
                                <th>Full Name</th>
                                <th>Email Address</th>
                                <th>Barangay</th>
                                <th>Municipality</th>
                                <th>Province</th>
                                <th>Region</th>
                                <th>Position (SK Role)</th>
                                <th>Term Start</th>
                                <th>Term End</th>
                                <th>Status</th>
                                <th>Actions</th>
                            @endif
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
                                @if ($isOfficials)
                                    <td>{{ $displayName }}</td>
                                    <td>{{ $account->email }}</td>
                                    <td>{{ $profile?->position ?? '-' }}</td>
                                    <td>{{ $account->barangay?->name ?? '-' }}</td>
                                    <td>{{ $profile?->municipality ?? '-' }}</td>
                                    <td>{{ $term?->term_start?->format('m/d/Y') ?? '-' }}</td>
                                    <td>{{ $term?->term_end?->format('m/d/Y') ?? '-' }}</td>
                                    <td>
                                        <span class="status-badge {{ strtolower($account->status) }}">{{ $account->status }}</span>
                                    </td>
                                    <td>
                                        <button
                                            type="button"
                                            class="btn-edit-modern btn-edit-account"
                                            data-account-id="{{ $account->id }}"
                                            data-first-name="{{ $profile?->first_name ?? '' }}"
                                            data-last-name="{{ $profile?->last_name ?? '' }}"
                                            data-middle-name="{{ $profile?->middle_name ?? '' }}"
                                            data-suffix="{{ $profile?->suffix ?? '' }}"
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
                                    </td>
                                @else
                                    <td>{{ $displayName }}</td>
                                    <td>{{ $account->email }}</td>
                                    <td>{{ $account->barangay?->name ?? '-' }}</td>
                                    <td>{{ $profile?->municipality ?? '-' }}</td>
                                    <td>{{ $profile?->province ?? '-' }}</td>
                                    <td>{{ $profile?->region ?? '-' }}</td>
                                    <td>{{ $profile?->position ?? '-' }}</td>
                                    <td>{{ $term?->term_start?->format('m/d/Y') ?? '-' }}</td>
                                    <td>{{ $term?->term_end?->format('m/d/Y') ?? '-' }}</td>
                                    <td>
                                        <span class="status-badge {{ strtolower($account->status) }}">{{ $account->status }}</span>
                                    </td>
                                    <td>
                                        <button
                                            type="button"
                                            class="btn-edit-modern btn-edit-account"
                                            data-account-id="{{ $account->id }}"
                                            data-first-name="{{ $profile?->first_name ?? '' }}"
                                            data-last-name="{{ $profile?->last_name ?? '' }}"
                                            data-middle-name="{{ $profile?->middle_name ?? '' }}"
                                            data-suffix="{{ $profile?->suffix ?? '' }}"
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
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isOfficials ? 9 : 11 }}" class="text-center">No accounts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                {{ $accounts->links() }}
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
@endsection

@section('scripts')
    @vite([
        'app/Modules/Accounts/assets/js/manage_account.js'
    ])
@endsection
