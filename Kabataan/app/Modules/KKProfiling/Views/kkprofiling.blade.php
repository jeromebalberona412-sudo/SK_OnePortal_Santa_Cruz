<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KK Profiling - {{ $barangay }} - SK OnePortal</title>
    @vite([
        'app/Modules/Homepage/assets/css/homepage.css',
        'app/Modules/KKProfiling/assets/css/kkprofiling.css',
        'app/Modules/KKProfiling/assets/js/kkprofiling.js',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])
</head>
<body class="homepage-body">

    @include('dashboard::loading')

    {{-- Navbar --}}
    <nav class="top-navbar">
        <div class="navbar-container">
            <a href="{{ route('homepage') }}" class="navbar-left">
                <img src="/images/skoneportal_logo.webp" alt="SK OnePortal" class="navbar-logo">
                <span class="navbar-title">SK OnePortal</span>
            </a>
            <div class="navbar-links">
                <a href="{{ route('homepage') }}" class="nav-link">Home</a>
                <a href="{{ route('about') }}" class="nav-link">About</a>
                <a href="{{ route('about') }}#services" class="nav-link">Services</a>
                <a href="{{ route('about') }}#barangay" class="nav-link">Barangay</a>
                <a href="{{ route('about') }}#contact" class="nav-link">Contact</a>
            </div>
            <div class="navbar-right">
                <button type="button" class="nav-btn solid" id="navLoginBtn">Login</button>
                <button class="nav-hamburger" id="navHamburger" aria-label="Open menu">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </nav>

    <div class="nav-drawer" id="navDrawer">
        <a href="{{ route('homepage') }}" class="nav-link">Home</a>
        <a href="{{ route('about') }}" class="nav-link">About</a>
        <a href="{{ route('about') }}#services" class="nav-link">Services</a>
        <a href="{{ route('about') }}#barangay" class="nav-link">Barangay</a>
        <a href="{{ route('about') }}#contact" class="nav-link">Contact</a>
        <div class="nav-drawer-actions">
            <button type="button" class="nav-btn solid" id="navDrawerLoginBtn">Login</button>
        </div>
    </div>

    <main class="kkp-main">
        <div class="kkp-page-wrap">

            {{-- Back link --}}
            <a href="{{ route('about') }}#barangay" class="kkp-back-link">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
                Back to Barangays
            </a>

            {{-- Success Alert --}}
            @if (session('success'))
                <div class="kkp-alert kkp-alert-success">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Paper Form Card --}}
            <div class="kkp-paper">
                <form method="POST" action="{{ route('kkprofiling.submit', ['barangay' => $slug]) }}" id="kkProfilingForm">
                    @csrf

                    {{-- ── FORM HEADER ── --}}
                    <div class="kkp-form-header">
                        <div class="kkp-form-title">KK Survey Questionnaire</div>
                        <div class="kkp-form-meta">
                            <div class="kkp-meta-row">
                                <span class="kkp-meta-label">Respondent #:</span>
                                <input type="text" name="respondent_number" class="kkp-underline-input" placeholder="________________" readonly>
                            </div>
                            <div class="kkp-meta-row">
                                <span class="kkp-meta-label">Date:</span>
                                <input type="text" class="kkp-underline-input" value="{{ date('m/d/Y') }}" readonly>
                            </div>
                        </div>
                        <div class="kkp-form-logo">
                            <img src="/images/skoneportal_logo.webp" alt="SK OnePortal Logo">
                        </div>
                    </div>

                    {{-- ── NOTICE BOX ── --}}
                    <div class="kkp-notice-box">
                        <p class="kkp-notice-title">TO THE RESPONDENT:</p>
                        <p class="kkp-notice-body">We are currently conducting a study that focuses on assessing the demographic information of the Katipunan ng Kabataan. We would like to ask your participation by taking time to answer this questionnaire. Please read the questions carefully and answer them accurately.</p>
                        <p class="kkp-notice-confidential">REST ASSURED THAT ALL INFORMATION GATHERED FROM THIS STUDY WILL BE TREATED WITH UTMOST CONFIDENTIALITY.</p>
                    </div>

                    {{-- ── I. PROFILE ── --}}
                    <div class="kkp-section-heading">I. PROFILE</div>

                    {{-- ── NAME OF RESPONDENT ── --}}
                    <div class="kkp-row-label">Name of Respondent:</div>
                    <div class="kkp-name-row">
                        <div class="kkp-name-col">
                            <input type="text" name="last_name" id="kkpLastName" class="kkp-uline" placeholder=" " required maxlength="100">
                            <label class="kkp-col-label">Last Name</label>
                        </div>
                        <div class="kkp-name-col">
                            <input type="text" name="first_name" id="kkpFirstName" class="kkp-uline" placeholder=" " required maxlength="100">
                            <label class="kkp-col-label">First Name</label>
                        </div>
                        <div class="kkp-name-col">
                            <input type="text" name="middle_name" id="kkpMiddleName" class="kkp-uline" placeholder=" " maxlength="100">
                            <label class="kkp-col-label">Middle Name</label>
                        </div>
                        <div class="kkp-name-col kkp-name-col-sm">
                            <select name="suffix" id="kkpSuffix" class="kkp-uline kkp-uline-select">
                                <option value="">—</option>
                                <option>Jr.</option><option>Sr.</option>
                                <option>II</option><option>III</option><option>IV</option><option>V</option>
                            </select>
                            <label class="kkp-col-label">Suffix</label>
                        </div>
                    </div>

                    {{-- ── LOCATION ── --}}
                    <div class="kkp-row-label">Location:</div>
                    <div class="kkp-loc-row">
                        <div class="kkp-loc-col">
                            <input type="text" class="kkp-uline kkp-readonly" value="Region IV-A (CALABARZON)" readonly>
                            <label class="kkp-col-label">Region</label>
                        </div>
                        <div class="kkp-loc-col">
                            <input type="text" class="kkp-uline kkp-readonly" value="Laguna" readonly>
                            <label class="kkp-col-label">Province</label>
                        </div>
                        <div class="kkp-loc-col">
                            <input type="text" class="kkp-uline kkp-readonly" value="Santa Cruz" readonly>
                            <label class="kkp-col-label">City/Municipality</label>
                        </div>
                        <div class="kkp-loc-col">
                            <input type="text" class="kkp-uline kkp-readonly" value="{{ $barangay }}" readonly>
                            <label class="kkp-col-label">Barangay</label>
                        </div>
                        <div class="kkp-loc-col">
                            <input type="text" name="purok_zone" class="kkp-uline" placeholder=" " required maxlength="100">
                            <label class="kkp-col-label">Purok/Zone</label>
                        </div>
                    </div>

                    {{-- ── PERSONAL INFO: Sex + Age + Birthday | Email + Contact ── --}}
                    <div class="kkp-personal-row">
                        <div class="kkp-personal-left">
                            <div class="kkp-sex-block">
                                <span class="kkp-sex-label">Sex Assigned by Birth:</span>
                                <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="sexChk" value="Male" onchange="kkpSingleCheck(this,'kkpSex')"> Male</label>
                                <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="sexChk" value="Female" onchange="kkpSingleCheck(this,'kkpSex')"> Female</label>
                                <input type="hidden" id="kkpSex" name="sex">
                            </div>
                            <div class="kkp-age-dob-row">
                                <div class="kkp-inline-pair">
                                    <label class="kkp-inline-label">Age: *</label>
                                    <input type="number" name="age" id="kkpAge" min="15" max="30" class="kkp-uline kkp-uline-short" placeholder=" " required>
                                </div>
                                <div class="kkp-inline-pair">
                                    <label class="kkp-inline-label">Birthday:</label>
                                    <input type="date" name="birthday" id="kkpBirthday" class="kkp-uline kkp-uline-med" required>
                                    <span class="kkp-hint">(dd/mm/yy)</span>
                                </div>
                            </div>
                        </div>
                        <div class="kkp-personal-right">
                            <div class="kkp-inline-pair">
                                <label class="kkp-inline-label">E-mail address:</label>
                                <input type="email" name="email" class="kkp-uline kkp-uline-med" placeholder=" " maxlength="150">
                            </div>
                            <div class="kkp-inline-pair">
                                <label class="kkp-inline-label">Contact #:</label>
                                <input type="text" name="contact_number" class="kkp-uline kkp-uline-med" placeholder=" " maxlength="15">
                            </div>
                        </div>
                    </div>

                    {{-- ── II. DEMOGRAPHIC CHARACTERISTICS ── --}}
                    <div class="kkp-section-heading" style="margin-top:10px;">II. DEMOGRAPHIC CHARACTERISTICS</div>
                    <p class="kkp-demo-instruction">Please put a Check mark (✓) next to the word or Phrase that matches your response.</p>

                    <div class="kkp-demo-grid">
                        {{-- LEFT COLUMN --}}
                        <div class="kkp-demo-col">
                            <div class="kkp-demo-block">
                                <div class="kkp-demo-block-label">Civil Status</div>
                                <div class="kkp-demo-block-options">
                                    <div class="kkp-demo-options-2col">
                                        <div>
                                            <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="civil_status[]" value="Single"> Single</label>
                                            <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="civil_status[]" value="Married"> Married</label>
                                            <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="civil_status[]" value="Widowed"> Widowed</label>
                                            <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="civil_status[]" value="Divorced"> Divorced</label>
                                        </div>
                                        <div>
                                            <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="civil_status[]" value="Separated"> Separated</label>
                                            <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="civil_status[]" value="Annulled"> Annulled</label>
                                            <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="civil_status[]" value="Unknown"> Unknown</label>
                                            <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="civil_status[]" value="Live-in"> Live-in</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="kkp-demo-block">
                                <div class="kkp-demo-block-label">Youth Age Group</div>
                                <div class="kkp-demo-block-options">
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="youth_age_group[]" value="Child Youth (15-17 yrs old)"> Child Youth (15-17 yrs old)</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="youth_age_group[]" value="Core Youth (18-24 yrs old)"> Core Youth (18-24 yrs old)</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="youth_age_group[]" value="Young Adult (15-30 yrs old)"> Young Adult (15-30 yrs old)</label>
                                </div>
                            </div>
                            <div class="kkp-demo-block">
                                <div class="kkp-demo-block-label">Educational Background</div>
                                <div class="kkp-demo-block-options">
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="education[]" value="Elementary Level"> Elementary Level</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="education[]" value="Elementary Grad"> Elementary Grad</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="education[]" value="High School Level"> High school level</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="education[]" value="High School Grad"> High school Grad</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="education[]" value="Vocational Grad"> Vocational Grad</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="education[]" value="College Level"> College Level</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="education[]" value="College Grad"> College Grad</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="education[]" value="Masters Level"> Masters Level</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="education[]" value="Masters Grad"> Masters Grad</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="education[]" value="Doctorate Level"> Doctorate Level</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="education[]" value="Doctorate Graduate"> Doctorate Graduate</label>
                                </div>
                            </div>
                        </div>
                        {{-- RIGHT COLUMN --}}
                        <div class="kkp-demo-col">
                            <div class="kkp-demo-block">
                                <div class="kkp-demo-block-label">Youth Classification</div>
                                <div class="kkp-demo-block-options">
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="youth_classification[]" value="In School Youth"> In school Youth</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="youth_classification[]" value="Out of School Youth"> Out of School Youth</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="youth_classification[]" value="Working Youth"> Working Youth</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="youth_classification[]" value="Youth w/ Specific Needs"> Youth w/ Specific needs:</label>
                                    <label class="kkp-chk-lbl kkp-chk-indent"><input type="checkbox" class="kkp-sq-chk" name="youth_classification[]" value="Person w/ Disability"> Person w/ Disability</label>
                                    <label class="kkp-chk-lbl kkp-chk-indent"><input type="checkbox" class="kkp-sq-chk" name="youth_classification[]" value="Children in Conflict w/ Law"> Children In Conflict w/ Law</label>
                                    <label class="kkp-chk-lbl kkp-chk-indent"><input type="checkbox" class="kkp-sq-chk" name="youth_classification[]" value="Indigenous People"> Indigenous People</label>
                                </div>
                            </div>
                            <div class="kkp-demo-block">
                                <div class="kkp-demo-block-label">Work Status</div>
                                <div class="kkp-demo-block-options">
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="work_status[]" value="Employed"> Employed</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="work_status[]" value="Unemployed"> Unemployed</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="work_status[]" value="Self-Employed"> Self-Employed</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="work_status[]" value="Currently looking for a Job"> Currently looking for a Job</label>
                                    <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="work_status[]" value="Not Interested Looking for a Job"> Not Interested Looking for a Job</label>
                                </div>
                            </div>
                            <div class="kkp-voter-section">
                                <div class="kkp-voter-row">
                                    <div class="kkp-voter-cell">
                                        <div class="kkp-voter-cell-label">Registered SK Voter?</div>
                                        <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="sk_voter[]" value="Yes"> Yes</label>
                                        <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="sk_voter[]" value="No"> No</label>
                                    </div>
                                    <div class="kkp-voter-cell">
                                        <div class="kkp-voter-cell-label">Did you vote last SK?</div>
                                        <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="sk_voted[]" value="Yes"> Yes</label>
                                        <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="sk_voted[]" value="No"> No</label>
                                    </div>
                                    <div class="kkp-voter-cell">
                                        <div class="kkp-voter-cell-label">If Yes, How many times?</div>
                                        <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="vote_frequency[]" value="1-2 Times"> 1-2 Times</label>
                                        <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="vote_frequency[]" value="3-4 Times"> 3-4 Times</label>
                                        <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="vote_frequency[]" value="5 and above"> 5 and above</label>
                                    </div>
                                </div>
                                <div class="kkp-voter-row">
                                    <div class="kkp-voter-cell">
                                        <div class="kkp-voter-cell-label">Registered National Voter?</div>
                                        <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="national_voter[]" value="Yes"> Yes</label>
                                        <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="national_voter[]" value="No"> No</label>
                                    </div>
                                    <div class="kkp-voter-cell">
                                        <div class="kkp-voter-cell-label">Have you attended a KK Assembly?</div>
                                        <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="kk_assembly[]" value="Yes"> Yes</label>
                                        <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="kk_assembly[]" value="No"> No</label>
                                    </div>
                                    <div class="kkp-voter-cell">
                                        <div class="kkp-voter-cell-label">If No, Why?</div>
                                        <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="kk_reason[]" value="There was no KK Assembly"> There was no KK Assembly</label>
                                        <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="kk_reason[]" value="Not Interested to Attend"> Not Interested to Attend</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── FOOTER: FB + Group Chat ── --}}
                    <div class="kkp-footer-row">
                        <div class="kkp-footer-fb">
                            <label class="kkp-inline-label">FB Account:</label>
                            <input type="text" name="facebook" class="kkp-uline kkp-uline-fb" placeholder=" " maxlength="150">
                        </div>
                        <div class="kkp-footer-chat">
                            <span class="kkp-inline-label">Willing to join the group chat?</span>
                            <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="group_chat[]" value="Yes"> Yes</label>
                            <label class="kkp-chk-lbl"><input type="checkbox" class="kkp-sq-chk" name="group_chat[]" value="No"> No</label>
                        </div>
                    </div>

                    {{-- ── THANK YOU ── --}}
                    <div class="kkp-thankyou">Thank you for your participation!</div>

                    {{-- ── SIGNATURE ── --}}
                    <div class="kkp-sig-section">
                        <div class="kkp-sig-container">
                            <div class="kkp-sig-overlay" id="kkpSignatureOverlay" style="display:none;">
                                <img id="kkpSignaturePreview" class="kkp-sig-overlay-img" alt="Signature">
                            </div>
                            <div class="kkp-sig-name-wrapper">
                                <input type="text" id="kkpSignatureName" name="signature_name"
                                       placeholder="" readonly class="kkp-sig-name-input">
                            </div>
                            <div class="kkp-sig-label-bottom">Name and Signature of Participant</div>
                            <button type="button" class="kkp-sig-trigger-btn" id="kkpSignatureTrigger"
                                    title="Sign here" style="display:none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                </svg>
                                Sign
                            </button>
                            <input type="hidden" id="kkpSignatureData" name="signature">
                        </div>
                    </div>

                    {{-- ── SUBMIT ── --}}
                    <button type="submit" class="kkp-submit-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h6"/></svg>
                        Submit KK Profiling
                    </button>

                </form>
            </div>{{-- end kkp-paper --}}

        </div>
    </main>

    {{-- ══════════════════════════════════════════
         SIGNATURE PAD MODAL
    ══════════════════════════════════════════ --}}
    <div class="kkp-sig-pad-overlay" id="kkpSignaturePadOverlay" style="display:none;">
        <div class="kkp-sig-pad-modal">
            <div class="kkp-sig-pad-header">
                <h3 class="kkp-sig-pad-title">✍️ Please Sign Here</h3>
                <button type="button" class="kkp-sig-pad-close" id="kkpSignaturePadClose" aria-label="Close">×</button>
            </div>
            <div class="kkp-sig-pad-body">
                <div class="kkp-sig-canvas-wrap">
                    <canvas id="kkpSignaturePadCanvas" class="kkp-sig-canvas"></canvas>
                    <div class="kkp-sig-canvas-placeholder" id="kkpSignatureCanvasPlaceholder">
                        Sign here with your mouse or finger
                    </div>
                </div>
            </div>
            <div class="kkp-sig-pad-footer">
                <button type="button" class="kkp-sig-btn-clear" id="kkpSignaturePadClear">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Clear
                </button>
                <button type="button" class="kkp-sig-btn-save" id="kkpSignaturePadSave">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Save Signature
                </button>
            </div>
        </div>
    </div>

        </div>
    </main>

    <footer class="about-footer">
        <div class="about-footer-inner">
            <div class="footer-brand">
                <img src="/images/skoneportal_logo.webp" alt="SK OnePortal" class="footer-logo">
                <div>
                    <p class="footer-title">SK OnePortal Kabataan</p>
                    <p class="footer-sub">Santa Cruz, Laguna</p>
                </div>
            </div>
            <p class="footer-copy">&copy; {{ date('Y') }} SK OnePortal. Built for the youth of Santa Cruz, Laguna.</p>
        </div>
    </footer>

</body>
</html>
