<!DOCTYPE html>
<html lang="en">
<head>
    @include('favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>My Profile - SK OnePortal</title>
    @vite([
        'app/Modules/Layout/assets/css/kabataan-bootstrap.css',
        'app/Modules/Layout/assets/css/kabataan-responsive.css',
        'app/Modules/Layout/assets/css/kabataan-header.css',
        'app/Modules/Layout/assets/css/kabataan-logout.css',
        'app/Modules/Layout/assets/js/kabataan-header.js',
        'app/Modules/Layout/assets/js/kabataan-logout.js',
        'app/Modules/Profile/assets/css/profile.css',
        'app/Modules/KKProfiling/assets/css/kkprofiling.css',
        'app/Modules/KKProfiling/assets/css/kk-profiling-update.css',
        'app/Modules/Profile/assets/js/profile.js',
        'app/Modules/Profile/assets/js/profile-participation.js',
        'app/Modules/Dashboard/assets/css/chatbot.css',
        'app/Modules/Dashboard/assets/js/chatbot.js',
        'app/Modules/Dashboard/assets/css/notif.css',
        'app/Modules/Dashboard/assets/js/notif.js',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])
    <script>
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
                closeScheduleModal();
                if (typeof closeKkPreviewModal === 'function') {
                    closeKkPreviewModal();
                }
            }
        });

        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                closeScheduleModal();
            }
            if (e.target.classList.contains('kabataan-modal-backdrop')) {
                if (typeof closeKkPreviewModal === 'function') {
                    closeKkPreviewModal();
                }
            }
        });
    </script>
