<aside class="main-sidebar" id="mainSidebar">
    <div class="sidebar-content">

        <div class="sidebar-admin">
            
            <div class="admin-avatar">
                <!-- Admin Logo Image -->
                <img src="{{ asset('images/logo.png') }}" alt="Admin Logo" class="admin-logo">
            </div>

            <div class="admin-info">
                <span class="admin-name">Sk Officials User</span>
                <span class="admin-role">Calios</span>
            </div>

        </div>

        <nav class="sidebar-nav">
            <ul class="nav-list">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">

                        <!-- Clean Dashboard Icon -->
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" 
                             viewBox="0 0 24 24" 
                             fill="none" 
                             stroke="currentColor" 
                             stroke-width="2" 
                             stroke-linecap="round" 
                             stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>

                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>

                <!-- Profile -->
                <li class="nav-item">
                    <a href="{{ route('profile') }}" class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}">

                        <!-- Clean Profile Icon -->
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" 
                             viewBox="0 0 24 24" 
                             fill="none" 
                             stroke="currentColor" 
                             stroke-width="2" 
                             stroke-linecap="round" 
                             stroke-linejoin="round">
                            <circle cx="12" cy="7" r="4"></circle>
                            <path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>
                        </svg>

                        <span class="nav-text">Profile</span>
                    </a>
                </li>

                <!-- Calendar -->
                <li class="nav-item">
                    <a href="{{ route('calendar') }}" class="nav-link {{ request()->routeIs('calendar') ? 'active' : '' }}">

                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                        </svg>

                        <span class="nav-text">Calendar</span>
                    </a>
                </li>

                <!-- Announcements -->
                <li class="nav-item">
                    <a href="{{ route('announcements') }}" class="nav-link {{ request()->routeIs('announcements') ? 'active' : '' }}">

                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M3 11l18-5v6l-18 5z"></path>
                            <path d="M6 21v-5.5"></path>
                        </svg>

                        <span class="nav-text">Announcements</span>
                    </a>
                </li>

                <!-- Committees -->
                <li class="nav-item">
                    <a href="{{ route('committees') }}" class="nav-link {{ request()->routeIs('committees') ? 'active' : '' }}">

                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <circle cx="12" cy="8" r="3"></circle>
                            <path d="M4 21v-1a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v1"></path>
                        </svg>

                        <span class="nav-text">Committees</span>
                    </a>
                </li>

                <!-- Programs -->
                <li class="nav-item">
                    <a href="{{ route('programs') }}" class="nav-link {{ request()->routeIs('programs') ? 'active' : '' }}">

                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                            <path d="M7 8h10"></path>
                            <path d="M7 12h6"></path>
                        </svg>

                        <span class="nav-text">Programs</span>
                    </a>
                </li>

                <!-- Events -->
                <li class="nav-item">
                    <a href="{{ route('events') }}" class="nav-link {{ request()->routeIs('events') ? 'active' : '' }}">

                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                            <path d="M8 4V2"></path>
                            <path d="M16 4V2"></path>
                            <circle cx="12" cy="16" r="3"></circle>
                        </svg>

                        <span class="nav-text">Events</span>
                    </a>
                </li>

                <!-- Kabataan List -->
                <li class="nav-item">
                    <a href="{{ route('kabataan') }}" class="nav-link {{ request()->routeIs('kabataan') ? 'active' : '' }}">

                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>
                            <circle cx="12" cy="9" r="4"></circle>
                        </svg>

                        <span class="nav-text">Kabataan List</span>
                    </a>
                </li>

                <!-- KK Profiling Requests -->
                <li class="nav-item">
                    <a href="{{ route('kk-profiling-requests') }}" class="nav-link {{ request()->routeIs('kk-profiling-requests') ? 'active' : '' }}">

                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                            <path d="M8 10h8"></path>
                            <path d="M8 14h5"></path>
                            <path d="M6 3v2"></path>
                            <path d="M18 3v2"></path>
                        </svg>

                        <span class="nav-text">KK Profiling Requests</span>
                    </a>
                </li>

            </ul>
        </nav>

    </div>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay"></div>