<?php $__env->startSection('title', 'Calendar - SK OnePortal'); ?>

<?php $__env->startPush('styles'); ?>
    <?php
        $calendarCssPath = app_path('Modules/Calendar/assets/css/calendar.css');
        $calendarCssVersion = file_exists($calendarCssPath) ? filemtime($calendarCssPath) : time();
    ?>
    <link rel="stylesheet" href="<?php echo e(url('/modules/calendar/css/calendar.css')); ?>?v=<?php echo e($calendarCssVersion); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="calendar-page-container">
        <section class="calendar-header-section">
            <div class="calendar-header-left">
                <h1 class="calendar-title">Calendar</h1>
                <p class="calendar-subtitle">View and annotate your monthly schedule.</p>
            </div>
            <div class="calendar-header-right">
                <span id="calendarMonthLabel" class="calendar-current-date"></span>
                <button type="button" id="calendarPrevBtn" class="calendar-nav-btn" aria-label="Previous">&laquo; Prev</button>
                <button type="button" id="calendarNextBtn" class="calendar-nav-btn" aria-label="Next">Next &raquo;</button>
                <button type="button" id="calendarJumpBtn" class="calendar-jump-btn" aria-label="Jump to date">Jump to date</button>
            </div>
        </section>

        <section class="calendar-main-section">
            <div class="calendar-legend">
                <span class="legend-item"><span class="legend-dot has-notes"></span>Day with notes</span>
                <span class="legend-item"><span class="legend-dot today"></span>Today</span>
            </div>

            <div class="calendar-grid" id="calendarGrid"></div>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <?php
        $calendarJsPath = app_path('Modules/Calendar/assets/js/calendar.js');
        $calendarJsVersion = file_exists($calendarJsPath) ? filemtime($calendarJsPath) : time();
    ?>
    <script src="<?php echo e(url('/modules/calendar/js/calendar.js')); ?>?v=<?php echo e($calendarJsVersion); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout::app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\Calendar\Providers/../Views/calendar.blade.php ENDPATH**/ ?>