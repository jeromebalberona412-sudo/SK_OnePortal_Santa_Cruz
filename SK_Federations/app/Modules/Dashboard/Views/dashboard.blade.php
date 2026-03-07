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
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>
    <script>
        // Prevent back navigation after logout
        (function() {
            window.history.pushState(null, "", window.location.href);
            window.onpopstate = function() {
                window.history.pushState(null, "", window.location.href);
            };
        })();
    </script>
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
            <button type="button" onclick="showLogoutModal()" class="menu-item" style="width: 100%; text-align: left; background: none; cursor: pointer; border: none; color: inherit; font: inherit;">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
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

    @include('dashboard::logout-modal')

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script src="{{ url('/modules/dashboard/js/dashboard.js') }}"></script>
    <script>
        // Show loading when navigating to profile (both sidebar and navbar)
        document.querySelectorAll('a[href="{{ route('profile') }}"]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                LoadingScreen.show('Loading Profile', 'Please wait...');
                setTimeout(() => {
                    window.location.href = this.href;
                }, 300);
            });
        });
    </script>
    <style>
        /* Modal Base Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            padding: 20px;
        }

        .modal-content {
            background-color: #ffffff;
            border-radius: 16px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .modal-close-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            font-size: 20px;
            color: #94a3b8;
            cursor: pointer;
            padding: 8px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
            z-index: 10;
        }

        .modal-close-icon:hover {
            background-color: #f1f5f9;
            color: #475569;
        }

        .btn-cancel-modern {
            background: #f1f5f9;
            color: #475569;
            border: none;
            padding: 14px 32px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            min-width: 120px;
        }

        .btn-cancel-modern:hover {
            background: #e2e8f0;
            transform: translateY(-1px);
        }
    </style>
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
