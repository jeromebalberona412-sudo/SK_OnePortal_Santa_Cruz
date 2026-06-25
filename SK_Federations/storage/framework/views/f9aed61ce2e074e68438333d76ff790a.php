<?php $__env->startSection('title', 'Kabataan Monitoring - SK OnePortal'); ?>

<?php $__env->startPush('main-class'); ?>
    km-main
<?php $__env->stopPush(); ?>

<?php $__env->startPush('main-attributes'); ?>
    data-detail-base="<?php echo e(url('/kabataan-monitoring')); ?>"
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(url('/modules/kabataan-monitoring/css/kabataan-monitoring.css')); ?>?v=<?php echo e(time()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="km-container">

            <section class="km-page-header">
                <h1 class="km-page-title">Kabataan Monitoring</h1>
                <p class="km-page-subtitle">KKK Profiling Masterlist — Track youth engagement, participation status, and support interventions across all barangays of Santa Cruz, Laguna.</p>
            </section>

            
            <section class="km-masterlist-top">
                <div class="km-masterlist-topbar">
                    <div>
                        <h2><i class="fas fa-list-alt" style="color:#213F99;margin-right:8px;"></i>KKK Profiling Masterlist</h2>
                        <p>Youth profiling records grouped by barangay</p>
                    </div>
                    <div class="km-masterlist-actions">
                        <select id="km-brgy-filter" style="padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:14px;min-width:220px;margin-right:12px;">
                            <option value="all">All Barangays</option>
                            <option value="Alipit">Alipit</option>
                            <option value="Bagumbayan">Bagumbayan</option>
                            <option value="Bubukal">Bubukal</option>
                            <option value="Calios">Calios</option>
                            <option value="Duhat">Duhat</option>
                            <option value="Gatid">Gatid</option>
                            <option value="Jasaan">Jasaan</option>
                            <option value="Labuin">Labuin</option>
                            <option value="Malinao">Malinao</option>
                            <option value="Oogong">Oogong</option>
                            <option value="Pagsawitan">Pagsawitan</option>
                            <option value="Palasan">Palasan</option>
                            <option value="Patimbao">Patimbao</option>
                            <option value="Poblacion I">Poblacion I</option>
                            <option value="Poblacion II">Poblacion II</option>
                            <option value="Poblacion III">Poblacion III</option>
                            <option value="Poblacion IV">Poblacion IV</option>
                            <option value="Poblacion V">Poblacion V</option>
                            <option value="San Jose">San Jose</option>
                            <option value="San Juan">San Juan</option>
                            <option value="San Pablo Norte">San Pablo Norte</option>
                            <option value="San Pablo Sur">San Pablo Sur</option>
                            <option value="Santisima Cruz">Santisima Cruz</option>
                            <option value="Santo Angel Central">Santo Angel Central</option>
                            <option value="Santo Angel Norte">Santo Angel Norte</option>
                            <option value="Santo Angel Sur">Santo Angel Sur</option>
                        </select>
                        <button class="km-export-btn" onclick="exportCSV()">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                    </div>
                </div>
            </section>

            
            <div id="km-brgy-cards"></div>
            <p id="km-empty" class="km-empty" hidden>No profiles match your current filters.</p>

        </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(url('/shared/js/loading.js')); ?>"></script>
<script>
    window.kmConfig = {
        dataUrl: <?php echo json_encode(route('api.kabataan-monitoring.index'), 15, 512) ?>,
    };
</script>
    <script src="<?php echo e(url('/modules/kabataan-monitoring/js/kabataan-monitoring.js')); ?>?v=<?php echo e(time()); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout::app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\KabataanMonitoring\Providers/../Views/index.blade.php ENDPATH**/ ?>