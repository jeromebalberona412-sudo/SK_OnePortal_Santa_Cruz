<?php $__env->startSection('title', 'Reports - SK OnePortal'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(url('/modules/reports/css/reports.css')); ?>?v=<?php echo e(time()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="reports-page">
    <section class="reports-header">
        <div>
            <h1>Reports</h1>
            <p>Review program and activity reports uploaded by barangay SK Officials.</p>
        </div>
        <a href="<?php echo e(route('barangay.abyip')); ?>" class="reports-abyip-link">
            <i class="fas fa-file-invoice-dollar"></i> Barangay ABYIP Review
        </a>
    </section>

    <section class="reports-filters">
        <div class="reports-filter-item">
            <label for="reportsSearch">Search</label>
            <input type="search" id="reportsSearch" placeholder="Search program, activity, barangay, or file...">
        </div>
        <div class="reports-filter-item">
            <label for="reportsBarangayFilter">Barangay</label>
            <select id="reportsBarangayFilter">
                <option value="">All barangays</option>
            </select>
        </div>
        <div class="reports-filter-item">
            <label for="reportsStatusFilter">Status</label>
            <select id="reportsStatusFilter">
                <option value="">All statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </section>

    <section class="reports-table-card">
        <table class="reports-table">
            <thead>
                <tr>
                    <th>Barangay</th>
                    <th>Program</th>
                    <th>Activity</th>
                    <th>File</th>
                    <th>Date Uploaded</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="reportsTableBody">
                <tr><td colspan="7" class="reports-empty">Loading reports...</td></tr>
            </tbody>
        </table>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    window.reportsConfig = {
        listUrl: <?php echo json_encode(route('api.reports.index'), 15, 512) ?>,
    };
</script>
<script src="<?php echo e(url('/modules/reports/js/reports.js')); ?>?v=<?php echo e(time()); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout::app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\Reports\Providers/../Views/index.blade.php ENDPATH**/ ?>