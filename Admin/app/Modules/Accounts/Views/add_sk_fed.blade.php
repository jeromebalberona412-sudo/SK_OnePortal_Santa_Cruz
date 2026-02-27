<!-- Add SK Federation Modal -->
<div id="addAccountModal" class="modal-overlay" style="display: none;">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3 class="modal-title">Add SK Federation Account</h3>
            <button type="button" class="modal-close-btn" onclick="closeAddAccountModal()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="addSkFedForm" class="sk-fed-form">
                @csrf
                <div class="form-section">
                    <h4 class="section-title">Personal Information</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="first_name" class="form-label-modern required">
                                    First Name
                                </label>
                                <input type="text" id="first_name" name="first_name" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="last_name" class="form-label-modern required">
                                    Last Name
                                </label>
                                <input type="text" id="last_name" name="last_name" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="middle_initial" class="form-label-modern">
                                    Middle Initial
                                </label>
                                <input type="text" id="middle_initial" name="middle_initial" class="form-input-modern" maxlength="5">
                                <span class="form-error"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="suffix" class="form-label-modern">
                                    Suffix
                                </label>
                                <input type="text" id="suffix" name="suffix" class="form-input-modern" maxlength="20">
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
                                <input type="email" id="email" name="email" class="form-input-modern" required>
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
                                <input type="password" id="password" name="password" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="password_confirmation" class="form-label-modern required">
                                    Confirm Password
                                </label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input-modern" required>
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
                                <label for="barangay" class="form-label-modern required">
                                    Barangay
                                </label>
                                <select id="barangay" name="barangay" class="form-input-modern" required>
                                    <option value="">Select Barangay</option>
                                    <option value="Alipit">Alipit</option>
                                    <option value="Bagumbayan">Bagumbayan</option>
                                    <option value="Bubukal">Bubukal</option>
                                    <option value="Calios">Calios</option>
                                    <option value="Duhat">Duhat</option>
                                    <option value="Gatid">Gatid</option>
                                    <option value="Jasaan">Jasaan</option>
                                    <option value="Labuin">Labuin</option>
                                    <option value="Malinao">Malinao</option>
                                    <option value="Oogong">Oogong</option>
                                    <option value="Pagsawitan">Pagsawitan</option>
                                    <option value="Palasan">Palasan</option>
                                    <option value="Patimbao">Patimbao</option>
                                    <option value="Poblacion I">Poblacion I</option>
                                    <option value="Poblacion II">Poblacion II</option>
                                    <option value="Poblacion III">Poblacion III</option>
                                    <option value="Poblacion IV">Poblacion IV</option>
                                    <option value="Poblacion V">Poblacion V</option>
                                    <option value="San Jose">San Jose</option>
                                    <option value="San Juan">San Juan</option>
                                    <option value="San Pablo Norte">San Pablo Norte</option>
                                    <option value="San Pablo Sur">San Pablo Sur</option>
                                    <option value="Santisima Cruz">Santisima Cruz</option>
                                    <option value="Santo Angel Central">Santo Angel Central</option>
                                    <option value="Santo Angel Norte">Santo Angel Norte</option>
                                    <option value="Santo Angel Sur">Santo Angel Sur</option>
                                </select>
                                <span class="form-error"></span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="municipality" class="form-label-modern required">
                                    Municipality
                                </label>
                                <input type="text" id="municipality" name="municipality" class="form-input-modern" value="Sta. Cruz" readonly required>
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
                                    Position (SK Role)
                                </label>
                                <select id="position" name="position" class="form-input-modern" required>
                                    <option value="">Select Position</option>
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
                                <label for="term_start" class="form-label-modern required">
                                    Term Start
                                </label>
                                <input type="date" id="term_start" name="term_start" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="term_end" class="form-label-modern required">
                                    Term End
                                </label>
                                <input type="date" id="term_end" name="term_end" class="form-input-modern" required>
                                <span class="form-error"></span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label for="status" class="form-label-modern required">
                                    Status
                                </label>
                                <select id="status" name="status" class="form-input-modern" required>
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
            <button type="button" class="btn-secondary-modern" onclick="closeAddAccountModal()">Cancel</button>
            <button type="submit" form="addSkFedForm" class="btn-primary-modern btn-green">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 4v16m8-8H4"/>
                </svg>
                Create Account
            </button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="addSuccessModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Success</h3>
        </div>
        <div class="modal-body">
            <p>Account successfully created!</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary-modern btn-blue" onclick="closeAddSuccessModal()">OK</button>
        </div>
    </div>
</div>