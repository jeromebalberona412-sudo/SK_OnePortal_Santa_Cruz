<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK Officials Profile - SK Officials Portal</title>
    
    <!-- Font Awesome (Neutral Default Icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/Profile/assets/css/profile.css'
    ])
</head>
<body>

<!-- ================= HEADER ================= -->
@include('layout::header')

<!-- ================= SIDEBAR ================= -->
@include('layout::sidebar')

<!-- ================= MAIN CONTENT ================= -->
<main class="main-content">
    <div class="profile-container">
        
        <!-- 1️⃣ SK CHAIRMAN SECTION -->
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
                        <div class="profile-icon">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <span class="status-badge active">Active</span>
                    </div>
                    <div class="official-info">
                        <h3>Paula A Talais</h3>
                        <p class="official-position">SK Chairman</p>
                        <div class="official-meta">
                            <span class="meta-item">
                                <i class="fa-solid fa-venus"></i>
                                Female
                            </span>
                            <span class="meta-item">
                                <i class="fa-solid fa-cake-candles"></i>
                                20 years old
                            </span>
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

        <!-- 3️⃣ SK KAGAWAD SECTION -->
        <section class="sk-kagawad-section">
            <div class="section-header">
                <h2 class="section-title">
                    <span class="icon"><i class="fa-solid fa-users"></i></span>
                    SK Kagawad Members
                </h2>
            </div>
            
            <div class="kagawad-grid">
                <!-- Kagawad 1 -->
                <div class="official-card">
                    <div class="official-header">
                        <div class="official-photo">
                            <div class="profile-icon">
                                <i class="fa-regular fa-circle-user"></i>
                            </div>
                            <span class="status-badge active">Active</span>
                        </div>
                        <div class="official-info">
                            <h3>Maria Santos</h3>
                            <p class="official-position">SK Kagawad</p>
                            <div class="official-meta">
                                <span class="meta-item">
                                    <i class="fa-solid fa-venus"></i>
                                    Female
                                </span>
                                <span class="meta-item">
                                    <i class="fa-solid fa-cake-candles"></i>
                                    23 years old
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="official-details">
                        <div class="detail-row">
                            <div class="detail-item">
                                <label><i class="fa-solid fa-cake-candles"></i> Birth Date</label>
                                <p>May 10, 2001</p>
                            </div>
                            <div class="detail-item">
                                <label><i class="fa-solid fa-mobile-screen"></i> Contact</label>
                                <p>0913-456-7890</p>
                            </div>
                            <div class="detail-item">
                                <label><i class="fa-solid fa-envelope"></i> Email</label>
                                <p>maria.santos@email.com</p>
                            </div>
                        </div>
                        
                        <div class="committee-section">
                            <h4><i class="fa-solid fa-clipboard-list"></i> Committee</h4>
                            <div class="committee-tags">
                                <span class="committee-tag">Health & Nutrition</span>
                                <span class="committee-tag">Women & Children</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kagawad 2 -->
                <div class="official-card">
                    <div class="official-header">
                        <div class="official-photo">
                            <div class="profile-icon">
                                <i class="fa-regular fa-circle-user"></i>
                            </div>
                            <span class="status-badge active">Active</span>
                        </div>
                        <div class="official-info">
                            <h3>John Cruz</h3>
                            <p class="official-position">SK Kagawad</p>
                            <div class="official-meta">
                                <span class="meta-item">
                                    <i class="fa-solid fa-mars"></i>
                                    Male
                                </span>
                                <span class="meta-item">
                                    <i class="fa-solid fa-cake-candles"></i>
                                    24 years old
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="official-details">
                        <div class="detail-row">
                            <div class="detail-item">
                                <label><i class="fa-solid fa-cake-candles"></i> Birth Date</label>
                                <p>August 22, 2000</p>
                            </div>
                            <div class="detail-item">
                                <label><i class="fa-solid fa-mobile-screen"></i> Contact</label>
                                <p>0914-567-8901</p>
                            </div>
                            <div class="detail-item">
                                <label><i class="fa-solid fa-envelope"></i> Email</label>
                                <p>john.cruz@email.com</p>
                            </div>
                        </div>
                        
                        <div class="committee-section">
                            <h4><i class="fa-solid fa-clipboard-list"></i> Committee</h4>
                            <div class="committee-tags">
                                <span class="committee-tag">Sports & Recreation</span>
                                <span class="committee-tag">Environment</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kagawad 3 -->
                <div class="official-card">
                    <div class="official-header">
                        <div class="official-photo">
                            <div class="profile-icon">
                                <i class="fa-regular fa-circle-user"></i>
                            </div>
                            <span class="status-badge active">Active</span>
                        </div>
                        <div class="official-info">
                            <h3>Ana Reyes</h3>
                            <p class="official-position">SK Kagawad</p>
                            <div class="official-meta">
                                <span class="meta-item">
                                    <i class="fa-solid fa-venus"></i>
                                    Female
                                </span>
                                <span class="meta-item">
                                    <i class="fa-solid fa-cake-candles"></i>
                                    22 years old
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="official-details">
                        <div class="detail-row">
                            <div class="detail-item">
                                <label><i class="fa-solid fa-cake-candles"></i> Birth Date</label>
                                <p>December 5, 2002</p>
                            </div>
                            <div class="detail-item">
                                <label><i class="fa-solid fa-mobile-screen"></i> Contact</label>
                                <p>0915-678-9012</p>
                            </div>
                            <div class="detail-item">
                                <label><i class="fa-solid fa-envelope"></i> Email</label>
                                <p>ana.reyes@email.com</p>
                            </div>
                        </div>
                        
                        <div class="committee-section">
                            <h4><i class="fa-solid fa-clipboard-list"></i> Committee</h4>
                            <div class="committee-tags">
                                <span class="committee-tag">Education</span>
                                <span class="committee-tag">Arts & Culture</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kagawad 4 -->
                <div class="official-card">
                    <div class="official-header">
                        <div class="official-photo">
                            <div class="profile-icon">
                                <i class="fa-regular fa-circle-user"></i>
                            </div>
                            <span class="status-badge active">Active</span>
                        </div>
                        <div class="official-info">
                            <h3>Mark Torres</h3>
                            <p class="official-position">SK Kagawad</p>
                            <div class="official-meta">
                                <span class="meta-item">
                                    <i class="fa-solid fa-mars"></i>
                                    Male
                                </span>
                                <span class="meta-item">
                                    <i class="fa-solid fa-cake-candles"></i>
                                    26 years old
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="official-details">
                        <div class="detail-row">
                            <div class="detail-item">
                                <label><i class="fa-solid fa-cake-candles"></i> Birth Date</label>
                                <p>February 18, 1998</p>
                            </div>
                            <div class="detail-item">
                                <label><i class="fa-solid fa-mobile-screen"></i> Contact</label>
                                <p>0916-789-0123</p>
                            </div>
                            <div class="detail-item">
                                <label><i class="fa-solid fa-envelope"></i> Email</label>
                                <p>mark.torres@email.com</p>
                            </div>
                        </div>
                        
                        <div class="committee-section">
                            <h4><i class="fa-solid fa-clipboard-list"></i> Committee</h4>
                            <div class="committee-tags">
                                <span class="committee-tag">Peace & Order</span>
                                <span class="committee-tag">Infrastructure</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kagawad 5 -->
                <div class="official-card">
                    <div class="official-header">
                        <div class="official-photo">
                            <div class="profile-icon">
                                <i class="fa-regular fa-circle-user"></i>
                            </div>
                            <span class="status-badge active">Active</span>
                        </div>
                        <div class="official-info">
                            <h3>Lisa Martinez</h3>
                            <p class="official-position">SK Kagawad</p>
                            <div class="official-meta">
                                <span class="meta-item">
                                    <i class="fa-solid fa-venus"></i>
                                    Female
                                </span>
                                <span class="meta-item">
                                    <i class="fa-solid fa-cake-candles"></i>
                                    21 years old
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="official-details">
                        <div class="detail-row">
                            <div class="detail-item">
                                <label><i class="fa-solid fa-cake-candles"></i> Birth Date</label>
                                <p>July 30, 2003</p>
                            </div>
                            <div class="detail-item">
                                <label><i class="fa-solid fa-mobile-screen"></i> Contact</label>
                                <p>0917-890-1234</p>
                            </div>
                            <div class="detail-item">
                                <label><i class="fa-solid fa-envelope"></i> Email</label>
                                <p>lisa.martinez@email.com</p>
                            </div>
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

                <!-- Kagawad 6 -->
                <div class="official-card">
                    <div class="official-header">
                        <div class="official-photo">
                            <div class="profile-icon">
                                <i class="fa-regular fa-circle-user"></i>
                            </div>
                            <span class="status-badge active">Active</span>
                        </div>
                        <div class="official-info">
                            <h3>David Lim</h3>
                            <p class="official-position">SK Kagawad</p>
                            <div class="official-meta">
                                <span class="meta-item">
                                    <i class="fa-solid fa-mars"></i>
                                    Male
                                </span>
                                <span class="meta-item">
                                    <i class="fa-solid fa-cake-candles"></i>
                                    25 years old
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="official-details">
                        <div class="detail-row">
                            <div class="detail-item">
                                <label><i class="fa-solid fa-cake-candles"></i> Birth Date</label>
                                <p>September 12, 1999</p>
                            </div>
                            <div class="detail-item">
                                <label><i class="fa-solid fa-mobile-screen"></i> Contact</label>
                                <p>0918-901-2345</p>
                            </div>
                            <div class="detail-item">
                                <label><i class="fa-solid fa-envelope"></i> Email</label>
                                <p>david.lim@email.com</p>
                            </div>
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

        <!-- 4️⃣ SK SECRETARY SECTION -->
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
                        <div class="profile-icon">
                            <i class="fa-solid fa-user-pen"></i>
                        </div>
                        <span class="status-badge active">Active</span>
                    </div>
                    <div class="official-info">
                        <h3>Grace Fernandez</h3>
                        <p class="official-position">SK Secretary</p>
                        <div class="official-meta">
                            <span class="meta-item">
                                <i class="fa-solid fa-venus"></i>
                                Female
                            </span>
                            <span class="meta-item">
                                <i class="fa-solid fa-cake-candles"></i>
                                23 years old
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="official-details">
                    <div class="detail-row">
                        <div class="detail-item">
                            <label><i class="fa-solid fa-user"></i> Full Name</label>
                            <p>Grace Fernandez</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-mobile-screen"></i> Contact Number</label>
                            <p>0919-012-3456</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-envelope"></i> Email Address</label>
                            <p>grace.fernandez@email.com</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-calendar"></i> Appointment Date</label>
                            <p>January 15, 2023</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-circle-check"></i> Status</label>
                            <p><span class="status-badge active">Active</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5️⃣ SK TREASURER SECTION -->
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
                        <div class="profile-icon">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <span class="status-badge active">Active</span>
                    </div>
                    <div class="official-info">
                        <h3>Robert Chen</h3>
                        <p class="official-position">SK Treasurer</p>
                        <div class="official-meta">
                            <span class="meta-item">
                                <i class="fa-solid fa-mars"></i>
                                Male
                            </span>
                            <span class="meta-item">
                                <i class="fa-solid fa-cake-candles"></i>
                                24 years old
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="official-details">
                    <div class="detail-row">
                        <div class="detail-item">
                            <label><i class="fa-solid fa-user"></i> Full Name</label>
                            <p>Robert Chen</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-mobile-screen"></i> Contact Number</label>
                            <p>0920-123-4567</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-envelope"></i> Email Address</label>
                            <p>robert.chen@email.com</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-calendar"></i> Appointment Date</label>
                            <p>January 15, 2023</p>
                        </div>
                        <div class="detail-item">
                            <label><i class="fa-solid fa-circle-check"></i> Status</label>
                            <p><span class="status-badge active">Active</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/Profile/assets/js/profile.js'
])

</body>
</html>
