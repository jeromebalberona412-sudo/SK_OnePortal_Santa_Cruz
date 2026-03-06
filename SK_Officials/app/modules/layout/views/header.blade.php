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

                    <!-- Logout Trigger -->
                    <a href="#" class="dropdown-item" id="logoutTrigger">
                        <span class="dropdown-icon">&#128682;</span>
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