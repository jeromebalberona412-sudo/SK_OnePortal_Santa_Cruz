<aside class="main-sidebar" id="mainSidebar">
    <div class="sidebar-content">

        <div class="sidebar-admin">
            
            <div class="admin-avatar">
                <!-- Barangay Logo -->
                @if($barangayLogoUrl)
                    <img src="{{ $barangayLogoUrl }}" alt="{{ $barangayName ?? 'Barangay' }} Logo" class="admin-logo">
                @else
                    <img src="{{ asset('images/SK_OnePortal_logo.png') }}" alt="SK OnePortal Logo" class="admin-logo">
                @endif
            </div>

            <div class="admin-info">
                <span class="admin-name">Sk Officials User</span>
                <span class="admin-role">{{ $barangayName ?? 'Barangay' }}</span>
            </div>

        </div>

        <nav class="sidebar-nav">
            <ul class="nav-list">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="7" r="4"></circle>
                            <path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>
                        </svg>
                        <span class="nav-text">Profile</span>
                    </a>
                </li>

                <!-- Calendar -->
                <li class="nav-item">
                    <a href="{{ route('calendar') }}" class="nav-link {{ request()->routeIs('calendar') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                        </svg>
                        <span class="nav-text">Calendar</span>
                    </a>
                </li>

                <!-- Community Feed -->
                <li class="nav-item">
                    <a href="{{ route('announcements') }}" class="nav-link {{ request()->routeIs('announcements') ? 'active' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <!-- Group of people: 3 persons together -->
                            <circle cx="9" cy="6" r="3"></circle>
                            <path d="M2 21v-2a5 5 0 0 1 10 0v2"></path>
                            <circle cx="18" cy="7" r="2.5"></circle>
                            <path d="M14 21v-1.5a4 4 0 0 1 8 0V21"></path>
                        </svg>
                        <span class="nav-text">Community Feed</span>
                    </a>
                </li>

                <!-- ── Youth Management (Dropdown) ── -->
                <li class="nav-item nav-item-dropdown {{ request()->routeIs('kk-profiling-requests', 'schedule-kk-profiling', 'kabataan', 'previous-kabataan') ? 'open' : '' }}" id="youthManagementDropdown">
                    <a href="#" class="nav-link nav-link-dropdown" id="youthManagementToggleLink">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span class="nav-text">Youth Management</span>
                        <svg class="nav-chevron" id="youthManagementChevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </a>

                    <ul class="nav-submenu" id="youthManagementSubmenu">

                        <!-- Schedule KK Profiling -->
                        <li class="nav-subitem">
                            <a href="{{ route('schedule-kk-profiling') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('schedule-kk-profiling') ? 'active' : '' }}">
                                <span>Schedule KK Profiling</span>
                            </a>
                        </li>

                        <!-- KK Profiling Requests -->
                        <li class="nav-subitem">
                            <a href="{{ route('kk-profiling-requests') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('kk-profiling-requests') ? 'active' : '' }}">
                                <span>KK Profiling Requests</span>
                            </a>
                        </li>

                        <!-- Kabataan -->
                        <li class="nav-subitem">
                            <a href="{{ route('kabataan') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('kabataan') ? 'active' : '' }}">
                                <span>Kabataan</span>
                            </a>
                        </li>

                        <!-- Previous Kabataan -->
                        <li class="nav-subitem">
                            <a href="{{ route('previous-kabataan') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('previous-kabataan') ? 'active' : '' }}">
                                <span>Previous Kabataan</span>
                            </a>
                        </li>

                    </ul>
                </li>

                <!-- ── Planning & Development (Dropdown) ── -->
                <li class="nav-item nav-item-dropdown {{ request()->routeIs('abyip.*', 'committees', 'programs', 'budget-finance', 'schedule-programs') ? 'open' : '' }}" id="planningDevDropdown">
                    <a href="#" class="nav-link nav-link-dropdown" id="planningDevToggleLink">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                            <polyline points="2 17 12 22 22 17"></polyline>
                            <polyline points="2 12 12 17 22 12"></polyline>
                        </svg>
                        <span class="nav-text">Program & Planning</span>
                        <svg class="nav-chevron" id="planningDevChevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </a>

                    <ul class="nav-submenu" id="planningDevSubmenu">

                        <!-- ABYIP -->
                        <li class="nav-subitem">
                            <a href="{{ route('abyip.index') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('abyip.*') ? 'active' : '' }}">
                                <span>ABYIP</span>
                            </a>
                        </li>

                        <!-- Committees -->
                        <li class="nav-subitem">
                            <a href="{{ route('committees') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('committees') ? 'active' : '' }}">
                                <span>Committees</span>
                            </a>
                        </li>

                        <!-- Programs -->
                        <li class="nav-subitem">
                            <a href="{{ route('programs') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('programs') ? 'active' : '' }}">
                                <span>Programs</span>
                            </a>
                        </li>

                        <!-- Budget & Finance -->
                        <li class="nav-subitem">
                            <a href="{{ route('budget-finance') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('budget-finance') ? 'active' : '' }}">
                                <span>Budget &amp; Finance</span>
                            </a>
                        </li>

                        <!-- Programs Management -->
                        <li class="nav-subitem">
                            <a href="{{ route('schedule-programs') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('schedule-programs') ? 'active' : '' }}">
                                <span>Programs Management</span>
                            </a>
                        </li>

                    </ul>
                </li>

                <!-- ── Archived (Dropdown) ── -->
                <li class="nav-item nav-item-dropdown {{ request()->routeIs('deleted-kabataan', 'deleted-abyip', 'rejected-kkprofiling', 'rejected-scholarship') ? 'open' : '' }}" id="archivedDropdown">
                    <a href="#" class="nav-link nav-link-dropdown" id="archivedToggleLink">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="21 8 21 21 3 21 3 8"></polyline>
                            <rect x="1" y="3" width="22" height="5"></rect>
                            <line x1="10" y1="12" x2="14" y2="12"></line>
                        </svg>
                        <span class="nav-text">Archived</span>
                        <svg class="nav-chevron" id="archivedChevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </a>

                    <ul class="nav-submenu" id="archivedSubmenu">

                        <!-- ── Deleted Items group label ── -->
                        <li class="nav-subitem nav-subgroup-label">
                            <span class="nav-subgroup-title">
                                <svg class="nav-subicon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6l-1 14H6L5 6"></path>
                                    <path d="M10 11v6"></path>
                                    <path d="M14 11v6"></path>
                                    <path d="M9 6V4h6v2"></path>
                                </svg>
                                Deleted Items
                            </span>
                        </li>

                        <!-- Deleted Kabataan -->
                        <li class="nav-subitem">
                            <a href="{{ route('deleted-kabataan') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('deleted-kabataan') ? 'active' : '' }}">
                                <svg class="nav-subicon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span>Deleted Kabataan</span>
                            </a>
                        </li>

                        <!-- ── Rejected Items group label ── -->
                        <li class="nav-subitem nav-subgroup-label">
                            <span class="nav-subgroup-title">
                                <svg class="nav-subicon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                </svg>
                                Rejected Items
                            </span>
                        </li>

                        <!-- Rejected KK Profiling -->
                        <li class="nav-subitem">
                            <a href="{{ route('rejected-kkprofiling') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('rejected-kkprofiling') ? 'active' : '' }}">
                                <svg class="nav-subicon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                <span>Rejected KK Profiling</span>
                            </a>
                        </li>

                        <!-- Rejected Scholarship -->
                        <li class="nav-subitem">
                            <a href="{{ route('rejected-scholarship') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('rejected-scholarship') ? 'active' : '' }}">
                                <svg class="nav-subicon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z"/>
                                </svg>
                                <span>Rejected Scholarships</span>
                            </a>
                        </li>

                    </ul>
                </li>

            </ul>
        </nav>

    </div>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
