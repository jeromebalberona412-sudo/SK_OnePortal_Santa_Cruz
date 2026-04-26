<!-- Add SK Federation Modal -->
<div id="addAccountModal" class="modal-overlay" style="display:none;">
    <div class="modal-content modal-large modal-light" id="addSkFedModalContent">

        <div class="modal-header modal-header-blue-grad">
            <h3 class="modal-title">Add SK Federation Account</h3>
            <div class="modal-controls">
                <button type="button" class="modal-win-btn" id="addFedResizeBtn"
                        onclick="toggleAddFedSize()" title="Maximize">
                    <svg id="addFedResizeIcon" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 3H5a2 2 0 0 0-2 2v3"/>
                        <path d="M21 8V5a2 2 0 0 0-2-2h-3"/>
                        <path d="M3 16v3a2 2 0 0 0 2 2h3"/>
                        <path d="M16 21h3a2 2 0 0 0 2-2v-3"/>
                    </svg>
                </button>
                <button type="button" class="modal-win-btn" onclick="closeAddAccountModal()" title="Close">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="modal-body modal-body-light">
            <form id="addSkFedForm" class="sk-officials-form">
                @csrf
                <input type="hidden" name="role" value="sk_fed">
                <input type="hidden" name="term_status" value="ACTIVE">

                <div class="form-section-light">
                    <h4 class="section-title-light">👤 Personal Information</h4>
                    <div class="form-grid">
                        <div class="form-group-light">
                            <label class="form-label-light required">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-input-light" placeholder="First Name" required>
                            <span class="form-error-light"></span>
                        </div>
                        <div class="form-group-light">
                            <label class="form-label-light">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" class="form-input-light" placeholder="Middle Name" maxlength="100">
                            <span class="form-error-light"></span>
                        </div>
                        <div class="form-group-light">
                            <label class="form-label-light required">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-input-light" placeholder="Last Name" required>
                            <span class="form-error-light"></span>
                        </div>
                        <div class="form-group-light">
                            <label class="form-label-light">Suffix</label>
                            <select id="suffix" name="suffix" class="form-input-light">
                                <option value="" disabled selected>Select Suffix</option>
                                <option value="None">None</option>
                                <option value="Jr.">Jr.</option>
                                <option value="Sr.">Sr.</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                                <option value="IV">IV</option>
                                <option value="V">V</option>
                            </select>
                            <span class="form-error-light"></span>
                        </div>
                        <div class="form-group-light">
                            <label class="form-label-light required">Sex</label>
                            <select id="sex" name="sex" class="form-input-light" required>
                                <option value="" disabled selected>Select Sex</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            <span class="form-error-light"></span>
                        </div>
                        <div class="form-group-light">
                            <label class="form-label-light required">Birthdate</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" class="form-input-light" required>
                            <span class="form-error-light"></span>
                        </div>
                        <div class="form-group-light">
                            <label class="form-label-light required">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number" class="form-input-light" placeholder="Contact Number" maxlength="20" required>
                            <span class="form-error-light"></span>
                        </div>
                    </div>
                </div>

                <div class="form-section-light">
                    <h4 class="section-title-light">🏛️ Position Information</h4>
                    <div class="form-grid">
                        <div class="form-group-light">
                            <label class="form-label-light required">Position</label>
                            <select id="position" name="position" class="form-input-light" required>
                                <option value="" disabled selected>Select Position</option>
                                <option value="Chairman">SK Chairman</option>
                                <option value="Councilor">SK Councilor</option>
                                <option value="Kagawad">SK Kagawad</option>
                                <option value="Treasurer">SK Treasurer</option>
                                <option value="Secretary">SK Secretary</option>
                                <option value="Auditor">SK Auditor</option>
                                <option value="PIO">SK PIO</option>
                            </select>
                            <span class="form-error-light"></span>
                        </div>
                        <div class="form-group-light">
                            <label class="form-label-light required">Status</label>
                            <select id="status" name="status" class="form-input-light" required>
                                <option value="" disabled selected>Select Status</option>
                                <option value="ACTIVE">Active</option>
                                <option value="INACTIVE">Inactive</option>
                            </select>
                            <span class="form-error-light"></span>
                        </div>
                    </div>
                </div>

                <div class="form-section-light">
                    <h4 class="section-title-light">📍 Address</h4>
                    <div class="form-grid">
                        <div class="form-group-light">
                            <label class="form-label-light required">Region</label>
                            <input type="text" id="region" name="region" class="form-input-light" value="IV-A CALABARZON" readonly required>
                        </div>
                        <div class="form-group-light">
                            <label class="form-label-light required">Province</label>
                            <input type="text" id="province" name="province" class="form-input-light" value="Laguna" readonly required>
                        </div>
                        <div class="form-group-light">
                            <label class="form-label-light required">Municipality</label>
                            <input type="text" id="municipality" name="municipality" class="form-input-light" value="Santa Cruz" readonly required>
                        </div>
                        <div class="form-group-light">
                            <label class="form-label-light required">Barangay</label>
                            <select id="barangay_id" name="barangay_id" class="form-input-light" required>
                                <option value="" disabled selected>Select Barangay</option>
                                @foreach($barangays as $barangay)
                                    <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
                                @endforeach
                            </select>
                            <span class="form-error-light"></span>
                        </div>
                    </div>
                </div>

                <div class="form-section-light">
                    <h4 class="section-title-light">📅 Term</h4>
                    <div class="form-grid">
                        <div class="form-group-light">
                            <label class="form-label-light required">Term Start</label>
                            <input type="date" id="term_start" name="term_start" class="form-input-light" required>
                            <span class="form-error-light"></span>
                        </div>
                        <div class="form-group-light">
                            <label class="form-label-light required">Term End</label>
                            <input type="date" id="term_end" name="term_end" class="form-input-light" required>
                            <span class="form-error-light"></span>
                        </div>
                    </div>
                </div>

                <div class="form-section-light">
                    <h4 class="section-title-light">📧 Account</h4>
                    <div class="form-grid">
                        <div class="form-group-light">
                            <label class="form-label-light required">Email Address</label>
                            <input type="email" id="email" name="email" class="form-input-light" placeholder="Email Address" required>
                            <span class="form-error-light"></span>
                        </div>
                    </div>
                </div>

                <div class="form-actions-light">
                    <button type="button" class="btn-cancel-light" onclick="closeAddAccountModal()">Cancel</button>
                    <button type="submit" form="addSkFedForm" class="btn-submit-light">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
                        Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="addSuccessModal" class="modal-overlay" style="display:none;">
    <div class="modal-content modal-light" style="max-width:360px;">
        <div class="modal-header modal-header-blue-grad">
            <h3 class="modal-title">Success</h3>
        </div>
        <div class="modal-body modal-body-light" style="text-align:center;padding:2rem 1.5rem;">
            <p style="color:#1e293b;margin:0 0 1.5rem;">Account successfully created!</p>
            <button type="button" class="btn-submit-light" onclick="closeAddSuccessModal()">OK</button>
        </div>
    </div>
</div>

@vite(['app/Modules/Accounts/assets/js/add_sk_fed.js'])
