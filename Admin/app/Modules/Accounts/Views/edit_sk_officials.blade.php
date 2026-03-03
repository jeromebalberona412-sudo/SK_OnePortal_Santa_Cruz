<!-- Edit SK Officials Modal -->
@vite(['app/Modules/Accounts/assets/css/edit_sk_officials.css'])
<div id="editSkOfficialsModal" class="modal-overlay" style="display: none;">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3 class="modal-title">Edit SK Officials Account</h3>
            <div class="modal-controls">
                <button type="button" class="modal-fullscreen-btn" onclick="toggleFullscreenEditSkOfficialsModal()" title="Fullscreen">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                    </svg>
                </button>
                <button type="button" class="modal-close-btn" onclick="closeEditSkOfficialsModal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="modal-body">
            <form id="editSkOfficialsForm" class="sk-fed-form" data-account-id="">
                @csrf
                <input type="hidden" name="term_status" id="edit_sk_officials_term_status" value="ACTIVE">

                <div class="form-section">
                    <h4 class="section-title">Personal Information</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_first_name" class="form-label-modern required">First Name</label>
                                <input type="text" id="edit_sk_officials_first_name" name="first_name" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_last_name" class="form-label-modern required">Last Name</label>
                                <input type="text" id="edit_sk_officials_last_name" name="last_name" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_middle_name" class="form-label-modern">Middle Name / Initial</label>
                                <input type="text" id="edit_sk_officials_middle_name" name="middle_name" class="form-input-modern" maxlength="100" placeholder="e.g., Marie">
                                <span class="form-error"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_suffix" class="form-label-modern">Suffix</label>
                                <select id="edit_sk_officials_suffix" name="suffix" class="form-input-modern">
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
                                <label for="edit_sk_officials_email" class="form-label-modern required">Email Address</label>
                                <input type="email" id="edit_sk_officials_email" name="email" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_contact_number" class="form-label-modern required">Contact Number</label>
                                <input type="text" id="edit_sk_officials_contact_number" name="contact_number" class="form-input-modern" maxlength="20" placeholder="e.g., 09171234567" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_date_of_birth" class="form-label-modern required">Date of Birth</label>
                                <input type="date" id="edit_sk_officials_date_of_birth" name="date_of_birth" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_age" class="form-label-modern required">Age</label>
                                <input type="number" id="edit_sk_officials_age" name="age" class="form-input-modern" min="0" max="150" readonly required>
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
                                <label for="edit_sk_officials_position" class="form-label-modern required">SK Role</label>
                                <select id="edit_sk_officials_position" name="position" class="form-input-modern" required>
                                    <option value="">Select SK Role</option>
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
                                <label for="edit_sk_officials_barangay_id" class="form-label-modern required">Barangay</label>
                                <select id="edit_sk_officials_barangay_id" name="barangay_id" class="form-input-modern" required>
                                    <option value="">Select Barangay</option>
                                    @foreach($barangays as $barangay)
                                        <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
                                    @endforeach
                                </select>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_municipality" class="form-label-modern required">Municipality</label>
                                <input type="text" id="edit_sk_officials_municipality" class="form-input-modern" value="Santa Cruz" readonly required>
                                <span class="form-error"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_status" class="form-label-modern required">Status</label>
                                <select id="edit_sk_officials_status" name="status" class="form-input-modern" required>
                                    <option value="">Select Status</option>
                                    <option value="ACTIVE">Active</option>
                                    <option value="INACTIVE">Inactive</option>
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
                                <label for="edit_sk_officials_term_start" class="form-label-modern required">Term Start</label>
                                <input type="date" id="edit_sk_officials_term_start" name="term_start" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="edit_sk_officials_term_end" class="form-label-modern required">Term End</label>
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

@vite(['app/Modules/Accounts/assets/js/edit_sk_officials.js'])
