<?php $__env->startSection('title', 'Barangay Monitoring - SK Federation'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(url('/modules/barangay-monitoring/css/barangay-monitoring.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="bm-container">
            <section class="bm-kpi-grid" aria-label="Monitoring summary">
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label">Total Barangay</div>
                    <div class="bm-kpi-value"><?php echo e($stats['total_barangays']); ?></div>
                    <div class="bm-kpi-note">Active barangays in monitoring</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label">Total Programs</div>
                    <div class="bm-kpi-value"><?php echo e($stats['total_programs']); ?></div>
                    <div class="bm-kpi-note">Cross-barangay total</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label">Average Participation Rate</div>
                    <div class="bm-kpi-value"><?php echo e($stats['average_participation_rate']); ?>%</div>
                    <div class="bm-kpi-note">Across all barangays</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label">Compliance Rate</div>
                    <div class="bm-kpi-value"><?php echo e($stats['compliance_rate']); ?>%</div>
                    <div class="bm-kpi-note">Compliant barangays</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label">Non-Compliance Rate</div>
                    <div class="bm-kpi-value"><?php echo e($stats['non_compliance_rate']); ?>%</div>
                    <div class="bm-kpi-note">Non-compliant barangays</div>
                </article>
            </section>

            <section class="bm-card" aria-label="All barangays list">
                <div class="bm-card-head">
                    <h3>All Barangays</h3>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <select id="bmFilterStatus" onchange="bmFilterBarangays()" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;color:#475569;background:#fff;cursor:pointer;">
                            <option value="all">All Status</option>
                            <option value="compliant">Compliant</option>
                            <option value="partial">Partial</option>
                            <option value="non-compliant">Non-Compliant</option>
                        </select>
                        <select id="bmFilterBarangay" onchange="bmFilterBarangays()" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;color:#475569;background:#fff;cursor:pointer;">
                            <option value="all">All Barangays</option>
                            <option value="Alipit">Alipit</option>
                            <option value="Bagumbayan">Bagumbayan</option>
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
                    </div>
                </div>
                <div class="bm-card-body">
                    <div class="bm-list-grid" id="bm-list-grid">
                        <?php $__currentLoopData = $barangays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $barangay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a
                                href="<?php echo e(route('barangay-monitoring.show', ['barangay' => $barangay['slug']])); ?>"
                                class="bm-list-item"
                                data-status="<?php echo e($barangay['status']); ?>"
                                data-barangay="<?php echo e(strtolower($barangay['name'])); ?>"
                                data-date="<?php echo e(strtotime($barangay['last_update'])); ?>"
                            >
                                <div class="bm-list-head">
                                    <h4><?php echo e($barangay['name']); ?></h4>
                                    <span class="bm-status <?php echo e($barangay['status']); ?>"><?php echo e(ucfirst(str_replace('-', ' ', $barangay['status']))); ?></span>
                                </div>
                                <div class="bm-list-meta">
                                    <span><i class="fas fa-user"></i> SK Chairman: <?php echo e($barangay['sk_chairman']); ?></span>
                                </div>
                                <div class="bm-list-meta">
                                    <span><i class="fas fa-layer-group"></i> Annual Programs: <?php echo e($barangay['active_programs']); ?></span>
                                    <span><i class="fas fa-users"></i> Participation Rate: <?php echo e($barangay['participation_rate']); ?>%</span>
                                </div>
                                <div class="bm-list-meta">
                                    <span><i class="fas fa-file-alt"></i> Report Rate: <?php echo e($barangay['report_rate']); ?>%</span>
                                </div>
                                <div class="bm-list-foot">
                                    <span>Last Update: <?php echo e($barangay['last_update']); ?></span>
                                    <span class="bm-link-cta">View full details <i class="fas fa-arrow-right"></i></span>
                                </div>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <p class="bm-empty" id="bm-empty" hidden>No barangays match the current search/filter.</p>
                </div>
            </section>
        </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(url('/shared/js/loading.js')); ?>"></script>
<script src="<?php echo e(url('/modules/barangay-monitoring/js/barangay-monitoring.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout::app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\BarangayMonitoring\Providers/../Views/index.blade.php ENDPATH**/ ?>