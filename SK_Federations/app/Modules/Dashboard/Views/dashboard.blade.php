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
            <a href="{{ route('profile') }}" class="menu-item">
                <i class="fas fa-user"></i>
                <span>My Profile</span>
            </a>
            <a href="{{ route('dashboard') }}" class="menu-item active">
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
                <h1>Dashboard</h1>
                <p>Welcome to SK Federation Portal</p>
            </div>


            <div class="profile-card" style="min-height: 400px; display: flex; align-items: center; justify-content: center;">
                <div style="text-align: center; color: #64748b;">
                    <i class="fas fa-chart-line" style="font-size: 64px; margin-bottom: 20px; opacity: 0.3;"></i>
                    <h2 style="font-size: 24px; margin-bottom: 10px; color: #1e293b;">Dashboard Coming Soon</h2>
                    <p style="font-size: 16px;">Wala Muna.</p>
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
