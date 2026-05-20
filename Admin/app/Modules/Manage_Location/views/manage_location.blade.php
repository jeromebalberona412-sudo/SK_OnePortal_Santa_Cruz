@extends('layouts.app')

@section('title', 'Manage Location')

@section('head')
    @vite(['app/Modules/Manage_Location/assets/css/manage_location.css'])
@endsection

@section('content')
@include('layout::header')
@include('layout::sidebar')

<div id="mainContent" class="main-content-modern ml-page">

    {{-- Page Header --}}
    <div class="ml-page-header">
        <div class="ml-header-left">
            <h1 class="ml-page-title">Manage Location</h1>
            <p class="ml-page-subtitle">Manage Barangay, Purok, and Sitio locations for the SK OnePortal system.</p>
        </div>
        <div class="ml-header-right">
            <button type="button" id="mlAddBtn" class="ml-btn-add">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add Barangay
            </button>
            <div class="ml-search-wrap">
                <svg class="ml-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" id="mlSearch" class="ml-search-input" placeholder="Search by Barangay, Purok, or Sitio…" autocomplete="off">
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="ml-stats-row" id="mlStatsRow"></div>

    {{-- Table Card --}}
    <div class="ml-table-card">
        {{-- Loading overlay --}}
        <div class="ml-table-loading" id="mlTableLoading" style="display:none;">
            <div class="ml-spinner"></div>
            <span>Loading records…</span>
        </div>

        <div class="ml-table-wrapper">
            <table class="ml-table" id="mlTable">
                <thead>
                    <tr>
                        <th>Barangay Name</th>
                        <th>Purok</th>
                        <th>Sitio</th>
                        <th class="ml-col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="mlTableBody">
                    @forelse($barangays as $b)
                        <tr
                            data-id="{{ $b->id }}"
                            data-name="{{ $b->name }}"
                            data-municipality="{{ $b->municipality ?? '' }}"
                            data-province="{{ $b->province ?? '' }}"
                            data-region="{{ $b->region ?? '' }}"
                            data-status="{{ $b->status ?? 'Active' }}"
                            data-total-purok="{{ $b->total_purok }}"
                            data-total-sitio="{{ $b->total_sitio }}"
                        >
                            <td class="ml-name-cell">{{ $b->name }}</td>
                            <td class="ml-count-cell">{{ $b->total_purok }}</td>
                            <td class="ml-count-cell">{{ $b->total_sitio }}</td>
                            <td>
                                <div class="ml-action-btns">
                                    <button type="button" class="ml-btn-view" data-id="{{ $b->id }}" aria-label="View {{ $b->name }}">View</button>
                                    <button type="button" class="ml-btn-edit" data-id="{{ $b->id }}" aria-label="Edit {{ $b->name }}">Edit</button>
                                    <button type="button" class="ml-btn-delete" data-id="{{ $b->id }}" aria-label="Delete {{ $b->name }}">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="ml-empty-state">
                                <div class="ml-empty-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                </div>
                                <p class="ml-empty-text">No Barangay records found</p>
                                <p class="ml-empty-subtext">Click "Add Barangay" to create your first location record</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="ml-pagination">
            <span class="ml-pagination-info" id="mlPaginationInfo">No records</span>
            <div class="ml-pagination-controls">
                <button type="button" id="mlPrevBtn" class="ml-page-btn" disabled>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    Previous
                </button>
                <div id="mlPageNumbers" class="ml-page-numbers"></div>
                <button type="button" id="mlNextBtn" class="ml-page-btn" disabled>
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
<div class="ml-modal-backdrop" id="mlViewModal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="mlViewModalTitle">
    <div class="ml-modal-box ml-view-modal-box" id="mlViewModalBox">
        <div class="ml-modal-header ml-view-modal-header">
            <h2 class="ml-modal-title" id="mlViewModalTitle">Barangay Details</h2>
            <div class="ml-view-controls">
                <button type="button" class="ml-view-toggle" id="mlViewToggleBtn" aria-label="Maximize">□</button>
                <button type="button" class="ml-view-close" id="mlViewCloseBtn" aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="ml-view-body" id="mlViewBody"></div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     ADD/EDIT MODAL
