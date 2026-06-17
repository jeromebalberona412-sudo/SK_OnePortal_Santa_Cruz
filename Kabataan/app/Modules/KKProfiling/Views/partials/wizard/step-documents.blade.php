{{-- Step 3: Optional supporting documents — one image only (School ID or Barangay Clearance) --}}
<section class="kkp-wizard-panel" id="kkpWizardStep3" data-wizard-step="3" hidden>
    <div class="kkp-wizard-panel-card">
        <h2 class="kkp-wizard-panel-title">Supporting Documents <span class="kkp-wizard-optional">(Optional)</span></h2>
        <p class="kkp-wizard-panel-desc">
            You may upload one supporting image to help verify your registration. Choose either a School ID or a Barangay Clearance — only one image (JPG or PNG, max 10MB). You can skip this step if you have no document ready.
        </p>

        <fieldset class="kkp-wizard-doc-type-fieldset" id="kkpDocTypeFieldset">
            <legend class="kkp-wizard-doc-type-legend">Select document type</legend>
            <div class="kkp-wizard-doc-type-options" role="radiogroup" aria-label="Document type">
                <label class="kkp-wizard-doc-type-option">
                    <input type="radio" name="document_type" value="school_id" id="kkpDocTypeSchoolId">
                    <span class="kkp-wizard-doc-type-card">
                        <span class="kkp-wizard-doc-type-name">School ID</span>
                        <span class="kkp-wizard-doc-type-desc">Valid school identification card</span>
                    </span>
                </label>
                <label class="kkp-wizard-doc-type-option">
                    <input type="radio" name="document_type" value="barangay_clearance" id="kkpDocTypeBarangayClearance">
                    <span class="kkp-wizard-doc-type-card">
                        <span class="kkp-wizard-doc-type-name">Barangay Clearance</span>
                        <span class="kkp-wizard-doc-type-desc">Barangay-issued clearance certificate</span>
                    </span>
                </label>
            </div>
        </fieldset>

        <div class="kkp-wizard-upload-panel" id="kkpSchoolIdUpload" hidden>
            <div class="kkp-wizard-upload-field">
                <label class="kkp-wizard-upload-label" for="kkpSchoolId">Upload School ID</label>
                <input type="file" id="kkpSchoolId" name="school_id" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="kkp-wizard-file-input">
                <p class="kkp-wizard-upload-hint">JPG or PNG only — max 10MB</p>
            </div>
        </div>

        <div class="kkp-wizard-upload-panel" id="kkpBarangayClearanceUpload" hidden>
            <div class="kkp-wizard-upload-field">
                <label class="kkp-wizard-upload-label" for="kkpBarangayClearance">Upload Barangay Clearance</label>
                <input type="file" id="kkpBarangayClearance" name="barangay_clearance" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="kkp-wizard-file-input">
                <p class="kkp-wizard-upload-hint">JPG or PNG only — max 10MB</p>
            </div>
        </div>

        <p class="kkp-field-error" id="kkpDocUploadError" hidden role="alert"></p>
    </div>
</section>
