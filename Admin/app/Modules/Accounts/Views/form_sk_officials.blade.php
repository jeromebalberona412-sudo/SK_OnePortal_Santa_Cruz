{{-- ============================================================
     SK Officials Form — shared Add / Edit modal
     Add mode:  $mode = 'add'  (no $official passed)
     Edit mode: $mode = 'edit' (pass $official data via JS)
     ============================================================ --}}

{{-- ── ADD SK OFFICIALS MODAL ─────────────────────────────── --}}
<div id="addSkOfficialsModal" class="modal-overlay" style="display:none;">
    <div class="modal-content modal-large modal-light" id="addSkOfficialsModalContent">

        <div class="modal-header modal-header-blue-grad">
            <h3 class="modal-title">Add SK Official Account</h3>
            <div class="modal-controls">
                <button type="button" class="modal-win-btn" id="addOfficialsResizeBtn"
                        onclick="toggleAddOfficialsSize()" title="Maximize">
                    <svg id="addOfficialsResizeIcon" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 3H5a2 2 0 0 0-2 2v3"/>
                        <path d="M21 8V5a2 2 0 0 0-2-2h-3"/>
                        <path d="M3 16v3a2 2 0 0 0 2 2h3"/>
                        <path d="M16 21h3a2 2 0 0 0 2-2v-3"/>
                    </svg>
                </button>
                <button type="button" class="modal-win-btn" onclick="closeAddSkOfficialsModal()" title="Close">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="modal-body modal-body-light">
            <p class="add-mode-label">How do you want to add?</p>
            <div class="add-mode-tabs">
                <button type="button" class="add-mode-tab active" id="tabManual"
                        onclick="switchAddOfficialTab('manual')">Manual entry</button>
                <button type="button" class="add-mode-tab" id="tabBatch" style="display:none;"
                    onclick="switchAddOfficialTab('batch')">Batch Upload</button>
            </div>

            {{-- ── MANUAL ENTRY ── --}}
            <div id="addOfficialManualPane">
                <form id="addSkOfficialsForm" class="sk-officials-form" novalidate>
                    @csrf
                    <input type="hidden" name="role" value="sk_official">
                    <input type="hidden" name="term_status" value="ACTIVE">

                    <div class="form-section-light">
                        <h4 class="section-title-light">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px;">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Personal Information
                        </h4>
                        <div class="form-grid">
                            <div class="form-group-light">
                                <label class="form-label-light required">First Name</label>
                                <input type="text" name="first_name" id="official_first_name" class="form-input-light" placeholder="First Name" required>
                                <span class="form-error-light"></span>
                            </div>
                            <div class="form-group-light">
                                <label class="form-label-light">Middle Name</label>
                                <input type="text" name="middle_name" id="official_middle_name" class="form-input-light" placeholder="Middle Name" maxlength="100">
                                <span class="form-error-light"></span>
                            </div>
                            <div class="form-group-light">
                                <label class="form-label-light required">Last Name</label>
                                <input type="text" name="last_name" id="official_last_name" class="form-input-light" placeholder="Last Name" required>
                                <span class="form-error-light"></span>
                            </div>
                            <div class="form-group-light">
                                <label class="form-label-light">Suffix</label>
                                <select name="suffix" id="official_suffix" class="form-input-light">
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
                                <select name="sex" id="official_sex" class="form-input-light" required>
                                    <option value="" disabled selected>Select Sex</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                <span class="form-error-light"></span>
                            </div>
                            <div class="form-group-light">
                                <label class="form-label-light required">Birthdate</label>
                                <input type="date" name="date_of_birth" id="official_date_of_birth" class="form-input-light" required>
                                <span class="form-error-light"></span>
                            </div>
                            <div class="form-group-light">
                                <label class="form-label-light required">Age</label>
                                <input type="number" name="age" id="official_age" class="form-input-light" min="0" max="150" readonly>
                                <span class="form-error-light"></span>
                            </div>
                            <div class="form-group-light">
                                <label class="form-label-light required">Contact Number</label>
                                <input type="text" name="contact_number" id="official_contact_number" class="form-input-light" placeholder="Contact Number" maxlength="20" required>
                                <span class="form-error-light"></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-section-light">
                        <h4 class="section-title-light">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px;">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                            Position Information
                        </h4>
                        <div class="form-grid">
                            <div class="form-group-light">
                                <label class="form-label-light required">Position</label>
                                <select name="position" id="official_position" class="form-input-light" required>
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
                                <select name="status" id="official_status" class="form-input-light" required>
                                    <option value="" disabled selected>Select Status</option>
                                    <option value="ACTIVE">Active</option>
                                    <option value="INACTIVE">Inactive</option>
                                </select>
                                <span class="form-error-light"></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-section-light">
                        <h4 class="section-title-light">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px;">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            Address
                        </h4>
                        <div class="form-grid">
                            <div class="form-group-light">
                                <label class="form-label-light required">Region</label>
                                <input type="text" name="region" id="official_region" class="form-input-light" value="IV-A CALABARZON" readonly required>
                            </div>
                            <div class="form-group-light">
                                <label class="form-label-light required">Province</label>
                                <input type="text" name="province" id="official_province" class="form-input-light" value="Laguna" readonly required>
                            </div>
                            <div class="form-group-light">
                                <label class="form-label-light required">Municipality</label>
                                <input type="text" name="municipality" id="official_municipality" class="form-input-light" value="Santa Cruz" readonly required>
                            </div>
                            <div class="form-group-light">
                                <label class="form-label-light required">Barangay</label>
                                <select name="barangay_id" id="official_barangay_id" class="form-input-light" required>
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
                        <h4 class="section-title-light">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px;">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            Term & Committee
                        </h4>
                        <div class="form-grid">
                            <div class="form-group-light">
                                <label class="form-label-light required">Term Start Date</label>
                                <input type="date" name="term_start" id="official_term_start" class="form-input-light" required>
                                <span class="form-error-light"></span>
                            </div>
                            <div class="form-group-light">
                                <label class="form-label-light required">Term End Date</label>
                                <input type="date" name="term_end" id="official_term_end" class="form-input-light" required>
                                <span class="form-error-light"></span>
                            </div>
                            <div class="form-group-light">
                                <label class="form-label-light">Committee</label>
                                <input type="text" name="committee" id="official_committee" class="form-input-light" placeholder="Committee (optional)">
                                <span class="form-error-light"></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-section-light">
                        <h4 class="section-title-light">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:6px;">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            Account
                        </h4>
                        <div class="form-grid">
                            <div class="form-group-light">
                                <label class="form-label-light required">Email Address</label>
                                <input type="email" name="email" id="official_email" class="form-input-light" placeholder="Email Address" required>
                                <span class="form-error-light"></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions-light">
                        <button type="button" class="btn-cancel-light" onclick="closeAddSkOfficialsModal()">Cancel</button>
                        <button type="submit" form="addSkOfficialsForm" class="btn-submit-light">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
                            Create Account
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── BATCH UPLOAD ── --}}
            <div id="addOfficialBatchPane" style="display:none;">
                <p class="batch-hint">Upload Excel/CSV. First row must be headers. Supported: <strong>.xlsx</strong>, <strong>.xls</strong>, <strong>.csv</strong></p>
                <details class="expected-cols-toggle">
                    <summary>Expected columns</summary>
                    <p class="expected-cols-list">first_name, middle_name, last_name, suffix, sex, date_of_birth, contact_number, position, status, region, province, municipality, barangay, term_start, term_end, committee, email</p>
                </details>
                <div class="batch-dropzone" id="officialDropzone">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    <p class="dropzone-text">Drop file here or <label for="officialBatchFile" class="dropzone-browse">browse</label></p>
                    <p class="dropzone-sub" id="officialFileName">No file selected</p>
                    <input type="file" id="officialBatchFile" accept=".xlsx,.xls,.csv" style="display:none;">
                </div>
                <div id="officialBatchPreview" style="display:none;" class="batch-preview-always"></div>
                <div class="batch-footer">
                    <button type="button" class="btn-cancel-light" onclick="closeAddSkOfficialsModal()">Cancel</button>
                    <button type="button" class="btn-submit-light" id="officialBatchSaveBtn">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ── EDIT SK OFFICIALS MODAL ─────────────────────────────── --}}
<div id="editSkOfficialsModal" class="modal-overlay" style="display: none;">
    <div class="modal-content modal-large modal-themed">
        <div class="modal-header modal-header-yellow">
            <h3 class="modal-title">Edit SK Officials Account</h3>
            <div class="modal-controls">
                <button type="button" class="modal-restore-btn" id="editOfficialsRestoreBtn" onclick="toggleRestoreEditSkOfficialsModal()" title="Restore Down" style="display:none;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="7" width="11" height="11" rx="1.5"/>
                        <path d="M7 7V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-2"/>
                    </svg>
                </button>
                <button type="button" class="modal-fullscreen-btn" onclick="toggleFullscreenEditSkOfficialsModal()" title="Maximize">
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
            <form id="editSkOfficialsForm" class="sk-fed-form" data-account-id="" novalidate>
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
                                <input type="number" id="edit_sk_officials_age" name="age" class="form-input-modern" min="0" max="150" readonly>
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
        <div class="modal-footer edit-modal-footer">
            <button type="button" class="btn-secondary-modern" onclick="closeEditSkOfficialsModal()">Cancel</button>
            <button type="submit" form="editSkOfficialsForm" class="btn-primary-modern btn-green">Update Account</button>
        </div>
    </div>
</div>
