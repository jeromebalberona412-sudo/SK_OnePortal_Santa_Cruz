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

    <!-- ══ Tab Bar ══════════════════════════════════════════ -->
    <div class="profile-tab-bar">
        <button type="button" class="profile-tab active" id="tabBtnInfo" aria-controls="tabInfo" aria-selected="true">
            <i class="fa-solid fa-user"></i> Profile Information
        </button>
        <button type="button" class="profile-tab" id="tabBtnSettings" aria-controls="tabSettings" aria-selected="false">
            <i class="fa-solid fa-gear"></i> Account Settings
        </button>
    </div>

    <!-- ══ Tab: Profile Information ══════════════════════════ -->
    <div class="profile-tab-content active" id="tabInfo">

        <!-- SK Chairman Card -->
        <section class="sk-chairman-section">
            <div class="official-card">
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

    </div><!-- /tabInfo -->

    <!-- ══ Tab: Account Settings ═════════════════════════════ -->
    <div class="profile-tab-content" id="tabSettings">

        <section class="sk-account-settings-section">
            <div class="account-settings-card">
                <div class="account-settings-card-header">
                    <h2 class="account-settings-card-title">
                        <i class="fa-solid fa-gear"></i> Account Settings
                    </h2>
                </div>

                <!-- Email Address Row -->
                <div class="account-settings-block" id="settingsBlockEmail">
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
                        <button type="button" class="account-settings-btn" id="btnToggleChangeEmail" aria-expanded="false" aria-controls="panelChangeEmail">
                            <i class="fa-solid fa-envelope"></i>
                            Change Email
                        </button>
                    </div>

                    <div class="account-settings-panel" id="panelChangeEmail" hidden>
                        <div class="cp-inline-card">
                            <div class="cp-inline-header">
                                <div class="cp-inline-icon"><i class="fa-solid fa-envelope"></i></div>
                                <div>
                                    <div class="cp-inline-title">Change Email</div>
                                    <div class="cp-inline-sub">Enter your current email, new email, and password to request a change.</div>
                                </div>
                            </div>

                            <div class="cp-inline-alert cp-inline-success" id="ceInlineSuccess" style="display:none;">
                                <i class="fa-solid fa-circle-check"></i>
                                <span id="ceInlineSuccessText">Verification link sent to your new email address.</span>
                            </div>
                            <div class="cp-inline-alert cp-inline-error" id="ceInlineError" style="display:none;">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span id="ceInlineErrorText">Please fix the errors below.</span>
                            </div>

                            <form class="cp-inline-form" id="ceInlineForm" novalidate>
                                <div class="cp-inline-field">
                                    <label class="cp-inline-label" for="ceInlineCurrentEmail">Current Email</label>
                                    <div class="cp-inline-input-wrap">
                                        <i class="fa-solid fa-envelope cp-inline-input-icon"></i>
                                        <input type="email" id="ceInlineCurrentEmail" class="cp-inline-input" placeholder="Enter your current email" autocomplete="email" maxlength="100">
                                    </div>
                                    <div class="cp-inline-field-error" id="ceInlineCurrentEmailError"></div>
                                </div>
                                <div class="cp-inline-field">
                                    <label class="cp-inline-label" for="ceInlineNewEmail">New Email Address</label>
                                    <div class="cp-inline-input-wrap">
                                        <i class="fa-solid fa-envelope cp-inline-input-icon"></i>
                                        <input type="email" id="ceInlineNewEmail" class="cp-inline-input" placeholder="Enter your new email" autocomplete="email" maxlength="100">
                                    </div>
                                    <div class="cp-inline-field-error" id="ceInlineNewEmailError"></div>
                                </div>
                                <div class="cp-inline-field">
                                    <label class="cp-inline-label" for="ceInlinePassword">Current Password</label>
                                    <div class="cp-inline-input-wrap">
                                        <i class="fa-solid fa-lock cp-inline-input-icon"></i>
                                        <input type="password" id="ceInlinePassword" class="cp-inline-input" placeholder="Enter your current password" autocomplete="current-password">
                                        <button type="button" class="cp-eye-btn" data-target="ceInlinePassword" aria-label="Toggle password visibility"><i class="fa-solid fa-eye"></i></button>
                                    </div>
                                    <div class="cp-inline-field-error" id="ceInlinePasswordError"></div>
                                </div>
                                <div class="cp-inline-actions">
                                    <button type="button" class="cp-inline-cancel" id="ceInlineCancel">Cancel</button>
                                    <button type="submit" class="cp-inline-submit" id="ceInlineSubmit"><span>Send Verification Link</span></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="account-settings-divider"></div>

                <!-- Password Row -->
                <div class="account-settings-block" id="settingsBlockPassword">
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
                        <button type="button" class="account-settings-btn" id="btnToggleChangePassword" aria-expanded="false" aria-controls="panelChangePassword">
                            <i class="fa-solid fa-key"></i>
                            Change Password
                        </button>
                    </div>

                    <div class="account-settings-panel" id="panelChangePassword" hidden>
                        <div class="cp-inline-card">
                            <div class="cp-inline-header">
                                <div class="cp-inline-icon"><i class="fa-solid fa-key"></i></div>
                                <div>
                                    <div class="cp-inline-title">Change Password</div>
                                    <div class="cp-inline-sub">Enter your current password and choose a new one.</div>
                                </div>
                            </div>

                            <div class="cp-inline-alert cp-inline-success" id="cpInlineSuccess" style="display:none;">
                                <i class="fa-solid fa-circle-check"></i>
                                <span id="cpInlineSuccessText">Password updated successfully.</span>
                            </div>
                            <div class="cp-inline-alert cp-inline-error" id="cpInlineError" style="display:none;">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span id="cpInlineErrorText">Please fix the errors below.</span>
                            </div>

                            <form class="cp-inline-form" id="cpInlineForm" novalidate>
                                <div class="cp-inline-field">
                                    <label class="cp-inline-label" for="cpCurrent">Current Password</label>
                                    <div class="cp-inline-input-wrap">
                                        <i class="fa-solid fa-lock cp-inline-input-icon"></i>
                                        <input type="password" id="cpCurrent" class="cp-inline-input" placeholder="Enter current password" autocomplete="current-password">
                                        <button type="button" class="cp-eye-btn" data-target="cpCurrent" aria-label="Toggle password visibility"><i class="fa-solid fa-eye"></i></button>
                                    </div>
                                    <div class="cp-inline-field-error" id="cpCurrentError"></div>
                                </div>
                                <div class="cp-inline-field">
                                    <label class="cp-inline-label" for="cpNew">New Password</label>
                                    <div class="cp-inline-input-wrap">
                                        <i class="fa-solid fa-lock cp-inline-input-icon"></i>
                                        <input type="password" id="cpNew" class="cp-inline-input" placeholder="Enter new password" autocomplete="new-password">
                                        <button type="button" class="cp-eye-btn" data-target="cpNew" aria-label="Toggle password visibility"><i class="fa-solid fa-eye"></i></button>
                                    </div>
                                    <div class="cp-inline-field-error" id="cpNewError"></div>
                                </div>
                                <div class="cp-inline-field">
                                    <label class="cp-inline-label" for="cpConfirm">Confirm New Password</label>
                                    <div class="cp-inline-input-wrap">
                                        <i class="fa-solid fa-lock cp-inline-input-icon"></i>
                                        <input type="password" id="cpConfirm" class="cp-inline-input" placeholder="Confirm new password" autocomplete="new-password">
                                        <button type="button" class="cp-eye-btn" data-target="cpConfirm" aria-label="Toggle password visibility"><i class="fa-solid fa-eye"></i></button>
                                    </div>
                                    <div class="cp-inline-field-error" id="cpConfirmError"></div>
                                </div>
                                <div class="cp-inline-actions">
                                    <button type="button" class="cp-inline-cancel" id="cpInlineCancel">Cancel</button>
                                    <button type="submit" class="cp-inline-submit" id="cpInlineSubmit"><span>Update Password</span></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div><!-- /account-settings-card -->
        </section>

    </div><!-- /tabSettings -->

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
