<header class="main-header">
    <div class="header-container">

        <!-- Left: Sidebar Toggle + Logo -->
        <div class="header-left">
            <button class="sidebar-toggle" id="sidebarToggle">
                <span class="hamburger-icon">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </span>
                <svg class="x-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>

            <div class="logo">
                <img src="{{ asset('images/logo.png') }}" 
                     alt="SK Officials Logo" 
                     class="logo-img">
                <span class="logo-text">SK Officials Portal</span>
            </div>
        </div>

        <!-- Right: User Menu -->
        <div class="header-right">
            <div class="user-menu">
                <button class="user-menu-btn" id="userMenuToggle">
                    <img src="{{ asset('images/logo.png') }}" 
                         alt="User Avatar" 
                         class="user-avatar">
                    <span class="dropdown-arrow">&#9662;</span>
                </button>

                <div class="user-dropdown" id="userDropdown">

                    <!-- User Card -->
                    <div class="dropdown-user-card">
                        <img src="{{ asset('images/logo.png') }}" alt="User Avatar" class="dropdown-user-avatar">
                        <div class="dropdown-user-info">
                            <span class="dropdown-user-name">SK Officials User</span>
                            <span class="dropdown-user-role">Calios</span>
                        </div>
                    </div>

                    <div class="dropdown-divider"></div>

                    <!-- View Profile -->
                    <a href="{{ route('profile') }}" class="dropdown-item">
                        <span class="dropdown-item-icon dropdown-item-icon--profile">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="7" r="4"></circle>
                                <path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>
                            </svg>
                        </span>
                        View Profile
                    </a>

                    <!-- Change Password -->
                    <a href="#" class="dropdown-item" id="changePasswordTrigger">
                        <span class="dropdown-item-icon dropdown-item-icon--password">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        Change Password
                    </a>

                    <div class="dropdown-divider"></div>

                    <!-- Logout -->
                    <a href="#" class="dropdown-item dropdown-item--logout" id="logoutTrigger">
                        <span class="dropdown-item-icon dropdown-item-icon--logout">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                        </span>
                        Logout
                    </a>

                    <!-- Hidden Logout Form -->
                    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">
                        @csrf
                    </form>

                </div>
            </div>
        </div>

    </div>
</header>

<!-- ================= LOGOUT MODAL ================= -->
<div class="logout-modal" id="logoutModal" style="display:none;">
    <div class="logout-overlay"></div>
    <div class="logout-box">
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to logout?</p>
        <div class="logout-actions">
            <button id="cancelLogout" class="btn-cancel">No</button>
            <button id="confirmLogout" class="btn-confirm">Yes</button>
        </div>
    </div>
</div>