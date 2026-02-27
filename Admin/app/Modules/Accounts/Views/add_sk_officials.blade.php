<!-- Add SK Officials Modal -->
<div id="addSkOfficialsModal" class="modal-overlay" style="display: none;">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3 class="modal-title">Add Sk Official Account</h3>
            <button type="button" class="modal-close-btn" onclick="closeAddSkOfficialsModal()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="addSkOfficialsForm" class="sk-fed-form">
                @csrf
                <input type="hidden" name="role" value="sk_official">
                <input type="hidden" name="term_status" value="ACTIVE">
                <div class="form-section">
                    <h4 class="section-title">Personal Information</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="official_first_name" class="form-label-modern required">
                                    First Name
                                </label>
                                <input type="text" id="official_first_name" name="first_name" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="official_last_name" class="form-label-modern required">
                                    Last Name
                                </label>
                                <input type="text" id="official_last_name" name="last_name" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="official_middle_name" class="form-label-modern">
                                    Middle Name / Initial
                                </label>
                                <input type="text" id="official_middle_name" name="middle_name" class="form-input-modern" maxlength="100" placeholder="e.g., Marie">
                                <span class="form-error"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="official_suffix" class="form-label-modern">
                                    Suffix
                                </label>
                                <select id="official_suffix" name="suffix" class="form-input-modern">
                                    <option value="">Select Suffix</option>
                                    <option value="Jr.">Jr.</option>
                                    <option value="Sr.">Sr.</option>
                                    <option value="II">II</option>
                                    <option value="III">III</option>
                                    <option value="IV">IV</option>
                                    <option value="V">V</option>
                                </select>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="official_email" class="form-label-modern required">
                                    Email Address
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
                                <label for="official_password_confirmation" class="form-label-modern required">
                                    Confirm Password
                                </label>
                                <input type="password" id="official_password_confirmation" name="password_confirmation" class="form-input-modern" required>
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
                                <label for="official_barangay_id" class="form-label-modern required">
                                    Barangay
                                </label>
                                <select id="official_barangay_id" name="barangay_id" class="form-input-modern" required>
                                    <option value="">Select Barangay</option>
                                    @foreach($barangays as $barangay)
                                        <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
                                    @endforeach
                                </select>
                                <span class="form-error"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="official_municipality" class="form-label-modern required">
                                    Municipality
                                </label>
                                <input type="text" id="official_municipality" name="municipality" class="form-input-modern" value="Santa Cruz" readonly required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="official_province" class="form-label-modern required">
                                    Province
                                </label>
                                <input type="text" id="official_province" name="province" class="form-input-modern" value="Laguna" readonly required>
                                <span class="form-error"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="official_region" class="form-label-modern required">
                                    Region
                                </label>
                                <input type="text" id="official_region" name="region" class="form-input-modern" value="IV-A CALABARZON" readonly required>
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
                                <label for="official_position" class="form-label-modern required">
                                    Position (SK Role)
                                </label>
                                <select id="official_position" name="position" class="form-input-modern" required>
                                    <option value="">Select Position</option>
                                    <option value="Chairman">SK Chairman</option>
                                    <option value="Councilor">SK Councilor</option>
                                    <option value="Kagawad">SK Kagawad</option>
                                    <option value="Treasurer">SK Treasurer</option>
                                    <option value="Secretary">SK Secretary</option>
                                    <option value="Auditor">SK Auditor</option>
                                    <option value="PIO">SK PIO</option>
                                </select>
                                <span class="form-error"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="official_term_start" class="form-label-modern required">
                                    Term Start
                                </label>
                                <input type="date" id="official_term_start" name="term_start" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
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
                                    <option value="ACTIVE">Active</option>
                                    <option value="INACTIVE">Inactive</option>
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
