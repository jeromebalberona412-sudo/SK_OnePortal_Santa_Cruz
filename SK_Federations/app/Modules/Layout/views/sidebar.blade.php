@php
    $archiveOpen = request()->routeIs('archive');
@endphp

<aside class="sidebar">
    <a href="{{ route('profile') }}" class="sidebar-profile sidebar-profile-link" id="sidebar-profile-link">
        <img src="{{ $avatar }}" alt="Profile" class="sidebar-avatar">
        <div class="sidebar-user-info">
            <div class="s-name">{{ $user->name ?? 'User' }}</div>
            <div class="s-role">{{ $formattedRole }}</div>
        </div>
    </a>

    <nav class="sidebar-nav">
        <div class="menu-section-label">Main</div>

        <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-tooltip="Dashboard" id="sidebar-dashboard-link">
            <i class="fas fa-home"></i><span>Dashboard</span>
        </a>

        <a href="{{ route('calendar') }}" class="menu-item {{ request()->routeIs('calendar') ? 'active' : '' }}" data-tooltip="Calendar">
            <i class="fas fa-calendar-alt"></i><span>Calendar</span>
        </a>

        <a href="{{ route('profile') }}" class="menu-item {{ request()->routeIs('profile') ? 'active' : '' }}" data-tooltip="Profile">
            <i class="fas fa-user"></i><span>Profile</span>
        </a>

        <div class="menu-section-label">Modules</div>

        <a href="{{ route('community-feed') }}" class="menu-item {{ request()->routeIs('community-feed', 'sk-fed-profile', 'skfed.barangay-profile') ? 'active' : '' }}" data-tooltip="SK Community Feed" id="sidebar-community-feed-link">
            <i class="fas fa-rss"></i><span>SK Community Feed</span>
        </a>

        <a href="{{ route('barangay-monitoring') }}" class="menu-item {{ request()->routeIs('barangay-monitoring*') ? 'active' : '' }}" data-tooltip="Barangay Monitoring">
            <i class="fas fa-map-marker-alt"></i><span>Barangay Monitoring</span>
        </a>

        <a href="{{ route('kabataan-monitoring') }}" class="menu-item {{ request()->routeIs('kabataan-monitoring*') ? 'active' : '' }}" data-tooltip="Kabataan Monitoring">
            <i class="fas fa-users"></i><span>Kabataan Monitoring</span>
        </a>

        <a href="{{ route('reports') }}" class="menu-item {{ request()->routeIs('reports') ? 'active' : '' }}" data-tooltip="Reports">
            <i class="fas fa-chart-bar"></i><span>Reports</span>
        </a>

        <a href="{{ route('barangay.abyip') }}" class="menu-item {{ request()->routeIs('barangay.abyip') ? 'active' : '' }}" data-tooltip="Barangay ABYIP">
            <i class="fas fa-file-invoice-dollar"></i><span>Barangay ABYIP</span>
        </a>

        <a href="javascript:void(0);" class="menu-item {{ $archiveOpen ? 'active' : '' }}" onclick="toggleArchiveMenu(event)" data-tooltip="Archive">
            <i class="fas fa-archive"></i><span>Archive</span>
            <i class="fas fa-chevron-down" id="archiveChevron" style="margin-left:auto;font-size:12px;transition:transform 0.3s ease;transform:rotate({{ $archiveOpen ? '180deg' : '0deg' }});"></i>
        </a>

        <div id="archiveSubmenu" style="display:{{ $archiveOpen ? 'block' : 'none' }};padding-left:20px;border-left:2px solid #e2e8f0;margin-left:10px;">
            <a href="{{ route('archive') }}" class="menu-item {{ request()->routeIs('archive') ? 'active' : '' }}" style="font-size:13px;">
                <i class="fas fa-trash"></i><span>Deleted Reports</span>
            </a>
            <a href="{{ route('archive') }}" class="menu-item {{ request()->routeIs('archive') ? 'active' : '' }}" style="font-size:13px;">
                <i class="fas fa-box"></i><span>Archived Reports</span>
            </a>
        </div>

        <div class="menu-divider"></div>
    </nav>
</aside>
