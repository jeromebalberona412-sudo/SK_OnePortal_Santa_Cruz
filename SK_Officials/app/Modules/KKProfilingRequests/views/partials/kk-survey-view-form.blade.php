{{-- Read-only KK Survey Questionnaire — same layout as Kabataan List view --}}
<div class="kk-view-evaluation-banner" id="kkViewEvaluationBanner" hidden role="status"></div>
<div class="kabataan-form-scroll kk-view-paper">

    <div class="kkp-form-header">
        <div class="kkp-form-title-col">
            <div class="kkp-form-main-title">KK Survey Questionnaire</div>
            <div class="kkp-form-header-fields">
                <div class="kkp-hdr-field">
                    <span class="kkp-hdr-label">Respondent #:</span>
                    <span class="kkp-hdr-input kkp-hdr-input-readonly" id="vRespondentNumber"></span>
                </div>
                <div class="kkp-hdr-field">
                    <span class="kkp-hdr-label">Date:</span>
                    <span class="kkp-hdr-input kkp-hdr-input-readonly" id="vDate"></span>
                </div>
            </div>
        </div>
        <div class="kkp-form-logo">
            <img
                id="kkRequestBarangayLogo"
                src="{{ $barangayLogoUrl ?? asset('images/SK_OnePortal_logo.png') }}"
                alt="{{ ($barangayName ?? 'Barangay') }} SK Logo"
                onerror="this.onerror=null;this.src='{{ asset('images/SK_OnePortal_logo.png') }}';"
            >
        </div>
    </div>

    <div class="kkf-notice-box">
        <div class="kkf-notice-title">TO THE RESPONDENT:</div>
        <p class="kkf-notice-body">We are currently conducting a study that focuses on assessing the demographic information of the Katipunan ng Kabataan. We would like to ask your participation by taking your time to answer this questionnaire. Please read the questions carefully and answer them accurately.</p>
        <p class="kkf-notice-confidential">REST ASSURED THAT ALL INFORMATION GATHERED FROM THIS STUDY WILL BE TREATED WITH UTMOST CONFIDENTIALITY.</p>
    </div>

    <div class="kkf-section-title">I. Profile</div>

    <div class="kkf-row-label">Name of Respondent:</div>
    <div class="kkf-name-row">
        <div class="kkf-name-col"><span class="kkf-view-val kkf-uline" id="vLastName"></span><label class="kkf-col-label">Last Name</label></div>
        <div class="kkf-name-col"><span class="kkf-view-val kkf-uline" id="vFirstName"></span><label class="kkf-col-label">First Name</label></div>
        <div class="kkf-name-col"><span class="kkf-view-val kkf-uline" id="vMiddleName"></span><label class="kkf-col-label">Middle Name</label></div>
        <div class="kkf-name-col kkf-name-col--sm"><span class="kkf-view-val kkf-uline" id="vSuffix"></span><label class="kkf-col-label">Suffix</label></div>
    </div>

    <div class="kkf-row-label">Location:</div>
    <div class="kkf-loc-row">
        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline" id="vRegion"></span><label class="kkf-col-label">Region</label></div>
        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline" id="vProvince"></span><label class="kkf-col-label">Province</label></div>
        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline" id="vCity"></span><label class="kkf-col-label">City/Municipality</label></div>
        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline" id="vBarangay"></span><label class="kkf-col-label">Barangay</label></div>
        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline" id="vPurokZone"></span><label class="kkf-col-label">Purok/Zone</label></div>
    </div>

    <div class="kkf-personal-row">
        <div class="kkf-personal-left">
            <div class="kkf-sex-block">
                <span class="kkf-sex-label">Sex Assigned by Birth:</span>
                <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vSex" value="Male" disabled> Male</label>
                <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vSex" value="Female" disabled> Female</label>
            </div>
            <div class="kkf-age-dob-row">
                <div class="kkf-inline-pair"><span class="kkf-inline-label">Age:</span><span class="kkf-view-val kkf-uline kkf-uline-short" id="vAge"></span></div>
                <div class="kkf-inline-pair"><span class="kkf-inline-label">Birthday:</span><span class="kkf-view-val kkf-uline kkf-uline-med" id="vDob"></span><span class="kkf-hint">(dd/mm/yy)</span></div>
            </div>
        </div>
        <div class="kkf-personal-right">
            <div class="kkf-inline-pair"><span class="kkf-inline-label">E-mail address:</span><span class="kkf-view-val kkf-uline kkf-uline-med" id="vEmail"></span></div>
            <div class="kkf-inline-pair"><span class="kkf-inline-label">Contact #:</span><span class="kkf-view-val kkf-uline kkf-uline-med" id="vContact"></span></div>
        </div>
    </div>

    <div class="kkf-section-title">II. Demographic Characteristics</div>
    <p class="kkf-instruction">Please put a Check mark next to the word or Phrase that matches your response.</p>

    <div class="kkf-demo-grid">
        <div class="kkf-demo-col">
            <div class="kkf-demo-block">
                <div class="kkf-demo-block-label">Civil Status</div>
                <div class="kkf-demo-block-options">
                    <div class="kkf-demo-options-2col">
                        <div>
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Single" disabled> Single</label>
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Married" disabled> Married</label>
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Widowed" disabled> Widowed</label>
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Divorced" disabled> Divorced</label>
                        </div>
                        <div>
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Separated" disabled> Separated</label>
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Annulled" disabled> Annulled</label>
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Unknown" disabled> Unknown</label>
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Live-in" disabled> Live-in</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kkf-demo-block">
                <div class="kkf-demo-block-label">Youth Age Group</div>
                <div class="kkf-demo-block-options">
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthAgeGroup" value="Child Youth (15-17 yrs old)" disabled> Child Youth (15-17 yrs old)</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthAgeGroup" value="Core Youth (18-24 yrs old)" disabled> Core Youth (18-24 yrs old)</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthAgeGroup" value="Young Adult (15-30 yrs old)" disabled> Young Adult (15-30 yrs old)</label>
                </div>
            </div>
            <div class="kkf-demo-block">
                <div class="kkf-demo-block-label">Educational Background</div>
                <div class="kkf-demo-block-options">
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Elementary Level" disabled> Elementary Level</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Elementary Grad" disabled> Elementary Grad</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="High School Level" disabled> High school level</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="High School Grad" disabled> High school Grad</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Vocational Grad" disabled> Vocational Grad</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="College Level" disabled> College Level</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="College Grad" disabled> College Grad</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Masters Level" disabled> Masters Level</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Masters Grad" disabled> Masters Grad</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Doctorate Level" disabled> Doctorate Level</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Doctorate Graduate" disabled> Doctorate Graduate</label>
                </div>
            </div>
        </div>
        <div class="kkf-demo-col">
            <div class="kkf-demo-block">
                <div class="kkf-demo-block-label">Youth Classification</div>
                <div class="kkf-demo-block-options">
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="In School Youth" disabled> In school Youth</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="Out of School Youth" disabled> Out of School Youth</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="Working Youth" disabled> Working Youth</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="Youth w/ Specific Needs" disabled> Youth w/ Specific needs:</label>
                    <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="Person w/ Disability" disabled> Person w/ Disability</label>
                    <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="Children in Conflict w/ Law" disabled> Children In Conflict w/ Law</label>
                    <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="Indigenous People" disabled> Indigenous People</label>
                </div>
            </div>
            <div class="kkf-demo-block">
                <div class="kkf-demo-block-label">Work Status</div>
                <div class="kkf-demo-block-options">
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vWorkStatus" value="Employed" disabled> Employed</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vWorkStatus" value="Unemployed" disabled> Unemployed</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vWorkStatus" value="Self-Employed" disabled> Self-Employed</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vWorkStatus" value="Currently looking for a Job" disabled> Currently looking for a Job</label>
                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vWorkStatus" value="Not Interested Looking for a Job" disabled> Not Interested Looking for a Job</label>
                </div>
            </div>
            <div class="kkf-voter-section">
                <div class="kkf-voter-row">
                    <div class="kkf-voter-cell">
                        <div class="kkf-voter-cell-label">Registered SK Voter?</div>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vSKVoter" value="Yes" disabled> Yes</label>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vSKVoter" value="No" disabled> No</label>
                    </div>
                    <div class="kkf-voter-cell">
                        <div class="kkf-voter-cell-label">Did you vote last SK?</div>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingHistory" value="Yes" disabled> Yes</label>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingHistory" value="No" disabled> No</label>
                    </div>
                    <div class="kkf-voter-cell">
                        <div class="kkf-voter-cell-label">If Yes, How many times?</div>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingFrequency" value="1-2 Times" disabled> 1-2 Times</label>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingFrequency" value="3-4 Times" disabled> 3-4 Times</label>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingFrequency" value="5 and above" disabled> 5 and above</label>
                    </div>
                </div>
                <div class="kkf-voter-row">
                    <div class="kkf-voter-cell">
                        <div class="kkf-voter-cell-label">Registered National Voter?</div>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vNatVoter" value="Yes" disabled> Yes</label>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vNatVoter" value="No" disabled> No</label>
                    </div>
                    <div class="kkf-voter-cell">
                        <div class="kkf-voter-cell-label">Have you attended a KK Assembly?</div>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vKKAssembly" value="Yes" disabled> Yes</label>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vKKAssembly" value="No" disabled> No</label>
                    </div>
                    <div class="kkf-voter-cell">
                        <div class="kkf-voter-cell-label">If No, Why?</div>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingReason" value="There was no KK Assembly" disabled> There was no KK Assembly</label>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingReason" value="Not Interested to Attend" disabled> Not Interested to Attend</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="kkf-footer-row">
        <div class="kkf-footer-fb">
            <span class="kkf-inline-label">FB Account:</span>
            <span class="kkf-view-val kkf-uline kkf-uline-fb" id="vFacebook"></span>
        </div>
        <div class="kkf-footer-chat">
            <span class="kkf-inline-label">Willing to join the group chat?</span>
            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vGroupChat" value="Yes" disabled> Yes</label>
            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vGroupChat" value="No" disabled> No</label>
        </div>
    </div>

    <div class="kkf-sig-section">
        <div class="kkf-sig-container">
            <div class="kkf-sig-overlay" id="vSignatureOverlay" style="display: none;">
                <img id="vSignature" class="kkf-sig-overlay-img" alt="Signature">
            </div>
            <div class="kkf-sig-name-wrapper">
                <span id="vSignatureText" class="kkf-sig-name-input" style="display: block;"></span>
            </div>
            <div class="kkf-sig-label-bottom">Name and Signature of Participant</div>
        </div>
    </div>

</div>
