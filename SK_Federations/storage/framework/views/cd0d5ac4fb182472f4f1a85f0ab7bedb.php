<?php $__env->startSection('title', $barangay . ' - Kabataan Monitoring - SK OnePortal'); ?>

<?php $__env->startPush('main-class'); ?>
    km-main
<?php $__env->stopPush(); ?>

<?php $__env->startPush('main-attributes'); ?>
    data-detail-base="<?php echo e(url('/kabataan-monitoring')); ?>"
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(url('/modules/kabataan-monitoring/css/kabataan-monitoring.css')); ?>?v=<?php echo e(time()); ?>">
    <link rel="stylesheet" href="<?php echo e(url('/modules/kabataan-monitoring/css/kk-questionnaire-view.css')); ?>?v=<?php echo e(time()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="km-container">

            
            <div class="km-detail-header">
                <a href="<?php echo e(route('kabataan-monitoring')); ?>" class="km-back-link">
                    <i class="fas fa-arrow-left"></i> Back to Kabataan Monitoring
                </a>
                <div class="km-brgy-title-section">
                    <h1><i class="fas fa-map-marker-alt"></i> <?php echo e($barangay); ?></h1>
                    <p>KKK Profiling Masterlist</p>
                </div>
            </div>

            
            <section class="km-masterlist-top">
                <div class="km-masterlist-topbar">
                    <div>
                        <h2><i class="fas fa-list-alt" style="color:#213F99;margin-right:8px;"></i>KKK Profiling Masterlist</h2>
                        <p>Youth profiling records for <?php echo e($barangay); ?></p>
                    </div>
                    <div class="km-masterlist-actions">
                        <button class="km-export-btn" onclick="exportBarangayCSV()">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                    </div>
                </div>

                
                <div class="km-filter-row">
                    <div class="km-filter-row-inner">
                        <select id="km-brgy-year-filter" class="km-select">
                            <option value="all">All Years</option>
                            <option value="2026">2026</option>
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                        </select>

                        <select id="km-period-filter" class="km-select">
                            <option value="all">All</option>
                            <option value="recent">Recent</option>
                            <option value="month">This Month</option>
                        </select>

                        <div class="km-search-group">
                            <input type="text" id="km-brgy-search" class="km-search-input" placeholder="Search by name, barangay...">
                            <button type="button" class="km-search-btn" onclick="performBarangaySearch()">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <div class="km-table-card">
                <div class="km-table-wrapper">
                    <table class="km-table">
                        <thead>
                            <tr>
                                <th>Respondent #</th>
                                <th>
                                    FULLNAME
                                    <div class="table-col-hint">LN, FN, MN, Suffix</div>
                                </th>
                                <th>Age</th>
                                <th>Barangay</th>
                                <th>Purok/Zone</th>
                                <th>Registered Voter</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="km-table-tbody"></tbody>
                    </table>
                </div>
            </div>
            <p id="km-empty" class="km-empty" hidden>No profiles match your current filters.</p>

            
            <div class="km-pagination-wrapper">
                <div class="km-pagination-info">
                    <span id="km-pagination-text">Showing 0 of 0 records</span>
                </div>
                <div class="km-pagination-controls">
                    <button class="km-pagination-btn" id="km-prev-btn" onclick="previousPage()" disabled>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <div class="km-pagination-numbers" id="km-pagination-numbers"></div>
                    <button class="km-pagination-btn" id="km-next-btn" onclick="nextPage()" disabled>
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

        </div>

<div class="km-kkp-modal" id="kmKKPModal">
    <div class="km-kkp-modal-overlay" onclick="closeKKPModal()"></div>
    <div class="km-kkp-modal-content">
        <div class="km-kkp-modal-header">
            <h2><i class="fas fa-file-alt"></i> KK Survey Questionnaire</h2>
            <button type="button" class="km-kkp-modal-close" onclick="closeKKPModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="km-kkp-modal-body kk-qs-body" id="kmKKPFormContainer">
            <p class="km-kkp-loading">Loading questionnaire...</p>
        </div>
        <div class="km-kkp-modal-footer">
            <button type="button" class="km-kkp-btn-close" onclick="closeKKPModal()">Close</button>
            <button type="button" class="km-kkp-btn-print" onclick="printKKPForm()"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        window.kmPageMode = 'barangay-detail';
        window.kmBarangay = <?php echo json_encode($barangay, 15, 512) ?>;
        window.kmConfig = {
            dataUrl: <?php echo json_encode(route('api.kabataan-monitoring.index'), 15, 512) ?>,
            questionnaireUrl: <?php echo json_encode(url('/api/kabataan-monitoring/__ID__/questionnaire'), 15, 512) ?>,
        };
    </script>
    <script src="<?php echo e(url('/shared/js/loading.js')); ?>"></script>
    <script src="<?php echo e(url('/modules/kabataan-monitoring/js/kabataan-monitoring.js')); ?>?v=<?php echo e(time()); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout::app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\KabataanMonitoring\Providers/../Views/barangay-detail.blade.php ENDPATH**/ ?>