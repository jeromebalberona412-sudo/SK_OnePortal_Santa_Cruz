<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{{ $barangay }} - Kabataan Monitoring - SK Federation</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/modules/dashboard/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('/modules/kabataan-monitoring/css/kabataan-monitoring.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>
    <script>
        (function() {
            window.history.pushState(null, '', window.location.href);
            window.onpopstate = function() { window.history.pushState(null, '', window.location.href); };
        })();
    </script>

    @php
        $avatar = 'https://ui-avatars.com/api/?name=' . urlencode((string) ($user->name ?? 'User')) . '&background=213F99&color=fff&size=120';
        $formattedRole = $user->role ? ucwords(str_replace('_', ' ', (string) $user->role)) : 'SK Official';
    @endphp

    <nav class="navbar">
        <div class="navbar-left">
            <button class="menu-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                <i class="fas fa-bars toggle-icon-expand"></i>
                <i class="fas fa-ellipsis-v toggle-icon-collapse"></i>
            </button>
            <div class="navbar-brand">
                <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Fed Logo" class="brand-logo">
                <span class="brand-name">SK Federations</span>
            </div>
        </div>
        <div class="navbar-search">
            <i class="fas fa-search search-icon"></i>
            <input id="km-search" type="text" placeholder="Search name, barangay, or focus area..." aria-label="Search kabataan">
        </div>
        <div class="navbar-right">
            <button class="notif-btn" onclick="toggleNotifPopover(event)" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                <span class="notif-badge"></span>
            </button>
            <div class="profile-dropdown-wrapper">
                <button class="profile-btn" onclick="toggleProfileDropdown(event)" aria-label="Profile menu">
                    <img src="{{ $avatar }}" alt="Profile" class="nav-avatar">
                    <span class="nav-name">{{ $user->name ?? 'User' }}</span>
                    <i class="fas fa-chevron-down nav-chevron"></i>
                </button>
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-dropdown-header">
                        <div class="dd-name">{{ $user->name ?? 'User' }}</div>
                        <div class="dd-email">{{ $user->email ?? '' }}</div>
                    </div>
                    <a href="{{ route('profile') }}" class="dd-item" id="nav-profile-link"><i class="fas fa-user"></i> Profile</a>
                    <a href="{{ route('password.request') }}" class="dd-item" id="nav-change-pw-link"><i class="fas fa-lock"></i> Change Password</a>
                    <div class="dd-divider"></div>
                    <button class="dd-item danger" onclick="showLogoutModal()"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="notif-popover" id="notifPopover">
        <div class="notif-popover-header">
            <h4>Notifications</h4>
            <button class="notif-mark-all">Mark all as read</button>
        </div>
        <div class="notif-list">
            <div class="notif-empty">
                <i class="fas fa-bell-slash" style="font-size:28px;display:block;margin-bottom:8px;opacity:0.3;"></i>
                No notifications yet
            </div>
        </div>
    </div>

    <div class="sidebar-overlay"></div>

    <aside class="sidebar">
        <a href="{{ route('profile') }}" class="sidebar-profile sidebar-profile-link" id="sidebar-profile-link">
            <img src="{{ $avatar }}" alt="Profile" class="sidebar-avatar">
            <div class="sidebar-user-info">
                <div class="s-name">{{ $user->name ?? 'User' }}</div>
                <div class="s-role">{{ $formattedRole }}</div>
            </div>
        </a>
        <nav class="sidebar-nav">
            <div class="menu-section-label">Main</div>
            <a href="{{ route('dashboard') }}" class="menu-item" data-tooltip="Dashboard" id="nav-dashboard-link"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <div class="menu-section-label">Modules</div>
            <a href="{{ route('community-feed') }}" class="menu-item" data-tooltip="SK Community Feed"><i class="fas fa-rss"></i><span>SK Community Feed</span></a>
            <a href="{{ route('barangay-monitoring') }}" class="menu-item" data-tooltip="Barangay Monitoring"><i class="fas fa-map-marker-alt"></i><span>Barangay Monitoring</span></a>
            <a href="{{ route('kabataan-monitoring') }}" class="menu-item active" data-tooltip="Kabataan Monitoring"><i class="fas fa-users"></i><span>Kabataan Monitoring</span></a>
            <div class="menu-divider"></div>
            <button type="button" class="menu-item logout-item" data-tooltip="Logout" onclick="showLogoutModal()"><i class="fas fa-sign-out-alt"></i><span>Logout</span></button>
        </nav>
    </aside>

    <main class="main-content km-main" data-detail-base="{{ url('/kabataan-monitoring') }}">
        <div class="km-container">

            {{-- Back Button & Header --}}
            <div class="km-detail-header">
                <a href="{{ route('kabataan-monitoring') }}" class="km-back-link">
                    <i class="fas fa-arrow-left"></i> Back to Kabataan Monitoring
                </a>
                <div class="km-brgy-title-section">
                    <h1><i class="fas fa-map-marker-alt"></i> {{ $barangay }}</h1>
                    <p>KKK Profiling Masterlist</p>
                </div>
            </div>

            {{-- Summary Cards for Barangay --}}
            <section class="km-brgy-summary-section">
                <div class="km-summary-grid" aria-label="Barangay summary statistics">
                    <article class="km-summary-card km-summary-total">
                        <div class="km-summary-icon"><i class="fas fa-users"></i></div>
                        <div class="km-summary-body">
                            <div class="km-summary-label">Total Kabataan</div>
                            <div class="km-summary-value" id="km-brgy-total">0</div>
                            <div class="km-summary-note">Registered youth profiles</div>
                        </div>
                    </article>
                    <article class="km-summary-card km-summary-active">
                        <div class="km-summary-icon"><i class="fas fa-user-check"></i></div>
                        <div class="km-summary-body">
                            <div class="km-summary-label">Participation Rate</div>
                            <div class="km-summary-value" id="km-brgy-rate">0%</div>
                            <div class="km-summary-note">Active vs total registered</div>
                        </div>
                    </article>
                    <article class="km-summary-card km-summary-active">
                        <div class="km-summary-icon"><i class="fas fa-user-check"></i></div>
                        <div class="km-summary-body">
                            <div class="km-summary-label">Active</div>
                            <div class="km-summary-value" id="km-brgy-active">0</div>
                            <div class="km-summary-note">High & moderate engagement</div>
                        </div>
                    </article>
                    <article class="km-summary-card km-summary-inactive">
                        <div class="km-summary-icon"><i class="fas fa-user-times"></i></div>
                        <div class="km-summary-body">
                            <div class="km-summary-label">Inactive</div>
                            <div class="km-summary-value" id="km-brgy-inactive">0</div>
                            <div class="km-summary-note">Needs follow-up & intervention</div>
                        </div>
                    </article>
                </div>
            </section>

            {{-- Masterlist Table --}}
            <section class="km-masterlist-top">
                <div class="km-masterlist-topbar">
                    <div>
                        <h2><i class="fas fa-list-alt" style="color:#213F99;margin-right:8px;"></i>KKK Profiling Masterlist</h2>
                        <p>Youth profiling records for {{ $barangay }}</p>
                    </div>
                    <div class="km-masterlist-actions">
                        <button class="km-export-btn" onclick="exportBarangayCSV()">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                    </div>
                </div>
                <div class="km-filter-row">
                    <div class="km-chip-row" id="km-status-filter">
                        <button type="button" class="km-chip active" data-status="all">All</button>
                        <button type="button" class="km-chip" data-status="active">Active</button>
                        <button type="button" class="km-chip" data-status="moderate">Moderate</button>
                        <button type="button" class="km-chip" data-status="inactive">Inactive</button>
                    </div>
                    <div class="km-result-count" id="km-result-count"></div>
                </div>
            </section>

            {{-- Table --}}
            <div class="km-table-wrap">
                <table class="km-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Name</th><th>Age</th><th>Sex</th>
                            <th>Civil Status</th><th>Education</th><th>Work Status</th>
                            <th>Classification</th><th>Status</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="km-table-tbody"></tbody>
                </table>
            </div>
            <p id="km-empty" class="km-empty" hidden>No profiles match your current filters.</p>

            {{-- Pagination --}}
            <div class="km-pagination-wrapper">
                <div class="km-pagination-info">
                    <span id="km-pagination-text">Showing 0 of 0 records</span>
                </div>
                <div class="km-pagination-controls">
                    <button class="km-pagination-btn" id="km-prev-btn" onclick="previousPage()" disabled>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <div class="km-pagination-numbers" id="km-pagination-numbers"></div>
                    <button class="km-pagination-btn" id="km-next-btn" onclick="nextPage()" disabled>
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

        </div>
    </main>

    @include('dashboard::logout-modal')

    {{-- KK Profiling Form Modal --}}
    <div class="km-kkp-modal" id="kmKKPModal">
        <div class="km-kkp-modal-overlay" onclick="closeKKPModal()"></div>
        <div class="km-kkp-modal-content">
            <div class="km-kkp-modal-header">
                <h2><i class="fas fa-file-alt"></i> KK Survey Questionnaire</h2>
                <button class="km-kkp-modal-close" onclick="closeKKPModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="km-kkp-modal-body">
                <form id="kmKKPForm" class="km-kkp-form">
                    {{-- Form Header --}}
                    <div class="km-kkp-form-header">
                        <div class="km-kkp-header-info">
                            <div class="km-kkp-header-field">
                                <label>Respondent #:</label>
                                <input type="text" id="kmKKPRespondent" readonly>
                            </div>
                            <div class="km-kkp-header-field">
                                <label>Date:</label>
                                <input type="text" id="kmKKPDate" readonly>
                            </div>
                        </div>
                    </div>

                    {{-- Notice Box --}}
                    <div class="km-kkp-notice">
                        <p class="km-kkp-notice-title">TO THE RESPONDENT:</p>
                        <p>We are currently conducting a study that focuses on assessing the demographic information of the Katipunan ng Kabataan. We would like to ask your participation by taking time to answer this questionnaire. Please read the questions carefully and answer them accurately.</p>
                        <p class="km-kkp-notice-confidential">REST ASSURED THAT ALL INFORMATION GATHERED FROM THIS STUDY WILL BE TREATED WITH UTMOST CONFIDENTIALITY.</p>
                    </div>

                    {{-- I. PROFILE --}}
                    <div class="km-kkp-section-title">I. PROFILE</div>

                    {{-- Name --}}
                    <div class="km-kkp-field-group">
                        <label class="km-kkp-field-label">Name of Respondent:</label>
                        <div class="km-kkp-name-row">
                            <input type="text" id="kmKKPLastName" placeholder="Last Name" class="km-kkp-input">
                            <input type="text" id="kmKKPFirstName" placeholder="First Name" class="km-kkp-input">
                            <input type="text" id="kmKKPMiddleName" placeholder="Middle Name" class="km-kkp-input">
                            <select id="kmKKPSuffix" class="km-kkp-input km-kkp-input-sm">
                                <option value="">Suffix</option>
                                <option>Jr.</option><option>Sr.</option>
                                <option>II</option><option>III</option><option>IV</option><option>V</option>
                            </select>
                        </div>
                    </div>

                    {{-- Location --}}
                    <div class="km-kkp-field-group">
                        <label class="km-kkp-field-label">Location:</label>
                        <div class="km-kkp-location-row">
                            <input type="text" value="Region IV-A (CALABARZON)" readonly class="km-kkp-input km-kkp-input-readonly">
                            <input type="text" value="Laguna" readonly class="km-kkp-input km-kkp-input-readonly">
                            <input type="text" value="Santa Cruz" readonly class="km-kkp-input km-kkp-input-readonly">
                            <input type="text" id="kmKKPBarangay" readonly class="km-kkp-input km-kkp-input-readonly">
                            <input type="text" id="kmKKPPurok" placeholder="Purok/Zone" class="km-kkp-input">
                        </div>
                    </div>

                    {{-- Personal Info --}}
                    <div class="km-kkp-field-group">
                        <div class="km-kkp-personal-row">
                            <div class="km-kkp-personal-col">
                                <label>Sex Assigned by Birth:</label>
                                <div class="km-kkp-checkbox-group">
                                    <label><input type="radio" name="kmKKPSex" value="Male"> Male</label>
                                    <label><input type="radio" name="kmKKPSex" value="Female"> Female</label>
                                </div>
                            </div>
                            <div class="km-kkp-personal-col">
                                <label>Age: *</label>
                                <input type="number" id="kmKKPAge" min="15" max="30" class="km-kkp-input km-kkp-input-sm">
                            </div>
                            <div class="km-kkp-personal-col">
                                <label>Birthday:</label>
                                <input type="date" id="kmKKPBirthday" class="km-kkp-input">
                            </div>
                        </div>
                        <div class="km-kkp-personal-row">
                            <div class="km-kkp-personal-col">
                                <label>E-mail address:</label>
                                <input type="email" id="kmKKPEmail" class="km-kkp-input">
                            </div>
                            <div class="km-kkp-personal-col">
                                <label>Contact #:</label>
                                <input type="text" id="kmKKPContact" class="km-kkp-input">
                            </div>
                        </div>
                    </div>

                    {{-- II. DEMOGRAPHIC CHARACTERISTICS --}}
                    <div class="km-kkp-section-title">II. DEMOGRAPHIC CHARACTERISTICS</div>
                    <p class="km-kkp-instruction">Please put a Check mark (✓) next to the word or Phrase that matches your response.</p>

                    <div class="km-kkp-demo-grid">
                        {{-- Left Column --}}
                        <div class="km-kkp-demo-col">
                            <div class="km-kkp-demo-block">
                                <div class="km-kkp-demo-block-title">Civil Status</div>
                                <div class="km-kkp-checkbox-group">
                                    <label><input type="checkbox" name="kmKKPCivilStatus" value="Single"> Single</label>
                                    <label><input type="checkbox" name="kmKKPCivilStatus" value="Married"> Married</label>
                                    <label><input type="checkbox" name="kmKKPCivilStatus" value="Widowed"> Widowed</label>
                                    <label><input type="checkbox" name="kmKKPCivilStatus" value="Divorced"> Divorced</label>
                                    <label><input type="checkbox" name="kmKKPCivilStatus" value="Separated"> Separated</label>
                                    <label><input type="checkbox" name="kmKKPCivilStatus" value="Annulled"> Annulled</label>
                                </div>
                            </div>

                            <div class="km-kkp-demo-block">
                                <div class="km-kkp-demo-block-title">Youth Age Group</div>
                                <div class="km-kkp-checkbox-group">
                                    <label><input type="checkbox" name="kmKKPYouthAge" value="Child Youth (15-17 yrs old)"> Child Youth (15-17 yrs old)</label>
                                    <label><input type="checkbox" name="kmKKPYouthAge" value="Core Youth (18-24 yrs old)"> Core Youth (18-24 yrs old)</label>
                                    <label><input type="checkbox" name="kmKKPYouthAge" value="Young Adult (15-30 yrs old)"> Young Adult (15-30 yrs old)</label>
                                </div>
                            </div>

                            <div class="km-kkp-demo-block">
                                <div class="km-kkp-demo-block-title">Educational Background</div>
                                <div class="km-kkp-checkbox-group">
                                    <label><input type="checkbox" name="kmKKPEducation" value="High School Level"> High School Level</label>
                                    <label><input type="checkbox" name="kmKKPEducation" value="High School Grad"> High School Grad</label>
                                    <label><input type="checkbox" name="kmKKPEducation" value="College Level"> College Level</label>
                                    <label><input type="checkbox" name="kmKKPEducation" value="College Grad"> College Grad</label>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column --}}
                        <div class="km-kkp-demo-col">
                            <div class="km-kkp-demo-block">
                                <div class="km-kkp-demo-block-title">Youth Classification</div>
                                <div class="km-kkp-checkbox-group">
                                    <label><input type="checkbox" name="kmKKPYouthClass" value="In School Youth"> In School Youth</label>
                                    <label><input type="checkbox" name="kmKKPYouthClass" value="Out of School Youth"> Out of School Youth</label>
                                    <label><input type="checkbox" name="kmKKPYouthClass" value="Working Youth"> Working Youth</label>
                                </div>
                            </div>

                            <div class="km-kkp-demo-block">
                                <div class="km-kkp-demo-block-title">Work Status</div>
                                <div class="km-kkp-checkbox-group">
                                    <label><input type="checkbox" name="kmKKPWorkStatus" value="Employed"> Employed</label>
                                    <label><input type="checkbox" name="kmKKPWorkStatus" value="Unemployed"> Unemployed</label>
                                    <label><input type="checkbox" name="kmKKPWorkStatus" value="Self-Employed"> Self-Employed</label>
                                </div>
                            </div>

                            <div class="km-kkp-demo-block">
                                <div class="km-kkp-demo-block-title">SK Voter Status</div>
                                <div class="km-kkp-checkbox-group">
                                    <label><input type="checkbox" name="kmKKPSKVoter" value="Yes"> Registered SK Voter</label>
                                    <label><input type="checkbox" name="kmKKPSKVoted" value="Yes"> Voted in Last SK Election</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="km-kkp-footer">
                        <div class="km-kkp-footer-field">
                            <label>FB Account:</label>
                            <input type="text" id="kmKKPFacebook" class="km-kkp-input">
                        </div>
                        <div class="km-kkp-footer-field">
                            <label>Willing to join the group chat?</label>
                            <div class="km-kkp-checkbox-group">
                                <label><input type="radio" name="kmKKPGroupChat" value="Yes"> Yes</label>
                                <label><input type="radio" name="kmKKPGroupChat" value="No"> No</label>
                            </div>
                        </div>
                    </div>

                    <div class="km-kkp-thankyou">Thank you for your participation!</div>
                </form>
            </div>
            <div class="km-kkp-modal-footer">
                <button type="button" class="km-kkp-btn-close" onclick="closeKKPModal()">Close</button>
                <button type="button" class="km-kkp-btn-print" onclick="printKKPForm()"><i class="fas fa-print"></i> Print</button>
            </div>
        </div>
    </div>

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script src="{{ url('/modules/dashboard/js/dashboard.js') }}"></script>
    <script src="{{ url('/modules/kabataan-monitoring/js/kabataan-monitoring.js') }}"></script>
    <script>
        window.logoutRoute = "{{ route('logout') }}";
        window.loginRoute  = "{{ route('login') }}";
        window.kmPageMode  = 'barangay-detail';
        window.kmBarangay  = "{{ $barangay }}";

        document.getElementById('sidebar-profile-link')?.addEventListener('click', function(e) {
            e.preventDefault(); LoadingScreen.show('Loading Profile', 'Please wait...');
            setTimeout(() => { window.location.href = this.href; }, 300);
        });
        document.getElementById('nav-profile-link')?.addEventListener('click', function(e) {
            e.preventDefault(); LoadingScreen.show('Loading Profile', 'Please wait...');
            setTimeout(() => { window.location.href = this.href; }, 300);
        });
        document.getElementById('nav-change-pw-link')?.addEventListener('click', function(e) {
            e.preventDefault(); LoadingScreen.show('Loading', 'Please wait...');
            setTimeout(() => { window.location.href = this.href; }, 300);
        });
    </script>
</body>
</html>
