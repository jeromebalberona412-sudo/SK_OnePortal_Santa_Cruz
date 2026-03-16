<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Profile - SK OnePortal</title>
    @vite([
        'app/Modules/Profile/assets/css/profile.css',
        'app/Modules/Profile/assets/js/profile.js',
        'app/Modules/Dashboard/assets/css/chatbot.css',
        'app/Modules/Dashboard/assets/js/chatbot.js',
        'app/Modules/Dashboard/assets/css/notif.css',
        'app/Modules/Dashboard/assets/js/notif.js',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])
    <script>
        // Define functions in global scope before page loads
        function openEditModal() {
            const modal = document.getElementById('editProfileModal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeEditModal() {
            const modal = document.getElementById('editProfileModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        function closeSuccessModal() {
            const modal = document.getElementById('successModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
                window.location.reload();
            }
        }

        function calculateAge() {
            const birthdateInput = document.getElementById('edit_birthdate');
            const ageInput = document.getElementById('edit_age');
            
            if (birthdateInput && ageInput) {
                const birthdate = new Date(birthdateInput.value);
                const today = new Date();
                let age = today.getFullYear() - birthdate.getFullYear();
                const monthDiff = today.getMonth() - birthdate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                    age--;
                }
                
                ageInput.value = age;
            }
        }

        function updateProfile(event) {
            event.preventDefault();
            
            const form = document.getElementById('editProfileForm');
            const formData = new FormData(form);
            
            const updatedData = {
                first_name: formData.get('first_name'),
                middle_initial: formData.get('middle_initial'),
                last_name: formData.get('last_name'),
                suffix: formData.get('suffix'),
                username: formData.get('username'),
                birthdate: formData.get('birthdate'),
                age: formData.get('age'),
                email: formData.get('email'),
                contact_number: formData.get('contact_number'),
                province: formData.get('province'),
                municipality: formData.get('municipality'),
                barangay: formData.get('barangay')
            };
            
            sessionStorage.setItem('profile_updated', JSON.stringify(updatedData));
            
            closeEditModal();
            
            setTimeout(() => {
                const successModal = document.getElementById('successModal');
                if (successModal) {
                    successModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            }, 300);
            
            return false;
        }

        function viewProgramDetails(programId) {
            console.log('Viewing program:', programId);
            alert('Program details will be available when the backend is implemented!');
        }

        function openScheduleModal() {
            const modal = document.getElementById('scheduleModal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeScheduleModal() {
            const modal = document.getElementById('scheduleModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        // Close modals on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
                closeSuccessModal();
                closeScheduleModal();
            }
        });

        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                closeEditModal();
                closeSuccessModal();
                closeScheduleModal();
            }
        });
    </script>
