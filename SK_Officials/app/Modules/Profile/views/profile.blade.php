<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile — SK Officials Portal</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Profile/assets/css/profile.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>

@include('loading')
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

    <!-- ══ Profile Content Grid ══════════════════════════════════════════ -->
    <div class="profile-content-grid">

        <!-- Left Column - Profile Information -->
        <div class="profile-left-column">

            <!-- Action Buttons at Top -->
            <div class="profile-top-actions">
                <a href="{{ route('change-email') }}" class="btn-setting-action">
                    Change Email
                </a>
                <a href="{{ route('change-password') }}" class="btn-setting-action">
                    Change Password
                </a>
            </div>

            <!-- SK Chairman Card -->
            <section class="sk-chairman-section">
                <div class="official-card">

                    <!-- Profile Picture Header -->
                    <div class="profile-pic-header">
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
                                    <p>example@gmail.com</p>
                                </div>
                            </div>
                        </div>

                    </div><!-- /official-details -->
                </div><!-- /official-card -->
            </section>

            <!-- Account Settings Card -->
            <section class="sk-account-settings-section">
                <div class="info-card">
                    <div class="card-header">
                        <h2>
                            <i class="fa-solid fa-gear"></i>
                            Account Settings
                        </h2>
                    </div>
                    <div class="card-body">
                        <p style="color: #6b7280; text-align: center; padding: 20px;">
                            Use the buttons at the top of the page to change your email or password.
                        </p>
                    </div>
                </div>
            </section>

        </div><!-- /profile-left-column -->

    </div><!-- /profile-content-grid -->

</div><!-- /profile-container -->
</main>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Profile/assets/js/profile.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
