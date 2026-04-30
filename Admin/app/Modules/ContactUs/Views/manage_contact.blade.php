@extends('layouts.app')

@section('title', 'Manage Contact')

@section('head')
    @vite(['app/Modules/ContactUs/assets/css/contact.css'])
@endsection

@section('content')
@include('layout::header')
@include('layout::sidebar')

<div class="main-content-modern contact-page" id="mainContent">
    <div class="manage-contact-container">

        {{-- Page Header --}}
        <div class="page-header-modern-with-button">
            <div class="page-header-left">
                <h1 class="page-title-modern">Manage Contact</h1>
                <p class="page-subtitle-modern">Manage contact details displayed on the homepage</p>
            </div>
            <div class="page-header-right">
                <button type="button" class="btn-primary-modern btn-green" id="btnAddContact">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Contact
                </button>
            </div>
        </div>

        {{-- Contacts Table Card --}}
        <div class="contact-table-card">
            <div class="contact-table-wrapper">
                <table class="contact-table" id="contactTable">
                    <thead>
                        <tr>
                            <th>Platform</th>
                            <th>Contact Detail</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="contactTableBody">
                        {{-- Rows rendered by JS --}}
                    </tbody>
                </table>

                {{-- Empty State --}}
                <div class="contact-empty-state" id="emptyState" style="display:none;">
                    <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    <p>No contacts yet</p>
                    <span>Click "Add Contact" to add your first contact entry</span>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ── ADD CONTACT MODAL ── --}}
<div class="modal-overlay" id="addContactModal" style="display:none;">
    <div class="modal-container" id="addModalContainer">
        <div class="modal-header modal-header-blue">
            <h3 class="modal-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 4v16m8-8H4"/>
                </svg>
                Add New Contact
            </h3>
            <div class="modal-controls">
                <button type="button" class="contact-view-toggle" id="btnMaxAdd" aria-label="Maximize">□</button>
                <button type="button" class="contact-view-close" id="btnCloseAdd">&times;</button>
            </div>
        </div>
        <div class="modal-body">
            <form id="addContactForm">

                <div class="form-group-modern">
                    <label for="addLabel" class="form-label-modern">Platform <span class="required-star">*</span> <span class="form-hint">(e.g. "Main Office", "Facebook Page")</span></label>
                    <input type="text" id="addLabel" class="form-input-modern" placeholder="Enter platform name" required>
                    <span class="field-error" id="addLabelError">Platform name is required.</span>
                </div>

                <div class="form-group-dynamic visible" id="addValueGroup">
                    <label for="addValue" class="form-label-modern">Contact Detail <span class="required-star">*</span></label>
                    <input type="text" id="addValue" class="form-input-modern" placeholder="Enter the contact detail">
                    <span class="field-error" id="addValueError">Contact detail is required.</span>
                </div>

            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary-modern" id="btnCancelAdd">Cancel</button>
            <button type="button" class="btn-primary-modern btn-green" id="btnSubmitAdd">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M12 4v16m8-8H4"/>
                </svg>
                Add Contact
            </button>
        </div>
    </div>
</div>

{{-- ── EDIT CONTACT MODAL ── --}}
<div class="modal-overlay" id="editContactModal" style="display:none;">
    <div class="modal-container" id="editModalContainer">
        <div class="modal-header modal-header-yellow">
            <h3 class="modal-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit Contact
            </h3>
            <div class="modal-controls">
                <button type="button" class="contact-view-toggle" id="btnMaxEdit" aria-label="Maximize">□</button>
                <button type="button" class="contact-view-close" id="btnCloseEdit">&times;</button>
            </div>
        </div>
        <div class="modal-body">
            <form id="editContactForm" onsubmit="return false;">
                <input type="hidden" id="editContactId">

                <div class="form-group-modern">
                    <label for="editLabel" class="form-label-modern">Platform <span class="required-star">*</span> <span class="form-hint">(e.g. "Main Office", "Facebook Page")</span></label>
                    <input type="text" id="editLabel" class="form-input-modern" placeholder="Enter platform name" required>
                    <span class="field-error" id="editLabelError">Platform name is required.</span>
                </div>

                <div class="form-group-dynamic visible" id="editValueGroup">
                    <label for="editValue" class="form-label-modern">Contact Detail <span class="required-star">*</span></label>
                    <input type="text" id="editValue" class="form-input-modern" placeholder="Enter the contact detail" required>
                    <span class="field-error" id="editValueError">Contact detail is required.</span>
                </div>

            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary-modern" id="btnCancelEdit">Cancel</button>
            <button type="button" class="btn-primary-modern btn-green" id="btnSubmitEdit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Save Changes
            </button>
        </div>
    </div>
</div>

{{-- ── DELETE CONFIRMATION MODAL ── --}}
<div class="modal-overlay" id="deleteContactModal" style="display:none;">
    <div class="modal-container modal-small">
        <div class="modal-body delete-modal-body">
            <div class="delete-warning">
                <p class="delete-confirm-text">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:6px;color:#dc2626;flex-shrink:0;">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                    Are you sure you want to delete
                </p>
                <p class="delete-confirm-name" id="deleteContactLabel">"this contact"</p>
                <p class="delete-warning-sub">This action cannot be undone.</p>
            </div>
        </div>
        <div class="modal-footer modal-footer-center modal-footer-plain">
            <button type="button" class="btn-secondary-modern" id="btnCancelDelete">Cancel</button>
            <button type="button" class="btn-primary-modern btn-delete" id="btnSubmitDelete">Delete</button>
        </div>
    </div>
</div>

{{-- ── TOAST (matches account module design, text only) ── --}}
<div id="contactToastAdd" role="status" aria-live="polite">
    <span id="contactToastAddMsg">Contact added successfully!</span>
</div>

<div id="contactToastEdit" role="status" aria-live="polite">
    <span id="contactToastEditMsg">Contact updated successfully!</span>
</div>

<div id="contactToastDelete" role="status" aria-live="polite">
    <span id="contactToastDeleteMsg">Contact deleted successfully!</span>
</div>

@endsection

@section('scripts')
    @vite(['app/Modules/ContactUs/assets/js/contact.js'])
@endsection