</head>
<body class="youth-profile kabataan-app-page">
    @include('dashboard::loading')
    @include('layout::kabataan-header', ['user' => $user, 'showSearch' => true, 'pageBadge' => 'My Profile'])

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
                        <img src="{{ $profileImageUrl }}" alt="Profile" class="profile-avatar" id="profileAvatar">
                        <button
                            class="change-photo-btn{{ $canChangeProfileImage ? '' : ' is-disabled' }}"
                            id="changePhotoBtn"
                            type="button"
                            title="{{ $canChangeProfileImage ? 'Change profile picture' : 'Profile picture update locked' }}"
                            @if(!$canChangeProfileImage) disabled @endif
                        >
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"/>
                            </svg>
                        </button>
                        <input type="file" id="photoUpload" accept="image/jpeg,image/jpg,image/png,image/webp" style="display: none;" @if(!$canChangeProfileImage) disabled @endif>
                    </div>
                    <div class="profile-header-info">
                        <h1 class="profile-name">{{ $fullName ?? strtoupper($user->name) }}</h1>
                        <p class="profile-location">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            {{ $barangayName ?? 'Santa Cruz' }}, Santa Cruz, Laguna
                        </p>
                    </div>
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
                            @if($kabataanRegistration)
                                <div class="kkp-preview-toolbar">
                                    <button type="button" class="btn-primary kkp-preview-btn" onclick="openKkPreviewModal()">View Personal Information</button>
                                </div>
                            @else
                                <div class="empty-state">
                                    <h3>No KK Profiling Record</h3>
                                    <p>Wala pang completed KK Profiling form para sa account na ito.</p>
                                </div>
                            @endif
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
                                    <div class="stat-icon approved">
                                        <svg viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="stat-info">
                                        <p class="stat-label">Approved</p>
                                        <p class="stat-value">{{ $approvedPrograms ?? 0 }}</p>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon evaluation">
                                        <svg viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="stat-info">
                                        <p class="stat-label">Pending Review</p>
                                        <p class="stat-value">{{ $evaluationPrograms ?? 0 }}</p>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon completed">
                                        <svg viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
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

                    <!-- Account Settings Card -->
                    <div class="info-card">
                        <div class="card-header">
                            <h2>
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                </svg>
                                Account Settings
                            </h2>
                        </div>
                        <div class="card-body">
                            <div
                                id="profileImageUploadRoot"
                                class="profile-image-settings"
                                data-upload-url="{{ route('profile.upload-picture') }}"
                                data-can-change="{{ $canChangeProfileImage ? '1' : '0' }}"
                                data-next-change="{{ $profileImageNextChangeDisplay ?? '' }}"
                            >
                                <div class="profile-image-settings__header">
                                    <div>
                                        <h3>Current Profile Picture</h3>
                                        <p>Your profile photo appears on your profile and header across Kabataan.</p>
                                    </div>
                                </div>

                                <div class="profile-image-settings__preview">
                                    <img src="{{ $profileImageUrl }}" alt="Current profile picture" id="profileImagePreview" class="profile-image-settings__avatar">
                                </div>

                                @if(!$canChangeProfileImage && $profileImageNextChangeDisplay)
                                    <div class="profile-image-lock-notice" id="profileImageLockNotice">
                                        Next profile picture update available on: <strong>{{ $profileImageNextChangeDisplay }}</strong>
                                    </div>
                                @else
                                    <div class="profile-image-lock-notice is-hidden" id="profileImageLockNotice"></div>
                                @endif

                                <div class="profile-image-upload-zone{{ $canChangeProfileImage ? '' : ' is-disabled' }}" id="profileImageDropZone">
                                    <input type="file" id="profileImageFileInput" accept="image/jpeg,image/jpg,image/png,image/webp" hidden @if(!$canChangeProfileImage) disabled @endif>
                                    <div class="profile-image-upload-zone__icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none"><path d="M12 16V4m0 0L8 8m4-4 4 4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </div>
                                    <p class="profile-image-upload-zone__title">Upload New Profile Picture</p>
                                    <p class="profile-image-upload-zone__hint">Drag and drop an image here, or click to browse</p>
                                    <p class="profile-image-upload-zone__meta">JPG, JPEG, PNG, WEBP up to 10MB</p>
                                    <button type="button" class="btn-setting-action profile-image-upload-btn" id="profileImageBrowseBtn" @if(!$canChangeProfileImage) disabled @endif>
                                        Choose Image
                                    </button>
                                </div>

                                <div class="profile-image-progress is-hidden" id="profileImageProgress">
                                    <div class="profile-image-progress__bar"><span id="profileImageProgressFill"></span></div>
                                    <p id="profileImageProgressText">Uploading profile picture...</p>
                                </div>

                                <p class="profile-image-feedback is-hidden" id="profileImageFeedback" role="alert"></p>
                            </div>

                            <div class="setting-divider"></div>

                            <!-- Email Address Section -->
                            <div class="account-setting-item">
                                <div class="setting-info">
                                    <h3>Email Address</h3>
                                    <p>Change your account email address via verification link.</p>
                                </div>
                                <a href="{{ route('change-email') }}" class="btn-setting-action">
                                    Change Email
                                </a>
                            </div>
                            
                            <div class="setting-divider"></div>
                            
                            <!-- Password Section -->
                            <div class="account-setting-item">
                                <div class="setting-info">
                                    <h3>Password</h3>
                                    <p>Change your account password via email reset link.</p>
                                </div>
                                <a href="{{ route('change-password') }}" class="btn-setting-action">
                                    Change Password
                                </a>
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
                                <button class="tab-btn" data-filter="approved">
                                    <svg viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Approved
                                </button>
                                <button class="tab-btn" data-filter="completed">
                                    <svg viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Completed
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
                                        @if(!empty($program->answers_preview))
                                            <p class="program-answer-preview">{{ $program->answers_preview }}</p>
                                        @endif
                                        <p class="program-date">
                                            <svg viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                            </svg>
                                            @if(($program->source ?? '') === 'survey')
                                                Responded: {{ \Carbon\Carbon::parse($program->created_at)->format('M d, Y') }}
                                            @else
                                                Applied: {{ \Carbon\Carbon::parse($program->created_at)->format('M d, Y') }}
                                            @endif
                                        </p>
                                        <span class="status-badge {{ $program->status }}">
                                            @if($program->status === 'pending')
                                                <svg viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                            @elseif($program->status === 'approved')
                                                <svg viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            @elseif($program->status === 'evaluation')
                                                <svg viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            @elseif($program->status === 'completed')
                                                <svg viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
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
                                    <button type="button" class="view-details-btn-small" onclick="viewProgramDetails(@json($program->id))">
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

    <div class="modal-backdrop kabataan-modal-backdrop" id="kkPreviewModal" style="display: none;">
        <div class="modal-box kabataan-modal-box kk-preview-modal-container" id="kkPreviewModalPanel">
            <div class="modal-header">
                <h2 class="modal-title">Personal Information</h2>
                <div class="modal-window-controls">
                    <button type="button" class="modal-toggle-btn" id="kkPreviewFullscreenBtn" data-modal-toggle aria-label="Maximize">□</button>
                    <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
                </div>
            </div>
            <div class="modal-body kabataan-modal-body">
                @if($kabataanRegistration)
                    <div class="kabataan-form-scroll">
                        @include('profile::partials.kk-profiling-preview', [
                            'kabataanRegistration' => $kabataanRegistration,
                            'user' => $user,
                            'barangayName' => $barangayName,
                            'barangayLogoUrl' => $barangayLogoUrl,
                            'profile' => $profile ?? [],
                        ])
                    </div>
                @endif
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

                    // Build program date map for JS from real calendar events
                    $programDateMap = $calendarEvents ?? [];
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

                @if(!empty($abyipPrograms))
                <div class="upcoming-programs-section abyip-programs-section">
                    <h3 class="upcoming-title">Youth Programs (ABYIP)</h3>
                    <div class="abyip-programs-grid">
                        @foreach($abyipPrograms as $abyipProgram)
                            <div class="abyip-program-chip">
                                <span class="abyip-program-emoji">{{ $abyipProgram['emoji'] ?? '📋' }}</span>
                                <div>
                                    <p class="abyip-program-name">{{ $abyipProgram['title'] ?? 'Program' }}</p>
                                    <p class="abyip-program-meta">{{ $abyipProgram['short_label'] ?? 'Youth Program' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Floating Popover --}}
                <div id="calPopover" class="cal-popover" style="display:none;"></div>
            </div>
        </div>
    </div>

    <script>
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
    <!-- Program Details Modal -->
    <div id="programDetailsModal" class="program-modal" style="display: none;">
        <div class="modal-overlay"></div>
        <div class="modal-container" style="max-width: 900px;">
            <div class="modal-header">
                <h2 id="programModalTitle">Program Details</h2>
                <button class="modal-close" onclick="closeProgramDetailsModal()">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body" style="padding: 32px; overflow-y: auto; max-height: calc(90vh - 80px); min-height: 400px;">
                <div class="modern-program-card" id="programDetailsContent">
                    <!-- Content will be dynamically inserted here -->
                </div>
            </div>
        </div>
    </div>

    <style>
    @keyframes modalSlideIn {
        from { opacity: 0; transform: scale(0.9) translateY(20px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }
    
    /* Program Modal Styles */
    .program-modal {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
    }
    
    .program-modal .modal-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }
    
    .program-modal .modal-container {
        position: relative;
        background: white;
        border-radius: 20px;
        width: 90%;
        max-height: 90vh;
        overflow: hidden;
        animation: modalSlideIn 0.3s ease;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    }
    
    .modern-program-card {
        background: white;
    }
    
    .program-card-header {
        padding: 24px;
        border-radius: 12px 12px 0 0;
        margin-bottom: 24px;
    }
    
    .program-title-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
    }
    
    .program-category-tag {
        display: inline-block;
        font-size: 14px;
        font-weight: 600;
        color: white;
        margin-bottom: 8px;
        opacity: 0.9;
    }
    
    .program-card-title {
        font-size: 24px;
        font-weight: 700;
        color: white;
        margin: 0;
    }
    
    .program-status-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        color: white;
        white-space: nowrap;
    }
    
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: white;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .program-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    
    .detail-card {
        display: flex;
        gap: 12px;
        padding: 16px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }
    
    .detail-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .detail-icon svg {
        width: 20px;
        height: 20px;
        color: white;
    }
    
    .detail-content {
        flex: 1;
    }
    
    .detail-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    
    .detail-value {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
    }
    
    .program-description-section {
        margin-bottom: 24px;
        padding: 20px;
        background: #f8fafc;
        border-radius: 12px;
        border-left: 4px solid #0450a8;
    }
    
    .section-heading {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 12px;
    }
    
    .section-heading svg {
        width: 20px;
        height: 20px;
        color: #0450a8;
    }
    
    .description-text {
        font-size: 14px;
        line-height: 1.6;
        color: #475569;
        margin: 0;
    }
    
    .terms-section {
        margin-bottom: 24px;
    }
    
    .terms-toggle {
        width: 100%;
        padding: 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .terms-toggle:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    
    .terms-toggle.active {
        border-color: #0450a8;
        background: #eff6ff;
    }
    
    .terms-toggle-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .chevron-icon {
        width: 20px;
        height: 20px;
        color: #64748b;
        transition: transform 0.3s ease;
    }
    
    .terms-toggle.active .chevron-icon {
        transform: rotate(180deg);
    }
    
    .terms-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }
    
    .terms-list {
        list-style: none;
        padding: 16px 0 0 0;
        margin: 0;
    }
    
    .terms-list li {
        padding: 8px 0 8px 28px;
        position: relative;
        font-size: 14px;
        color: #475569;
        line-height: 1.6;
    }
    
    .terms-list li:before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #22c55e;
        font-weight: 700;
    }
    
    .terms-agreement {
        padding-top: 16px;
        border-top: 1px solid #e2e8f0;
        margin-top: 16px;
    }
    
    .agreement-checkbox {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        user-select: none;
    }
    
    .agreement-checkbox input[type="checkbox"] {
        display: none;
    }
    
    .checkbox-custom {
        width: 20px;
        height: 20px;
        border: 2px solid #cbd5e1;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }
    
    .agreement-checkbox input[type="checkbox"]:checked + .checkbox-custom {
        background: #0450a8;
        border-color: #0450a8;
    }
    
    .agreement-checkbox input[type="checkbox"]:checked + .checkbox-custom:after {
        content: "✓";
        color: white;
        font-size: 14px;
        font-weight: 700;
    }
    
    .agreement-text {
        font-size: 14px;
        color: #1e293b;
        font-weight: 500;
    }
    
    .program-action {
        text-align: center;
    }
    
    .apply-now-button {
        padding: 14px 32px;
        background: linear-gradient(135deg, #0450a8 0%, #022a54 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(4, 80, 168, 0.3);
    }
    
    .apply-now-button:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(4, 80, 168, 0.4);
    }
    
    .apply-now-button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .apply-now-button svg {
        width: 20px;
        height: 20px;
    }
    
    .apply-note {
        margin-top: 12px;
        font-size: 13px;
        color: #64748b;
    }
    </style>

    <script>
    window.__participationDetails = @json($participationDetails ?? []);
    </script>

    <script>
    window.addEventListener('unload', function () {});

    function resetKkPreviewModalState() {
        const backdrop = document.getElementById('kkPreviewModal');
        const panel = document.getElementById('kkPreviewModalPanel');
        const toggleBtn = document.getElementById('kkPreviewFullscreenBtn');
        if (backdrop) backdrop.classList.remove('modal-maximized');
        if (panel) panel.classList.remove('modal-maximized');
        if (toggleBtn) {
            toggleBtn.textContent = '□';
            toggleBtn.setAttribute('aria-label', 'Maximize');
        }
    }

    function openKkPreviewModal() {
        const modal = document.getElementById('kkPreviewModal');
        if (modal) {
            resetKkPreviewModalState();
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeKkPreviewModal() {
        const modal = document.getElementById('kkPreviewModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            resetKkPreviewModalState();
        }
    }

    document.getElementById('kkPreviewFullscreenBtn')?.addEventListener('click', function () {
        const backdrop = document.getElementById('kkPreviewModal');
        const panel = document.getElementById('kkPreviewModalPanel');
        if (!backdrop || !panel) return;
        const isMax = !backdrop.classList.contains('modal-maximized');
        backdrop.classList.toggle('modal-maximized', isMax);
        panel.classList.toggle('modal-maximized', isMax);
        this.textContent = isMax ? '⧉' : '□';
        this.setAttribute('aria-label', isMax ? 'Restore down' : 'Maximize');
    });

    document.querySelectorAll('#kkPreviewModal [data-modal-close]').forEach(function (btn) {
        btn.addEventListener('click', closeKkPreviewModal);
    });
    </script>
</body>
</html>
