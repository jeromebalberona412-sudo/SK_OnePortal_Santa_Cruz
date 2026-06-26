<?php $__env->startSection('title', 'Audit Logs'); ?>

<?php
    $auditCssVersion = @filemtime(app_path('Modules/AuditLog/assets/css/auditlogs.css')) ?: time();
    $auditJsVersion = @filemtime(app_path('Modules/AuditLog/assets/js/auditlogs.js')) ?: time();
?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(url('/modules/audit-log/css/auditlogs.css')); ?>?v=<?php echo e($auditCssVersion); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $statCards = [
        ['label' => 'Total Audit Logs', 'metricKey' => 'total_logs', 'value' => $stats['total_logs'] ?? 0, 'tone' => 'azure', 'icon' => 'activity'],
        ['label' => "Today's Activities", 'metricKey' => 'today_activities', 'value' => $stats['today_activities'] ?? 0, 'tone' => 'teal', 'icon' => 'users'],
        ['label' => 'Security Events', 'metricKey' => 'security_events', 'value' => $stats['security_events'] ?? 0, 'tone' => 'red', 'icon' => 'reject'],
        ['label' => 'Active Users Logged Today', 'metricKey' => 'active_users_today', 'value' => $stats['active_users_today'] ?? 0, 'tone' => 'violet', 'icon' => 'officials'],
    ];
?>

<div id="mainContent" class="auditlog-page container-fluid">
<div id="auditLogApp" class="auditlog-container" data-audit-routes='<?php echo json_encode($routes, 15, 512) ?>'>
    <div class="page-header-modern-with-button">
        <div class="page-header-top">
            <h1 class="page-title-modern">Audit Logs</h1>
            <p class="page-subtitle-modern">Centralized activity trail across Admin, Federation, Officials, and Kabataan portals</p>
        </div>
        <div class="page-header-filters">
            <form class="audit-filter-form" id="auditFilterForm" novalidate>
                <div class="audit-filter-grid">
                    <div class="search-container">
                        <div class="search-input-wrap">
                            <input type="search" id="auditSearch" class="search-input form-control" placeholder="Search email, action, entity, IP…" aria-label="Search audit logs">
                            <button type="button" class="search-btn" id="auditSearchBtn" aria-label="Search">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="filter-dropdown-container">
                        <input type="date" id="auditDateFrom" class="filter-dropdown audit-date-input" aria-label="Date from">
                    </div>
                    <div class="filter-dropdown-container">
                        <input type="date" id="auditDateTo" class="filter-dropdown audit-date-input" aria-label="Date to">
                    </div>
                    <div class="filter-dropdown-container">
                        <select id="auditRole" class="filter-dropdown form-select" aria-label="Filter by role">
                            <option value="">All Roles</option>
                            <?php $__currentLoopData = $filterOptions['roles']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roleOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($roleOption['value']); ?>"><?php echo e($roleOption['label']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="filter-dropdown-container">
                        <select id="auditBarangay" class="filter-dropdown form-select" aria-label="Filter by barangay">
                            <option value="">All Barangays</option>
                            <?php $__currentLoopData = $filterOptions['barangays']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $barangayOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($barangayOption['id']); ?>"><?php echo e($barangayOption['name']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="filter-dropdown-container">
                        <select id="auditEventType" class="filter-dropdown form-select" aria-label="Filter by event type">
                            <option value="">All Event Types</option>
                            <?php $__currentLoopData = $filterOptions['event_types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eventType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($eventType); ?>"><?php echo e($eventType); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <section class="audit-stats-grid" aria-label="Audit statistics">
        <?php $__currentLoopData = $statCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php echo $__env->make('dashboard::components.statcard', $card, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </section>

    <div class="audit-table-card" aria-label="Audit logs table">
        <div class="audit-table-wrap" id="auditTableWrap">
            <div class="table-responsive audit-table-responsive">
                <table class="audit-table" id="auditLogsTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Event Type</th>
                            <th>IP Address</th>
                            <th class="audit-col-actions">View</th>
                        </tr>
                    </thead>
                    <tbody id="auditLogsTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="audit-page-footer pagination-footer" aria-label="Table pagination">
    <div class="pagination-footer-nav">
        <button type="button" class="pagination-arrow" id="auditPrevBtn" disabled aria-label="Previous page">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <span class="pagination-page-label">Page</span>
        <input type="number" class="pagination-page-input" id="auditPageInput" value="1" min="1" aria-label="Current page">
        <span class="pagination-page-of">of <span id="auditTotalPages">1</span></span>
        <button type="button" class="pagination-arrow" id="auditNextBtn" disabled aria-label="Next page">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
        </button>
    </div>
    <div class="pagination-footer-right">
        <select id="auditPerPage" class="pagination-rows-select" aria-label="Rows per page">
            <option value="100" selected>100 rows</option>
            <option value="500">500 rows</option>
            <option value="1000">1000 rows</option>
        </select>
        <span class="pagination-record-count" id="auditPaginationInfo">0 records</span>
    </div>
</div>
</div>

<div class="audit-view-modal" id="auditDetailsModal" hidden>
    <div class="audit-view-modal-backdrop" data-close-modal></div>
    <div class="audit-view-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="auditModalTitle">
        <div class="audit-view-modal-header">
            <div>
                <h3 id="auditModalTitle">Audit Log Details</h3>
                <p id="auditModalSubtitle"></p>
            </div>
            <button type="button" class="audit-view-modal-close" data-close-modal aria-label="Close details">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="audit-view-modal-body">
            <div class="audit-view-detail-grid" id="auditDetailGrid"></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="<?php echo e(url('/modules/audit-log/js/auditlogs.js')); ?>?v=<?php echo e($auditJsVersion); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout::app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\AuditLog\Providers/../views/auditlogs.blade.php ENDPATH**/ ?>