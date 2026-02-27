@extends('layouts.app')

@section('title', 'Manage Account')

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
                <h1 class="page-title-modern" id="pageTitle">Manage SK Federation Account</h1>
                <p class="page-subtitle-modern" id="pageSubtitle">Create or manage SK Federation member accounts</p>
            </div>
            <div class="page-header-right">
                <div class="search-add-container">
                    <div class="filter-dropdown-container">
                        <select id="accountTypeFilter" class="filter-dropdown">
                            <option value="sk_federation">SK Federation</option>
                            <option value="sk_officials">SK Officials</option>
                        </select>
                    </div>
                    <div class="search-container">
                        <input type="text" id="searchInput" class="search-input" placeholder="Search accounts...">
                        <button type="button" class="search-btn" id="searchBtn">
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
                        <span id="addButtonText">Add SK Federation</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card-modern">
            <div class="table-responsive">
                <table class="accounts-table">
                    <thead>
                        <tr>
                            <th colspan="2" class="table-group-header">Personal Information</th>
                            <th colspan="4" class="table-group-header">Location Information</th>
                            <th colspan="5" class="table-group-header">Term Information</th>
                        </tr>
                        <tr>
                            <!-- Table Columns -->
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
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Record 1 -->
                        <tr>
                            <td>Jerome Balberona</td>
                            <td>jerome.balberona@example.com</td>
                            <td>San Roque</td>
                            <td>San Pablo City</td>
                            <td>Laguna</td>
                            <td>Region IV-A (CALABARZON)</td>
                            <td>SK Chairman</td>
                            <td>01/01/2023</td>
                            <td>12/31/2025</td>
                            <td><span class="status-badge active">Active</span></td>
                            <td>
                                <button type="button" class="btn-edit-modern" onclick="openEditModal(0)">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    Edit
                                </button>
                            </td>
                        </tr>
                        <!-- Record 2 -->
                        <tr>
                            <td>Maria Santos</td>
                            <td>maria.santos@example.com</td>
                            <td>Santo Niño</td>
                            <td>San Pablo City</td>
                            <td>Laguna</td>
                            <td>Region IV-A (CALABARZON)</td>
                            <td>SK Kagawad</td>
                            <td>01/01/2023</td>
                            <td>12/31/2025</td>
                            <td><span class="status-badge active">Active</span></td>
                            <td>
                                <button type="button" class="btn-edit-modern" onclick="openEditModal(1)">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    Edit
                                </button>
                            </td>
                        </tr>
                        <!-- Record 3 -->
                        <tr>
                            <td>John Cruz</td>
                            <td>john.cruz@example.com</td>
                            <td>San Antonio</td>
                            <td>Calauan</td>
                            <td>Laguna</td>
                            <td>Region IV-A (CALABARZON)</td>
                            <td>SK Treasurer</td>
                            <td>01/01/2023</td>
                            <td>12/31/2025</td>
                            <td><span class="status-badge active">Active</span></td>
                            <td>
                                <button type="button" class="btn-edit-modern" onclick="openEditModal(2)">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    Edit
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Include Add SK Federation Modal -->
@include('manage_account::add_sk_fed')
<!-- Include Add SK Officials Modal -->
@include('manage_account::add_sk_officials')
<!-- Include Edit SK Federation Modal -->
@include('manage_account::edit_sk_fed')
<!-- Include Edit SK Officials Modal -->
@include('manage_account::edit_sk_officials')
@endsection

@section('scripts')
    <script src="{{ asset('admin/modules/manage_account/assets/js/manage_account.js') }}"></script>
    <script src="{{ asset('admin/modules/manage_account/assets/js/edit_sk_fed.js') }}"></script>
    <script src="{{ asset('admin/modules/manage_account/assets/js/edit_sk_officials.js') }}"></script>
@endsection