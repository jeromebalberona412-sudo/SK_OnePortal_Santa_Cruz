<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings - SK OnePortal</title>
    @vite([
        'app/Modules/Profile/assets/css/settings.css',
        'app/Modules/Dashboard/assets/css/chatbot.css',
        'app/Modules/Dashboard/assets/js/chatbot.js',
        'app/Modules/Dashboard/assets/css/notif.css',
        'app/Modules/Dashboard/assets/js/notif.js',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])
</head>
<body class="youth-settings">
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

    <!-- Settings Main -->
    <main class="settings-main">
        <div class="settings-container">

            <!-- Left Sidebar -->
            <aside class="settings-sidebar">
                <div class="settings-sidebar-header">
                    <h2>
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                        </svg>
                        Settings
                    </h2>
                </div>
                <nav class="settings-nav">
                    <!-- Password & Security -->
                    <button class="settings-nav-item active" data-section="password">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        Password &amp; Security
                    </button>
                    {{-- Future options go here --}}
                </nav>
            </aside>

            <!-- Right Content -->
            <section class="settings-content">

                <!-- Password & Security Section -->
                <div id="section-password">
                    <div class="settings-content-header">
                        <div class="settings-content-icon">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="settings-content-header-text">
                            <h2>Password &amp; Security</h2>
                            <p>Update your password to keep your account secure.</p>
                        </div>
                    </div>

                    <div class="settings-content-body">

                        @if(session('success'))
                            <div class="alert alert-success">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-error">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                {{ session('error') }}
                            </div>
                        @endif

                        <form class="password-form" id="passwordForm" method="POST" action="{{ route('change-password') }}">
                            @csrf

                            <!-- Current Password -->
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <div class="input-wrapper">
                                    <input type="password" id="current_password" name="current_password"
                                           placeholder="Enter your current password" autocomplete="current-password">
                                    <button type="button" class="toggle-password" onclick="togglePw('current_password', this)" aria-label="Toggle password visibility">
                                        <svg viewBox="0 0 20 20" fill="currentColor" class="eye-icon">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="form-divider"></div>

                            <!-- New Password -->
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <div class="input-wrapper">
                                    <input type="password" id="new_password" name="new_password"
                                           placeholder="Enter new password" autocomplete="new-password"
                                           oninput="checkStrength(this.value)">
                                    <button type="button" class="toggle-password" onclick="togglePw('new_password', this)" aria-label="Toggle password visibility">
                                        <svg viewBox="0 0 20 20" fill="currentColor" class="eye-icon">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="password-strength">
                                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                                    <span class="strength-label" id="strengthLabel"></span>
                                </div>
                                <span class="form-hint">At least 8 characters with a mix of letters and numbers.</span>
                            </div>

                            <!-- Confirm New Password -->
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <div class="input-wrapper">
                                    <input type="password" id="confirm_password" name="confirm_password"
                                           placeholder="Re-enter new password" autocomplete="new-password">
                                    <button type="button" class="toggle-password" onclick="togglePw('confirm_password', this)" aria-label="Toggle password visibility">
                                        <svg viewBox="0 0 20 20" fill="currentColor" class="eye-icon">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn-primary" id="savePasswordBtn">Save Changes</button>
                                <button type="button" class="btn-secondary" onclick="resetForm()">Cancel</button>
                            </div>
                        </form>

                    </div>
                </div>

            </section>
        </div>
    </main>

    <!-- Password Change Success Modal -->
    <div id="passwordSuccessModal" style="display:none;position:fixed;inset:0;z-index:2000;align-items:center;justify-content:center;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);" onclick="closePasswordSuccessModal()"></div>
        <div style="position:relative;background:white;border-radius:20px;max-width:400px;width:90%;padding:48px 32px;text-align:center;animation:modalSlideIn 0.3s ease;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <div style="width:72px;height:72px;background:linear-gradient(135deg,#22c55e,#16a34a);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 8px 24px rgba(34,197,94,0.35);">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width:36px;height:36px;color:white;">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </div>
            <h3 style="font-size:22px;font-weight:700;color:#1e293b;margin-bottom:10px;">Password Changed Successfully!</h3>
            <p style="color:#64748b;font-size:14px;line-height:1.6;margin-bottom:28px;">Your password has been updated. Keep it safe!</p>
            <button onclick="closePasswordSuccessModal()" style="padding:12px 32px;background:linear-gradient(135deg,#0450a8,#0d5fc4);color:white;border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;box-shadow:0 4px 12px rgba(4,80,168,0.3);font-family:inherit;">OK</button>
        </div>
    </div>

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

    <script>
    // Toggle show/hide password
    function togglePw(inputId, btn) {
        const input = document.getElementById(inputId);
        const isText = input.type === 'text';
        input.type = isText ? 'password' : 'text';
        btn.querySelector('.eye-icon').style.opacity = isText ? '1' : '0.4';
    }

    // Password strength checker
    function checkStrength(val) {
        const fill  = document.getElementById('strengthFill');
        const label = document.getElementById('strengthLabel');
        if (!val) { fill.style.width = '0%'; label.textContent = ''; return; }

        let score = 0;
        if (val.length >= 8)  score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = [
            { w: '25%', bg: '#ef4444', text: 'Weak' },
            { w: '50%', bg: '#f97316', text: 'Fair' },
            { w: '75%', bg: '#eab308', text: 'Good' },
            { w: '100%', bg: '#22c55e', text: 'Strong' },
        ];
        const lvl = levels[score - 1] || levels[0];
        fill.style.width      = lvl.w;
        fill.style.background = lvl.bg;
        label.textContent     = lvl.text;
        label.style.color     = lvl.bg;
    }

    // ── Per-field error helpers ───────────────────────────────────────────────
    function setFieldError(inputId, message) {
        const input = document.getElementById(inputId);
        input.classList.add('input-error');
        let msg = input.closest('.form-group').querySelector('.field-error-msg');
        if (!msg) {
            msg = document.createElement('span');
            msg.className = 'field-error-msg';
            input.closest('.input-wrapper').after(msg);
        }
        msg.textContent = message;
    }

    function clearFieldError(inputId) {
        const input = document.getElementById(inputId);
        input.classList.remove('input-error');
        input.closest('.form-group').querySelector('.field-error-msg')?.remove();
    }

    function clearAllFieldErrors() {
        ['current_password', 'new_password', 'confirm_password'].forEach(clearFieldError);
    }

    // Clear error on typing
    ['current_password', 'new_password', 'confirm_password'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', () => clearFieldError(id));
    });

    // Form submit handler
    document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        clearAllFieldErrors();

        const current = document.getElementById('current_password').value.trim();
        const newPw   = document.getElementById('new_password').value.trim();
        const confirm = document.getElementById('confirm_password').value.trim();

        let hasError = false;

        if (!current) {
            setFieldError('current_password', 'Please enter your current password.');
            hasError = true;
        }
        if (!newPw) {
            setFieldError('new_password', 'Please enter a new password.');
            hasError = true;
        } else if (newPw.length < 8) {
            setFieldError('new_password', 'Password must be at least 8 characters.');
            hasError = true;
        }
        if (!confirm) {
            setFieldError('confirm_password', 'Please confirm your new password.');
            hasError = true;
        } else if (newPw && newPw !== confirm) {
            setFieldError('confirm_password', "Password doesn't match.");
            hasError = true;
        }

        if (hasError) return;

        // Show loading and submit
        if (window.showLoading) showLoading('Updating password');
        this.submit();
    });

    function handlePasswordChange(e) {
        e.preventDefault();
        clearAllFieldErrors();

        const current = document.getElementById('current_password').value.trim();
        const newPw   = document.getElementById('new_password').value.trim();
        const confirm = document.getElementById('confirm_password').value.trim();

        let hasError = false;

        if (!current) {
            setFieldError('current_password', 'Please enter your current password.');
            hasError = true;
        }
        if (!newPw) {
            setFieldError('new_password', 'Please enter a new password.');
            hasError = true;
        } else if (newPw.length < 8) {
            setFieldError('new_password', 'Password must be at least 8 characters.');
            hasError = true;
        }
        if (!confirm) {
            setFieldError('confirm_password', 'Please confirm your new password.');
            hasError = true;
        } else if (newPw && newPw !== confirm) {
            setFieldError('confirm_password', "Password doesn't match.");
            hasError = true;
        }

        if (hasError) return;

        // Success
        document.getElementById('passwordForm').reset();
        document.getElementById('strengthFill').style.width = '0%';
        document.getElementById('strengthLabel').textContent = '';
        showPasswordSuccessModal();
    }

    function resetForm() {
        document.getElementById('passwordForm').reset();
        document.getElementById('strengthFill').style.width = '0%';
        document.getElementById('strengthLabel').textContent = '';
        clearAllFieldErrors();
    }

    // Sidebar nav switching (for future sections)
    document.querySelectorAll('.settings-nav-item').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.settings-nav-item').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    function showPasswordSuccessModal() {
        const modal = document.getElementById('passwordSuccessModal');
        modal.style.display = 'flex';
    }

    function closePasswordSuccessModal() {
        document.getElementById('passwordSuccessModal').style.display = 'none';
    }

    // ── Logout Modal ──────────────────────────────────────────────────────────
    (function () {
        const logoutBtn  = document.querySelector('.logout-btn');
        const logoutForm = logoutBtn?.closest('form');
        const modal      = document.getElementById('logoutConfirmModal');
        const confirmBtn = document.getElementById('confirmLogoutBtn');

        logoutBtn?.addEventListener('click', e => {
            e.preventDefault();
            modal.style.display = 'flex';
        });

        confirmBtn?.addEventListener('click', () => logoutForm?.submit());

        modal?.querySelector('div')?.addEventListener('click', closeLogoutModal);
    })();

    function closeLogoutModal() {
        document.getElementById('logoutConfirmModal').style.display = 'none';
    }
    </script>

</body>
</html>
