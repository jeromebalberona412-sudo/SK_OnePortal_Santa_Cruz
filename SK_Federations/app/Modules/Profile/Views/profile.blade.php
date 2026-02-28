<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - SK Federation</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/modules/dashboard/css/dashboard.css') }}">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-left">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="navbar-brand">SK FEDERATION</div>
        </div>
        <div class="navbar-right">
            <a href="{{ route('profile') }}" class="user-profile">
                <img src="https://ui-avatars.com/api/?name=Paula+Talabis&background=213F99&color=fff" alt="User Avatar" class="user-avatar">
                <div class="user-info">
                    <div class="user-name">Paula Talabis</div>
                    <div class="user-role">SK Member</div>
                </div>
            </a>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar">
        <nav class="sidebar-menu">
            <a href="{{ route('profile') }}" class="menu-item active">
                <i class="fas fa-user"></i>
                <span>My Profile</span>
            </a>
            <a href="{{ route('dashboard') }}" class="menu-item">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <div class="menu-divider"></div>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" class="menu-item" style="width: 100%; text-align: left; background: none; cursor: pointer;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="profile-container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>My Profile</h1>
                <p>Manage your personal information and account settings</p>
            </div>

            <!-- Profile Card -->
            <div class="profile-card">
                <!-- Profile Header with Avatar -->
                <div class="profile-header">
                    <div class="profile-avatar-container">
                        <img src="https://ui-avatars.com/api/?name=Paula+Talabis&background=213F99&color=fff&size=120" alt="Profile Avatar" class="profile-avatar-large">
                        <label for="avatarUpload" class="avatar-upload-btn" onclick="triggerAvatarUpload()">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" id="avatarUpload" class="avatar-upload-input" accept="image/*" onchange="handleAvatarUpload(event)">
                    </div>
                    <h2 class="profile-name">Paula Talabis</h2>
                    <p class="profile-email">paulatalabis@gmail.com</p>
                </div>

                <!-- Profile Tabs -->
                <div class="profile-tabs">
                    <button class="tab-btn" onclick="switchTab('personal-info')">Personal Information</button>
                    <button class="tab-btn" onclick="switchTab('change-password')">Change Password</button>
                </div>

                <!-- Tab: Personal Information -->
                <div id="personal-info" class="tab-content">
                    <div class="success-message"></div>
                    
                    <form onsubmit="saveProfileInfo(event)">
                        <div class="form-section">
                            <h3 class="form-section-title">Basic Information</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="firstName">First Name</label>
                                    <input type="text" id="firstName" name="firstName" class="form-control" value="Paula" required>
                                </div>
                                <div class="form-group">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" id="lastName" name="lastName" class="form-control" value="Talabis" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-control" value="paulatalabis@gmail.com" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" value="+63 912 345 6789">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="birthdate">Date of Birth</label>
                                <input type="date" id="birthdate" name="birthdate" class="form-control" value="2000-01-15">
                            </div>
                        </div>

                        <div class="form-section">
                            <h3 class="form-section-title">Address</h3>
                            
                            <div class="form-group">
                                <label for="barangay">Barangay (Santa Cruz, Laguna)</label>
                                <select id="barangay" name="barangay" class="form-control" required>
                                    <option value="">Select Barangay</option>
                                    <option value="Alipit">Alipit</option>
                                    <option value="Bagumbayan">Bagumbayan</option>
                                    <option value="Barangay I (Poblacion)">Barangay I (Poblacion)</option>
                                    <option value="Barangay II (Poblacion)">Barangay II (Poblacion)</option>
                                    <option value="Barangay III (Poblacion)">Barangay III (Poblacion)</option>
                                    <option value="Barangay IV (Poblacion)">Barangay IV (Poblacion)</option>
                                    <option value="Barangay V (Poblacion)">Barangay V (Poblacion)</option>
                                    <option value="Bubukal">Bubukal</option>
                                    <option value="Calios">Calios</option>
                                    <option value="Duhat">Duhat</option>
                                    <option value="Gatid">Gatid</option>
                                    <option value="Jasaan">Jasaan</option>
                                    <option value="Labuin">Labuin</option>
                                    <option value="Malinao">Malinao</option>
                                    <option value="Oogong">Oogong</option>
                                    <option value="Pagsawitan">Pagsawitan</option>
                                    <option value="Palasan">Palasan</option>
                                    <option value="Patimbao">Patimbao</option>
                                    <option value="San Jose">San Jose</option>
                                    <option value="San Juan">San Juan</option>
                                    <option value="San Pablo Norte">San Pablo Norte</option>
                                    <option value="San Pablo Sur">San Pablo Sur</option>
                                    <option value="Santisima Cruz">Santisima Cruz</option>
                                    <option value="Santo Angel Central">Santo Angel Central</option>
                                    <option value="Santo Angel Norte">Santo Angel Norte</option>
                                    <option value="Santo Angel Sur">Santo Angel Sur</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3 class="form-section-title">SK Federation Information</h3>
                            
                            <div class="form-group">
                                <label for="position">Position</label>
                                <select id="position" name="position" class="form-control" required>
                                    <option value="">Select Position</option>
                                    <option value="SK Federation President">SK Federation President</option>
                                    <option value="SK Federation Vice President">SK Federation Vice President</option>
                                    <option value="Secretary">Secretary</option>
                                    <option value="Treasurer">Treasurer</option>
                                    <option value="Auditor">Auditor</option>
                                    <option value="Public Relations Officer (PRO)">Public Relations Officer (PRO)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                            <button type="button" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab: Change Password -->
                <div id="change-password" class="tab-content">
                    <div class="success-message"></div>
                    
                    <form onsubmit="changePassword(event)">
                        <div class="form-section">
                            <h3 class="form-section-title">Change Your Password</h3>
                            
                            <div class="form-group">
                                <label for="currentPassword">Current Password</label>
                                <input type="password" id="currentPassword" name="currentPassword" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="newPassword">New Password</label>
                                <input type="password" id="newPassword" name="newPassword" class="form-control" required>
                                <div class="password-strength">
                                    <div class="password-strength-bar"></div>
                                </div>
                                <div class="password-hint"></div>
                            </div>

                            <div class="form-group">
                                <label for="confirmPassword">Confirm New Password</label>
                                <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" required>
                            </div>

                            <div style="background: #f0f4ff; padding: 16px; border-radius: 8px; margin-top: 20px;">
                                <p style="font-size: 14px; color: #213F99; margin-bottom: 8px; font-weight: 600;">Password Requirements:</p>
                                <ul style="font-size: 13px; color: #64748b; margin-left: 20px;">
                                    <li>At least 8 characters long</li>
                                    <li>Contains uppercase and lowercase letters</li>
                                    <li>Contains at least one number</li>
                                    <li>Contains at least one special character</li>
                                </ul>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key"></i>
                                Change Password
                            </button>
                            <button type="button" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ url('/modules/dashboard/js/dashboard.js') }}"></script>
    <script>
        (() => {
            const heartbeatIntervalMs = {{ (int) config('sk_fed_auth.single_session.heartbeat_interval_seconds', 30) }} * 1000;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            let intervalId = null;

            async function sendHeartbeat() {
                try {
                    await fetch("{{ route('skfed.heartbeat') }}", {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({}),
                    });
                } catch (error) {
                }
            }

            sendHeartbeat();
            intervalId = window.setInterval(sendHeartbeat, heartbeatIntervalMs);

            window.addEventListener('beforeunload', () => {
                if (intervalId !== null) {
                    window.clearInterval(intervalId);
                }
            });
        })();
    </script>
</body>
</html>
