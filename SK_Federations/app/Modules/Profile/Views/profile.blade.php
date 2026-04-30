<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>My Profile - SK Federation</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/modules/dashboard/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('/modules/profile/css/profile.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body data-heartbeat-interval-ms="{{ (int) config('sk_fed_auth.single_session.heartbeat_interval_seconds', 30) * 1000 }}">
    <script>
        (function() {
            window.history.pushState(null, "", window.location.href);
            window.onpopstate = function() { window.history.pushState(null, "", window.location.href); };
        })();
    </script>

    @php
        $avatar = 'https://ui-avatars.com/api/?name=' . urlencode((string) ($user->name ?? 'User')) . '&background=213F99&color=fff&size=120';
        $formattedRole = $user->role ? ucwords(str_replace('_', ' ', (string) $user->role)) : 'SK Official';
        $selectedBarangayId = old('barangay_id', $user->barangay_id);
    @endphp

    {{-- ── NAVBAR ── --}}
    <nav class="navbar">
        <div class="navbar-left">
            <button class="menu-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                <i class="fas fa-bars toggle-icon-expand"></i>
                <i class="fas fa-ellipsis-v toggle-icon-collapse"></i>
            </button>
            <div class="navbar-brand">
                <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Fed Logo" class="brand-logo">
                <span class="brand-name">SK Federations</span>
            </div>
        </div>

        <div class="navbar-search">
            <i class="fas fa-search search-icon"></i>
            <input type="text" placeholder="Search..." aria-label="Search">
        </div>

        <div class="navbar-right">
            <button class="notif-btn" onclick="toggleNotifPopover(event)" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                <span class="notif-badge"></span>
            </button>

            <div class="profile-dropdown-wrapper">
                <button class="profile-btn" onclick="toggleProfileDropdown(event)" aria-label="Profile menu">
                    <img src="{{ $avatar }}" alt="Profile" class="nav-avatar">
                    <span class="nav-name">{{ $user->name ?? 'User' }}</span>
                    <i class="fas fa-chevron-down nav-chevron"></i>
                </button>

                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-dropdown-header">
                        <div class="dd-name">{{ $user->name ?? 'User' }}</div>
                        <div class="dd-email">{{ $user->email ?? '' }}</div>
                    </div>
                    <a href="{{ route('profile') }}" class="dd-item">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="{{ route('password.request') }}" class="dd-item" id="nav-change-pw-link">
                        <i class="fas fa-lock"></i> Change Password
                    </a>
                    <div class="dd-divider"></div>
                    <button class="dd-item danger" onclick="showLogoutModal()">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>
        </div>
    </nav>

    {{-- Notification Popover --}}
    <div class="notif-popover" id="notifPopover">
        <div class="notif-popover-header">
            <h4>Notifications</h4>
            <button class="notif-mark-all">Mark all as read</button>
        </div>
        <div class="notif-list">
            <div class="notif-empty">
                <i class="fas fa-bell-slash" style="font-size:28px; display:block; margin-bottom:8px; opacity:0.3;"></i>
                No notifications yet
            </div>
        </div>
    </div>

    <div class="sidebar-overlay"></div>

    {{-- ── SIDEBAR ── --}}
    <aside class="sidebar">
        {{-- Clickable profile section --}}
        <a href="{{ route('profile') }}" class="sidebar-profile sidebar-profile-link sidebar-profile-active">
            <img src="{{ $avatar }}" alt="Profile" class="sidebar-avatar">
            <div class="sidebar-user-info">
                <div class="s-name">{{ $user->name ?? 'User' }}</div>
                <div class="s-role">{{ $formattedRole }}</div>
            </div>
        </a>

        <nav class="sidebar-nav">
            <div class="menu-section-label">Main</div>

            <a href="{{ route('dashboard') }}" class="menu-item" data-tooltip="Dashboard" id="sidebar-dashboard-link">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            <div class="menu-section-label">Modules</div>

            <a href="{{ route('community-feed') }}" class="menu-item" data-tooltip="SK Community Feed" id="sidebar-community-feed-link">
                <i class="fas fa-rss"></i>
                <span>SK Community Feed</span>
            </a>
            <a href="{{ route('barangay-monitoring') }}" class="menu-item" data-tooltip="Barangay Monitoring">
                <i class="fas fa-map-marker-alt"></i>
                <span>Barangay Monitoring</span>
            </a>
            <a href="#" class="menu-item is-disabled" data-tooltip="Program Monitoring (Temporarily Disabled)" aria-disabled="true" tabindex="-1" onclick="return false;">
                <i class="fas fa-tasks"></i>
                <span>Program Monitoring</span>
            </a>
            <a href="{{ route('kabataan-monitoring') }}" class="menu-item" data-tooltip="Kabataan Monitoring">
                <i class="fas fa-users"></i>
                <span>Kabataan Monitoring</span>
            </a>
            <a href="#" class="menu-item" data-tooltip="Reports">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>

            <div class="menu-divider"></div>

            <button type="button" class="menu-item logout-item" data-tooltip="Logout" onclick="showLogoutModal()">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        </nav>
    </aside>

    {{-- ── MAIN CONTENT ── --}}
    <main class="main-content">
        <div class="profile-container">

            <div class="profile-card">
                <div class="profile-header">
                    <img src="{{ url('/modules/authentication/images/Background.png') }}"
                         alt="" aria-hidden="true"
                         style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:0.08;mix-blend-mode:luminosity;pointer-events:none;">
                    <div class="profile-avatar-container">
                        <img src="{{ $avatar }}" alt="Profile Avatar" class="profile-avatar-large">
                    </div>
                    <h2 class="profile-name">{{ $user->name }}</h2>
                    <p class="profile-email">{{ $user->email }}</p>
                </div>

                <div class="profile-info-section">
                    @if (session('status'))
                        <div class="success-message show">{{ session('status') }}</div>
                    @else
                        <div class="success-message"></div>
                    @endif

                    <h3 class="section-title">Personal Information</h3>

                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Name</span>
                            <span class="info-value">{{ $user->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value">{{ $user->email ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Date of Birth</span>
                            <span class="info-value">{{ optional($officialProfile?->date_of_birth)->format('F d, Y') ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Age</span>
                            <span class="info-value">{{ $officialProfile->age ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Contact Number</span>
                            <span class="info-value">{{ $officialProfile->contact_number ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Position</span>
                            <span class="info-value">{{ $officialProfile->position ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Municipality</span>
                            <span class="info-value">{{ $officialProfile->municipality ?? 'Santa Cruz' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Region</span>
                            <span class="info-value">{{ $officialProfile->region ?? 'IV-A CALABARZON' }}</span>
                        </div>
                        <div class="info-item info-item-full">
                            <span class="info-label">Barangay</span>
                            <span class="info-value">
                                @if ($selectedBarangayId)
                                    {{ $barangays->firstWhere('id', $selectedBarangayId)?->name ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <button type="button" class="btn-edit-profile" onclick="openEditModal()">
                            <i class="fas fa-edit"></i>
                            Edit Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('dashboard::logout-modal')
    @include('profile::edit-profile-modal')

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script src="{{ url('/modules/dashboard/js/dashboard.js') }}"></script>
    <script>
        window.logoutRoute = "{{ route('logout') }}";
        window.loginRoute  = "{{ route('login') }}";
        window.forgotPasswordRoute = "{{ route('password.request') }}";

        document.getElementById('sidebar-dashboard-link')?.addEventListener('click', function(e) {
            e.preventDefault();
            LoadingScreen.show('Loading Dashboard', 'Please wait...');
            setTimeout(() => { window.location.href = this.href; }, 300);
        });

        document.getElementById('nav-change-pw-link')?.addEventListener('click', function(e) {
            e.preventDefault();
            LoadingScreen.show('Loading', 'Please wait...');
            setTimeout(() => { window.location.href = this.href; }, 300);
        });

        (() => {
            const heartbeatMs = Number(document.body.dataset.heartbeatIntervalMs || 30000);
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            let id = null;

            async function beat() {
                try {
                    await fetch("{{ route('skfed.heartbeat') }}", {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                        credentials: 'same-origin',
                        body: JSON.stringify({}),
                    });
                } catch (_) {}
            }

            beat();
            id = setInterval(beat, heartbeatMs);
            window.addEventListener('beforeunload', () => clearInterval(id));
        })();
    </script>
    <script src="{{ url('/modules/profile/js/profile.js') }}"></script>
</body>
</html>
