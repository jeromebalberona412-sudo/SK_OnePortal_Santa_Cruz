<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KK Profiling - {{ $barangay }} - SK OnePortal</title>
    @vite([
        'app/Modules/Homepage/assets/css/homepage.css',
        'app/Modules/Homepage/assets/css/kkprofiling.css',
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
        <div class="kkp-container">

            {{-- Header --}}
            <div class="kkp-header">
                <a href="{{ route('about') }}#barangay" class="kkp-back-link">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
                    Back to Barangays
                </a>
                <div class="kkp-header-content">
                    <span class="kkp-header-badge">Barangay {{ $barangay }}</span>
                    <h1>KK Survey Questionnaire</h1>
                    <p>Katipunan ng Kabataan Profiling Form — Santa Cruz, Laguna</p>
                </div>
            </div>

            {{-- Success Alert --}}
            @if (session('success'))
                <div class="kkp-alert kkp-alert-success">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Form Card --}}
            <div class="kkp-form-card">
                <form method="POST" action="{{ route('kkprofiling.submit', ['barangay' => $slug]) }}" class="kkp-form">
                    @csrf

                    {{-- General Info --}}
                    <div class="kkp-general-row">
                        <div class="kkp-field-inline">
                            <label>Respondent #:</label>
                            <input type="text" name="respondent_number" placeholder="Auto-generated" readonly style="background:#f8fafc;cursor:not-allowed;">
                        </div>
                        <div class="kkp-field-inline">
                            <label>Date:</label>
                            <input type="text" value="{{ date('m/d/Y') }}" readonly style="background:#f8fafc;cursor:not-allowed;">
                        </div>
                    </div>

                    {{-- I. PROFILE --}}
                    <div class="kkp-section-title">I. PROFILE</div>

                    <div class="kkp-section-label">Name of Respondent:</div>
                    <div class="kkp-row">
                        <div class="kkp-col"><label>Last Name</label><input type="text" name="last_name" required maxlength="100"></div>
                        <div class="kkp-col"><label>First Name</label><input type="text" name="first_name" required maxlength="100"></div>
                        <div class="kkp-col"><label>Middle Name</label><input type="text" name="middle_name" maxlength="100"></div>
                        <div class="kkp-col kkp-col-xs"><label>Suffix</label><select name="suffix"><option value="">None</option><option>Jr.</option><option>Sr.</option><option>II</option><option>III</option><option>IV</option><option>V</option></select></div>
                    </div>

                    <div class="kkp-section-label">Location:</div>
                    <div class="kkp-row">
                        <div class="kkp-col"><label>Region</label><input type="text" value="Region IV-A (CALABARZON)" readonly style="background:#f8fafc;"></div>
                        <div class="kkp-col"><label>Province</label><input type="text" value="Laguna" readonly style="background:#f8fafc;"></div>
                        <div class="kkp-col"><label>City/Municipality</label><input type="text" value="Santa Cruz" readonly style="background:#f8fafc;"></div>
                    </div>
                    <div class="kkp-row">
                        <div class="kkp-col"><label>Barangay</label><input type="text" value="{{ $barangay }}" readonly style="background:#f8fafc;"></div>
                        <div class="kkp-col"><label>Purok/Zone</label><input type="text" name="purok_zone" required maxlength="100" placeholder="e.g. Purok 1"></div>
                    </div>

                    <div class="kkp-personal-row">
                        <div class="kkp-personal-group">
                            <label>Sex Assigned by Birth:</label>
                            <div class="kkp-radio-inline">
                                <label><input type="radio" name="sex" value="Male" required> Male</label>
                                <label><input type="radio" name="sex" value="Female" required> Female</label>
                            </div>
                        </div>
                        <div class="kkp-personal-group">
                            <label>Age:</label>
                            <input type="number" name="age" required min="15" max="30" style="max-width:80px;">
                        </div>
                        <div class="kkp-personal-group">
                            <label>Birthday:</label>
                            <input type="date" name="birthday" required>
                        </div>
                    </div>

                    <div class="kkp-row">
                        <div class="kkp-col"><label>E-mail address:</label><input type="email" name="email" maxlength="150"></div>
                        <div class="kkp-col"><label>Contact #:</label><input type="tel" name="contact_number" maxlength="15" placeholder="09XX XXX XXXX"></div>
                    </div>

                    {{-- II. DEMOGRAPHIC CHARACTERISTICS --}}
                    <div class="kkp-section-title">II. DEMOGRAPHIC CHARACTERISTICS</div>
                    <p class="kkp-instruction">Please put a Check mark next to the word or Phrase that matches your response.</p>

                    <div class="kkp-demo-grid">
                        <div class="kkp-demo-col">
                            <div class="kkp-demo-block">
                                <div class="kkp-demo-label">Civil Status</div>
                                <div class="kkp-checkbox-grid">
                                    <label><input type="checkbox" name="civil_status[]" value="Single"> Single</label>
                                    <label><input type="checkbox" name="civil_status[]" value="Married"> Married</label>
                                    <label><input type="checkbox" name="civil_status[]" value="Widowed"> Widowed</label>
                                    <label><input type="checkbox" name="civil_status[]" value="Divorced"> Divorced</label>
                                    <label><input type="checkbox" name="civil_status[]" value="Separated"> Separated</label>
                                    <label><input type="checkbox" name="civil_status[]" value="Annulled"> Annulled</label>
                                    <label><input type="checkbox" name="civil_status[]" value="Unknown"> Unknown</label>
                                    <label><input type="checkbox" name="civil_status[]" value="Live-in"> Live-in</label>
                                </div>
                            </div>
                            <div class="kkp-demo-block">
                                <div class="kkp-demo-label">Youth Age Group</div>
                                <div class="kkp-checkbox-list">
                                    <label><input type="checkbox" name="youth_age_group[]" value="Child Youth (15-17)"> Child Youth (15-17 yrs old)</label>
                                    <label><input type="checkbox" name="youth_age_group[]" value="Core Youth (18-24)"> Core Youth (18-24 yrs old)</label>
                                    <label><input type="checkbox" name="youth_age_group[]" value="Young Adult (15-30)"> Young Adult (15-30 yrs old)</label>
                                </div>
                            </div>
                            <div class="kkp-demo-block">
                                <div class="kkp-demo-label">Educational Background</div>
                                <div class="kkp-checkbox-list">
                                    <label><input type="checkbox" name="education[]" value="Elementary Level"> Elementary Level</label>
                                    <label><input type="checkbox" name="education[]" value="Elementary Grad"> Elementary Grad</label>
                                    <label><input type="checkbox" name="education[]" value="High school Level"> High school Level</label>
                                    <label><input type="checkbox" name="education[]" value="High school Grad"> High school Grad</label>
                                    <label><input type="checkbox" name="education[]" value="Vocational Grad"> Vocational Grad</label>
                                    <label><input type="checkbox" name="education[]" value="College Level"> College Level</label>
                                    <label><input type="checkbox" name="education[]" value="College Grad"> College Grad</label>
                                    <label><input type="checkbox" name="education[]" value="Masters Level"> Masters Level</label>
                                    <label><input type="checkbox" name="education[]" value="Masters Grad"> Masters Grad</label>
                                    <label><input type="checkbox" name="education[]" value="Doctorate Level"> Doctorate Level</label>
                                    <label><input type="checkbox" name="education[]" value="Doctorate Graduate"> Doctorate Graduate</label>
                                </div>
                            </div>
                        </div>

                        <div class="kkp-demo-col">
                            <div class="kkp-demo-block">
                                <div class="kkp-demo-label">Youth Classification</div>
                                <div class="kkp-checkbox-list">
                                    <label><input type="checkbox" name="youth_classification[]" value="In school Youth"> In school Youth</label>
                                    <label><input type="checkbox" name="youth_classification[]" value="Out of School Youth"> Out of School Youth</label>
                                    <label><input type="checkbox" name="youth_classification[]" value="Working Youth"> Working Youth</label>
                                    <label><input type="checkbox" name="youth_classification[]" value="Youth w/ Specific needs"> Youth w/ Specific needs:</label>
                                    <label style="padding-left:20px;"><input type="checkbox" name="youth_classification[]" value="Person w/ Disability"> Person w/ Disability</label>
                                    <label style="padding-left:20px;"><input type="checkbox" name="youth_classification[]" value="Children In Conflict w/ Law"> Children In Conflict w/ Law</label>
                                    <label style="padding-left:20px;"><input type="checkbox" name="youth_classification[]" value="Indigenous People"> Indigenous People</label>
                                </div>
                            </div>
                            <div class="kkp-demo-block">
                                <div class="kkp-demo-label">Work Status</div>
                                <div class="kkp-checkbox-list">
                                    <label><input type="checkbox" name="work_status[]" value="Employed"> Employed</label>
                                    <label><input type="checkbox" name="work_status[]" value="Unemployed"> Unemployed</label>
                                    <label><input type="checkbox" name="work_status[]" value="Self-Employed"> Self-Employed</label>
                                    <label><input type="checkbox" name="work_status[]" value="Currently looking for a Job"> Currently looking for a Job</label>
                                    <label><input type="checkbox" name="work_status[]" value="Not Interested Looking for a Job"> Not Interested Looking for a Job</label>
                                </div>
                            </div>
                            <div class="kkp-voter-grid">
                                <div class="kkp-voter-block">
                                    <div class="kkp-voter-label">Registered SK Voter?</div>
                                    <label><input type="radio" name="sk_voter" value="Yes"> Yes</label>
                                    <label><input type="radio" name="sk_voter" value="No"> No</label>
                                </div>
                                <div class="kkp-voter-block">
                                    <div class="kkp-voter-label">Did you vote last SK elections?</div>
                                    <label><input type="radio" name="sk_voted" value="Yes"> Yes</label>
                                    <label><input type="radio" name="sk_voted" value="No"> No</label>
                                </div>
                                <div class="kkp-voter-block">
                                    <div class="kkp-voter-label">If Yes, How many times?</div>
                                    <label><input type="radio" name="vote_frequency" value="1-2"> 1-2 Times</label>
                                    <label><input type="radio" name="vote_frequency" value="3-4"> 3-4 Times</label>
                                    <label><input type="radio" name="vote_frequency" value="5+"> 5 and above</label>
                                </div>
                                <div class="kkp-voter-block">
                                    <div class="kkp-voter-label">Registered National Voter?</div>
                                    <label><input type="radio" name="national_voter" value="Yes"> Yes</label>
                                    <label><input type="radio" name="national_voter" value="No"> No</label>
                                </div>
                                <div class="kkp-voter-block">
                                    <div class="kkp-voter-label">Have you already attended a KK Assembly?</div>
                                    <label><input type="radio" name="kk_assembly" value="Yes"> Yes</label>
                                    <label><input type="radio" name="kk_assembly" value="No"> No</label>
                                </div>
                                <div class="kkp-voter-block">
                                    <div class="kkp-voter-label">If No, Why?</div>
                                    <label><input type="checkbox" name="kk_reason[]" value="No meeting"> There was no KK Assembly Meeting</label>
                                    <label><input type="checkbox" name="kk_reason[]" value="Not interested"> Not interested to Attend</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Social --}}
                    <div class="kkp-social-row">
                        <div class="kkp-field-inline">
                            <label>FB Account:</label>
                            <input type="text" name="facebook" placeholder="facebook.com/username" maxlength="150">
                        </div>
                        <div class="kkp-field-inline">
                            <label>Willing to join the group chat?</label>
                            <label><input type="radio" name="group_chat" value="Yes"> Yes</label>
                            <label><input type="radio" name="group_chat" value="No"> No</label>
                        </div>
                    </div>

                    {{-- Signature --}}
                    <div class="kkp-signature-row">
                        <label>Name and Signature of Participant:</label>
                        <input type="text" name="signature" required maxlength="150" placeholder="Type your full name">
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="kkp-submit-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h6"/></svg>
                        Submit KK Profiling
                    </button>

                </form>
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

    <script>
    (function() {
        var btn = document.getElementById('navHamburger');
        var drawer = document.getElementById('navDrawer');
        if (btn && drawer) {
            btn.addEventListener('click', function() { drawer.classList.toggle('open'); });
            document.addEventListener('click', function(e) {
                if (!btn.contains(e.target) && !drawer.contains(e.target)) {
                    drawer.classList.remove('open');
                }
            });
        }
    })();
    </script>
</body>
</html>