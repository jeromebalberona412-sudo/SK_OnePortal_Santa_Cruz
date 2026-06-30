@php
    /** @var callable(string, string): bool $isChecked */
    $checked = $isChecked ?? fn () => false;
@endphp

<div class="kk-view-paper kk-print-questionnaire">
    <div class="kkp-form-header">
        <div class="kkp-form-title-col">
            <div class="kkp-form-main-title">KK Survey Questionnaire</div>
            <div class="kkp-form-header-fields">
                <div class="kkp-hdr-field">
                    <span class="kkp-hdr-label">Respondent #:</span>
                    <span class="kkp-hdr-input kkp-hdr-input-readonly">{{ $respondentNumber ?? '—' }}</span>
                </div>
                <div class="kkp-hdr-field">
                    <span class="kkp-hdr-label">Date:</span>
                    <span class="kkp-hdr-input kkp-hdr-input-readonly">{{ $date ?? '' }}</span>
                </div>
            </div>
        </div>
        <div class="kkp-form-logo">
            <img
                src="{{ $barangayLogoUrl ?? asset('images/SK_OnePortal_logo.png') }}"
                alt="Barangay SK Logo"
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
        <div class="kkf-name-col"><span class="kkf-view-val kkf-uline">{{ $lastName ?? '' }}</span><label class="kkf-col-label">Last Name</label></div>
        <div class="kkf-name-col"><span class="kkf-view-val kkf-uline">{{ $firstName ?? '' }}</span><label class="kkf-col-label">First Name</label></div>
        <div class="kkf-name-col"><span class="kkf-view-val kkf-uline">{{ $middleName ?? '' }}</span><label class="kkf-col-label">Middle Name</label></div>
        <div class="kkf-name-col kkf-name-col--sm"><span class="kkf-view-val kkf-uline">{{ $suffix ?? '' }}</span><label class="kkf-col-label">Suffix</label></div>
    </div>

    <div class="kkf-row-label">Location:</div>
    <div class="kkf-loc-row">
        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline">{{ $region ?? '' }}</span><label class="kkf-col-label">Region</label></div>
        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline">{{ $province ?? '' }}</span><label class="kkf-col-label">Province</label></div>
        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline">{{ $city ?? '' }}</span><label class="kkf-col-label">City/Municipality</label></div>
        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline">{{ $barangay ?? '' }}</span><label class="kkf-col-label">Barangay</label></div>
        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline">{{ $purokZone ?? '' }}</span><label class="kkf-col-label">Purok/Zone</label></div>
    </div>

    <div class="kkf-personal-row">
        <div class="kkf-personal-left">
            <div class="kkf-sex-block">
                <span class="kkf-sex-label">Sex Assigned by Birth:</span>
                <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('sex', 'Male'))> Male</label>
                <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('sex', 'Female'))> Female</label>
            </div>
            <div class="kkf-age-dob-row">
                <div class="kkf-inline-pair"><span class="kkf-inline-label">Age:</span><span class="kkf-view-val kkf-uline kkf-uline-short">{{ $age ?? '' }}</span></div>
                <div class="kkf-inline-pair"><span class="kkf-inline-label">Birthday:</span><span class="kkf-view-val kkf-uline kkf-uline-med">{{ $birthday ?? '' }}</span><span class="kkf-hint">(dd/mm/yy)</span></div>
            </div>
        </div>
        <div class="kkf-personal-right">
            <div class="kkf-inline-pair"><span class="kkf-inline-label">E-mail address:</span><span class="kkf-view-val kkf-uline kkf-uline-med">{{ $email ?? '' }}</span></div>
            <div class="kkf-inline-pair"><span class="kkf-inline-label">Contact #:</span><span class="kkf-view-val kkf-uline kkf-uline-med">{{ $contactNumber ?? '' }}</span></div>
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
                            @foreach (['Single', 'Married', 'Widowed', 'Divorced'] as $option)
                                <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('civil_status', $option))> {{ $option }}</label>
                            @endforeach
                        </div>
                        <div>
                            @foreach (['Separated', 'Annulled', 'Unknown', 'Live-in'] as $option)
                                <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('civil_status', $option))> {{ $option }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="kkf-demo-block">
                <div class="kkf-demo-block-label">Youth Age Group</div>
                <div class="kkf-demo-block-options">
                    @foreach (['Child Youth (15-17 yrs old)', 'Core Youth (18-24 yrs old)', 'Young Adult (15-30 yrs old)'] as $option)
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('youth_age_group', $option))> {{ $option }}</label>
                    @endforeach
                </div>
            </div>
            <div class="kkf-demo-block">
                <div class="kkf-demo-block-label">Educational Background</div>
                <div class="kkf-demo-block-options">
                    @foreach (['Elementary Level', 'Elementary Grad', 'High School Level', 'High School Grad', 'Vocational Grad', 'College Level', 'College Grad', 'Masters Level', 'Masters Grad', 'Doctorate Level', 'Doctorate Graduate'] as $option)
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('education', $option))> {{ $option }}</label>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="kkf-demo-col">
            <div class="kkf-demo-block">
                <div class="kkf-demo-block-label">Youth Classification</div>
                <div class="kkf-demo-block-options">
                    @foreach (['In School Youth', 'Out of School Youth', 'Working Youth', 'Youth w/ Specific Needs'] as $option)
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('youth_classification', $option))> {{ $option === 'In School Youth' ? 'In school Youth' : ($option === 'Youth w/ Specific Needs' ? 'Youth w/ Specific needs:' : $option) }}</label>
                    @endforeach
                    @foreach (['Person w/ Disability', 'Children in Conflict w/ Law', 'Indigenous People'] as $option)
                        <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('youth_classification', $option))> {{ $option === 'Children in Conflict w/ Law' ? 'Children In Conflict w/ Law' : $option }}</label>
                    @endforeach
                </div>
            </div>
            <div class="kkf-demo-block">
                <div class="kkf-demo-block-label">Work Status</div>
                <div class="kkf-demo-block-options">
                    @foreach (['Employed', 'Unemployed', 'Self-Employed', 'Currently looking for a Job', 'Not Interested Looking for a Job'] as $option)
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('work_status', $option))> {{ $option }}</label>
                    @endforeach
                </div>
            </div>
            <div class="kkf-voter-section">
                <div class="kkf-voter-row">
                    <div class="kkf-voter-cell">
                        <div class="kkf-voter-cell-label">Registered SK Voter?</div>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('sk_voter', 'Yes'))> Yes</label>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('sk_voter', 'No'))> No</label>
                    </div>
                    <div class="kkf-voter-cell">
                        <div class="kkf-voter-cell-label">Did you vote last SK?</div>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('sk_voted', 'Yes'))> Yes</label>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('sk_voted', 'No'))> No</label>
                    </div>
                    <div class="kkf-voter-cell">
                        <div class="kkf-voter-cell-label">If Yes, How many times?</div>
                        @foreach (['1-2 Times', '3-4 Times', '5 and above'] as $option)
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('kk_times', $option))> {{ $option }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="kkf-voter-row">
                    <div class="kkf-voter-cell">
                        <div class="kkf-voter-cell-label">Registered National Voter?</div>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('national_voter', 'Yes'))> Yes</label>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('national_voter', 'No'))> No</label>
                    </div>
                    <div class="kkf-voter-cell">
                        <div class="kkf-voter-cell-label">Have you attended a KK Assembly?</div>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('kk_assembly', 'Yes'))> Yes</label>
                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('kk_assembly', 'No'))> No</label>
                    </div>
                    <div class="kkf-voter-cell">
                        <div class="kkf-voter-cell-label">If No, Why?</div>
                        @foreach (['There was no KK Assembly', 'Not Interested to Attend'] as $option)
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('kk_reason', $option))> {{ $option }}</label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="kkf-footer-row">
        <div class="kkf-footer-fb">
            <span class="kkf-inline-label">FB Account:</span>
            <span class="kkf-view-val kkf-uline kkf-uline-fb">{{ $facebook ?? '' }}</span>
        </div>
        <div class="kkf-footer-chat">
            <span class="kkf-inline-label">Willing to join the group chat?</span>
            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('group_chat', 'Yes'))> Yes</label>
            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" disabled @checked($checked('group_chat', 'No'))> No</label>
        </div>
    </div>

    <div class="kkf-sig-section">
        <div class="kkf-sig-container">
            @if (! empty($signatureImage))
                <div class="kkf-sig-overlay" style="display:flex;">
                    <img src="{{ $signatureImage }}" class="kkf-sig-overlay-img" alt="Signature">
                </div>
            @endif
            <div class="kkf-sig-name-wrapper">
                <span class="kkf-sig-name-input" style="display:block;">{{ $signatureName ?? '' }}</span>
            </div>
            <div class="kkf-sig-label-bottom">Name and Signature of Participant</div>
        </div>
    </div>
</div>
