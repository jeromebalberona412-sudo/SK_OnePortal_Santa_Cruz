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
            <div class="section-header">
                <h2 class="section-title">
                    <span class="icon"><i class="fa-solid fa-user-tie"></i></span>
                    SK Chairman
                </h2>
            </div>
            <div class="official-card">
                <div class="official-header">
                    <div class="official-photo">
                        <div class="profile-icon"><i class="fa-solid fa-user-tie"></i></div>
                        <span class="status-badge active">Active</span>
                    </div>
                    <div class="official-info">
                        <h3>Paula A Talais</h3>
                        <p class="official-position">SK Chairman</p>
                        <div class="official-meta">
                            <span class="meta-item"><i class="fa-solid fa-venus"></i> Female</span>
                            <span class="meta-item"><i class="fa-solid fa-cake-candles"></i> 20 years old</span>
                        </div>
                    </div>
                </div>
                <div class="official-details">
                    <div class="detail-row">
                        <div class="detail-item">
                            <label><i class="fa-solid fa-user"></i> Full Name (FN, MN LN, Suffix)</label>
                            <p>Paula A Talais</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-envelope"></i> Email Address</label>
                            <p>kianpaula4@gmail.com</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-location-dot"></i> Barangay</label>
                            <p>Palasan</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-city"></i> Municipality</label>
                            <p>Santa Cruz</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-briefcase"></i> Position (SK Role)</label>
                            <p>Chairman</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-calendar-day"></i> Date of Birth</label>
                            <p>Apr 2, 2005</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-person"></i> Age</label>
                            <p>20</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-mobile-screen"></i> Contact Number</label>
                            <p>09169064515</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-envelope-circle-check"></i> Email Verification</label>
                            <p>03/03/2026 04:04 PM</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-calendar-check"></i> Term Start</label>
                            <p>Feb 28, 2026</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-calendar-xmark"></i> Term End</label>
                            <p>Jan 7, 2030</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-circle-check"></i> Status</label>
                            <p>Account Status: ACTIVE</p>
                            <p>Term Status: ACTIVE</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SK Secretary -->
        <section class="sk-secretary-section">
            <div class="section-header">
                <h2 class="section-title">
                    <span class="icon"><i class="fa-solid fa-pen-to-square"></i></span>
                    SK Secretary
                </h2>
            </div>
            <div class="official-card">
                <div class="official-header">
                    <div class="official-photo">
                        <div class="profile-icon"><i class="fa-solid fa-user-pen"></i></div>
                        <span class="status-badge active">Active</span>
                    </div>
                    <div class="official-info">
                        <h3>Maria Clara Santos</h3>
                        <p class="official-position">SK Secretary</p>
                        <div class="official-meta">
                            <span class="meta-item"><i class="fa-solid fa-venus"></i> Female</span>
                            <span class="meta-item"><i class="fa-solid fa-cake-candles"></i> 23 years old</span>
                        </div>
                    </div>
                </div>
                <div class="official-details">
                    <div class="detail-row">
                        <div class="detail-item"><label><i class="fa-solid fa-user"></i> Full Name</label><p>Maria Clara Santos</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-envelope"></i> Email Address</label><p>maria.santos@email.com</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-location-dot"></i> Barangay</label><p>Palasan</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-city"></i> Municipality</label><p>Santa Cruz</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-briefcase"></i> Position (SK Role)</label><p>Secretary</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-calendar-day"></i> Date of Birth</label><p>March 15, 2002</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-person"></i> Age</label><p>23</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-mobile-screen"></i> Contact Number</label><p>0919-012-3456</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-calendar-check"></i> Term Start</label><p>Feb 28, 2026</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-calendar-xmark"></i> Term End</label><p>Jan 7, 2030</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-circle-check"></i> Status</label><p>Account Status: ACTIVE</p><p>Term Status: ACTIVE</p></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SK Treasurer -->
        <section class="sk-treasurer-section">
            <div class="section-header">
                <h2 class="section-title">
                    <span class="icon"><i class="fa-solid fa-coins"></i></span>
                    SK Treasurer
                </h2>
            </div>
            <div class="official-card">
                <div class="official-header">
                    <div class="official-photo">
                        <div class="profile-icon"><i class="fa-solid fa-user-shield"></i></div>
                        <span class="status-badge active">Active</span>
                    </div>
                    <div class="official-info">
                        <h3>Robert James Tan</h3>
                        <p class="official-position">SK Treasurer</p>
                        <div class="official-meta">
                            <span class="meta-item"><i class="fa-solid fa-mars"></i> Male</span>
                            <span class="meta-item"><i class="fa-solid fa-cake-candles"></i> 24 years old</span>
                        </div>
                    </div>
                </div>
                <div class="official-details">
                    <div class="detail-row">
                        <div class="detail-item"><label><i class="fa-solid fa-user"></i> Full Name</label><p>Robert James Tan</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-envelope"></i> Email Address</label><p>robert.tan@email.com</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-location-dot"></i> Barangay</label><p>Palasan</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-city"></i> Municipality</label><p>Santa Cruz</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-briefcase"></i> Position (SK Role)</label><p>Treasurer</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-calendar-day"></i> Date of Birth</label><p>June 10, 2001</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-person"></i> Age</label><p>24</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-mobile-screen"></i> Contact Number</label><p>0920-123-4567</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-calendar-check"></i> Term Start</label><p>Feb 28, 2026</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-calendar-xmark"></i> Term End</label><p>Jan 7, 2030</p></div>
                        <div class="detail-item"><label><i class="fa-solid fa-circle-check"></i> Status</label><p>Account Status: ACTIVE</p><p>Term Status: ACTIVE</p></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SK Kagawad (bottom) -->
        <section class="sk-kagawad-section">
            <div class="section-header">
                <h2 class="section-title">
                    <span class="icon"><i class="fa-solid fa-users"></i></span>
                    SK Kagawad Members
                </h2>
            </div>
            <div class="kagawad-grid">

                <div class="official-card">
                    <div class="official-header">
                        <div class="official-photo">
                            <div class="profile-icon"><i class="fa-regular fa-circle-user"></i></div>
                            <span class="status-badge active">Active</span>
                        </div>
                        <div class="official-info">
                            <h3>Juan Dela Cruz</h3>
                            <p class="official-position">SK Kagawad</p>
                            <div class="official-meta">
                                <span class="meta-item"><i class="fa-solid fa-venus"></i> Female</span>
                                <span class="meta-item"><i class="fa-solid fa-cake-candles"></i> 23 years old</span>
                            </div>
                        </div>
                    </div>
                    <div class="official-details">
                        <div class="detail-row">
                            <div class="detail-item"><label><i class="fa-solid fa-cake-candles"></i> Birth Date</label><p>May 10, 2001</p></div>
                            <div class="detail-item"><label><i class="fa-solid fa-mobile-screen"></i> Contact</label><p>0913-456-7890</p></div>
                            <div class="detail-item"><label><i class="fa-solid fa-envelope"></i> Email</label><p>jerome.balberona@email.com</p></div>
                        </div>
                        <div class="committee-section">
                            <h4><i class="fa-solid fa-clipboard-list"></i> Committee</h4>
                            <div class="committee-tags">
                                <span class="committee-tag">Health &amp; Nutrition</span>
                                <span class="committee-tag">Women &amp; Children</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="official-card">
                    <div class="official-header">
                        <div class="official-photo">
                            <div class="profile-icon"><i class="fa-regular fa-circle-user"></i></div>
                            <span class="status-badge active">Active</span>
                        </div>
                        <div class="official-info">
                            <h3>Gabriel Garcia</h3>
                            <p class="official-position">SK Kagawad</p>
                            <div class="official-meta">
                                <span class="meta-item"><i class="fa-solid fa-mars"></i> Male</span>
                                <span class="meta-item"><i class="fa-solid fa-cake-candles"></i> 24 years old</span>
                            </div>
                        </div>
                    </div>
                    <div class="official-details">
                        <div class="detail-row">
                            <div class="detail-item"><label><i class="fa-solid fa-cake-candles"></i> Birth Date</label><p>August 22, 2000</p></div>
                            <div class="detail-item"><label><i class="fa-solid fa-mobile-screen"></i> Contact</label><p>0914-567-8901</p></div>
                            <div class="detail-item"><label><i class="fa-solid fa-envelope"></i> Email</label><p>gabriel.garcia@email.com</p></div>
                        </div>
                        <div class="committee-section">
                            <h4><i class="fa-solid fa-clipboard-list"></i> Committee</h4>
                            <div class="committee-tags">
                                <span class="committee-tag">Sports &amp; Recreation</span>
                                <span class="committee-tag">Environment</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="official-card">
                    <div class="official-header">
                        <div class="official-photo">
                            <div class="profile-icon"><i class="fa-regular fa-circle-user"></i></div>
                            <span class="status-badge active">Active</span>
                        </div>
                        <div class="official-info">
                            <h3>Frankien Belangoy</h3>
                            <p class="official-position">SK Kagawad</p>
                            <div class="official-meta">
                                <span class="meta-item"><i class="fa-solid fa-venus"></i> Female</span>
                                <span class="meta-item"><i class="fa-solid fa-cake-candles"></i> 22 years old</span>
                            </div>
                        </div>
                    </div>
                    <div class="official-details">
                        <div class="detail-row">
                            <div class="detail-item"><label><i class="fa-solid fa-cake-candles"></i> Birth Date</label><p>December 5, 2002</p></div>
                            <div class="detail-item"><label><i class="fa-solid fa-mobile-screen"></i> Contact</label><p>0915-678-9012</p></div>
                            <div class="detail-item"><label><i class="fa-solid fa-envelope"></i> Email</label><p>frankien.belangoy@email.com</p></div>
                        </div>
                        <div class="committee-section">
                            <h4><i class="fa-solid fa-clipboard-list"></i> Committee</h4>
                            <div class="committee-tags">
                                <span class="committee-tag">Education</span>
                                <span class="committee-tag">Arts &amp; Culture</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="official-card">
                    <div class="official-header">
                        <div class="official-photo">
                            <div class="profile-icon"><i class="fa-regular fa-circle-user"></i></div>
                            <span class="status-badge active">Active</span>
                        </div>
                        <div class="official-info">
                            <h3>Jerome Balberona</h3>
                            <p class="official-position">SK Kagawad</p>
                            <div class="official-meta">
                                <span class="meta-item"><i class="fa-solid fa-mars"></i> Male</span>
                                <span class="meta-item"><i class="fa-solid fa-cake-candles"></i> 26 years old</span>
                            </div>
                        </div>
                    </div>
                    <div class="official-details">
                        <div class="detail-row">
                            <div class="detail-item"><label><i class="fa-solid fa-cake-candles"></i> Birth Date</label><p>February 18, 1998</p></div>
                            <div class="detail-item"><label><i class="fa-solid fa-mobile-screen"></i> Contact</label><p>0916-789-0123</p></div>
                            <div class="detail-item"><label><i class="fa-solid fa-envelope"></i> Email</label><p>juan.delacruz@email.com</p></div>
                        </div>
                        <div class="committee-section">
                            <h4><i class="fa-solid fa-clipboard-list"></i> Committee</h4>
                            <div class="committee-tags">
                                <span class="committee-tag">Peace &amp; Order</span>
                                <span class="committee-tag">Infrastructure</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="official-card">
                    <div class="official-header">
                        <div class="official-photo">
                            <div class="profile-icon"><i class="fa-regular fa-circle-user"></i></div>
                            <span class="status-badge active">Active</span>
                        </div>
                        <div class="official-info">
                            <h3>Jane Doe</h3>
                            <p class="official-position">SK Kagawad</p>
                            <div class="official-meta">
                                <span class="meta-item"><i class="fa-solid fa-venus"></i> Female</span>
                                <span class="meta-item"><i class="fa-solid fa-cake-candles"></i> 21 years old</span>
                            </div>
                        </div>
                    </div>
                    <div class="official-details">
                        <div class="detail-row">
                            <div class="detail-item"><label><i class="fa-solid fa-cake-candles"></i> Birth Date</label><p>July 30, 2003</p></div>
                            <div class="detail-item"><label><i class="fa-solid fa-mobile-screen"></i> Contact</label><p>0917-890-1234</p></div>
                            <div class="detail-item"><label><i class="fa-solid fa-envelope"></i> Email</label><p>jane.doe@email.com</p></div>
                        </div>
                        <div class="committee-section">
                            <h4><i class="fa-solid fa-clipboard-list"></i> Committee</h4>
                            <div class="committee-tags">
                                <span class="committee-tag">Social Services</span>
                                <span class="committee-tag">Livelihood</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="official-card">
                    <div class="official-header">
                        <div class="official-photo">
                            <div class="profile-icon"><i class="fa-regular fa-circle-user"></i></div>
                            <span class="status-badge active">Active</span>
                        </div>
                        <div class="official-info">
                            <h3>Mark Anthony Reyes</h3>
                            <p class="official-position">SK Kagawad</p>
                            <div class="official-meta">
                                <span class="meta-item"><i class="fa-solid fa-mars"></i> Male</span>
                                <span class="meta-item"><i class="fa-solid fa-cake-candles"></i> 25 years old</span>
                            </div>
                        </div>
                    </div>
                    <div class="official-details">
                        <div class="detail-row">
                            <div class="detail-item"><label><i class="fa-solid fa-cake-candles"></i> Birth Date</label><p>September 12, 1999</p></div>
                            <div class="detail-item"><label><i class="fa-solid fa-mobile-screen"></i> Contact</label><p>0918-901-2345</p></div>
                            <div class="detail-item"><label><i class="fa-solid fa-envelope"></i> Email</label><p>mark.reyes@email.com</p></div>
                        </div>
                        <div class="committee-section">
                            <h4><i class="fa-solid fa-clipboard-list"></i> Committee</h4>
                            <div class="committee-tags">
                                <span class="committee-tag">Technology</span>
                                <span class="committee-tag">Innovation</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
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

            <!-- Change Password card only -->
            <div class="account-settings-card">
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