═══════════════════════════════════════════════════════════ --}}
<div class="ml-modal-backdrop" id="mlEditModal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="mlEditModalTitle">
    <div class="ml-modal-box ml-edit-modal-box" id="mlEditModalBox">
        <div class="ml-modal-header ml-edit-modal-header">
            <h2 class="ml-modal-title" id="mlEditModalTitle">Add Barangay</h2>
            <div class="ml-edit-controls">
                <button type="button" class="ml-edit-toggle" id="mlEditToggleBtn" aria-label="Maximize">□</button>
                <button type="button" class="ml-edit-close" id="mlEditCloseBtn" aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="ml-edit-body">
            <form id="mlEditForm" novalidate>
                @csrf
                <input type="hidden" id="mlEditId" name="id">

                {{-- Section: Barangay Information --}}
                <div class="ml-form-section">
                    <div class="ml-form-section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span>Barangay Information</span>
                    </div>
                    <div class="ml-form-grid">
                        <div class="ml-form-group">
                            <label class="ml-form-label" for="mlEditName">Barangay Name <span class="ml-required">*</span></label>
                            <input type="text" id="mlEditName" name="name" class="ml-form-input" required>
                            <span class="ml-field-error" id="errName"></span>
                        </div>
                        <div class="ml-form-group">
                            <label class="ml-form-label" for="mlEditMunicipality">Municipality <span class="ml-required">*</span></label>
                            <input type="text" id="mlEditMunicipality" name="municipality" class="ml-form-input" required>
                            <span class="ml-field-error" id="errMunicipality"></span>
                        </div>
                        <div class="ml-form-group">
                            <label class="ml-form-label" for="mlEditProvince">Province <span class="ml-required">*</span></label>
                            <input type="text" id="mlEditProvince" name="province" class="ml-form-input" required>
                            <span class="ml-field-error" id="errProvince"></span>
                        </div>
                        <div class="ml-form-group">
                            <label class="ml-form-label" for="mlEditRegion">Region <span class="ml-required">*</span></label>
                            <input type="text" id="mlEditRegion" name="region" class="ml-form-input" required>
                            <span class="ml-field-error" id="errRegion"></span>
                        </div>
                    </div>
                </div>

                {{-- Section: Purok List --}}
                <div class="ml-form-section">
                    <div class="ml-form-section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                        </svg>
                        <span>Purok List</span>
                        <button type="button" class="ml-btn-add-item" id="mlAddPurokBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Add Purok
                        </button>
                    </div>
                    <div id="mlPurokList" class="ml-dynamic-list"></div>
                </div>

                {{-- Section: Sitio List --}}
                <div class="ml-form-section">
                    <div class="ml-form-section-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        <span>Sitio List</span>
                        <button type="button" class="ml-btn-add-item" id="mlAddSitioBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Add Sitio
                        </button>
                    </div>
                    <div id="mlSitioList" class="ml-dynamic-list"></div>
                </div>

                {{-- Form Footer --}}
                <div class="ml-form-footer">
                    <button type="button" class="ml-btn-cancel" id="mlEditCancelBtn">Cancel</button>
                    <button type="submit" class="ml-btn-save" id="mlEditSaveBtn">
                        <span id="mlEditSaveBtnText">Save Changes</span>
                        <span id="mlEditSaveBtnSpinner" class="ml-btn-spinner" style="display:none;"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Toast --}}
<div id="mlToast" class="ml-toast" role="status" aria-live="polite">
    <span id="mlToastMsg"></span>
</div>

{{-- Delete Confirmation Modal --}}
<div class="ml-modal-backdrop ml-confirm-modal" id="mlDeleteModal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="mlDeleteModalTitle">
    <div class="ml-modal-box ml-confirm-modal-box">
        <div class="ml-confirm-icon ml-confirm-icon-danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
        </div>
        <h2 class="ml-confirm-title" id="mlDeleteModalTitle">Delete Barangay?</h2>
        <p class="ml-confirm-message" id="mlDeleteModalMessage">Are you sure you want to delete this barangay? This action cannot be undone.</p>
        <div class="ml-confirm-actions">
            <button type="button" class="ml-btn-confirm-cancel" id="mlDeleteCancelBtn">Cancel</button>
            <button type="button" class="ml-btn-confirm-delete" id="mlDeleteConfirmBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    <line x1="10" y1="11" x2="10" y2="17"/>
                    <line x1="14" y1="11" x2="14" y2="17"/>
                </svg>
                Delete Barangay
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    @vite(['app/Modules/Manage_Location/assets/js/manage_location.js'])
@endsection
