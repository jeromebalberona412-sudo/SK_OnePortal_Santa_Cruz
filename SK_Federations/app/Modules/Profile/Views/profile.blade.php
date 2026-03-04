<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Dashboard - SK Federation</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/modules/dashboard/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('/modules/profile/css/profile.css') }}">
</head>
<body
    data-heartbeat-interval-ms="{{ (int) config('sk_fed_auth.single_session.heartbeat_interval_seconds', 30) * 1000 }}"
    data-has-password-errors="{{ $errors->has('current_password') || $errors->has('password') ? '1' : '0' }}"
>
    <script>
        // Prevent back navigation after logout
        (function() {
            window.history.pushState(null, "", window.location.href);
            window.onpopstate = function() {
                window.history.pushState(null, "", window.location.href);
            };
        })();
    </script>
    @php
        $avatar = 'https://ui-avatars.com/api/?name=' . urlencode((string) ($user->name ?? 'User')) . '&background=213F99&color=fff&size=120';
        $formattedRole = $user->role ? strtoupper(str_replace('_', ' ', (string) $user->role)) : 'N/A';
        $selectedBarangayId = old('barangay_id', $user->barangay_id);
    @endphp

    <nav class="navbar">
        <div class="navbar-left">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="navbar-brand">SK FEDERATION</div>
        </div>
        <div class="navbar-right">
            <a href="{{ route('profile') }}" class="user-profile">
                <img src="{{ $avatar }}" alt="User Avatar" class="user-avatar">
                <div class="user-info">
                    <div class="user-name">{{ $user->name }}</div>
                    <div class="user-role">{{ $formattedRole }}</div>
                </div>
            </a>
        </div>
    </nav>

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
            <button type="button" onclick="showLogoutModal()" class="menu-item" style="width: 100%; text-align: left; background: none; cursor: pointer; border: none; color: inherit; font: inherit;">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        </nav>
    </aside>

    <main class="main-content">
        <div class="profile-container">
            <div class="page-header">
                <h1>My Profile</h1>
                <p>View your personal information and account details</p>
            </div>

            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar-container">
                        <img src="{{ $avatar }}" alt="Profile Avatar" class="profile-avatar-large">
                    </div>
                    <h2 class="profile-name">{{ $user->name }}</h2>
                    <p class="profile-email">{{ $user->email }}</p>
                </div>

                <div class="profile-tabs">
                    <button type="button" class="tab-btn" onclick="switchTab('personal-info')">Profile Information</button>
                    <button type="button" class="tab-btn" onclick="switchTab('change-password')">Change Password</button>
                </div>

                <div id="personal-info" class="tab-content">
                    @if ($errors->has('profile'))
                        <div class="success-message show">{{ $errors->first('profile') }}</div>
                    @endif
                    @if (session('status'))
                        <div class="success-message show">{{ session('status') }}</div>
                    @else
                        <div class="success-message"></div>
                    @endif

                    <form onsubmit="return false;">
                        <div class="form-section">
                            <h3 class="form-section-title">Personal Information</h3>

                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" readonly>
                                @error('name')
                                    <small style="color: #d0242b;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" readonly>
                                @error('email')
                                    <small style="color: #d0242b;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="date_of_birth">Date of Birth</label>
                                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', optional($officialProfile?->date_of_birth)->format('Y-m-d')) }}" readonly>
                                    @error('date_of_birth')
                                        <small style="color: #d0242b;">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="age">Age</label>
                                    <input type="number" id="age" name="age" class="form-control" value="{{ old('age', $officialProfile->age ?? '') }}" readonly>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contact_number">Contact number</label>
                                    <input type="text" id="contact_number" name="contact_number" class="form-control" value="{{ old('contact_number', $officialProfile->contact_number ?? '') }}" maxlength="20" readonly>
                                    @error('contact_number')
                                        <small style="color: #d0242b;">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="position">Position</label>
                                    <select id="position" name="position" class="form-control" disabled>
                                        <option value="">Select Position</option>
                                        @foreach (\App\Modules\Profile\Models\OfficialProfile::POSITIONS as $position)
                                            <option value="{{ $position }}" {{ old('position', $officialProfile->position ?? '') === $position ? 'selected' : '' }}>{{ $position }}</option>
                                        @endforeach
                                    </select>
                                    @error('position')
                                        <small style="color: #d0242b;">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="municipality">Municipality</label>
                                    <input type="text" id="municipality" name="municipality" class="form-control" value="{{ old('municipality', $officialProfile->municipality ?? 'Santa Cruz') }}" readonly>
                                    @error('municipality')
                                        <small style="color: #d0242b;">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="region">Region</label>
                                    <input type="text" id="region" name="region" class="form-control" value="{{ old('region', $officialProfile->region ?? 'IV-A CALABARZON') }}" readonly>
                                    @error('region')
                                        <small style="color: #d0242b;">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="barangay_id">Barangay</label>
                                <select id="barangay_id" name="barangay_id" class="form-control" disabled>
                                    <option value="">Select Barangay</option>
                                    @foreach ($barangays as $barangay)
                                        <option value="{{ $barangay->id }}" {{ (string) $selectedBarangayId === (string) $barangay->id ? 'selected' : '' }}>{{ $barangay->name }}</option>
                                    @endforeach
                                </select>
                                @error('barangay_id')
                                    <small style="color: #d0242b;">{{ $message }}</small>
                                @enderror
                                @if ($barangays->isEmpty())
                                    <small style="color: #64748b;">No barangay records available in database.</small>
                                @endif
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn btn-primary" onclick="openEditModal()">
                                <i class="fas fa-edit"></i>
                                Edit Profile
                            </button>
                        </div>
                    </form>
                </div>

                @include('profile::change-password-form')
            </div>
        </div>
    </main>

    @include('dashboard::logout-modal')

    @include('profile::edit-profile-modal')

    <script src="{{ url('/modules/dashboard/js/dashboard.js') }}"></script>
    <script>
        // Set route variables for JavaScript
        window.logoutRoute = "{{ route('logout') }}";
        window.forgotPasswordRoute = "{{ route('password.request') }}";

        (() => {
            const heartbeatIntervalMs = Number(document.body.dataset.heartbeatIntervalMs || 30000);
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

            const hasPasswordErrors = document.body.dataset.hasPasswordErrors === '1';
            if (hasPasswordErrors) {
                switchTab('change-password');
            }

        })();
    </script>
    <script src="{{ url('/modules/profile/js/profile.js') }}"></script>
</body>
</html>
