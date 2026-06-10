{{-- Scholar application form builder modal (same pattern as sports_requests) --}}
<div class="sports-modal-overlay" id="safCreateFormModal" style="display:none;">
    <div class="sports-modal-box sports-modal-form-builder" id="safCreateFormModalBox">
        <div class="sports-modal-header">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span id="safModalTitleText">Create Scholar Application Form</span>
            </h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="sports-modal-close" id="safFormMaximize" title="Fullscreen" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="sports-modal-close" id="safFormClose">&times;</button>
            </div>
        </div>

        <div class="sports-modal-body spfb-modal-body">
            <input type="hidden" id="safEditFormId" value="{{ $formId ?? '' }}">

            <div class="spfb-section-card spfb-section-basic">
                <div class="spfb-section-label">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                    Form Details
                </div>

                <div class="sports-field" style="margin-bottom:14px;">
                    <label for="safFormTitleInput">Form Title <span class="sports-req">*</span></label>
                    <input type="text" id="safFormTitleInput" class="sports-input" maxlength="120"
                           placeholder="e.g., SK Educational Assistance Application 2026">
                </div>

                @include('Program_Management::partials.committee-head-field', ['inputId' => 'safCommitteeHead'])

                <div class="sports-field-row" style="margin-bottom:14px;">
                    <div class="sports-field">
                        <label for="safStartDate">Start Date <span class="sports-req">*</span></label>
                        <input type="date" id="safStartDate" class="sports-input" autocomplete="off">
                    </div>
                    <div class="sports-field">
                        <label for="safEndDate">End Date <span class="sports-req">*</span></label>
                        <input type="date" id="safEndDate" class="sports-input" autocomplete="off">
                    </div>
                </div>

                <div class="sports-field-row">
                    <div class="sports-field">
                        @include('Program_Management::partials.time-dropdown-fields', [
                            'prefix' => 'safStartTime',
                            'label' => 'Start Time',
                            'required' => true,
                            'inputClass' => 'sports-input',
                        ])
                    </div>
                    <div class="sports-field">
                        @include('Program_Management::partials.time-dropdown-fields', [
                            'prefix' => 'safEndTime',
                            'label' => 'End Time',
                            'required' => true,
                            'inputClass' => 'sports-input',
                        ])
                    </div>
                </div>
            </div>

            <div class="spfb-section-card spfb-section-kk-profile">
                <div class="spfb-section-label">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                    KK Profiling Auto-Fill Fields
                </div>
                <p class="spfb-section-desc">Select which KK Profiling information will automatically be included in the scholarship application. Applicants will not need to enter these details again because the system will retrieve them from their KK Profile.</p>

                <div class="spfb-kk-fields-grid">
                    <div class="spfb-kk-field-group">
                        <h4 class="spfb-kk-group-title">Personal Information</h4>
                        <label class="spfb-checkbox-item">
                            <input type="checkbox" name="kk_profile_fields" value="last_name" class="spfb-checkbox kk-profiling-field">
                            <span class="spfb-checkbox-label">Last Name</span>
                        </label>
                        <label class="spfb-checkbox-item">
                            <input type="checkbox" name="kk_profile_fields" value="first_name" class="spfb-checkbox kk-profiling-field">
                            <span class="spfb-checkbox-label">First Name</span>
                        </label>
                        <label class="spfb-checkbox-item">
                            <input type="checkbox" name="kk_profile_fields" value="middle_name" class="spfb-checkbox kk-profiling-field">
                            <span class="spfb-checkbox-label">Middle Name</span>
                        </label>
                        <label class="spfb-checkbox-item">
                            <input type="checkbox" name="kk_profile_fields" value="suffix" class="spfb-checkbox kk-profiling-field">
                            <span class="spfb-checkbox-label">Suffix</span>
                        </label>
                        <label class="spfb-checkbox-item">
                            <input type="checkbox" name="kk_profile_fields" value="birthday" class="spfb-checkbox kk-profiling-field">
                            <span class="spfb-checkbox-label">Birthday</span>
                        </label>
                        <label class="spfb-checkbox-item">
                            <input type="checkbox" name="kk_profile_fields" value="age" class="spfb-checkbox kk-profiling-field">
                            <span class="spfb-checkbox-label">Age</span>
                        </label>
                        <label class="spfb-checkbox-item">
                            <input type="checkbox" name="kk_profile_fields" value="sex" class="spfb-checkbox kk-profiling-field">
                            <span class="spfb-checkbox-label">Sex</span>
                        </label>
                        <label class="spfb-checkbox-item">
                            <input type="checkbox" name="kk_profile_fields" value="civil_status" class="spfb-checkbox kk-profiling-field">
                            <span class="spfb-checkbox-label">Civil Status</span>
                        </label>
                        <label class="spfb-checkbox-item">
                            <input type="checkbox" name="kk_profile_fields" value="contact_number" class="spfb-checkbox kk-profiling-field">
                            <span class="spfb-checkbox-label">Contact Number</span>
                        </label>
                        <label class="spfb-checkbox-item">
                            <input type="checkbox" name="kk_profile_fields" value="home_address" class="spfb-checkbox kk-profiling-field">
                            <span class="spfb-checkbox-label">Home Address</span>
                        </label>
                    </div>

                    <div class="spfb-kk-field-group">
                        <h4 class="spfb-kk-group-title">Educational Information</h4>
                        <label class="spfb-checkbox-item">
                            <input type="checkbox" name="kk_profile_fields" value="current_school" class="spfb-checkbox kk-profiling-field">
                            <span class="spfb-checkbox-label">Current School</span>
                        </label>
                        <label class="spfb-checkbox-item">
                            <input type="checkbox" name="kk_profile_fields" value="year_level" class="spfb-checkbox kk-profiling-field">
                            <span class="spfb-checkbox-label">Year Level</span>
                        </label>
                        <label class="spfb-checkbox-item">
                            <input type="checkbox" name="kk_profile_fields" value="course_strand" class="spfb-checkbox kk-profiling-field">
                            <span class="spfb-checkbox-label">Course / Strand</span>
                        </label>
                    </div>

                    <div class="spfb-kk-field-group">
                        <h4 class="spfb-kk-group-title">Location Information</h4>
                        <label class="spfb-checkbox-item">
                            <input type="checkbox" name="kk_profile_fields" value="barangay" class="spfb-checkbox kk-profiling-field">
                            <span class="spfb-checkbox-label">Barangay</span>
                        </label>
                        <label class="spfb-checkbox-item">
                            <input type="checkbox" name="kk_profile_fields" value="city_municipality" class="spfb-checkbox kk-profiling-field">
                            <span class="spfb-checkbox-label">City/Municipality</span>
                        </label>
                        <label class="spfb-checkbox-item">
                            <input type="checkbox" name="kk_profile_fields" value="province" class="spfb-checkbox kk-profiling-field">
                            <span class="spfb-checkbox-label">Province</span>
                        </label>
                        <label class="spfb-checkbox-item">
                            <input type="checkbox" name="kk_profile_fields" value="region" class="spfb-checkbox kk-profiling-field">
                            <span class="spfb-checkbox-label">Region</span>
                        </label>
                    </div>
                </div>
            </div>

            @include('GForm_Builder::partials.announcement-field')
            @include('GForm_Builder::partials.custom-questions-builder', [
                'sectionTitle' => 'Application Form Builder',
                'hint' => 'Build the application form that Kabataan members will fill out when applying for this scholarship.',
                'emptyMessage' => 'No questions yet. Click <strong>Add Question</strong> to start building your form.',
            ])
        </div>

        <div class="sports-modal-footer">
            <button type="button" class="sports-btn sports-btn-outline" id="safFormCancelBtn">Cancel</button>
            <button type="button" class="sports-btn sports-btn-primary" id="safFormSaveBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                <span id="safFormSaveBtnText">Save Form</span>
            </button>
        </div>
    </div>
</div>
