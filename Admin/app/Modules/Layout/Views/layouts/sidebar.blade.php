<!-- Sidebar Navigation -->
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <!-- Mobile Close Button -->
            <button class="sidebar-close-btn" id="sidebarCloseBtn" onclick="toggleSidebar()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            
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
                <!-- Dashboard Button -->
                <div class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link dashboard-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"/>
                            <rect x="14" y="3" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/>
                        </svg>
                        Dashboard
                    </a>
                </div>
                
                <!-- Profile Button -->
                <div class="nav-item">
                    <a href="{{ route('profile') }}" class="nav-link profile-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>
                        </svg>
                        Profile
                    </a>
                </div>
                
                <!-- Add SK Fed Button -->
                <div class="nav-item">
                    <a href="{{ route('accounts.manage') }}" class="nav-link manage-account-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 4v16m8-8H4"/>
                        </svg>
                        Manage Account
                    </a>
                </div>
                
                <!-- Audit Logs Button -->
                <div class="nav-item">
                    <a href="{{ route('auditlogs.index') }}" class="nav-link auditlogs-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14,2 14,8 20,8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10,9 9,9 8,9"/>
                        </svg>
                        Audit Logs
                    </a>
                </div>
            </div>
        </div>
    </nav>
</div>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>