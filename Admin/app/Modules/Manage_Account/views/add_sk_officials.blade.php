<!-- Add SK Officials Modal -->
<div id="addSkOfficialsModal" class="modal-overlay" style="display: none;">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3 class="modal-title">Add SK Officials Account</h3>
            <button type="button" class="modal-close-btn" onclick="closeAddSkOfficialsModal()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="addSkOfficialsForm" class="sk-fed-form">
                @csrf
                <div class="form-section">
                    <h4 class="section-title">Personal Information</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="official_full_name" class="form-label-modern required">
                                    Full Name
                                </label>
                                <input type="text" id="official_full_name" name="full_name" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="official_email" class="form-label-modern required">
                                    Email
                                </label>
                                <input type="email" id="official_email" name="email" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="official_password" class="form-label-modern required">
                                    Password
                                </label>
                                <input type="password" id="official_password" name="password" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="official_sk_role" class="form-label-modern required">
                                    SK Role
                                </label>
                                <select id="official_sk_role" name="sk_role" class="form-input-modern" required>
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
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="section-title">Location Information</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="official_barangay" class="form-label-modern required">
                                    Barangay
                                </label>
                                <input type="text" id="official_barangay" name="barangay" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="official_municipality" class="form-label-modern required">
                                    Municipality
                                </label>
                                <input type="text" id="official_municipality" name="municipality" class="form-input-modern" required>
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
                                <label for="official_term_start" class="form-label-modern required">
                                    Term Start
                                </label>
                                <input type="date" id="official_term_start" name="term_start" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="official_term_end" class="form-label-modern required">
                                    Term End
                                </label>
                                <input type="date" id="official_term_end" name="term_end" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="official_status" class="form-label-modern required">
                                    Status
                                </label>
                                <select id="official_status" name="status" class="form-input-modern" required>
                                    <option value="">Select Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary-modern" onclick="closeAddSkOfficialsModal()">Cancel</button>
            <button type="submit" form="addSkOfficialsForm" class="btn-primary-modern btn-green">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 4v16m8-8H4"/>
                </svg>
                Create Account
            </button>
        </div>
    </div>
</div>

<!-- SK Officials Success Modal -->
<div id="skOfficialsSuccessModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title"></h3>
        </div>
        <div class="modal-body">
            <p>SK Officials account has been created successfully.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary-modern btn-blue" onclick="closeSkOfficialsSuccessModal()">OK</button>
        </div>
    </div>
</div>