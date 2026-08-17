{{-- Editable KK Survey Questionnaire — same layout as Kabataan manual form --}}
<div class="kk-profiling-edit-root" id="kkProfilingEditRoot" hidden>
    <div class="kkp-paper kk-view-paper kk-profiling-form-scroll" id="kkEditFormScroll">
                    {{-- PAPER FORM HEADER --}}
                    <div class="kkp-form-header">
                        <div class="kkp-form-title-col">
                            <div class="kkp-form-main-title">KK Survey Questionnaire</div>
                            <div class="kkp-form-header-fields">
                                <div class="kkp-hdr-field">
                                    <span class="kkp-hdr-label">Respondent #:</span>
                                    <input type="text" id="kkEditRespondentNumber" class="kkp-hdr-input kkp-hdr-input-readonly" value="Auto-assigned on save" readonly tabindex="-1">
                                </div>
                                <div class="kkp-hdr-field">
                                    <label class="kkp-hdr-label" for="kkEditDate">Date:</label>
                                    <input type="text" id="kkEditDate" class="kkp-hdr-input kkp-hdr-input-readonly" readonly tabindex="-1">
                                </div>
                            </div>
                        </div>
                        <div class="kkp-form-logo">
                            <img
                                id="kkEditBarangayLogo"
                                src="{{ $barangayLogoUrl ?? asset('images/SK_OnePortal_logo.png') }}"
                                alt="{{ ($barangayName ?? 'Barangay') }} SK Logo"
                                onerror="this.onerror=null;this.src='{{ asset('images/SK_OnePortal_logo.png') }}';"
                            >
                        </div>
                    </div>

                    {{-- TO THE RESPONDENT NOTICE --}}
                    <div class="kkf-notice-box">
                        <div class="kkf-notice-title">TO THE RESPONDENT:</div>
                        <p class="kkf-notice-body">We are currently conducting a study that focuses on assessing the demographic information of the Katipunan ng Kabataan. We would like to ask your participation by taking your time to answer this questionnaire. Please read the questions carefully and answer them accurately.</p>
                        <p class="kkf-notice-confidential">REST ASSURED THAT ALL INFORMATION GATHERED FROM THIS STUDY WILL BE TREATED WITH UTMOST CONFIDENTIALITY.</p>
                    </div>

                    {{-- I. PROFILE --}}
                    <div class="kkf-section-title">I. Profile</div>

                    {{-- Name Row --}}
                    <div class="kkf-row-label">Name of Respondent:</div>
                    <div class="kkf-name-row">
                        <div class="kkf-name-col">
                            <input type="text" id="kkEditLastName" class="kkf-uline" placeholder=" " required>
                            <label for="kkEditLastName" class="kkf-col-label">Last Name *</label>
                        </div>
                        <div class="kkf-name-col">
                            <input type="text" id="kkEditFirstName" class="kkf-uline" placeholder=" " required>
                            <label for="kkEditFirstName" class="kkf-col-label">First Name *</label>
                        </div>
                        <div class="kkf-name-col">
                            <input type="text" id="kkEditMiddleName" class="kkf-uline" placeholder=" ">
                            <label for="kkEditMiddleName" class="kkf-col-label">Middle Name *</label>
                        </div>
                        <div class="kkf-name-col kkf-name-col--sm">
                            <select id="kkEditSuffix" class="kkf-uline kkf-uline-select">
                                <option value="None">None</option>
                                <option value="Jr.">Jr.</option>
                                <option value="Sr.">Sr.</option>
                                <option value="I">I</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                                <option value="IV">IV</option>
                                <option value="V">V</option>
                                <option value="Others">Others</option>
                            </select>
                            <label for="kkEditSuffix" class="kkf-col-label">Suffix</label>
                            <div class="kkp-custom-suffix-wrap" id="kkEditCustomSuffixWrap">
                                <input type="text" id="kkEditCustomSuffix" class="kkf-uline" placeholder="Please specify suffix" maxlength="5" autocomplete="off">
                                <label for="kkEditCustomSuffix" class="kkf-col-label">Please specify suffix</label>
                            </div>
                        </div>
                    </div>

                    {{-- Location Row --}}
                    <div class="kkf-row-label">Location:</div>
                    <div class="kkf-loc-row">
                        <div class="kkf-loc-col">
                            <input type="text" id="kkEditRegion" class="kkf-uline kkf-readonly" readonly tabindex="-1" placeholder=" ">
                            <label for="kkEditRegion" class="kkf-col-label">Region</label>
                        </div>
                        <div class="kkf-loc-col">
                            <input type="text" id="kkEditProvince" class="kkf-uline kkf-readonly" readonly tabindex="-1" placeholder=" ">
                            <label for="kkEditProvince" class="kkf-col-label">Province</label>
                        </div>
                        <div class="kkf-loc-col">
                            <input type="text" id="kkEditCity" class="kkf-uline kkf-readonly" readonly tabindex="-1" placeholder=" ">
                            <label for="kkEditCity" class="kkf-col-label">City/Municipality</label>
                        </div>
                        <div class="kkf-loc-col">
                            <input type="text" id="kkEditBarangay" class="kkf-uline kkf-readonly" readonly tabindex="-1" placeholder=" ">
                            <label for="kkEditBarangay" class="kkf-col-label">Barangay</label>
                        </div>
                        <div class="kkf-loc-col">
                            <select id="kkEditPurokZone" class="kkf-uline kkf-uline-select">
                                <option value="">Select</option>
                                @include('layout::partials.barangay-zone-options')
                            </select>
                            <label for="kkEditPurokZone" class="kkf-col-label">Purok/Zone</label>
                        </div>
                    </div>

                    {{-- Personal Info Row: Sex | Age + Birthday | Email + Contact --}}
                    <div class="kkp-personal-row">
                        <div class="kkp-personal-left">
                            <div class="kkp-sex-block">
                                <div class="kkp-sex-label-box">Sex Assigned by Birth:</div>
                                <div class="kkp-sex-options">
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="kkEditSexChk" value="Male" onchange="kkEditSingleCheck(this,'kkEditSex')"> Male</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="kkEditSexChk" value="Female" onchange="kkEditSingleCheck(this,'kkEditSex')"> Female</label>
                                </div>
                                <input type="hidden" id="kkEditSex" value="">
                            </div>
                        </div>
                        <div class="kkp-personal-center">
                            <div class="kkp-age-dob-row">
                                <div class="kkp-inline-pair kkp-inline-pair--age">
                                    <label class="kkp-inline-label" for="kkEditAgeInput">Age:</label>
                                    <div class="kkp-age-select-box">
                                        <select id="kkEditAgeInput" class="kkp-uline kkp-uline-select kkp-uline-age kkp-age-select-compact">
                                            <option value="" disabled>Select</option>
                                            @for ($ageOption = 15; $ageOption <= 30; $ageOption++)
                                                <option value="{{ $ageOption }}">{{ $ageOption }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="kkp-inline-pair kkp-inline-pair--birthday">
                                    <label class="kkp-inline-label" for="kkEditDob">Birthday:</label>
                                    <div class="kkp-birthday-field">
                                        <input type="date" id="kkEditDob" class="kkp-uline kkp-uline-med">
                                        <span class="kkp-hint kkp-birthday-hint">(mm/dd/yyyy)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="kkp-personal-right">
                            <div class="kkp-inline-pair kkp-inline-pair--email">
                                <label class="kkp-inline-label" for="kkEditEmail">E-mail address: <span class="kkp-optional-label">(Optional)</span></label>
                                <input type="email" id="kkEditEmail" class="kkp-uline kkp-uline-med kkp-email-input" placeholder=" " maxlength="254" autocomplete="email">
                            </div>
                            <div class="kkp-inline-pair">
                                <label class="kkp-inline-label" for="kkEditContactInput">Contact #:</label>
                                <input type="tel" id="kkEditContactInput" class="kkp-uline kkp-uline-med" placeholder="09XXXXXXXXX" inputmode="numeric" maxlength="11">
                            </div>
                        </div>
                    </div>

                    {{-- II. DEMOGRAPHIC CHARACTERISTICS --}}
                    <div class="kkf-section-title">II. Demographic Characteristics</div>
                    <p class="kkf-instruction">Please put a Check mark next to the word or Phrase that matches your response.</p>

                    <div class="kkf-demo-grid">
                        {{-- LEFT COLUMN --}}
                        <div class="kkf-demo-col">
                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Civil Status</div>
                                <div class="kkf-demo-block-options">
                                    <div class="kkf-demo-options-2col">
                                        <div>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditCivilStatus" value="Single" onchange="kkEditSingleCheck(this,'kkEditCivilStatus')"> Single</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditCivilStatus" value="Married" onchange="kkEditSingleCheck(this,'kkEditCivilStatus')"> Married</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditCivilStatus" value="Widowed" onchange="kkEditSingleCheck(this,'kkEditCivilStatus')"> Widowed</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditCivilStatus" value="Divorced" onchange="kkEditSingleCheck(this,'kkEditCivilStatus')"> Divorced</label>
                                        </div>
                                        <div>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditCivilStatus" value="Separated" onchange="kkEditSingleCheck(this,'kkEditCivilStatus')"> Separated</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditCivilStatus" value="Annulled" onchange="kkEditSingleCheck(this,'kkEditCivilStatus')"> Annulled</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditCivilStatus" value="Unknown" onchange="kkEditSingleCheck(this,'kkEditCivilStatus')"> Unknown</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditCivilStatus" value="Live-in" onchange="kkEditSingleCheck(this,'kkEditCivilStatus')"> Live-in</label>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="kkEditCivilStatus" value="">
                            </div>

                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Youth Age Group</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditYouthAgeGroup" value="Child Youth (15-17 yrs old)" disabled tabindex="-1" onchange="kkEditSingleCheck(this,'kkEditYouthAgeGroup')"> Child Youth (15-17 yrs old)</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditYouthAgeGroup" value="Core Youth (18-24 yrs old)" disabled tabindex="-1" onchange="kkEditSingleCheck(this,'kkEditYouthAgeGroup')"> Core Youth (18-24 yrs old)</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditYouthAgeGroup" value="Young Adult (15-30 yrs old)" disabled tabindex="-1" onchange="kkEditSingleCheck(this,'kkEditYouthAgeGroup')"> Young Adult (15-30 yrs old)</label>
                                </div>
                                <input type="hidden" id="kkEditYouthAgeGroup" value="">
                            </div>

                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Educational Background</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditEducationalBackground" value="Elementary Level" onchange="kkEditSingleCheck(this,'kkEditEducationalBackground')"> Elementary Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditEducationalBackground" value="Elementary Grad" onchange="kkEditSingleCheck(this,'kkEditEducationalBackground')"> Elementary Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditEducationalBackground" value="High School Level" onchange="kkEditSingleCheck(this,'kkEditEducationalBackground')"> High school level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditEducationalBackground" value="High School Grad" onchange="kkEditSingleCheck(this,'kkEditEducationalBackground')"> High school Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditEducationalBackground" value="Vocational Grad" onchange="kkEditSingleCheck(this,'kkEditEducationalBackground')"> Vocational Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditEducationalBackground" value="College Level" onchange="kkEditSingleCheck(this,'kkEditEducationalBackground')"> College Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditEducationalBackground" value="College Grad" onchange="kkEditSingleCheck(this,'kkEditEducationalBackground')"> College Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditEducationalBackground" value="Masters Level" onchange="kkEditSingleCheck(this,'kkEditEducationalBackground')"> Masters Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditEducationalBackground" value="Masters Grad" onchange="kkEditSingleCheck(this,'kkEditEducationalBackground')"> Masters Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditEducationalBackground" value="Doctorate Level" onchange="kkEditSingleCheck(this,'kkEditEducationalBackground')"> Doctorate Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditEducationalBackground" value="Doctorate Graduate" onchange="kkEditSingleCheck(this,'kkEditEducationalBackground')"> Doctorate Graduate</label>
                                </div>
                                <input type="hidden" id="kkEditEducationalBackground" value="">
                            </div>
                        </div>
                        {{-- RIGHT COLUMN --}}
                        <div class="kkf-demo-col">
                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Youth Classification</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditYouthClassification" value="In School Youth" onchange="kkEditSingleCheck(this,'kkEditYouthClassification')"> In school Youth</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditYouthClassification" value="Out of School Youth" onchange="kkEditSingleCheck(this,'kkEditYouthClassification')"> Out of School Youth</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditYouthClassification" value="Working Youth" onchange="kkEditSingleCheck(this,'kkEditYouthClassification')"> Working Youth</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditYouthClassification" value="Youth w/ Specific Needs" onchange="kkEditSingleCheck(this,'kkEditYouthClassification')"> Youth w/ Specific needs:</label>
                                    <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk" name="kkEditYouthClassification" value="Person w/ Disability" onchange="kkEditSingleCheck(this,'kkEditYouthClassification')"> Person w/ Disability</label>
                                    <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk" name="kkEditYouthClassification" value="Children in Conflict w/ Law" onchange="kkEditSingleCheck(this,'kkEditYouthClassification')"> Children In Conflict w/ Law</label>
                                    <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk" name="kkEditYouthClassification" value="Indigenous People" onchange="kkEditSingleCheck(this,'kkEditYouthClassification')"> Indigenous People</label>
                                </div>
                                <input type="hidden" id="kkEditYouthClassification" value="">
                            </div>

                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Work Status</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditWorkStatus" value="Employed" onchange="kkEditSingleCheck(this,'kkEditWorkStatus')"> Employed</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditWorkStatus" value="Unemployed" onchange="kkEditSingleCheck(this,'kkEditWorkStatus')"> Unemployed</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditWorkStatus" value="Self-Employed" onchange="kkEditSingleCheck(this,'kkEditWorkStatus')"> Self-Employed</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditWorkStatus" value="Currently looking for a Job" onchange="kkEditSingleCheck(this,'kkEditWorkStatus')"> Currently looking for a Job</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditWorkStatus" value="Not Interested Looking for a Job" onchange="kkEditSingleCheck(this,'kkEditWorkStatus')"> Not Interested Looking for a Job</label>
                                </div>
                                <input type="hidden" id="kkEditWorkStatus" value="">
                            </div>

                            {{-- Voter / Civic Participation — same layout as Kabataan KK form --}}
                            <div class="kkp-voter-questions-wrap">
                                <div class="kkp-voter-questions-grid">
                                    <div class="kkp-voter-questions-col">
                                        <div class="kkp-demo-block">
                                            <div class="kkp-demo-block-label">Registered SK Voter?</div>
                                            <div class="kkp-demo-block-options">
                                                <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="kkEditRegisteredSKVoter" value="Yes" onchange="kkEditSingleCheck(this,'kkEditRegisteredSKVoter')"> Yes</label>
                                                <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="kkEditRegisteredSKVoter" value="No" onchange="kkEditSingleCheck(this,'kkEditRegisteredSKVoter')"> No</label>
                                                <input type="hidden" id="kkEditRegisteredSKVoter" value="">
                                            </div>
                                        </div>
                                        <div class="kkp-demo-block">
                                            <div class="kkp-demo-block-label">Registered National Voter?</div>
                                            <div class="kkp-demo-block-options">
                                                <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="kkEditRegisteredNationalVoter" value="Yes" onchange="kkEditSingleCheck(this,'kkEditRegisteredNationalVoter')"> Yes</label>
                                                <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="kkEditRegisteredNationalVoter" value="No" onchange="kkEditSingleCheck(this,'kkEditRegisteredNationalVoter')"> No</label>
                                                <input type="hidden" id="kkEditRegisteredNationalVoter" value="">
                                            </div>
                                        </div>
                                        <div class="kkp-demo-block kkp-assembly-question" id="kkEditAssemblyQuestion">
                                            <div class="kkp-demo-block-label">Have you attended a KK Assembly?</div>
                                            <div class="kkp-demo-block-options">
                                                <label class="kkp-chk-lbl kkp-assembly-chk-yes">
                                                    <input type="checkbox" class="kkp-sq-chk" name="kkEditAttendedKKAssembly" value="Yes" onchange="kkEditSingleCheck(this,'kkEditAttendedKKAssembly')"> Yes
                                                    <span class="kkp-assembly-arrow kkp-assembly-arrow--yes" aria-hidden="true">→</span>
                                                </label>
                                                <label class="kkp-chk-lbl kkp-assembly-chk-no">
                                                    <input type="checkbox" class="kkp-sq-chk" name="kkEditAttendedKKAssembly" value="No" onchange="kkEditSingleCheck(this,'kkEditAttendedKKAssembly')"> No
                                                    <span class="kkp-assembly-arrow kkp-assembly-arrow--no" aria-hidden="true">→</span>
                                                </label>
                                                <input type="hidden" id="kkEditAttendedKKAssembly" value="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="kkp-voter-questions-col">
                                        <div class="kkp-demo-block">
                                            <div class="kkp-demo-block-label">Did you vote last SK?</div>
                                            <div class="kkp-demo-block-options">
                                                <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="kkEditVotingHistory" value="Yes" onchange="kkEditSingleCheck(this,'kkEditVotingHistory')"> Yes</label>
                                                <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="kkEditVotingHistory" value="No" onchange="kkEditSingleCheck(this,'kkEditVotingHistory')"> No</label>
                                                <input type="hidden" id="kkEditVotingHistory" value="">
                                            </div>
                                        </div>
                                        <div class="kkp-demo-block kkp-assembly-followup" id="kkEditAssemblyYesCell">
                                            <div class="kkp-demo-block-label">If Yes, How many times?</div>
                                            <div class="kkp-demo-block-options">
                                                <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="kkEditVotingFrequency" value="1-2 Times" onchange="kkEditSingleCheck(this,'kkEditVotingFrequency')"> 1-2 Times</label>
                                                <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="kkEditVotingFrequency" value="3-4 Times" onchange="kkEditSingleCheck(this,'kkEditVotingFrequency')"> 3-4 Times</label>
                                                <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="kkEditVotingFrequency" value="5 and above" onchange="kkEditSingleCheck(this,'kkEditVotingFrequency')"> 5 and above</label>
                                                <input type="hidden" id="kkEditVotingFrequency" value="">
                                            </div>
                                        </div>
                                        <div class="kkp-demo-block kkp-assembly-followup" id="kkEditAssemblyNoCell">
                                            <div class="kkp-demo-block-label">If No, Why?</div>
                                            <div class="kkp-demo-block-options">
                                                <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="kkEditVotingReason" value="There was no KK Assembly Meeting" onchange="kkEditSingleCheck(this,'kkEditVotingReason')"> There was no KK Assembly Meeting</label>
                                                <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="kkEditVotingReason" value="Not interested to Attend" onchange="kkEditSingleCheck(this,'kkEditVotingReason')"> Not interested to Attend</label>
                                                <input type="hidden" id="kkEditVotingReason" value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER: FB Account + Group Chat (with spacing) --}}
                    <div class="kkf-footer-row">
                        <div class="kkf-footer-fb">
                            <label class="kkf-inline-label" for="kkEditFacebookAccount">FB Account:</label>
                            <input type="text" id="kkEditFacebookAccount" class="kkf-uline kkf-uline-fb" placeholder=" ">
                        </div>
                        <div class="kkf-footer-spacer"></div>
                        <div class="kkf-footer-chat">
                            <span class="kkf-inline-label">Willing to join the group chat?</span>
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditWillingToJoinGroupChat" value="Yes" onchange="kkEditSingleCheck(this,'kkEditWillingToJoinGroupChat')"> Yes</label>
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kkEditWillingToJoinGroupChat" value="No" onchange="kkEditSingleCheck(this,'kkEditWillingToJoinGroupChat')"> No</label>
                            <input type="hidden" id="kkEditWillingToJoinGroupChat" value="">
                        </div>
                    </div>

                    <div class="kkp-thankyou">Thank you for your participation!</div>

                    <div class="kkp-sig-section-left">
                        <div class="kkp-sig-container kkp-sig-container--readonly">
                            <div class="kkp-sig-overlay" id="kkEditSignatureOverlay" style="display:none;">
                                <img id="kkEditSignaturePreview" class="kkp-sig-overlay-img" alt="Signature">
                            </div>
                            <div class="kkp-sig-name-wrapper">
                                <input type="text" id="kkEditSignatureName" class="kkp-sig-name-input" readonly tabindex="-1">
                            </div>
                            <div class="kkp-sig-label-bottom">Name and Signature of Participant</div>
                        </div>
                    </div>

    </div>
</div>
