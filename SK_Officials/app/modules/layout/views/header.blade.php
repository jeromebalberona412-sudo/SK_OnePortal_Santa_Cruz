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

        <!-- Right: Notification Bell + User Menu -->
        <div class="header-right">

            <!-- Notification Bell -->
            <div class="notif-menu" id="notifMenu">
                <button class="notification-btn" id="notificationBtn" aria-label="Notifications" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px;">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span class="notification-badge" id="notifBadge">3</span>
                </button>

                <!-- Notification Dropdown -->
                <div class="notif-dropdown" id="notifDropdown">

                    <!-- Header -->
                    <div class="notif-dropdown-header">
                        <div class="notif-dropdown-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            Notifications
                        </div>
                        <button class="notif-mark-all-btn" id="notifMarkAllBtn" title="Mark all as read">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Mark all as read
                        </button>
                    </div>

                    <!-- Notification List -->
                    <div class="notif-list" id="notifList">

                        <div class="notif-item notif-unread" data-id="1">
                            <div class="notif-item-body">
                                <div class="notif-item-title">SK Federation General Assembly</div>
                                <div class="notif-item-text">All SK officials are required to attend the General Assembly on May 5, 2026 at 9:00 AM.</div>
                                <div class="notif-item-time">9:12 AM · Apr 24, 2026</div>
                            </div>
                            <span class="notif-unread-dot"></span>
                        </div>

                        <div class="notif-item notif-unread" data-id="2">
                            <div class="notif-item-body">
                                <div class="notif-item-title">Committee Meeting — Education</div>
                                <div class="notif-item-text">Education Committee meeting scheduled for May 10, 2026 at 2:00 PM. Attendance is required.</div>
                                <div class="notif-item-time">8:05 AM · Apr 24, 2026</div>
                            </div>
                            <span class="notif-unread-dot"></span>
                        </div>

                        <div class="notif-item notif-unread" data-id="3">
                            <div class="notif-item-body">
                                <div class="notif-item-title">KK Profiling Deadline Reminder</div>
                                <div class="notif-item-text">KK Profiling submission deadline is on May 15, 2026. Please submit all pending requests.</div>
                                <div class="notif-item-time">6:30 AM · Apr 24, 2026</div>
                            </div>
                            <span class="notif-unread-dot"></span>
                        </div>

                        <div class="notif-item" data-id="4">
                            <div class="notif-item-body">
                                <div class="notif-item-title">Youth Sports Festival Registration</div>
                                <div class="notif-item-text">Registration for the Youth Sports Festival is now open. Register your team before April 30.</div>
                                <div class="notif-item-time">3:45 PM · Apr 23, 2026</div>
                            </div>
                        </div>

                        <div class="notif-item" data-id="5">
                            <div class="notif-item-body">
                                <div class="notif-item-title">Budget Review — Q2 2026</div>
                                <div class="notif-item-text">Q2 budget review has been approved. Updated allocation is now available in Budget &amp; Finance.</div>
                                <div class="notif-item-time">10:20 AM · Apr 22, 2026</div>
                            </div>
                        </div>

                    </div><!-- /notif-list -->

                    <!-- Empty state (hidden by default) -->
                    <div class="notif-empty" id="notifEmpty" style="display:none;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <p>You're all caught up!</p>
                    </div>

                    <!-- See All Notifications -->
                    <div class="notif-dropdown-footer">
                        <a href="{{ route('notifications') }}" class="notif-see-all-btn">
                            See All Notifications
                        </a>
                    </div>

                </div><!-- /notif-dropdown -->
            </div><!-- /notif-menu -->

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
                    <a href="{{ route('change-password') }}" class="dropdown-item" id="changePasswordTrigger">
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