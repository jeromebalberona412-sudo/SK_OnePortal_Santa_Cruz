<?php
    $items = $notifications ?? [];
    $hasItems = count($items) > 0;
?>

<div class="notif-popover" id="notifPopover" role="menu" aria-label="Notifications">
    <div class="notif-popover-header">
        <div class="notif-popover-title">
            <i class="fas fa-bell"></i>
            <h4>Notifications</h4>
            <span class="notif-count-pill" id="notifCountPill" style="<?php echo e(($unreadNotificationCount ?? 0) > 0 ? '' : 'display: none;'); ?>"><?php echo e($unreadNotificationCount ?? 0); ?></span>
        </div>
        <button type="button" class="notif-mark-all" id="notifMarkAllBtn" title="Mark all as read">
            <i class="fas fa-check-double"></i>
            Mark all as read
        </button>
    </div>

    <div class="notif-list" id="notifList" <?php if(! $hasItems): ?> style="display: none;" <?php endif; ?>>
        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="notif-item <?php echo e(($notification['unread'] ?? false) ? 'notif-unread' : ''); ?>" data-id="<?php echo e($notification['id']); ?>">
                <div class="notif-icon">
                    <i class="fas <?php echo e($notification['icon'] ?? 'fa-bell'); ?>"></i>
                </div>
                <div class="notif-content">
                    <div class="notif-item-title"><?php echo e($notification['title']); ?></div>
                    <div class="notif-item-text"><?php echo e($notification['text']); ?></div>
                    <div class="notif-item-time"><?php echo e($notification['time']); ?></div>
                </div>
                <?php if($notification['unread'] ?? false): ?>
                    <span class="notif-unread-dot"></span>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="notif-empty" id="notifEmpty" <?php if($hasItems): ?> style="display: none;" <?php endif; ?>>
        <i class="fas fa-bell-slash"></i>
        <p>No notifications yet</p>
    </div>

    <div class="notif-popover-footer">
        <a href="<?php echo e(route('notifications.index')); ?>" class="notif-see-all-btn">
            See All Notifications
        </a>
    </div>
</div>
<?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\Notifications\Providers/../Views/dropdown-popover.blade.php ENDPATH**/ ?>