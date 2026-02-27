<!-- Edit SK Officials Modal -->
<link rel="stylesheet" href="{{ asset('admin/modules/manage_account/assets/css/edit_sk_officials.css') }}">
<div id="editSkOfficialsModal" class="modal-overlay" style="display: none;">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3 class="modal-title">Edit SK Officials Account</h3>
            <button type="button" class="modal-close-btn" onclick="closeEditSkOfficialsModal()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="editSkOfficialsForm" class="sk-officials-form" onsubmit="handleEditSkOfficialsSubmit(event)">
                @csrf
                <div class="form-section">
                    <h4 class="section-title">Personal Information</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_full_name" class="form-label-modern required">
                                    Full Name
                                </label>
                                <input type="text" id="edit_sk_officials_full_name" name="full_name" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_email" class="form-label-modern required">
                                    Email Address
                                </label>
                                <input type="email" id="edit_sk_officials_email" name="email" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" id="edit_sk_officials_password_row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_password" class="form-label-modern">
                                    Password (Leave blank to keep current)
                                </label>
                                <input type="password" id="edit_sk_officials_password" name="password" class="form-input-modern">
                                <span class="form-error"></span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_password_confirmation" class="form-label-modern">
                                    Confirm Password
                                </label>
                                <input type="password" id="edit_sk_officials_password_confirmation" name="password_confirmation" class="form-input-modern">
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="section-title">SK Role & Location</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_sk_role" class="form-label-modern required">
                                    SK Role
                                </label>
                                <select id="edit_sk_officials_sk_role" name="sk_role" class="form-input-modern" required>
                                    <option value="">Select SK Role</option>
                                    <option value="sk_chairman">SK Chairman</option>
                                    <option value="sk_councilor">SK Councilor</option>
                                    <option value="sk_kagawad">SK Kagawad</option>
                                    <option value="sk_treasurer">SK Treasurer</option>
                                    <option value="sk_secretary">SK Secretary</option>
                                    <option value="sk_auditor">SK Auditor</option>
                                    <option value="sk_pio">SK PIO</option>
                                </select>
                                <span class="form-error"></span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_barangay" class="form-label-modern required">
                                    Barangay
                                </label>
                                <input type="text" id="edit_sk_officials_barangay" name="barangay" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_municipality" class="form-label-modern required">
                                    Municipality
                                </label>
                                <input type="text" id="edit_sk_officials_municipality" name="municipality" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_status" class="form-label-modern required">
                                    Status
                                </label>
                                <select id="edit_sk_officials_status" name="status" class="form-input-modern" required>
                                    <option value="">Select Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="section-title">Term Information</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_term_start" class="form-label-modern required">
                                    Term Start
                                </label>
                                <input type="date" id="edit_sk_officials_term_start" name="term_start" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_term_end" class="form-label-modern required">
                                    Term End
                                </label>
                                <input type="date" id="edit_sk_officials_term_end" name="term_end" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary-modern" onclick="closeEditSkOfficialsModal()">Cancel</button>
            <button type="submit" form="editSkOfficialsForm" class="btn-primary-modern btn-blue">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Update Account
            </button>
        </div>
    </div>
</div>

<!-- Edit SK Officials Success Modal -->
<div id="editSkOfficialsSuccessModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Success</h3>
        </div>
        <div class="modal-body">
            <p>SK Officials account has been updated successfully.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary-modern btn-blue" onclick="closeEditSkOfficialsSuccessModal()">OK</button>
        </div>
    </div>
</div>