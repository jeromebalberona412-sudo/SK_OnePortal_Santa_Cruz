<!-- Add SK Officials Modal -->
<div id="addSkOfficialsModal" class="modal-overlay" style="display:none;">
    <div class="modal-content modal-large modal-light" id="addSkOfficialsModalContent">

        {{-- Header --}}
        <div class="modal-header modal-header-blue-grad">
            <h3 class="modal-title">Add SK Official Account</h3>
            <div class="modal-controls">
                {{-- Restore-down / Maximize toggle --}}
                <button type="button" class="modal-win-btn" id="addOfficialsResizeBtn"
                        onclick="toggleAddOfficialsSize()" title="Maximize">
                    {{-- Default: maximize (four-corner arrows) --}}
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

        {{-- Body --}}
        <div class="modal-body modal-body-light">

            {{-- Tab switcher --}}
            <p class="add-mode-label">How do you want to add?</p>
            <div class="add-mode-tabs">
                <button type="button" class="add-mode-tab active" id="tabManual"
                        onclick="switchAddOfficialTab('manual')">Manual entry</button>
                <button type="button" class="add-mode-tab" id="tabBatch"
                        onclick="switchAddOfficialTab('batch')">Batch Upload</button>
            </div>

            {{-- ── MANUAL ENTRY ── --}}
            <div id="addOfficialManualPane">
                <form id="addSkOfficialsForm" class="sk-officials-form">
                    @csrf
                    <input type="hidden" name="role" value="sk_official">
                    <input type="hidden" name="term_status" value="ACTIVE">

                    <div class="form-section-light">
                        <h4 class="section-title-light">👤 Personal Information</h4>
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
                                <label class="form-label-light required">Contact Number</label>
                                <input type="text" name="contact_number" id="official_contact_number" class="form-input-light" placeholder="Contact Number" maxlength="20" required>
                                <span class="form-error-light"></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-section-light">
                        <h4 class="section-title-light">🏛️ Position Information</h4>
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
                        <h4 class="section-title-light">📍 Address</h4>
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
                        <h4 class="section-title-light">📅 Term & Committee</h4>
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
                        <h4 class="section-title-light">📧 Account</h4>
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

                {{-- Preview table — hidden until file is uploaded --}}
                <div id="officialBatchPreview" style="display:none;" class="batch-preview-always"></div>

                <div class="batch-footer">
                    <button type="button" class="btn-cancel-light" onclick="closeAddSkOfficialsModal()">Cancel</button>
                    <button type="button" class="btn-submit-light" id="officialBatchSaveBtn">Save</button>
                </div>
            </div>

        </div>{{-- end modal-body --}}
    </div>
</div>

<!-- SK Officials Success Modal -->
<div id="skOfficialsSuccessModal" class="modal-overlay" style="display:none;">
    <div class="modal-content modal-light" style="max-width:360px;">
        <div class="modal-header modal-header-blue-grad">
            <h3 class="modal-title">Success</h3>
        </div>
        <div class="modal-body modal-body-light" style="text-align:center;padding:2rem 1.5rem;">
            <p style="color:#1e293b;margin:0 0 1.5rem;">SK Officials account successfully created!</p>
            <button type="button" class="btn-submit-light" onclick="closeSkOfficialsSuccessModal()">OK</button>
        </div>
    </div>
</div>

@vite(['app/Modules/Accounts/assets/js/add_sk_officials.js'])