</head>
<body class="youth-profile">
    @include('dashboard::loading')
    <!-- Top Navigation Bar -->
    <nav class="top-navbar">
        <div class="navbar-container">
            <div class="navbar-left">
                <img src="/images/skoneportal_logo.webp" alt="SK OnePortal" class="navbar-logo">
                <span class="navbar-title">SK OnePortal</span>
            </div>
            
            <div class="navbar-center">
                <div class="search-bar">
                    <svg class="search-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                    </svg>
                    <input type="text" placeholder="Search posts, programs, announcements..." class="search-input">
                </div>
            </div>
            
            <div class="navbar-right">
                <button class="nav-icon-btn" title="Home" onclick="if(window.showLoading) showLoading('Redirecting'); window.location.href='{{ route('dashboard') }}'">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                </button>
                
                @include('dashboard::notification')
                
                @include('dashboard::chatbot')
                
                <div class="user-menu">
                    <button class="user-avatar-btn">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->first_name . ' ' . $user->last_name) }}&background=667eea&color=fff" alt="User Avatar">
                    </button>
                    <div class="user-dropdown">
                        <div class="dropdown-header">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->first_name . ' ' . $user->last_name) }}&background=667eea&color=fff" alt="User Avatar">
                            <div>
                                <p class="user-name">{{ $user->first_name }} {{ $user->last_name }}</p>
                                <p class="user-email">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('profile') }}" class="dropdown-item">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            My Profile
                        </a>
                        <a href="{{ route('settings') }}" class="dropdown-item">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                            </svg>
                            Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item logout-btn">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="profile-main">
        <div class="profile-container">
            <!-- Profile Header Card -->
            <div class="profile-header-card">
                <div class="profile-cover">
                    <div class="cover-gradient"></div>
                </div>
                <div class="profile-info-section">
                    <div class="profile-avatar-wrapper">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->first_name . ' ' . $user->last_name) }}&size=150&background=667eea&color=fff" alt="Profile" class="profile-avatar" id="profileAvatar">
                        <button class="change-photo-btn" id="changePhotoBtn" onclick="alert('Photo upload feature will be available when the backend is implemented!')">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"/>
                            </svg>
                        </button>
                        <input type="file" id="photoUpload" accept="image/*" style="display: none;">
                    </div>
                    <div class="profile-header-info">
                        <h1 class="profile-name">{{ $user->first_name }} {{ $user->middle_initial ? $user->middle_initial . '.' : '' }} {{ $user->last_name }} {{ $user->suffix ?? '' }}</h1>
                        <p class="profile-username">Add a Username</p>
                        <p class="profile-location">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            {{ $user->barangay }}, Santa Cruz, Laguna
                        </p>
                    </div>
                    <button class="edit-profile-btn" id="editProfileBtn" onclick="openEditModal()">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                        </svg>
                        Edit Profile
                    </button>
                </div>
            </div>

            <div class="profile-content-grid">
                <!-- Left Column - Personal Information -->
                <div class="profile-left-column">
                    <!-- Personal Information Card -->
                    <div class="info-card">
                        <div class="card-header">
                            <h2>
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                                Personal Information
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <div class="info-item">
                                    <label>Full Name</label>
                                    <p>{{ $user->first_name }} {{ $user->middle_initial ? $user->middle_initial . '.' : '' }} {{ $user->last_name }} {{ $user->suffix ?? '' }}</p>
                                </div>
                                <div class="info-item">
                                    <label>Username</label>
                                    <p>Add a Username</p>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-item">
                                    <label>Birthdate</label>
                                    <p>{{ \Carbon\Carbon::parse($user->birthdate)->format('F d, Y') }}</p>
                                </div>
                                <div class="info-item">
                                    <label>Age</label>
                                    <p>{{ $user->age }} years old</p>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-item">
                                    <label>Email Address</label>
                                    <p>{{ $user->email }}</p>
                                </div>
                                <div class="info-item">
                                    <label>Contact Number</label>
                                    <p>{{ $user->contact_number ?? 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-item full-width">
                                    <label>Complete Address</label>
                                    <p>{{ $user->barangay }}, {{ $user->municipality }}, {{ $user->province }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Participation Summary Card -->
                    <div class="info-card">
                        <div class="card-header">
                            <h2>
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                </svg>
                                Participation Summary
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="summary-stats">
                                <div class="stat-item">
                                    <div class="stat-icon programs">
                                        <svg viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/>
                                            <path d="M3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                                        </svg>
                                    </div>
                                    <div class="stat-info">
                                        <p class="stat-label">Programs Joined</p>
                                        <p class="stat-value">{{ $totalPrograms ?? 0 }}</p>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon ongoing">
                                        <svg viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="stat-info">
                                        <p class="stat-label">Ongoing</p>
                                        <p class="stat-value">{{ $ongoingPrograms ?? 0 }}</p>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon completed">
                                        <svg viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="stat-info">
                                        <p class="stat-label">Completed</p>
                                        <p class="stat-value">{{ $completedPrograms ?? 0 }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Program Participation -->
                <div class="profile-right-column">
                    <!-- Program Participation Card -->
                    <div class="info-card">
                        <div class="card-header">
                            <h2>
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                                Program Participation
                            </h2>
                            <button class="calendar-btn" onclick="openScheduleModal()" title="View Program Schedule">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Filter Tabs -->
                            <div class="filter-tabs">
                                <button class="tab-btn active" data-filter="all">
                                    <svg viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                    </svg>
                                    All
                                </button>
                                <button class="tab-btn" data-filter="pending">
                                    <svg viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    Pending
                                </button>
                                <button class="tab-btn" data-filter="ongoing">
                                    <svg viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                    </svg>
                                    Ongoing
                                </button>
                                <button class="tab-btn" data-filter="completed">
                                    <svg viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    History
                                </button>
                            </div>

                            <!-- Programs List -->
                            <div class="programs-list">
                                @forelse($programs ?? [] as $program)
                                <div class="program-item" data-status="{{ $program->status }}">
                                    <div class="program-icon-wrapper">
                                        <div class="program-icon {{ $program->status }}">
                                            <svg viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="program-details">
                                        <h3>{{ $program->name }}</h3>
                                        <p class="program-category">{{ $program->category ?? 'General Program' }}</p>
                                        <p class="program-date">
                                            <svg viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                            </svg>
                                            Applied: {{ \Carbon\Carbon::parse($program->created_at)->format('M d, Y') }}
                                        </p>
                                        <span class="status-badge {{ $program->status }}">
                                            @if($program->status === 'pending')
                                                <svg viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                            @elseif($program->status === 'ongoing')
                                                <svg viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                                </svg>
                                            @elseif($program->status === 'completed')
                                                <svg viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            @elseif($program->status === 'declined')
                                                <svg viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                            @else
                                                <svg viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                            {{ ucfirst($program->status) }}
                                        </span>
                                    </div>
                                    <button class="view-details-btn-small" onclick="viewProgramDetails({{ $program->id }})">
                                        <svg viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                                @empty
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect x="3" y="6" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
                                            <path d="M3 10 L12 6 L21 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <line x1="8" y1="13" x2="16" y2="13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            <line x1="10" y1="16" x2="14" y2="16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <h3>No Programs Yet</h3>
                                    <p>You haven't joined any programs yet. Explore available programs in the dashboard!</p>
                                    <a href="{{ route('dashboard') }}" class="explore-btn">
                                        <svg viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                                        </svg>
                                        Explore Programs
                                    </a>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Profile Modal -->
    <div class="modal-overlay" id="editProfileModal" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h2>Edit Profile</h2>
                <button class="modal-close" onclick="closeEditModal()">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm" onsubmit="updateProfile(event)">
                    <div class="form-section">
                        <h3>Personal Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>First Name <span class="required">*</span></label>
                                <input type="text" name="first_name" id="edit_first_name" value="{{ $user->first_name }}" required>
                            </div>
                            <div class="form-group">
                                <label>Middle Initial</label>
                                <input type="text" name="middle_initial" id="edit_middle_initial" value="{{ $user->middle_initial ?? '' }}" maxlength="1">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Last Name <span class="required">*</span></label>
                                <input type="text" name="last_name" id="edit_last_name" value="{{ $user->last_name }}" required>
                            </div>
                            <div class="form-group">
                                <label>Suffix</label>
                                <select name="suffix" id="edit_suffix">
                                    <option value="">None</option>
                                    <option value="Jr." {{ ($user->suffix ?? '') === 'Jr.' ? 'selected' : '' }}>Jr.</option>
                                    <option value="Sr." {{ ($user->suffix ?? '') === 'Sr.' ? 'selected' : '' }}>Sr.</option>
                                    <option value="II" {{ ($user->suffix ?? '') === 'II' ? 'selected' : '' }}>II</option>
                                    <option value="III" {{ ($user->suffix ?? '') === 'III' ? 'selected' : '' }}>III</option>
                                    <option value="IV" {{ ($user->suffix ?? '') === 'IV' ? 'selected' : '' }}>IV</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Username <span class="required">*</span></label>
                                <input type="text" name="username" id="edit_username" value="" placeholder="Add a Username" required>
                            </div>
                            <div class="form-group">
                                <label>Birthdate <span class="required">*</span></label>
                                <input type="date" name="birthdate" id="edit_birthdate" value="{{ $user->birthdate }}" required onchange="calculateAge()">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Age</label>
                                <input type="number" name="age" id="edit_age" value="{{ $user->age }}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Contact Number <span class="required">*</span></label>
                                <input type="tel" name="contact_number" id="edit_contact_number" value="{{ $user->contact_number ?? '' }}" placeholder="09XXXXXXXXX" pattern="[0-9]{11}" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Email Address <span class="required">*</span></label>
                                <input type="email" name="email" id="edit_email" value="{{ $user->email }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Address Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Province <span class="required">*</span></label>
                                <input type="text" name="province" id="edit_province" value="Laguna" readonly>
                            </div>
                            <div class="form-group">
                                <label>Municipality <span class="required">*</span></label>
                                <input type="text" name="municipality" id="edit_municipality" value="Santa Cruz" readonly>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Barangay <span class="required">*</span></label>
                                <select name="barangay" id="edit_barangay" required>
                                    <option value="">Select Barangay</option>
                                    <option value="Alipit" {{ $user->barangay === 'Alipit' ? 'selected' : '' }}>Alipit</option>
                                    <option value="Bagumbayan" {{ $user->barangay === 'Bagumbayan' ? 'selected' : '' }}>Bagumbayan</option>
                                    <option value="Bubukal" {{ $user->barangay === 'Bubukal' ? 'selected' : '' }}>Bubukal</option>
                                    <option value="Calios" {{ $user->barangay === 'Calios' ? 'selected' : '' }}>Calios</option>
                                    <option value="Duhat" {{ $user->barangay === 'Duhat' ? 'selected' : '' }}>Duhat</option>
                                    <option value="Gatid" {{ $user->barangay === 'Gatid' ? 'selected' : '' }}>Gatid</option>
                                    <option value="Jasaan" {{ $user->barangay === 'Jasaan' ? 'selected' : '' }}>Jasaan</option>
                                    <option value="Labuin" {{ $user->barangay === 'Labuin' ? 'selected' : '' }}>Labuin</option>
                                    <option value="Malinao" {{ $user->barangay === 'Malinao' ? 'selected' : '' }}>Malinao</option>
                                    <option value="Oogong" {{ $user->barangay === 'Oogong' ? 'selected' : '' }}>Oogong</option>
                                    <option value="Pagsawitan" {{ $user->barangay === 'Pagsawitan' ? 'selected' : '' }}>Pagsawitan</option>
                                    <option value="Palasan" {{ $user->barangay === 'Palasan' ? 'selected' : '' }}>Palasan</option>
                                    <option value="Patimbao" {{ $user->barangay === 'Patimbao' ? 'selected' : '' }}>Patimbao</option>
                                    <option value="Poblacion I" {{ $user->barangay === 'Poblacion I' ? 'selected' : '' }}>Poblacion I</option>
                                    <option value="Poblacion II" {{ $user->barangay === 'Poblacion II' ? 'selected' : '' }}>Poblacion II</option>
                                    <option value="Poblacion III" {{ $user->barangay === 'Poblacion III' ? 'selected' : '' }}>Poblacion III</option>
                                    <option value="Poblacion IV" {{ $user->barangay === 'Poblacion IV' ? 'selected' : '' }}>Poblacion IV</option>
                                    <option value="Poblacion V" {{ $user->barangay === 'Poblacion V' ? 'selected' : '' }}>Poblacion V</option>
                                    <option value="San Jose" {{ $user->barangay === 'San Jose' ? 'selected' : '' }}>San Jose</option>
                                    <option value="San Juan" {{ $user->barangay === 'San Juan' ? 'selected' : '' }}>San Juan</option>
                                    <option value="San Pablo Norte" {{ $user->barangay === 'San Pablo Norte' ? 'selected' : '' }}>San Pablo Norte</option>
                                    <option value="San Pablo Sur" {{ $user->barangay === 'San Pablo Sur' ? 'selected' : '' }}>San Pablo Sur</option>
                                    <option value="Santisima Cruz" {{ $user->barangay === 'Santisima Cruz' ? 'selected' : '' }}>Santisima Cruz</option>
                                    <option value="Santo Angel Central" {{ $user->barangay === 'Santo Angel Central' ? 'selected' : '' }}>Santo Angel Central</option>
                                    <option value="Santo Angel Norte" {{ $user->barangay === 'Santo Angel Norte' ? 'selected' : '' }}>Santo Angel Norte</option>
                                    <option value="Santo Angel Sur" {{ $user->barangay === 'Santo Angel Sur' ? 'selected' : '' }}>Santo Angel Sur</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancel</button>
                        <button type="submit" class="btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal-overlay" id="successModal" style="display: none;">
        <div class="modal-container success-modal" style="max-width:420px;">
            <div class="modal-body" style="text-align:center; padding: 48px 32px;">
                <div style="width:72px;height:72px;background:linear-gradient(135deg,#22c55e,#16a34a);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 8px 24px rgba(34,197,94,0.35);">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:36px;height:36px;color:white;">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h2 style="font-size:22px;font-weight:700;color:#1e293b;margin-bottom:10px;">Profile Updated Successfully!</h2>
                <p style="color:#64748b;font-size:14px;line-height:1.6;margin-bottom:28px;">Your profile information has been saved.</p>
                <button class="btn-primary" onclick="closeSuccessModal()" style="padding:12px 32px;background:linear-gradient(135deg,#667eea,#764ba2);border:none;border-radius:10px;color:white;font-size:14px;font-weight:600;cursor:pointer;box-shadow:0 4px 12px rgba(102,126,234,0.3);font-family:inherit;">OK</button>
            </div>
        </div>
    </div>

    <!-- Schedule Modal -->
    <div class="modal-overlay" id="scheduleModal" style="display: none;">
        <div class="modal-container schedule-modal-container">
            <div class="modal-header">
                <div class="schedule-modal-title">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h2>Program Schedule</h2>
                        <p class="schedule-modal-subtitle">{{ \Carbon\Carbon::now()->format('F Y') }} — Your Program Calendar</p>
                    </div>
                </div>
                <button class="modal-close" onclick="closeScheduleModal()">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body schedule-modal-body">
                @php
                    $categoryColors = [
                        'Education'             => ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'dot' => '#3b82f6'],
                        'Anti-Drugs'            => ['bg' => '#fce7f3', 'text' => '#be185d', 'dot' => '#ec4899'],
                        'Agriculture'           => ['bg' => '#dcfce7', 'text' => '#15803d', 'dot' => '#22c55e'],
                        'Disaster Preparedness' => ['bg' => '#ffedd5', 'text' => '#c2410c', 'dot' => '#f97316'],
                        'Sports Development'    => ['bg' => '#e0f2fe', 'text' => '#0369a1', 'dot' => '#0ea5e9'],
                        'Gender and Development'=> ['bg' => '#f3e8ff', 'text' => '#7e22ce', 'dot' => '#a855f7'],
                        'Health'                => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'dot' => '#ef4444'],
                        'Others'                => ['bg' => '#f1f5f9', 'text' => '#475569', 'dot' => '#94a3b8'],
                        'General Program'       => ['bg' => '#e0f2fe', 'text' => '#0450a8', 'dot' => '#0450a8'],
                    ];

                    // Build program date map for JS
                    $programDateMap = [];
                    foreach ($programs ?? [] as $program) {
                        $programDate = \Carbon\Carbon::parse($program->created_at)->addDays(7);
                        $key = $programDate->format('Y-m-d');
                        if (!isset($programDateMap[$key])) $programDateMap[$key] = [];
                        $programDateMap[$key][] = [
                            'name'     => $program->name,
                            'category' => $program->category ?? 'General Program',
                            'status'   => $program->status,
                        ];
                    }
                @endphp

                {{-- Legend --}}
                <div class="month-cal-legend">
                    @foreach($categoryColors as $cat => $colors)
                        <div class="legend-chip" style="background:{{ $colors['bg'] }}; color:{{ $colors['text'] }};">
                            <span class="legend-chip-dot" style="background:{{ $colors['dot'] }};"></span>
                            {{ $cat }}
                        </div>
                    @endforeach
                </div>

                {{-- Calendar shell — rendered by JS --}}
                <div class="month-cal-card">
                    <div class="month-cal-header">
                        <button class="cal-nav-btn" id="calPrevBtn" title="Previous month">
                            <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <span class="month-cal-title" id="calMonthTitle"></span>
                        <button class="cal-nav-btn" id="calNextBtn" title="Next month">
                            <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                    <div class="month-cal-grid" id="calGrid"></div>
                </div>

                {{-- Upcoming Programs List --}}
                <div class="upcoming-programs-section">
                    <h3 class="upcoming-title">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        Upcoming Programs
                    </h3>
                    <div id="upcomingList"></div>
                </div>

                {{-- Floating Popover --}}
                <div id="calPopover" class="cal-popover" style="display:none;"></div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        // ── Logout Modal — deferred until DOM is ready ────────────────
        function initLogout() {
            const logoutBtn   = document.querySelector('.logout-btn');
            const logoutForm  = logoutBtn?.closest('form');
            const logoutModal = document.getElementById('logoutConfirmModal');
            const confirmBtn  = document.getElementById('confirmLogoutBtn');

            if (!logoutBtn || !logoutModal) return;

            logoutBtn.addEventListener('click', e => {
                e.preventDefault();
                logoutModal.style.display = 'flex';
            });

            confirmBtn?.addEventListener('click', () => logoutForm?.submit());
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initLogout);
        } else {
            initLogout();
        }
    })();

    function closeLogoutModal() {
        document.getElementById('logoutConfirmModal').style.display = 'none';
    }

    (function () {
        // ── Data from server ──────────────────────────────────────────
        const PROGRAM_DATES = @json($programDateMap);
        const TODAY = new Date();
        TODAY.setHours(0,0,0,0);

        const CAT_COLORS = @json($categoryColors);
        const STATUS_COLORS = {
            pending:   { bg: '#fff7ed', text: '#c2410c', label: 'Pending' },
            ongoing:   { bg: '#eff6ff', text: '#1d4ed8', label: 'Ongoing' },
            completed: { bg: '#f0fdf4', text: '#15803d', label: 'Completed' },
            declined:  { bg: '#fef2f2', text: '#b91c1c', label: 'Declined' },
        };
        const MONTH_NAMES = ['January','February','March','April','May','June',
                             'July','August','September','October','November','December'];
        const DAY_HEADERS = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

        // ── State ─────────────────────────────────────────────────────
        let viewYear  = TODAY.getFullYear();
        let viewMonth = TODAY.getMonth(); // 0-indexed

        // ── Elements ──────────────────────────────────────────────────
        const grid        = document.getElementById('calGrid');
        const title       = document.getElementById('calMonthTitle');
        const prevBtn     = document.getElementById('calPrevBtn');
        const nextBtn     = document.getElementById('calNextBtn');
        const upcomingEl  = document.getElementById('upcomingList');
        const popover     = document.getElementById('calPopover');

        // ── Helpers ───────────────────────────────────────────────────
        function pad(n) { return String(n).padStart(2, '0'); }
        function dateKey(y, m, d) { return `${y}-${pad(m+1)}-${pad(d)}`; }
        function truncate(str, n) { return str.length > n ? str.slice(0, n) : str; }

        // ── Render calendar ───────────────────────────────────────────
        function render() {
            const isCurrentMonth = (viewYear === TODAY.getFullYear() && viewMonth === TODAY.getMonth());
            title.textContent = MONTH_NAMES[viewMonth] + ' ' + viewYear;

            // Badge
            const existing = title.parentElement.querySelector('.month-cal-badge');
            if (existing) existing.remove();
            if (isCurrentMonth) {
                const badge = document.createElement('span');
                badge.className = 'month-cal-badge';
                badge.textContent = 'This Month';
                title.after(badge);
            }

            // Build grid
            const firstDay = new Date(viewYear, viewMonth, 1).getDay();
            const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

            let html = DAY_HEADERS.map(d => `<div class="month-day-header">${d}</div>`).join('');

            // blank cells
            for (let i = 0; i < firstDay; i++) html += `<div class="month-day-cell empty"></div>`;

            for (let d = 1; d <= daysInMonth; d++) {
                const key   = dateKey(viewYear, viewMonth, d);
                const progs = PROGRAM_DATES[key] || [];
                const isToday = isCurrentMonth && d === TODAY.getDate();
                const first = progs[0];
                const cat   = first ? (first.category || 'General Program') : null;
                const cc    = cat ? (CAT_COLORS[cat] || CAT_COLORS['General Program']) : null;

                const numHtml = isToday
                    ? `<span class="month-day-num today-num">${d}</span>`
                    : `<span class="month-day-num">${d}</span>`;

                let inner = numHtml;
                if (first) {
                    inner += `<span class="month-day-label" style="color:${cc.text};">${truncate(cat, 7)}</span>`;
                    if (progs.length > 1) inner += `<span class="month-day-more" style="color:${cc.text};">+${progs.length - 1}</span>`;
                }

                const style = first ? `style="background:${cc.bg};"` : '';
                const cls   = ['month-day-cell', isToday ? 'today' : '', first ? 'has-event' : ''].filter(Boolean).join(' ');
                const data  = first ? `data-popover="${key}"` : '';

                html += `<div class="${cls}" ${style} ${data}>${inner}</div>`;
            }

            grid.innerHTML = html;
            renderUpcoming();
        }

        // ── Render upcoming list ──────────────────────────────────────
        function renderUpcoming() {
            const todayKey = dateKey(TODAY.getFullYear(), TODAY.getMonth(), TODAY.getDate());
            const items = Object.entries(PROGRAM_DATES)
                .filter(([k]) => k >= todayKey)
                .sort(([a], [b]) => a.localeCompare(b));

            if (items.length === 0) {
                upcomingEl.innerHTML = `
                    <div class="empty-schedule">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="6" width="18" height="14" rx="2" stroke="#cbd5e1" stroke-width="2"/>
                            <path d="M3 10h18" stroke="#cbd5e1" stroke-width="2"/>
                            <path d="M8 3v3M16 3v3" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <h3>No Upcoming Programs</h3>
                        <p>You have no upcoming programs scheduled.</p>
                    </div>`;
                return;
            }

            let html = '<div class="upcoming-list">';
            items.forEach(([key, progs]) => {
                const parts = key.split('-');
                const dt = new Date(+parts[0], +parts[1]-1, +parts[2]);
                const day = pad(dt.getDate());
                const mon = dt.toLocaleString('en', { month: 'short' });

                progs.forEach(prog => {
                    const cat    = prog.category || 'General Program';
                    const cc     = CAT_COLORS[cat] || CAT_COLORS['General Program'];
                    const sc     = STATUS_COLORS[prog.status] || STATUS_COLORS.pending;
                    html += `
                        <div class="upcoming-item">
                            <div class="upcoming-date-badge">
                                <span class="upcoming-day">${day}</span>
                                <span class="upcoming-month">${mon}</span>
                            </div>
                            <div class="upcoming-details">
                                <p class="upcoming-name">${prog.name}</p>
                                <div class="upcoming-meta">
                                    <span class="upcoming-cat" style="background:${cc.bg};color:${cc.text};">${cat}</span>
                                    <span class="upcoming-status" style="background:${sc.bg};color:${sc.text};">${sc.label}</span>
                                </div>
                            </div>
                        </div>`;
                });
            });
            html += '</div>';
            upcomingEl.innerHTML = html;
        }

        // ── Navigation ────────────────────────────────────────────────
        prevBtn.addEventListener('click', () => {
            viewMonth--;
            if (viewMonth < 0) { viewMonth = 11; viewYear--; }
            render();
        });

        nextBtn.addEventListener('click', () => {
            viewMonth++;
            if (viewMonth > 11) { viewMonth = 0; viewYear++; }
            render();
        });

        // ── Popover ───────────────────────────────────────────────────
        let hideTimer = null;

        function showPopover(cell) {
            const key   = cell.dataset.popover;
            const progs = PROGRAM_DATES[key];
            if (!progs) return;

            clearTimeout(hideTimer);

            const parts = key.split('-');
            const dt = new Date(+parts[0], +parts[1]-1, +parts[2]);
            const dateLabel = dt.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });

            let html = `<div class="cal-popover-date">${dateLabel}</div>`;
            progs.forEach(prog => {
                const cat = prog.category || 'General Program';
                const cc  = CAT_COLORS[cat] || CAT_COLORS['General Program'];
                const sc  = STATUS_COLORS[prog.status] || STATUS_COLORS.pending;
                html += `
                    <div class="cal-popover-program">
                        <div class="cal-popover-program-name">${prog.name}</div>
                        <div class="cal-popover-meta">
                            <span class="cal-popover-cat" style="background:${cc.bg};color:${cc.text};">${cat}</span>
                            <span class="cal-popover-status" style="background:${sc.bg};color:${sc.text};">${sc.label}</span>
                        </div>
                    </div>`;
            });

            popover.innerHTML = html;
            popover.style.display = 'block';

            const rect      = cell.getBoundingClientRect();
            const modalBody = document.querySelector('.schedule-modal-body');
            const bodyRect  = modalBody.getBoundingClientRect();

            let top  = rect.bottom - bodyRect.top + modalBody.scrollTop + 6;
            let left = rect.left   - bodyRect.left;
            if (left + 240 > bodyRect.width - 8) left = bodyRect.width - 248;
            if (left < 4) left = 4;

            popover.style.top  = top  + 'px';
            popover.style.left = left + 'px';
        }

        function hidePopover() {
            hideTimer = setTimeout(() => { popover.style.display = 'none'; }, 150);
        }

        document.addEventListener('mouseover', e => {
            const cell = e.target.closest('.month-day-cell.has-event');
            if (cell) { showPopover(cell); return; }
            if (e.target.closest('#calPopover')) { clearTimeout(hideTimer); return; }
            hidePopover();
        });

        document.addEventListener('mouseleave', e => {
            if (!e.target.closest('.month-day-cell.has-event') && !e.target.closest('#calPopover')) hidePopover();
        }, true);

        popover.addEventListener('mouseenter', () => clearTimeout(hideTimer));
        popover.addEventListener('mouseleave', hidePopover);

        // ── Init ──────────────────────────────────────────────────────
        render();
    })();
    </script>
    <!-- Logout Confirm Modal -->
    <div id="logoutConfirmModal" style="display:none;position:fixed;inset:0;z-index:2000;align-items:center;justify-content:center;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);" onclick="closeLogoutModal()"></div>
        <div style="position:relative;background:white;border-radius:20px;max-width:420px;width:90%;padding:40px 32px;text-align:center;animation:modalSlideIn 0.3s ease;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <div style="width:64px;height:64px;background:#fff3e0;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width:32px;height:32px;color:#f97316;">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:8px;">Are you sure you want to logout?</h3>
            <p style="color:#94a3b8;font-size:14px;margin-bottom:28px;">You will be redirected to the login page.</p>
            <div style="display:flex;gap:12px;justify-content:center;">
                <button onclick="closeLogoutModal()" style="min-width:100px;padding:12px 24px;background:#f1f5f9;color:#64748b;border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;">Cancel</button>
                <button id="confirmLogoutBtn" style="min-width:100px;padding:12px 24px;background:linear-gradient(135deg,#f44336,#d32f2f);color:white;border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;box-shadow:0 4px 12px rgba(244,67,54,0.3);font-family:inherit;">Logout</button>
            </div>
        </div>
    </div>

    <style>
    @keyframes modalSlideIn {
        from { opacity: 0; transform: scale(0.9) translateY(20px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }
    </style>

</body>
</html>
