<nav class="navbar sk-fed-navbar">
    <div class="navbar-left">
        <button class="menu-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
            <i class="fas fa-bars toggle-icon-expand"></i>
            <i class="fas fa-ellipsis-v toggle-icon-collapse"></i>
        </button>
        <div class="navbar-brand">
            <img src="<?php echo e(asset('Images/SK_OnePortal.png')); ?>" alt="SK OnePortal Logo" class="brand-logo">
            <span class="brand-name">SK OnePortal</span>
        </div>
    </div>

    <?php echo $__env->yieldPushContent('navbar-center'); ?>

    <div class="navbar-right">
        <div class="notif-menu" id="notifMenu">
            <button
                type="button"
                class="notif-btn"
                id="notifBtn"
                onclick="toggleNotifPopover(event)"
                aria-label="Notifications"
                aria-expanded="false"
                aria-haspopup="true"
            >
                <i class="fas fa-bell"></i>
                <span class="notif-badge" id="notifBadge" style="<?php echo e(($unreadNotificationCount ?? 0) > 0 ? '' : 'display: none;'); ?>"><?php echo e($unreadNotificationCount ?? 0); ?></span>
            </button>

            <?php echo $__env->make('notifications::dropdown-popover', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <div class="profile-dropdown-wrapper">
            <button class="profile-btn" onclick="toggleProfileDropdown(event)" aria-label="Profile menu">
                <img src="<?php echo e($avatar); ?>" alt="Profile" class="nav-avatar">
                <i class="fas fa-chevron-down nav-chevron"></i>
            </button>

            <div class="profile-dropdown" id="profileDropdown">
                <div class="profile-dropdown-header">
                    <div class="dd-name"><?php echo e($user->name ?? 'User'); ?></div>
                    <div class="dd-email"><?php echo e($user->email ?? ''); ?></div>
                </div>
                <a href="<?php echo e(route('profile')); ?>" class="dd-item" id="nav-profile-link">
                    <i class="fas fa-user"></i> Profile
                </a>
                <a href="<?php echo e(route('change-password')); ?>" class="dd-item" id="nav-change-pw-link">
                    <i class="fas fa-lock"></i> Change Password
                </a>
                <div class="dd-divider"></div>
                <button type="button" class="dd-item danger" onclick="showLogoutModal()">
                    Logout
                </button>
            </div>
        </div>
    </div>
</nav>
<?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\Layout\Providers/../views/header.blade.php ENDPATH**/ ?>