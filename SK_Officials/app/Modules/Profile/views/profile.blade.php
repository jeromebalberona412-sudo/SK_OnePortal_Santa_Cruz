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
        'app/Modules/Layout/css/header.css',
        'app/Modules/Layout/css/sidebar.css',
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

    <!-- -- Page Header -------------------------------------- -->
    <div class="profile-page-header">
        <div>
            <h1 class="profile-page-title">Profile</h1>
            <p class="profile-page-sub">Manage your profile information and account settings.</p>
        </div>
    </div>

    <!-- -- Tab Bar ------------------------------------------ -->
    <div class="profile-tab-bar">
        <button type="button" class="profile-tab active" id="tabBtnInfo" aria-controls="tabInfo" aria-selected="true">
            <i class="fa-solid fa-user"></i> Profile Information
        </button>
        <button type="button" class="profile-tab" id="tabBtnSettings" aria-controls="tabSettings" aria-selected="false">
            <i class="fa-solid fa-gear"></i> Account Settings
        </button>
    </div>

    <!-- -- Tab: Profile Information -------------------------- -->
    <div class="profile-tab-content active" id="tabInfo">

        <!-- SK Chairman Card -->
        <section class="sk-chairman-section">
            <div class="official-card">
                <div class="official-details">

                    <!-- -- Personal Information --------------------- -->
                    <div class="profile-field-group">
                        <div class="profile-field-group-label profile-field-group-label--bold">
                            <i class="fa-solid fa-user"></i> Personal Information
                        </div>

                        <!-- Name row -->
                        <div class="profile-field-row" style="margin-bottom: 14px;">
                            <div class="profile-field">
                                <label>First Name</label>
                                <p>{{ $profile['first_name'] }}</p>
                            </div>
                            <div class="profile-field">
                                <label>Middle Name</label>
                                <p>{{ $profile['middle_name'] }}</p>
                            </div>
                            <div class="profile-field">
                                <label>Last Name</label>
                                <p>{{ $profile['last_name'] }}</p>
                            </div>
                            <div class="profile-field">
                                <label>Suffix</label>
                                <p>{{ $profile['suffix'] }}</p>
                            </div>
                        </div>

                        <!-- Other personal fields -->
                        <div class="profile-field-row">
                            <div class="profile-field">
                                <label><i class="fa-solid fa-venus-mars"></i> Sex</label>
                                <p>{{ $profile['sex'] }}</p>
                            </div>
                            <div class="profile-field">
                                <label><i class="fa-solid fa-calendar-day"></i> Birthdate</label>
                                <p>{{ $profile['birthdate'] }}</p>
                            </div>
                            <div class="profile-field">
                                <label><i class="fa-solid fa-mobile-screen"></i> Contact Number</label>
                                <p>{{ $profile['contact_number'] }}</p>
                            </div>
                            <div class="profile-field">
                                <label><i class="fa-solid fa-briefcase"></i> Position</label>
                                <p>{{ $profile['position'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- -- Address ---------------------------------- -->
                    <div class="profile-field-group">
                        <div class="profile-field-group-label profile-field-group-label--bold">
                            <i class="fa-solid fa-location-dot"></i> Address
                        </div>
                        <div class="profile-field-row">
                            <div class="profile-field">
                                <label>Region</label>
                                <p>{{ $profile['region'] }}</p>
                            </div>
                            <div class="profile-field">
                                <label>Province</label>
                                <p>{{ $profile['province'] }}</p>
                            </div>
                            <div class="profile-field">
                                <label>Municipality</label>
                                <p>{{ $profile['municipality'] }}</p>
                            </div>
                            <div class="profile-field">
                                <label>Barangay</label>
                                <p>{{ $profile['barangay'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- -- Term & Committee Information ------------ -->
                    <div class="profile-field-group">
                        <div class="profile-field-group-label profile-field-group-label--bold">
                            <i class="fa-solid fa-calendar-check"></i> Term &amp; Committee Information
                        </div>
                        <div class="profile-field-row">
                            <div class="profile-field">
                                <label><i class="fa-solid fa-calendar-check"></i> Term Start</label>
                                <p>{{ $profile['term_start'] }}</p>
                            </div>
                            <div class="profile-field">
                                <label><i class="fa-solid fa-calendar-xmark"></i> Term End</label>
                                <p>{{ $profile['term_end'] }}</p>
                            </div>
                            <div class="profile-field">
                                <label><i class="fa-solid fa-clipboard-list"></i> Committee</label>
                                <p>{{ $profile['committee'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- -- Account ---------------------------------- -->
                    <div class="profile-field-group">
                        <div class="profile-field-group-label profile-field-group-label--bold">
                            <i class="fa-solid fa-circle-user"></i> Account
                        </div>
                        <div class="profile-field-row">
                            <div class="profile-field">
                                <label><i class="fa-solid fa-envelope"></i> Email</label>
                                <p>{{ $profile['email'] }}</p>
                            </div>
                        </div>
                    </div>

                </div><!-- /official-details -->
            </div><!-- /official-card -->
        </section>

    </div><!-- /tabInfo -->

    <!-- -- Tab: Account Settings ----------------------------- -->
    <div class="profile-tab-content" id="tabSettings">

        <section class="sk-account-settings-section">
            <div class="account-settings-card">
                <div class="account-settings-card-header">
                    <h2 class="account-settings-card-title">
                        <i class="fa-solid fa-gear"></i> Account Settings
                    </h2>
                </div>

                <!-- Email Address Row -->
                <div class="account-settings-row">
                    <div class="account-settings-info">
                        <div class="account-settings-icon">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div>
                            <div class="account-settings-label">Email Address</div>
                            <div class="account-settings-desc">{{ $profile['email'] }}</div>
                        </div>
                    </div>
                    <a href="{{ route('change-email') }}" class="account-settings-btn">
                        <i class="fa-solid fa-envelope"></i>
                        Change Email
                    </a>
                </div>

                <div class="account-settings-divider"></div>

                <!-- Password Row -->
                <div class="account-settings-row">
                    <div class="account-settings-info">
                        <div class="account-settings-icon">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <div>
                            <div class="account-settings-label">Password</div>
                            <div class="account-settings-desc">Update your account password securely.</div>
                        </div>
                    </div>
                    <a href="{{ route('change-password') }}" class="account-settings-btn">
                        <i class="fa-solid fa-key"></i>
                        Change Password
                    </a>
                </div>

            </div><!-- /account-settings-card -->
        </section>

    </div><!-- /tabSettings -->

</div><!-- /profile-container -->
</main>

@vite([
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
    'app/Modules/Profile/assets/js/profile.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
