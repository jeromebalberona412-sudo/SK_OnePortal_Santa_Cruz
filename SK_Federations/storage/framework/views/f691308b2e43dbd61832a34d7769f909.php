<?php
    $archiveReportsOpen = request()->routeIs('archive');
    $archiveManagementOpen = request()->routeIs('archived.*');
    $accountsOpen = request()->routeIs('accounts.*');
    $isFederationAccountsActive = request()->routeIs('accounts.federation.index') || (request()->routeIs('accounts.manage') && (request('account_type', 'sk_federation') === 'sk_federation'));
    $isOfficialsAccountsActive = request()->routeIs('accounts.officials.index');
?>

<aside class="sidebar">
    <nav class="sidebar-nav">
        <a href="<?php echo e(route('profile')); ?>" class="sidebar-profile sidebar-profile-link" id="sidebar-profile-link">
            <img src="<?php echo e($avatar); ?>" alt="Profile" class="sidebar-avatar">
            <div class="sidebar-user-info">
                <div class="s-name"><?php echo e($user->name ?? 'User'); ?></div>
                <div class="s-role"><?php echo e($formattedRole); ?></div>
            </div>
        </a>

        <a href="<?php echo e(route('dashboard')); ?>" class="menu-item <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>" data-tooltip="Dashboard" id="sidebar-dashboard-link">
            <i class="fas fa-home"></i><span>Dashboard</span>
        </a>

        <a href="<?php echo e(route('calendar')); ?>" class="menu-item <?php echo e(request()->routeIs('calendar') ? 'active' : ''); ?>" data-tooltip="Calendar">
            <i class="fas fa-calendar-alt"></i><span>Calendar</span>
        </a>

        <a href="<?php echo e(route('profile')); ?>" class="menu-item <?php echo e(request()->routeIs('profile') ? 'active' : ''); ?>" data-tooltip="Profile">
            <i class="fas fa-user"></i><span>Profile</span>
        </a>

        <a href="<?php echo e(route('community-feed')); ?>" class="menu-item <?php echo e(request()->routeIs('community-feed', 'sk-fed-profile', 'skfed.barangay-profile') ? 'active' : ''); ?>" data-tooltip="SK Community Feed" id="sidebar-community-feed-link">
            <i class="fas fa-rss"></i><span>SK Community Feed</span>
        </a>

        <a href="<?php echo e(route('barangay-monitoring')); ?>" class="menu-item <?php echo e(request()->routeIs('barangay-monitoring*') ? 'active' : ''); ?>" data-tooltip="Barangay Monitoring">
            <i class="fas fa-map-marker-alt"></i><span>Barangay Monitoring</span>
        </a>

        <a href="<?php echo e(route('kabataan-monitoring')); ?>" class="menu-item <?php echo e(request()->routeIs('kabataan-monitoring*') ? 'active' : ''); ?>" data-tooltip="Kabataan Monitoring">
            <i class="fas fa-users"></i><span>Kabataan Monitoring</span>
        </a>

        <a href="<?php echo e(route('reports')); ?>" class="menu-item <?php echo e(request()->routeIs('reports') ? 'active' : ''); ?>" data-tooltip="Reports">
            <i class="fas fa-chart-bar"></i><span>Reports</span>
        </a>

        <a href="<?php echo e(route('barangay.abyip')); ?>" class="menu-item <?php echo e(request()->routeIs('barangay.abyip') ? 'active' : ''); ?>" data-tooltip="Barangay ABYIP">
            <i class="fas fa-file-invoice-dollar"></i><span>Barangay ABYIP</span>
        </a>

        <a href="<?php echo e(route('barangay-logos.index')); ?>" class="menu-item <?php echo e(request()->routeIs('barangay-logos*') ? 'active' : ''); ?>" data-nav-key="barangay-logos" data-tooltip="Barangay Logos">
            <i class="fas fa-image"></i><span>Barangay Logos</span>
        </a>

        <button type="button" class="menu-item menu-dropdown-toggle <?php echo e($accountsOpen ? 'active' : ''); ?>" data-submenu-toggle="accountsSubmenu" data-tooltip="Accounts" aria-expanded="<?php echo e($accountsOpen ? 'true' : 'false'); ?>" onclick="toggleSubmenuDropdown(this, event)">
            <i class="fas fa-user-cog"></i><span>Manage Accounts</span>
            <i class="fas fa-chevron-down menu-dropdown-chevron <?php echo e($accountsOpen ? 'is-open' : ''); ?>" id="accountsChevron"></i>
        </button>

        <div id="accountsSubmenu" class="sidebar-submenu <?php echo e($accountsOpen ? 'is-open' : ''); ?>">
            <a href="<?php echo e(route('accounts.officials.index')); ?>" class="menu-item submenu-item <?php echo e($isOfficialsAccountsActive ? 'active' : ''); ?>">
                <i class="fas fa-users-cog"></i><span>SK Officials</span>
            </a>
            <a href="<?php echo e(route('accounts.federation.index')); ?>" class="menu-item submenu-item <?php echo e($isFederationAccountsActive ? 'active' : ''); ?>">
                <i class="fas fa-id-badge"></i><span>SK Federation</span>
            </a>
        </div>

        <a href="<?php echo e(route('auditlogs.index')); ?>" class="menu-item <?php echo e(request()->routeIs('auditlogs*') ? 'active' : ''); ?>" data-tooltip="Audit Logs">
            <i class="fas fa-clipboard-list"></i><span>Audit Logs</span>
        </a>

        <button type="button" class="menu-item menu-dropdown-toggle <?php echo e($archiveManagementOpen ? 'active' : ''); ?>" data-submenu-toggle="archiveManagementSubmenu" data-tooltip="Archive Management" aria-expanded="<?php echo e($archiveManagementOpen ? 'true' : 'false'); ?>" onclick="toggleSubmenuDropdown(this, event)">
            <i class="fas fa-box-archive"></i><span>Archive Management</span>
            <i class="fas fa-chevron-down menu-dropdown-chevron <?php echo e($archiveManagementOpen ? 'is-open' : ''); ?>" id="archiveManagementChevron"></i>
        </button>

        <div id="archiveManagementSubmenu" class="sidebar-submenu <?php echo e($archiveManagementOpen ? 'is-open' : ''); ?>">
            <a href="<?php echo e(route('archived.deleted-sk-federation')); ?>" class="menu-item submenu-item <?php echo e(request()->routeIs('archived.deleted-sk-federation') ? 'active' : ''); ?>">
                <i class="fas fa-user-slash"></i><span>Deleted SK Federation</span>
            </a>
            <a href="<?php echo e(route('archived.deleted-sk-officials')); ?>" class="menu-item submenu-item <?php echo e(request()->routeIs('archived.deleted-sk-officials') ? 'active' : ''); ?>">
                <i class="fas fa-user-times"></i><span>Deleted SK Officials</span>
            </a>
            <a href="<?php echo e(route('archived.sk-federation-records')); ?>" class="menu-item submenu-item <?php echo e(request()->routeIs('archived.sk-federation-records') ? 'active' : ''); ?>">
                <i class="fas fa-folder-open"></i><span>SK Federation Records</span>
            </a>
            <a href="<?php echo e(route('archived.sk-officials-records')); ?>" class="menu-item submenu-item <?php echo e(request()->routeIs('archived.sk-officials-records') ? 'active' : ''); ?>">
                <i class="fas fa-folder"></i><span>SK Officials Records</span>
            </a>
        </div>

        <button type="button" class="menu-item menu-dropdown-toggle <?php echo e($archiveReportsOpen ? 'active' : ''); ?>" data-submenu-toggle="archiveSubmenu" data-tooltip="Archive" aria-expanded="<?php echo e($archiveReportsOpen ? 'true' : 'false'); ?>" onclick="toggleSubmenuDropdown(this, event)">
            <i class="fas fa-archive"></i><span>Archive</span>
            <i class="fas fa-chevron-down menu-dropdown-chevron <?php echo e($archiveReportsOpen ? 'is-open' : ''); ?>" id="archiveChevron"></i>
        </button>

        <div id="archiveSubmenu" class="sidebar-submenu <?php echo e($archiveReportsOpen ? 'is-open' : ''); ?>">
            <a href="<?php echo e(route('archive')); ?>" class="menu-item submenu-item <?php echo e(request()->routeIs('archive') ? 'active' : ''); ?>">
                <i class="fas fa-trash"></i><span>Deleted Reports</span>
            </a>
            <a href="<?php echo e(route('archive')); ?>" class="menu-item submenu-item <?php echo e(request()->routeIs('archive') ? 'active' : ''); ?>">
                <i class="fas fa-box"></i><span>Archived Reports</span>
            </a>
        </div>

        <div class="menu-divider"></div>
    </nav>
</aside>
<?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\Layout\Providers/../views/sidebar.blade.php ENDPATH**/ ?>