{{-- Read-only KK Survey Questionnaire — matches Kabataan kkp-* layout with tabs --}}
<div class="kk-profiling-view-root">
    <div class="kk-profiling-view-tabs" id="kkViewTabs" role="tablist" aria-label="KK profiling details">
        <button type="button" class="kk-profiling-view-tab is-active" data-kk-view-tab="profile" role="tab" aria-selected="true">KK Profiling</button>
        <button type="button" class="kk-profiling-view-tab" data-kk-view-tab="documents" role="tab" aria-selected="false">Supporting Documents</button>
    </div>

    <div class="kk-profiling-view-tab-panel" id="kkViewTabProfile" role="tabpanel">
        <div class="kk-view-paper kk-profiling-form-scroll">
    <div class="kk-view-evaluation-banner" id="kkViewEvaluationBanner" hidden role="status"></div>

    <div class="kkp-form-header">
        <div class="kkp-form-title-col">
            <div class="kkp-form-main-title">KK Survey Questionnaire</div>
            <div class="kkp-form-header-fields">
                <div class="kkp-hdr-field">
                    <span class="kkp-hdr-label">Respondent #:</span>
                    <span class="kkp-hdr-input kkp-hdr-input-readonly" id="kkViewRespondentNumber"></span>
                </div>
                <div class="kkp-hdr-field">
                    <span class="kkp-hdr-label">Date:</span>
                    <span class="kkp-hdr-input kkp-hdr-input-readonly" id="kkViewDate"></span>
                </div>
            </div>
        </div>
        <div class="kkp-form-logo">
            <img
                id="kkViewBarangayLogo"
                src="{{ $barangayLogoUrl ?? asset('images/SK_OnePortal_logo.png') }}"
                alt="{{ ($barangayName ?? 'Barangay') }} SK Logo"
                onerror="this.onerror=null;this.src='{{ asset('images/SK_OnePortal_logo.png') }}';"
            >
        </div>
    </div>

    <div class="kkp-notice-box">
        <p class="kkp-notice-title">TO THE RESPONDENT:</p>
        <p class="kkp-notice-body">We are currently conducting a study that focuses on assessing the demographic information of the Katipunan ng Kabataan. We would like to<br>ask your participation by taking time to answer this questionnaire.&nbsp; Please read the questions carefully and answer them<br>accurately.</p>
        <p class="kkp-notice-confidential">REST ASSURED THAT ALL INFORMATION GATHERED FROM THIS STUDY WILL BE TREATED WITH UTMOST CONFIDENTIALITY.</p>
    </div>

    <div class="kkp-section-heading">I. PROFILE</div>

    <div class="kkp-row-label">Name of Respondent</div>
    <div class="kkp-name-row">
        <div class="kkp-name-col">
            <span class="kkp-uline kkp-view-value" id="kkViewLastName"></span>
            <span class="kkp-col-label">Last Name</span>
        </div>
        <div class="kkp-name-col">
            <span class="kkp-uline kkp-view-value" id="kkViewFirstName"></span>
            <span class="kkp-col-label">First Name</span>
        </div>
        <div class="kkp-name-col">
            <span class="kkp-uline kkp-view-value" id="kkViewMiddleName"></span>
            <span class="kkp-col-label">Middle Name</span>
        </div>
        <div class="kkp-name-col kkp-name-col-sm">
            <span class="kkp-uline kkp-view-value" id="kkViewSuffix"></span>
            <span class="kkp-col-label">Suffix</span>
        </div>
    </div>

    <div class="kkp-row-label">Location:</div>
    <div class="kkp-loc-row">
        <div class="kkp-loc-col">
            <span class="kkp-uline kkp-view-value kkp-readonly" id="kkViewRegion"></span>
            <span class="kkp-col-label">Region</span>
        </div>
        <div class="kkp-loc-col">
            <span class="kkp-uline kkp-view-value kkp-readonly" id="kkViewProvince"></span>
            <span class="kkp-col-label">Province</span>
        </div>
        <div class="kkp-loc-col">
            <span class="kkp-uline kkp-view-value kkp-readonly" id="kkViewCity"></span>
            <span class="kkp-col-label">City/Municipality</span>
        </div>
        <div class="kkp-loc-col">
            <span class="kkp-uline kkp-view-value kkp-readonly" id="kkViewBarangay"></span>
            <span class="kkp-col-label">Barangay</span>
        </div>
        <div class="kkp-loc-col">
            <span class="kkp-uline kkp-view-value" id="kkViewPurokZone"></span>
            <span class="kkp-col-label">Purok/Zone</span>
        </div>
    </div>

    <div class="kkp-personal-row">
        <div class="kkp-personal-left">
            <div class="kkp-sex-block">
                <div class="kkp-sex-label-box">Sex Assigned by Birth:</div>
                <div class="kkp-sex-options">
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewSex_Male" disabled> Male</label>
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewSex_Female" disabled> Female</label>
                </div>
            </div>
        </div>
        <div class="kkp-personal-center">
            <div class="kkp-age-dob-row">
                <div class="kkp-inline-pair">
                    <span class="kkp-inline-label">Age:</span>
                    <span class="kkp-uline kkp-uline-short kkp-view-value" id="kkViewAge"></span>
                </div>
                <div class="kkp-inline-pair">
                    <span class="kkp-inline-label">Birthday:</span>
                    <span class="kkp-uline kkp-uline-med kkp-view-value" id="kkViewBirthday"></span>
                </div>
                <span class="kkp-hint" style="display:block;text-align:center;margin-top:2px;">Age: 15–30 years old only</span>
                <span class="kkp-hint" style="display:block;text-align:center;margin-top:2px;">(mm/dd/yyyy)</span>
            </div>
        </div>
        <div class="kkp-personal-right">
            <div class="kkp-inline-pair">
                <span class="kkp-inline-label">E-mail address:</span>
                <span class="kkp-uline kkp-uline-med kkp-view-value" id="kkViewEmailAddress"></span>
            </div>
            <div class="kkp-inline-pair">
                <span class="kkp-inline-label">Contact #:</span>
                <span class="kkp-uline kkp-uline-med kkp-view-value" id="kkViewContactNumber"></span>
            </div>
        </div>
    </div>

    <div class="kkp-section-heading" style="margin-top:10px;">II. DEMOGRAPHIC CHARACTERISTICS</div>
    <p class="kkp-demo-instruction">Please put a Check mark (✓) next to the word or Phrase that matches your response.</p>

    <div class="kkp-demo-grid">
        <div class="kkp-demo-col">
            <div class="kkp-demo-block">
                <div class="kkp-demo-block-label">Civil Status</div>
                <div class="kkp-demo-block-options">
                    <div class="kkp-demo-options-2col">
                        <div>
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewCS_Single" disabled> Single</label>
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewCS_Married" disabled> Married</label>
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewCS_Widowed" disabled> Widowed</label>
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewCS_Divorced" disabled> Divorced</label>
                        </div>
                        <div>
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewCS_Separated" disabled> Separated</label>
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewCS_Annulled" disabled> Annulled</label>
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewCS_Unknown" disabled> Unknown</label>
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewCS_Livein" disabled> Live-in</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kkp-demo-block">
                <div class="kkp-demo-block-label">Youth Age Group</div>
                <div class="kkp-demo-block-options">
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewYAG_Child" disabled> Child Youth (15-17 yrs old)</label>
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewYAG_Core" disabled> Core Youth (18-24 yrs old)</label>
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewYAG_Young" disabled> Young Adult (15-30 yrs old)</label>
                </div>
            </div>
            <div class="kkp-demo-block">
                <div class="kkp-demo-block-label">Educational Background</div>
                <div class="kkp-demo-block-options">
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewEB_ElemLevel" disabled> Elementary Level</label>
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewEB_ElemGrad" disabled> Elementary Grad</label>
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewEB_HSLevel" disabled> High school level</label>
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewEB_HSGrad" disabled> High school Grad</label>
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewEB_VocGrad" disabled> Vocational Grad</label>
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewEB_ColLevel" disabled> College Level</label>
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewEB_ColGrad" disabled> College Grad</label>
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewEB_MasLevel" disabled> Masters Level</label>
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewEB_MasGrad" disabled> Masters Grad</label>
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewEB_DocLevel" disabled> Doctorate Level</label>
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewEB_DocGrad" disabled> Doctorate Graduate</label>
                </div>
            </div>
        </div>

        <div class="kkp-demo-col">
            <div class="kkp-demo-block">
                <div class="kkp-demo-block-label">Youth Classification</div>
                <div class="kkp-demo-block-options kkp-demo-two-col">
                    <div class="kkp-demo-left-col">
                        <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewYC_ISY" disabled> In school Youth</label>
                        <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewYC_OSY" disabled> Out of School Youth</label>
                        <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewYC_Working" disabled> Working Youth</label>
                    </div>
                    <div class="kkp-demo-right-col">
                        <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewYC_PWD" disabled> Person w/ Disability</label>
                        <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewYC_CICL" disabled> Children In Conflict w/ Law</label>
                        <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewYC_IP" disabled> Indigenous People</label>
                    </div>
                </div>
            </div>
            <div class="kkp-demo-block">
                <div class="kkp-demo-block-label">Work Status</div>
                <div class="kkp-demo-block-options">
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewWS_Employed" disabled> Employed</label>
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewWS_Unemployed" disabled> Unemployed</label>
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewWS_SelfEmployed" disabled> Self-Employed</label>
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewWS_Looking" disabled> Currently looking for a Job</label>
                    <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewWS_NotInterested" disabled> Not Interested Looking for a Job</label>
                </div>
            </div>

            <div class="kkp-voter-questions-grid">
                <div class="kkp-voter-questions-col">
                    <div class="kkp-demo-block">
                        <div class="kkp-demo-block-label">Registered SK Voter?</div>
                        <div class="kkp-demo-block-options">
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewSKV_Yes" disabled> Yes</label>
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewSKV_No" disabled> No</label>
                        </div>
                    </div>
                    <div class="kkp-demo-block">
                        <div class="kkp-demo-block-label">Registered National Voter?</div>
                        <div class="kkp-demo-block-options">
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewNV_Yes" disabled> Yes</label>
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewNV_No" disabled> No</label>
                        </div>
                    </div>
                    <div class="kkp-demo-block">
                        <div class="kkp-demo-block-label">Have you attended a KK Assembly?</div>
                        <div class="kkp-demo-block-options">
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewKK_Yes" disabled> Yes</label>
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewKK_No" disabled> No</label>
                        </div>
                    </div>
                </div>
                <div class="kkp-voter-questions-col">
                    <div class="kkp-demo-block">
                        <div class="kkp-demo-block-label">Did you vote last SK?</div>
                        <div class="kkp-demo-block-options">
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewVH_Yes" disabled> Yes</label>
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewVH_No" disabled> No</label>
                        </div>
                    </div>
                    <div class="kkp-demo-block">
                        <div class="kkp-demo-block-label">If Yes, How many times?</div>
                        <div class="kkp-demo-block-options">
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewKKTimes_12" disabled> 1-2 Times</label>
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewKKTimes_34" disabled> 3-4 Times</label>
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewKKTimes_5" disabled> 5 and above</label>
                        </div>
                    </div>
                    <div class="kkp-demo-block" id="kkViewAssemblyNoCell">
                        <div class="kkp-demo-block-label">If No, Why?</div>
                        <div class="kkp-demo-block-options">
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewVR_NoKK" disabled> There was no KK Assembly Meeting</label>
                            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewVR_NotInt" disabled> Not interested to Attend</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="kkp-footer-row">
        <div class="kkp-footer-fb">
            <span class="kkp-inline-label">FB Account:</span>
            <span class="kkp-uline kkp-uline-fb kkp-view-value" id="kkViewFacebookAccount"></span>
        </div>
        <div class="kkp-footer-chat">
            <span class="kkp-inline-label">Willing to join the group chat?</span>
            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewGC_Yes" disabled> Yes</label>
            <label class="kkp-chk-lbl kkp-view-chk"><input type="checkbox" class="kkp-sq-chk" id="kkViewGC_No" disabled> No</label>
        </div>
    </div>

    <div class="kkp-thankyou">Thank you for your participation!</div>

    <div class="kkp-sig-section-left">
        <div class="kkp-sig-container">
            <div class="kkp-sig-overlay" id="kkViewSignatureOverlay" style="display:none;">
                <img id="kkViewSignaturePreview" class="kkp-sig-overlay-img" alt="Signature">
            </div>
            <div class="kkp-sig-name-wrapper">
                <span class="kkp-sig-name-input kkp-view-value" id="kkViewSignatureName"></span>
            </div>
            <div class="kkp-sig-label-bottom">Name and Signature of Participant</div>
        </div>
    </div>

    <div class="kk-view-rejection-wrap" id="kkViewRejectionWrap" style="display:none;">
        <span class="kk-view-label">Rejection reason:</span>
        <p class="kk-view-rejection-text" id="kkViewRejectionText"></p>
    </div>

        </div>
    </div>

    <div class="kk-profiling-view-tab-panel" id="kkViewTabDocuments" role="tabpanel" hidden>
        <div class="kk-view-paper kk-view-documents-panel">
            <div class="kk-view-documents-title">Supporting Documents (ID)</div>
            <div class="kk-view-id-verification" id="kkViewIdVerification" hidden></div>
            <div class="kk-view-documents-wrap" id="kkViewDocumentsWrap" style="display:none;">
                <div class="kk-view-documents-grid" id="kkViewDocumentsGrid"></div>
            </div>
            <p class="kk-view-documents-empty" id="kkViewDocumentsEmpty" hidden>No supporting documents were uploaded for this submission.</p>
        </div>
    </div>
</div>
