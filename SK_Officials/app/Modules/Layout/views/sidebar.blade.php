<aside class="main-sidebar" id="mainSidebar">
    <div class="sidebar-content">

        <div class="sidebar-admin">
            
            <div class="admin-avatar">
                <img src="{{ $userAvatarUrl }}" alt="{{ $userAvatarAlt }}" class="admin-logo">
            </div>

            <div class="admin-info">
                <span class="admin-name">{{ $userDisplayName }}</span>
                <span class="admin-role">{{ $barangayName ?? 'Barangay' }}</span>
            </div>

        </div>

        <nav class="sidebar-nav">
            <ul class="nav-list">

                <!-- Home -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-no-loading>
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>

                <!-- Profile -->
                <li class="nav-item">
                    <a href="{{ route('profile') }}" class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}" data-no-loading>
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="7" r="4"></circle>
                            <path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>
                        </svg>
                        <span class="nav-text">Profile</span>
                    </a>
                </li>

                <!-- Calendar -->
                <li class="nav-item">
                    <a href="{{ route('calendar') }}" class="nav-link {{ request()->routeIs('calendar') ? 'active' : '' }}" data-no-loading>
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
                    <a href="{{ route('community-feed.index') }}" class="nav-link {{ request()->routeIs('community-feed.index') ? 'active' : '' }}" data-no-loading>
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
                <li class="nav-item nav-item-dropdown {{ request()->routeIs('kk-profiling-requests', 'schedule-kk-profiling', 'kabataan') ? 'open' : '' }}" id="youthManagementDropdown">
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
                            <a href="{{ route('schedule-kk-profiling') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('schedule-kk-profiling') ? 'active' : '' }}" data-no-loading>
                                <span>Schedule KK Profiling</span>
                            </a>
                        </li>

                        <!-- KK Profiling Requests -->
                        <li class="nav-subitem">
                            <a href="{{ route('kk-profiling-requests') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('kk-profiling-requests') ? 'active' : '' }}" data-no-loading>
                                <span>KK Profiling Requests</span>
                            </a>
                        </li>

                        <!-- Kabataan -->
                        <li class="nav-subitem">
                            <a href="{{ route('kabataan') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('kabataan') ? 'active' : '' }}" data-no-loading>
                                <span>Kabataan</span>
                            </a>
                        </li>

                        {{-- Previous Kabataan (hidden from sidebar) --}}
                        {{--
                        <li class="nav-subitem">
                            <a href="{{ route('previous-kabataan') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('previous-kabataan') ? 'active' : '' }}" data-no-loading>
                                <span>Previous Kabataan</span>
                            </a>
                        </li>
                        --}}

                    </ul>
                </li>

                <!-- ── Planning & Development (Dropdown) ── -->
                <li class="nav-item nav-item-dropdown {{ request()->routeIs('abyip.*', 'program-accomplishment.*', 'committees', 'programs', 'schedule-programs', 'schedule-programs.sports-application-form', 'sports-application-form', 'sports-programs.archived', '*.survey.*') ? 'open' : '' }}" id="planningDevDropdown">
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
                            <a href="{{ route('abyip.index') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('abyip.*') ? 'active' : '' }}" data-no-loading>
                                <span>ABYIP</span>
                            </a>
                        </li>

                        <!-- Program Accomplishments -->
                        <li class="nav-subitem">
                            <a href="{{ route('program-accomplishment.index') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('program-accomplishment.*') ? 'active' : '' }}" data-no-loading>
                                <span>Program Accomplishments</span>
                            </a>
                        </li>

                        <!-- Committees -->
                        <li class="nav-subitem">
                            <a href="{{ route('committees') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('committees') ? 'active' : '' }}" data-no-loading>
                                <span>Committees</span>
                            </a>
                        </li>

                        <!-- Programs -->
                        <li class="nav-subitem">
                            <a href="{{ route('programs') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('programs') ? 'active' : '' }}" data-no-loading>
                                <span>Programs</span>
                            </a>
                        </li>

                        <!-- Programs Management -->
                        <li class="nav-subitem">
                            <a href="{{ route('schedule-programs') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('schedule-programs', '*.survey.*') ? 'active' : '' }}" data-no-loading>
                                <span>Programs Management</span>
                            </a>
                        </li>

                    </ul>
                </li>

                <!-- ── Archived (Dropdown) ── -->
                <li class="nav-item nav-item-dropdown {{ request()->routeIs('archived-youth-records', 'deleted-abyip', 'rejected-kkprofiling', 'rejected-scholars', 'rejected-scholarship', 'rejected-sports', 'community-feed.archive', 'sports-programs.archived') ? 'open' : '' }}" id="archivedDropdown">
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

                        <!-- ── Aged-out youth ── -->
                        <li class="nav-subitem nav-subgroup-label">
                            <span class="nav-subgroup-title">
                                <svg class="nav-subicon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                Aged-Out Youth
                            </span>
                        </li>

                        <li class="nav-subitem">
                            <a href="{{ route('archived-youth-records') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('archived-youth-records') ? 'active' : '' }}" data-no-loading>
                                <span>Archived Youth Records</span>
                            </a>
                        </li>

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

                        <!-- Archived Sports Programs -->
                        <li class="nav-subitem">
                            <a href="{{ route('sports-programs.archived') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('sports-programs.archived') ? 'active' : '' }}" data-no-loading>
                                <span>Archived Sports Programs</span>
                            </a>
                        </li>

                        <!-- Deleted Posts -->
                        <li class="nav-subitem">
                            <a href="{{ route('community-feed.archive') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('community-feed.archive') ? 'active' : '' }}" data-no-loading>
                                <span>Deleted Posts</span>
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
                            <a href="{{ route('rejected-kkprofiling') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('rejected-kkprofiling') ? 'active' : '' }}" data-no-loading>
                                <span>Rejected KK Profiling</span>
                            </a>
                        </li>

                        <!-- Rejected Scholarship -->
                        <li class="nav-subitem">
                            <a href="{{ route('rejected-scholars') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('rejected-scholars', 'rejected-scholarship') ? 'active' : '' }}" data-no-loading>
                                <span>Rejected Scholarships</span>
                            </a>
                        </li>

                        <!-- Rejected Sports Applications -->
                        <li class="nav-subitem">
                            <a href="{{ route('rejected-sports') }}" class="nav-sublink nav-sublink-child {{ request()->routeIs('rejected-sports') ? 'active' : '' }}" data-no-loading>
                                <span>Rejected Sports</span>
                            </a>
                        </li>

                    </ul>
                </li>

            </ul>
        </nav>

    </div>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
