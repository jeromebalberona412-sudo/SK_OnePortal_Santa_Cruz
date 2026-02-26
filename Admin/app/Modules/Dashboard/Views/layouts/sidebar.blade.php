<!-- Sidebar Navigation -->
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <!-- Profile Section -->
            <div class="sidebar-profile-container">
                <div class="sidebar-profile">
                    <div class="admin-profile-avatar">
                        <img src="{{ asset('Images/logo.png') }}" alt="SK OnePortal Logo" class="logo-circle" style="border: 2px solid blue; border-radius: 50%;">
                    </div>
                    <div class="admin-profile-info">
                        <div class="admin-name">{{ $user->name ?? '' }}</div>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <div class="nav">
                <!-- Profile Button -->
                <div class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link profile-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>
                        </svg>
                        Profile
                    </a>
                </div>

                <!-- Add SK Fed Button -->
                <div class="nav-item">
                    <a href="{{ route('add.sk.fed') }}" class="nav-link add-sk-fed-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 4v16m8-8H4"/>
                        </svg>
                        Add SK Federation
                    </a>
                </div>
            </div>
        </div>
    </nav>
</div>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
