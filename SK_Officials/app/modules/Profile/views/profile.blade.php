<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK Officials Profile - SK Officials Portal</title>

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
        
        <!-- 1️⃣ BARANGAY INFORMATION SECTION -->
        <section class="barangay-info-section">
            <div class="section-header">
                <h2 class="section-title">🏢 Barangay Information</h2>
            </div>
            
            <div class="barangay-card">
                <div class="barangay-header">
                    <div class="barangay-logo">
                        <img src="{{ asset('images/default-avatar.png') }}" alt="Barangay Seal" class="seal-image">
                    </div>
                    <div class="barangay-details">
                        <h1 class="barangay-name">Barangay Santa Cruz</h1>
                        <p class="barangay-captain">Barangay Captain: Juan Dela Cruz</p>
                    </div>
                </div>
                
                <div class="barangay-info-grid">
                    <div class="info-item">
                        <label class="info-label">📍 Address</label>
                        <p class="info-value">123 Rizal Street, Poblacion</p>
                    </div>
                    <div class="info-item">
                        <label class="info-label">🏘️ Municipality</label>
                        <p class="info-value">Santa Cruz</p>
                    </div>
                    <div class="info-item">
                        <label class="info-label">🌎 Province</label>
                        <p class="info-value">Laguna</p>
                    </div>
                    <div class="info-item">
                        <label class="info-label">🗺️ Region</label>
                        <p class="info-value">CALABARZON (Region IV-A)</p>
                    </div>
                    <div class="info-item">
                        <label class="info-label">📮 ZIP Code</label>
                        <p class="info-value">4026</p>
                    </div>
                    <div class="info-item">
                        <label class="info-label">📞 Contact Number</label>
                        <p class="info-value">(049) 545-1234</p>
                    </div>
                    <div class="info-item">
                        <label class="info-label">📧 Email Address</label>
                        <p class="info-value">santacruz.laguna@gmail.com</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2️⃣ SK CHAIRMAN SECTION -->
        <section class="sk-chairman-section">
            <div class="section-header">
                <h2 class="section-title">👑 SK Chairman</h2>
            </div>
            
            <div class="official-card chairman-card">
                <div class="official-header">
                    <div class="official-photo">
                        <img src="{{ asset('images/default-avatar.png') }}" alt="SK Chairman" class="profile-image">
                        <span class="status-badge active">Active</span>
                    </div>
                    <div class="official-info">
                        <h3 class="official-name">Jerome Balberona</h3>
                        <p class="official-position">SK Chairman</p>
                        <div class="official-meta">
                            <span class="meta-item">👨 Male</span>
                            <span class="meta-item">🎂 January 15, 1995</span>
                            <span class="meta-item">📅 29 years old</span>
                        </div>
                    </div>
                </div>
                
                <div class="official-details">
                    <div class="detail-row">
                        <div class="detail-item">
                            <label>💍 Civil Status</label>
                            <p>Single</p>
                        </div>
                        <div class="detail-item">
                            <label>🏠 Address</label>
                            <p>123 Rizal Street, Poblacion, Santa Cruz, Laguna</p>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-item">
                            <label>📱 Contact</label>
                            <p>0912-345-6789</p>
                        </div>
                        <div class="detail-item">
                            <label>📧 Email</label>
                            <p>jerome.balberona@gmail.com</p>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div class="detail-item">
                            <label>📅 Term Start</label>
                            <p>June 30, 2023</p>
                        </div>
                        <div class="detail-item">
                            <label>📅 Term End</label>
                            <p>June 30, 2026</p>
                        </div>
                    </div>
                    
                    <div class="education-section">
                        <h4>🎓 Educational Background</h4>
                        <ul class="education-list">
                            <li>Bachelor of Science in Computer Science - Laguna State University</li>
                            <li>Senior High School - Santa Cruz National High School</li>
                            <li>Junior High School - Santa Cruz Central School</li>
                        </ul>
                    </div>
                    
                    <div class="committee-section">
                        <h4>📋 Committee Assignments</h4>
                        <div class="committee-tags">
                            <span class="committee-tag">Youth Development</span>
                            <span class="committee-tag">Sports & Recreation</span>
                            <span class="committee-tag">Education</span>
                            <span class="committee-tag">Health & Wellness</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3️⃣ SK KAGAWAD SECTION -->
        <section class="sk-kagawad-section">
            <div class="section-header">
                <h2 class="section-title">👥 SK Kagawad (Council Members)</h2>
            </div>
            
            <div class="kagawad-grid">
                <!-- Kagawad 1 -->
                <div class="official-card kagawad-card">
                    <div class="official-header">
                        <div class="official-photo">
                            <img src="{{ asset('images/default-avatar.png') }}" alt="SK Kagawad" class="profile-image">
                            <span class="status-badge active">Active</span>
                        </div>
                        <div class="official-info">
                            <h3 class="official-name">Maria Santos</h3>
                            <p class="official-position">SK Kagawad</p>
                            <div class="official-meta">
                                <span class="meta-item">👩 Female</span>
                                <span class="meta-item">🎂 March 22, 1996</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="official-details">
                        <div class="detail-item">
                            <label>📱 Contact</label>
                            <p>0917-234-5678</p>
                        </div>
                        <div class="detail-item">
                            <label>📧 Email</label>
                            <p>maria.santos@gmail.com</p>
                        </div>
                        <div class="detail-item">
                            <label>📋 Committee</label>
                            <p>Education & Youth Development</p>
                        </div>
                    </div>
                </div>

                <!-- Kagawad 2 -->
                <div class="official-card kagawad-card">
                    <div class="official-header">
                        <div class="official-photo">
                            <img src="{{ asset('images/default-avatar.png') }}" alt="SK Kagawad" class="profile-image">
                            <span class="status-badge active">Active</span>
                        </div>
                        <div class="official-info">
                            <h3 class="official-name">John Cruz</h3>
                            <p class="official-position">SK Kagawad</p>
                            <div class="official-meta">
                                <span class="meta-item">👨 Male</span>
                                <span class="meta-item">🎂 July 10, 1997</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="official-details">
                        <div class="detail-item">
                            <label>📱 Contact</label>
                            <p>0918-345-6789</p>
                        </div>
                        <div class="detail-item">
                            <label>📧 Email</label>
                            <p>john.cruz@gmail.com</p>
                        </div>
                        <div class="detail-item">
                            <label>📋 Committee</label>
                            <p>Sports & Recreation</p>
                        </div>
                    </div>
                </div>

                <!-- Kagawad 3 -->
                <div class="official-card kagawad-card">
                    <div class="official-header">
                        <div class="official-photo">
                            <img src="{{ asset('images/default-avatar.png') }}" alt="SK Kagawad" class="profile-image">
                            <span class="status-badge active">Active</span>
                        </div>
                        <div class="official-info">
                            <h3 class="official-name">Ana Reyes</h3>
                            <p class="official-position">SK Kagawad</p>
                            <div class="official-meta">
                                <span class="meta-item">👩 Female</span>
                                <span class="meta-item">🎂 September 5, 1996</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="official-details">
                        <div class="detail-item">
                            <label>📱 Contact</label>
                            <p>0919-456-7890</p>
                        </div>
                        <div class="detail-item">
                            <label>📧 Email</label>
                            <p>ana.reyes@gmail.com</p>
                        </div>
                        <div class="detail-item">
                            <label>📋 Committee</label>
                            <p>Health & Wellness</p>
                        </div>
                    </div>
                </div>

                <!-- Kagawad 4 -->
                <div class="official-card kagawad-card">
                    <div class="official-header">
                        <div class="official-photo">
                            <img src="{{ asset('images/default-avatar.png') }}" alt="SK Kagawad" class="profile-image">
                            <span class="status-badge active">Active</span>
                        </div>
                        <div class="official-info">
                            <h3 class="official-name">Mark Torres</h3>
                            <p class="official-position">SK Kagawad</p>
                            <div class="official-meta">
                                <span class="meta-item">👨 Male</span>
                                <span class="meta-item">🎂 November 18, 1995</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="official-details">
                        <div class="detail-item">
                            <label>📱 Contact</label>
                            <p>0920-567-8901</p>
                        </div>
                        <div class="detail-item">
                            <label>📧 Email</label>
                            <p>mark.torres@gmail.com</p>
                        </div>
                        <div class="detail-item">
                            <label>📋 Committee</label>
                            <p>Environment & Cleanliness</p>
                        </div>
                    </div>
                </div>

                <!-- Kagawad 5 -->
                <div class="official-card kagawad-card">
                    <div class="official-header">
                        <div class="official-photo">
                            <img src="{{ asset('images/default-avatar.png') }}" alt="SK Kagawad" class="profile-image">
                            <span class="status-badge active">Active</span>
                        </div>
                        <div class="official-info">
                            <h3 class="official-name">Lisa Martinez</h3>
                            <p class="official-position">SK Kagawad</p>
                            <div class="official-meta">
                                <span class="meta-item">👩 Female</span>
                                <span class="meta-item">🎂 February 14, 1997</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="official-details">
                        <div class="detail-item">
                            <label>📱 Contact</label>
                            <p>0921-678-9012</p>
                        </div>
                        <div class="detail-item">
                            <label>📧 Email</label>
                            <p>lisa.martinez@gmail.com</p>
                        </div>
                        <div class="detail-item">
                            <label>📋 Committee</label>
                            <p>Culture & Arts</p>
                        </div>
                    </div>
                </div>

                <!-- Kagawad 6 -->
                <div class="official-card kagawad-card">
                    <div class="official-header">
                        <div class="official-photo">
                            <img src="{{ asset('images/default-avatar.png') }}" alt="SK Kagawad" class="profile-image">
                            <span class="status-badge active">Active</span>
                        </div>
                        <div class="official-info">
                            <h3 class="official-name">David Lim</h3>
                            <p class="official-position">SK Kagawad</p>
                            <div class="official-meta">
                                <span class="meta-item">👨 Male</span>
                                <span class="meta-item">🎂 May 8, 1996</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="official-details">
                        <div class="detail-item">
                            <label>📱 Contact</label>
                            <p>0922-789-0123</p>
                        </div>
                        <div class="detail-item">
                            <label>📧 Email</label>
                            <p>david.lim@gmail.com</p>
                        </div>
                        <div class="detail-item">
                            <label>📋 Committee</label>
                            <p>Livelihood & Employment</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4️⃣ SK SECRETARY SECTION -->
        <section class="sk-secretary-section">
            <div class="section-header">
                <h2 class="section-title">📝 SK Secretary</h2>
            </div>
            
            <div class="official-card secretary-card">
                <div class="official-header">
                    <div class="official-photo">
                        <img src="{{ asset('images/default-avatar.png') }}" alt="SK Secretary" class="profile-image">
                        <span class="status-badge active">Active</span>
                    </div>
                    <div class="official-info">
                        <h3 class="official-name">Grace Fernandez</h3>
                        <p class="official-position">SK Secretary</p>
                    </div>
                </div>
                
                <div class="official-details">
                    <div class="detail-row">
                        <div class="detail-item">
                            <label>📱 Contact Number</label>
                            <p>0923-890-1234</p>
                        </div>
                        <div class="detail-item">
                            <label>📧 Email Address</label>
                            <p>grace.fernandez@gmail.com</p>
                        </div>
                    </div>
                    <div class="detail-item">
                        <label>📅 Appointment Date</label>
                        <p>July 1, 2023</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5️⃣ SK TREASURER SECTION -->
        <section class="sk-treasurer-section">
            <div class="section-header">
                <h2 class="section-title">💰 SK Treasurer</h2>
            </div>
            
            <div class="official-card treasurer-card">
                <div class="official-header">
                    <div class="official-photo">
                        <img src="{{ asset('images/default-avatar.png') }}" alt="SK Treasurer" class="profile-image">
                        <span class="status-badge active">Active</span>
                    </div>
                    <div class="official-info">
                        <h3 class="official-name">Robert Chen</h3>
                        <p class="official-position">SK Treasurer</p>
                    </div>
                </div>
                
                <div class="official-details">
                    <div class="detail-row">
                        <div class="detail-item">
                            <label>📱 Contact Number</label>
                            <p>0924-901-2345</p>
                        </div>
                        <div class="detail-item">
                            <label>📧 Email Address</label>
                            <p>robert.chen@gmail.com</p>
                        </div>
                    </div>
                    <div class="detail-item">
                        <label>📅 Appointment Date</label>
                        <p>July 1, 2023</p>
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
