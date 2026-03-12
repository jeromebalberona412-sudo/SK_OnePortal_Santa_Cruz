<!-- Add SK Federation Modal -->
<div id="addAccountModal" class="modal-overlay" style="display: none;">
    <div class="modal-content modal-large modal-themed">
        <div class="modal-header modal-header-green">
            <h3 class="modal-title">Add SK Federation Account</h3>
            <div class="modal-controls">
                <button type="button" class="modal-fullscreen-btn" onclick="toggleFullscreenAddAccountModal()" title="Fullscreen">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                    </svg>
                </button>
                <button type="button" class="modal-close-btn" onclick="closeAddAccountModal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="modal-body">
            <form id="addSkFedForm" class="sk-fed-form">
                @csrf
                <input type="hidden" name="role" value="sk_fed">
                <input type="hidden" name="term_status" value="ACTIVE">
                <div class="form-section">
                    <h4 class="section-title">Personal Information</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="first_name" class="form-label-modern required">
                                    First Name
                                </label>
                                <input type="text" id="first_name" name="first_name" class="form-input-modern" placeholder="Enter your First Name" required>
                                <span class="form-error"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="last_name" class="form-label-modern required">
                                    Last Name
                                </label>
                                <input type="text" id="last_name" name="last_name" class="form-input-modern" placeholder="Enter your Last Name" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="middle_name" class="form-label-modern">
                                    Middle Name / Initial
                                </label>
                                <input type="text" id="middle_name" name="middle_name" class="form-input-modern" maxlength="100" placeholder="Enter your Middle Name / Initial">
                                <span class="form-error"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="suffix" class="form-label-modern">
                                    Suffix
                                </label>
                                <select id="suffix" name="suffix" class="form-input-modern" required>
                                    <option value="" disabled selected>Select Suffix</option>
                                    <option value="None">None</option>
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
                                <label for="email" class="form-label-modern required">
                                    Email Address
                                </label>
                                <input type="email" id="email" name="email" class="form-input-modern" placeholder="Enter your Email Address" required>
                                <span class="form-error"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="contact_number" class="form-label-modern required">
                                    Contact Number
                                </label>
                                <input type="text" id="contact_number" name="contact_number" class="form-input-modern" maxlength="20" placeholder="Enter your Contact Number" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="date_of_birth" class="form-label-modern required">
                                    Date of Birth
                                </label>
                                <input type="date" id="date_of_birth" name="date_of_birth" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="age" class="form-label-modern required">
                                    Age
                                </label>
                                <input type="number" id="age" name="age" class="form-input-modern" min="0" max="150" readonly required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="password" class="form-label-modern required">
                                    Password
                                </label>
                                <div class="password-input-container">
                                    <input type="password" id="password" name="password" class="form-input-modern" placeholder="Enter your Password" required>
                                    <button type="button" id="passwordToggle" class="password-toggle-btn">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </div>
                                <span class="form-error"></span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="password_confirmation" class="form-label-modern required">
                                    Confirm Password
                                </label>
                                <div class="password-input-container">
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input-modern" placeholder="Confirm your Password" required>
                                    <button type="button" id="confirmPasswordToggle" class="password-toggle-btn">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </div>
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
                                <label for="barangay_id" class="form-label-modern required">
                                    Barangay
                                </label>
                                <select id="barangay_id" name="barangay_id" class="form-input-modern" required>
                                    <option value="" disabled selected>Select Barangay</option>
                                    @foreach($barangays as $barangay)
                                        <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
                                    @endforeach
                                </select>
                                <span class="form-error"></span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="municipality" class="form-label-modern required">
                                    Municipality
                                </label>
                                <input type="text" id="municipality" name="municipality" class="form-input-modern" value="Santa Cruz" readonly required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="province" class="form-label-modern required">
                                    Province
                                </label>
                                <input type="text" id="province" name="province" class="form-input-modern" value="Laguna" readonly required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="region" class="form-label-modern required">
                                    Region
                                </label>
                                <input type="text" id="region" name="region" class="form-input-modern" value="IV-A CALABARZON" readonly required>
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
                                <label for="position" class="form-label-modern required">
                                    Position (SK Federations Role)
                                </label>
                                <select id="position" name="position" class="form-input-modern" required>
                                    <option value="" disabled selected>Select Position</option>
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
                                <label for="status" class="form-label-modern required">
                                    Status
                                </label>
                                <select id="status" name="status" class="form-input-modern" required>
                                    <option value="" disabled selected>Select Status</option>
                                    <option value="ACTIVE">Active</option>
                                    <option value="INACTIVE">Inactive</option>
                                </select>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="term_start" class="form-label-modern required">
                                    Term Start
                                </label>
                                <input type="date" id="term_start" name="term_start" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="term_end" class="form-label-modern required">
                                    Term End
                                </label>
                                <input type="date" id="term_end" name="term_end" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Action Buttons -->
                <div class="form-actions">
                    <button type="button" class="btn-secondary-modern" onclick="closeAddAccountModal()">Cancel</button>
                    <button type="submit" form="addSkFedForm" class="btn-primary-modern btn-green">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 4v16m8-8H4"/>
                        </svg>
                        Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="addSuccessModal" class="modal-overlay" style="display: none;">
    <div class="modal-content modal-themed">
        <div class="modal-header modal-header-teal">
            <h3 class="modal-title">Success</h3>
        </div>
        <div class="modal-body">
            <p>Account successfully created!</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary-modern btn-teal" onclick="closeAddSuccessModal()">OK</button>
        </div>
    </div>
</div>

@vite(['app/Modules/Accounts/assets/js/add_sk_fed.js'])
