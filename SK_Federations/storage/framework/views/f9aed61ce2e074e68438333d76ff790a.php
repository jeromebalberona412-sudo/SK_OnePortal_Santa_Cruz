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

            <section class="km-hero">
                <img src="<?php echo e(url('/modules/kabataan-monitoring/images/sk-fed-logo.png')); ?>" alt="SK Federation logo" class="km-hero-logo">
                <div class="km-hero-copy">
                    <h1>Kabataan Monitoring</h1>
                    <p>KKK Profiling Masterlist — Track youth engagement, participation status, and support interventions across all barangays of Santa Cruz, Laguna.</p>
                </div>
            </section>

            
            <section class="km-summary-grid" aria-label="Summary statistics">
                <article class="km-summary-card km-summary-total">
                    <div class="km-summary-icon"><i class="fas fa-users"></i></div>
                    <div class="km-summary-body">
                        <div class="km-summary-label">Total Kabataan</div>
                        <div class="km-summary-value" id="km-kpi-total">0</div>
                        <div class="km-summary-note">Registered youth profiles</div>
                    </div>
                </article>
                <article class="km-summary-card km-summary-active">
                    <div class="km-summary-icon"><i class="fas fa-user-check"></i></div>
                    <div class="km-summary-body">
                        <div class="km-summary-label">Active Youth</div>
                        <div class="km-summary-value" id="km-kpi-active">0</div>
                        <div class="km-summary-note">High &amp; moderate engagement</div>
                    </div>
                </article>
                <article class="km-summary-card km-summary-inactive">
                    <div class="km-summary-icon"><i class="fas fa-user-times"></i></div>
                    <div class="km-summary-body">
                        <div class="km-summary-label">Inactive Youth</div>
                        <div class="km-summary-value" id="km-kpi-inactive">0</div>
                        <div class="km-summary-note">Needs follow-up &amp; intervention</div>
                    </div>
                </article>
                <article class="km-summary-card km-summary-rate">
                    <div class="km-summary-icon"><i class="fas fa-chart-pie"></i></div>
                    <div class="km-summary-body">
                        <div class="km-summary-label">Participation Rate</div>
                        <div class="km-summary-value" id="km-kpi-rate">0%</div>
                        <div class="km-summary-note">Active vs total registered</div>
                    </div>
                </article>
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