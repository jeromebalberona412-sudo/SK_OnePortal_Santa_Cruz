<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile — SK Officials Portal</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/Profile/assets/css/profile.css'
    ])
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="profile-container">

    <!-- ══ Page Header ══════════════════════════════════════ -->
    <div class="profile-page-header">
        <div>
            <h1 class="profile-page-title">Profile</h1>
            <p class="profile-page-sub">Manage your profile information and account settings.</p>
        </div>
    </div>

    <!-- ══ Tab Bar ══════════════════════════════════════════ -->
    <div class="profile-tab-bar">
        <button class="profile-tab active" data-tab="tab-info">
            <i class="fa-solid fa-user"></i>
            Profile Information
        </button>
        <button class="profile-tab" data-tab="tab-settings">
            <i class="fa-solid fa-gear"></i>
            Account Settings
        </button>
    </div>

    <!-- ══ TAB 1: Profile Information ═══════════════════════ -->
    <div class="profile-tab-content active" id="tab-info">

        <!-- SK Chairman -->
        <section class="sk-chairman-section">
            <div class="official-card">

                <!-- Profile Picture Header -->
                <div class="profile-pic-header">
                    <div class="profile-pic-avatar">
                        <i class="fa-solid fa-user-tie"></i>
                        <span class="profile-pic-badge active">Active</span>
                    </div>
                    <div class="profile-pic-info">
                        <h3 class="profile-pic-name">Jerome Sanico Balberona</h3>
                        <p class="profile-pic-role">SK Chairman</p>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="official-details">

                    <!-- ── Personal Information ───────────────────── -->
                    <div class="profile-field-group">
                        <div class="profile-field-group-label profile-field-group-label--bold">
                            <i class="fa-solid fa-user"></i> Personal Information
                        </div>

                        <!-- Name row -->
                        <div class="profile-field-row" style="margin-bottom: 14px;">
                            <div class="profile-field">
                                <label>First Name</label>
                                <p>Jerome</p>
                            </div>
                            <div class="profile-field">
                                <label>Middle Name</label>
                                <p>Sanico</p>
                            </div>
                            <div class="profile-field">
                                <label>Last Name</label>
                                <p>Balberona</p>
                            </div>
                            <div class="profile-field">
                                <label>Suffix</label>
                                <p>None</p>
                            </div>
                        </div>

                        <!-- Other personal fields -->
                        <div class="profile-field-row">
                            <div class="profile-field">
                                <label><i class="fa-solid fa-venus-mars"></i> Sex</label>
                                <p>Female</p>
                            </div>
                            <div class="profile-field">
                                <label><i class="fa-solid fa-calendar-day"></i> Birthdate</label>
                                <p>April 2, 2005</p>
                            </div>
                            <div class="profile-field">
                                <label><i class="fa-solid fa-mobile-screen"></i> Contact Number</label>
                                <p>09169064515</p>
                            </div>
                            <div class="profile-field">
                                <label><i class="fa-solid fa-briefcase"></i> Position</label>
                                <p>SK Chairman</p>
                            </div>
                        </div>
                    </div>

                    <!-- ── Address ────────────────────────────────── -->
                    <div class="profile-field-group">
                        <div class="profile-field-group-label profile-field-group-label--bold">
                            <i class="fa-solid fa-location-dot"></i> Address
                        </div>
                        <div class="profile-field-row">
                            <div class="profile-field">
                                <label>Region</label>
                                <p>CALABARZON</p>
                            </div>
                            <div class="profile-field">
                                <label>Province</label>
                                <p>Laguna</p>
                            </div>
                            <div class="profile-field">
                                <label>Municipality</label>
                                <p>Santa Cruz</p>
                            </div>
                            <div class="profile-field">
                                <label>Barangay</label>
                                <p>Calios</p>
                            </div>
                        </div>
                    </div>

                    <!-- ── Term & Committee Information ──────────── -->
                    <div class="profile-field-group">
                        <div class="profile-field-group-label profile-field-group-label--bold">
                            <i class="fa-solid fa-calendar-check"></i> Term &amp; Committee Information
                        </div>
                        <div class="profile-field-row">
                            <div class="profile-field">
                                <label><i class="fa-solid fa-calendar-check"></i> Term Start</label>
                                <p>February 28, 2026</p>
                            </div>
                            <div class="profile-field">
                                <label><i class="fa-solid fa-calendar-xmark"></i> Term End</label>
                                <p>January 7, 2030</p>
                            </div>
                            <div class="profile-field">
                                <label><i class="fa-solid fa-clipboard-list"></i> Committee</label>
                                <p>Sports</p>
                            </div>
                        </div>
                    </div>

                    <!-- ── Account ────────────────────────────────── -->
                    <div class="profile-field-group">
                        <div class="profile-field-group-label profile-field-group-label--bold">
                            <i class="fa-solid fa-circle-user"></i> Account
                        </div>
                        <div class="profile-field-row">
                            <div class="profile-field">
                                <label><i class="fa-solid fa-envelope"></i> Email</label>
                                <p>kianpaula4@gmail.com</p>
                            </div>
                        </div>
                    </div>

                </div><!-- /official-details -->
            </div><!-- /official-card -->
        </section>

    </div><!-- /tab-info -->

    <!-- ══ TAB 2: Account Settings ══════════════════════════ -->
    <div class="profile-tab-content" id="tab-settings">

        <section class="sk-account-settings-section">
            <div class="section-header">
                <h2 class="section-title">
                    <span class="icon"><i class="fa-solid fa-gear"></i></span>
                    Account Settings
                </h2>
            </div>

            <div class="account-settings-card">
                <div class="account-settings-row">
                    <div class="account-settings-info">
                        <div class="account-settings-icon">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div>
                            <div class="account-settings-label">Email Address</div>
                            <div class="account-settings-desc">Change your account email address via verification link.</div>
                        </div>
                    </div>
                    <a href="{{ route('change-email') }}" class="account-settings-btn">
                        <i class="fa-solid fa-envelope-open-text"></i>
                        Change Email
                    </a>
                </div>

                <div class="account-settings-divider"></div>

                <div class="account-settings-row">
                    <div class="account-settings-info">
                        <div class="account-settings-icon">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <div>
                            <div class="account-settings-label">Password</div>
                            <div class="account-settings-desc">Change your account password via email reset link.</div>
                        </div>
                    </div>
                    <a href="{{ route('change-password') }}" class="account-settings-btn">
                        <i class="fa-solid fa-key"></i>
                        Change Password
                    </a>
                </div>
            </div>

        </section>

    </div><!-- /tab-settings -->

</div><!-- /profile-container -->
</main>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/Profile/assets/js/profile.js'
])

</body>
</html>
