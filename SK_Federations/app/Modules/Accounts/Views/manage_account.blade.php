@extends('layout::app')

@section('title', 'Manage Account')

@php
    $isOfficials = ($accountType ?? 'sk_federation') === 'sk_officials';
    $pageTitle = $isOfficials ? 'Manage SK Officials Account' : 'Manage SK Federation Account';
    $pageSubtitle = $isOfficials
        ? 'Create or manage SK Officials member accounts'
        : 'Create or manage SK Federation member accounts';
    $addLabel = $isOfficials ? 'Add SK Official' : 'Add Federation Member';
    $accountCssVersion = @filemtime(app_path('Modules/Accounts/assets/css/account.css')) ?: time();
    $accountJsVersion = @filemtime(app_path('Modules/Accounts/assets/js/account.js')) ?: time();
    $batchTemplateType = $isOfficials ? 'officials' : 'federation';
    $batchRole = $isOfficials ? 'sk_official' : 'sk_fed';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ url('/modules/accounts/css/account.css') }}?v={{ $accountCssVersion }}">
@endpush

@section('content')
<div class="main-content-modern accounts-page container-fluid" id="mainContent"
     x-data="accountsPage()"
     x-init="init()"
     data-account-type="{{ $accountType ?? 'sk_federation' }}"
     data-batch-role="{{ $batchRole }}"
     data-batch-template-type="{{ $batchTemplateType }}">

    <div class="manage-account-container">
        <div class="page-header-modern-with-button row">
            <div class="page-header-left col-md-5 col-lg-4">
                <h1 class="page-title-modern" id="pageTitle">{{ $pageTitle }}</h1>
                <p class="page-subtitle-modern" id="pageSubtitle">{{ $pageSubtitle }}</p>
            </div>
            <div class="page-header-right col-12">
                <form method="GET" action="{{ $isOfficials ? route('accounts.officials.index') : route('accounts.federation.index') }}" class="accounts-filter-form">
                    <div class="accounts-filter-grid">
                        <div class="filter-dropdown-container">
                            <label class="filter-label" for="barangayFilter">Barangay</label>
                            <select id="barangayFilter" class="filter-dropdown form-select" name="barangay_id">
                                <option value="">All Barangays</option>
                                @foreach($barangays as $barangay)
                                    <option value="{{ $barangay->id }}" {{ request('barangay_id') == $barangay->id ? 'selected' : '' }}>{{ $barangay->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-dropdown-container">
                            <label class="filter-label" for="positionFilter">Position</label>
                            <select id="positionFilter" class="filter-dropdown form-select" name="position">
                                <option value="">All Positions</option>
                                @foreach($positionOptions ?? [] as $value => $label)
                                    <option value="{{ $value }}" {{ request('position') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="search-container">
                            <label class="filter-label" for="searchInput">Search</label>
                            <div class="search-input-wrap">
                                <input type="text" id="searchInput" name="search" class="search-input form-control" value="{{ request('search') }}" placeholder="Search accounts...">
                                <button type="submit" class="search-btn" id="searchBtn" aria-label="Search">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.35-4.35"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="header-action-buttons">
                            <label class="filter-label filter-label-invisible">Add</label>
                            <button type="button" class="btn-primary-modern btn-green" id="addAccountBtn" onclick="openAddAccountModal()">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                                <span id="addButtonText">{{ $addLabel }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="bulk-action-bar"
             x-show="selectedCount > 0"
             x-transition:enter="bulk-bar-enter"
             x-transition:leave="bulk-bar-leave"
             style="display:none;">
            <div class="bulk-action-left">
                <span class="bulk-selected-icon"><i class="fas fa-check-circle"></i></span>
                <span class="bulk-selected-count" x-text="selectedCount + ' account' + (selectedCount === 1 ? '' : 's') + ' selected'"></span>
            </div>
            <div class="bulk-action-right">
                <button type="button" class="btn-bulk-clear" @click="clearSelection()">Clear</button>
                <button type="button" class="btn-bulk-delete" @click="openBulkDelete()">
                    <i class="fas fa-trash-alt"></i>
                    Delete Selected
                </button>
            </div>
        </div>

        <div class="table-card-modern">
            <div class="table-responsive">
                <table class="accounts-table" id="accountsTable">
                    <thead>
                        <tr>
                            <th class="th-checkbox">
                                <input type="checkbox" class="account-checkbox account-checkbox-header"
                                       :checked="selectAll" @change="toggleSelectAll($event.target.checked)" aria-label="Select all visible rows">
                            </th>
                            <th class="th-name">Name</th>
                            <th class="th-email">Email Address</th>
                            @if($isOfficials)
                                <th class="th-barangay">Barangay</th>
                            @endif
                            <th class="th-position">Position</th>
                            @if($isOfficials)
                                <th class="th-term">Term End</th>
                            @endif
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="accountsTableBody">
                        @forelse($accounts as $account)
                            @php
                                $profile = $account->officialProfile;
                                $term = $profile?->latestTerm;
                                $firstName = $profile?->first_name ? mb_strtoupper($profile->first_name, 'UTF-8') : null;
                                $lastName = $profile?->last_name ? mb_strtoupper($profile->last_name, 'UTF-8') : null;
                                $middleName = trim((string) ($profile?->middle_name ?? ''));
                                $middleInitial = $middleName !== '' ? mb_strtoupper(mb_substr($middleName, 0, 1, 'UTF-8'), 'UTF-8').'.' : null;
                                $fullName = trim(collect([
                                    $firstName,
                                    $middleInitial,
                                    $lastName,
                                    $profile?->suffix,
                                ])->filter()->implode(' '));
                                $displayName = $fullName !== '' ? $fullName : ($account->name ?? 'N/A');
                            @endphp
                            <tr data-account-id="{{ $account->id }}">
                                <td class="td-checkbox">
                                    <input type="checkbox" class="account-checkbox account-row-checkbox"
                                           value="{{ $account->id }}"
                                           @change="toggleRow({{ $account->id }}, $event.target.checked)"
                                           :checked="selectedRows.includes({{ $account->id }})"
                                           aria-label="Select {{ $displayName }}">
                                </td>
                                <td class="td-name">{{ $displayName }}</td>
                                <td class="td-email">{{ $account->email }}</td>
                                @if($isOfficials)
                                    <td class="td-barangay">{{ $account->barangay?->name ?? '-' }}</td>
                                @endif
                                <td class="td-position">{{ $profile?->position ?? '-' }}</td>
                                @if($isOfficials)
                                    <td class="td-term">{{ $term?->term_end?->format('m/d/Y') ?? '-' }}</td>
                                @endif
                                <td class="td-actions">
                                    @include('accounts::account_actions_menu', [
                                        'account' => $account,
                                        'profile' => $profile,
                                        'term' => $term,
                                        'displayName' => $displayName,
                                        'firstName' => $firstName,
                                        'lastName' => $lastName,
                                        'middleName' => $middleName,
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isOfficials ? 7 : 5 }}" class="text-center">No accounts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-container">
                <div class="pagination-wrapper">
                    <nav class="pagination-nav" aria-label="Table pagination">
                        <button type="button" class="pagination-btn pagination-btn-prev" id="prevBtn" disabled>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                            Previous
                        </button>
                        <div class="pagination-numbers" id="paginationNumbers"></div>
                        <button type="button" class="pagination-btn pagination-btn-next" id="nextBtn" disabled>
                            Next
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
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

@include('accounts::form_sk_fed')
@include('accounts::form_sk_officials')
@include('accounts::view_account')
@include('accounts::delete_account_modal')

<div id="accountToast" role="status" aria-live="polite"><span id="accountToastMsg">Account successfully created!</span></div>
<div id="accountToastEdit" role="status" aria-live="polite"><span id="accountToastEditMsg">Account updated successfully!</span></div>
<div id="accountToastDelete" role="status" aria-live="polite"><span id="accountToastDeleteMsg">Account deleted successfully!</span></div>
@endsection

@push('scripts')
    <script src="{{ url('/modules/accounts/js/account.js') }}?v={{ $accountJsVersion }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
@endpush
