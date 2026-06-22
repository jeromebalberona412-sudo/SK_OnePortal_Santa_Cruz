{{-- Step 2: Optional supporting documents — one image only (School ID or Barangay Clearance) --}}
<section class="kkp-wizard-panel" id="kkpWizardStep2" data-wizard-step="2" hidden>
    <div class="kkp-wizard-panel-card kkp-wizard-panel-card--docs">
        <div class="kkp-wizard-panel-head">
            <div class="kkp-wizard-panel-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="12" y1="18" x2="12" y2="12"></line>
                    <line x1="9" y1="15" x2="15" y2="15"></line>
                </svg>
            </div>
            <div>
                <h2 class="kkp-wizard-panel-title">Supporting Documents <span class="kkp-wizard-optional">Optional</span></h2>
                <p class="kkp-wizard-panel-desc">
                    Upload one supporting image if available. This helps SK officials verify your registration faster.
                </p>
            </div>
        </div>

        <div class="kkp-wizard-info-callout" role="note">
            <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <p>Choose <strong>one</strong> document only — School ID or Barangay Clearance. JPG or PNG, max 10MB. You can continue without uploading if you don't have a file ready.</p>
        </div>

        <fieldset class="kkp-wizard-doc-type-fieldset" id="kkpDocTypeFieldset">
            <legend class="kkp-wizard-doc-type-legend">Select document type</legend>
            <div class="kkp-wizard-doc-type-options" role="radiogroup" aria-label="Document type">
                <label class="kkp-wizard-doc-type-option">
                    <input type="radio" name="document_type" value="school_id" id="kkpDocTypeSchoolId">
                    <span class="kkp-wizard-doc-type-card">
                        <span class="kkp-wizard-doc-type-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                                <circle cx="8" cy="12" r="2"></circle>
                                <path d="M14 10h5M14 14h5"></path>
                            </svg>
                        </span>
                        <span class="kkp-wizard-doc-type-text">
                            <span class="kkp-wizard-doc-type-name">School ID</span>
                            <span class="kkp-wizard-doc-type-desc">Valid school identification card</span>
                        </span>
                    </span>
                </label>
                <label class="kkp-wizard-doc-type-option">
                    <input type="radio" name="document_type" value="barangay_clearance" id="kkpDocTypeBarangayClearance">
                    <span class="kkp-wizard-doc-type-card">
                        <span class="kkp-wizard-doc-type-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                <polyline points="9 12 11 14 15 10"></polyline>
                            </svg>
                        </span>
                        <span class="kkp-wizard-doc-type-text">
                            <span class="kkp-wizard-doc-type-name">Barangay Clearance</span>
                            <span class="kkp-wizard-doc-type-desc">Barangay-issued clearance certificate</span>
                        </span>
                    </span>
                </label>
            </div>
        </fieldset>

        <div class="kkp-wizard-upload-panel" id="kkpSchoolIdUpload" hidden>
            <div class="kkp-wizard-upload-shell" data-upload-shell="kkpSchoolId">
                <label class="kkp-wizard-dropzone" id="kkpSchoolIdDropzone" for="kkpSchoolId">
                    <input type="file" id="kkpSchoolId" name="school_id" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="kkp-wizard-file-input">
                    <span class="kkp-wizard-dropzone-empty" id="kkpSchoolIdEmpty">
                        <span class="kkp-wizard-dropzone-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                        </span>
                        <span class="kkp-wizard-dropzone-title">Drag &amp; drop your School ID here</span>
                        <span class="kkp-wizard-dropzone-sub">or <span class="kkp-wizard-dropzone-link">browse files</span></span>
                        <span class="kkp-wizard-dropzone-hint">JPG or PNG · max 10MB</span>
                    </span>
                </label>
                <div class="kkp-wizard-dropzone-preview" id="kkpSchoolIdPreview" hidden>
                    <img id="kkpSchoolIdPreviewImg" alt="School ID preview">
                    <div class="kkp-wizard-dropzone-filemeta">
                        <span class="kkp-wizard-dropzone-filename" id="kkpSchoolIdFileName"></span>
                        <button type="button" class="kkp-wizard-dropzone-remove" data-clear-input="kkpSchoolId" aria-label="Remove School ID file">Remove</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="kkp-wizard-upload-panel" id="kkpBarangayClearanceUpload" hidden>
            <div class="kkp-wizard-upload-shell" data-upload-shell="kkpBarangayClearance">
                <label class="kkp-wizard-dropzone" id="kkpBarangayClearanceDropzone" for="kkpBarangayClearance">
                    <input type="file" id="kkpBarangayClearance" name="barangay_clearance" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="kkp-wizard-file-input">
                    <span class="kkp-wizard-dropzone-empty" id="kkpBarangayClearanceEmpty">
                        <span class="kkp-wizard-dropzone-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                        </span>
                        <span class="kkp-wizard-dropzone-title">Drag &amp; drop your Barangay Clearance here</span>
                        <span class="kkp-wizard-dropzone-sub">or <span class="kkp-wizard-dropzone-link">browse files</span></span>
                        <span class="kkp-wizard-dropzone-hint">JPG or PNG · max 10MB</span>
                    </span>
                </label>
                <div class="kkp-wizard-dropzone-preview" id="kkpBarangayClearancePreview" hidden>
                    <img id="kkpBarangayClearancePreviewImg" alt="Barangay Clearance preview">
                    <div class="kkp-wizard-dropzone-filemeta">
                        <span class="kkp-wizard-dropzone-filename" id="kkpBarangayClearanceFileName"></span>
                        <button type="button" class="kkp-wizard-dropzone-remove" data-clear-input="kkpBarangayClearance" aria-label="Remove Barangay Clearance file">Remove</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
