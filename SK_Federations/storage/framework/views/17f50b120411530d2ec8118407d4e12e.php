<?php $__env->startSection('title', ($barangayData['name'] ?? 'Barangay') . ' - Barangay Monitoring'); ?>

<?php $__env->startPush('main-class'); ?>
    bm-main
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(url('/modules/barangay-monitoring/css/barangay-monitoring.css')); ?>?v=<?php echo e(time()); ?>">
    <link rel="stylesheet" href="<?php echo e(url('/modules/barangay-abyip/css/barangay_abyip.css')); ?>?v=<?php echo e(time()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="bm-container bm-show-page" id="bmShowApp"
     data-barangay-slug="<?php echo e($barangayData['slug']); ?>"
     data-barangay-name="<?php echo e($barangayData['name']); ?>">

    <a class="bm-back-link" href="<?php echo e(route('barangay-monitoring')); ?>">
        <i class="fas fa-arrow-left"></i> Back to All Barangays
    </a>

    <header class="bm-show-hero">
        <div class="bm-show-hero-main">
            <h1 class="bm-show-title"><?php echo e($barangayData['name']); ?></h1>
            <p class="bm-show-subtitle">
                <i class="fas fa-map-marker-alt"></i>
                <?php echo e($barangayData['name']); ?>, <?php echo e($barangayData['municipality']); ?>

            </p>
        </div>
        <div class="bm-show-hero-status">
            <span class="bm-show-status-label">Compliance</span>
            <span class="bm-compliance-badge bm-compliance-<?php echo e($barangayData['compliance_status']); ?>">
                <?php if($barangayData['compliance_status'] === 'compliant'): ?>
                    <i class="fas fa-check-circle"></i> Compliant
                <?php elseif($barangayData['compliance_status'] === 'partial'): ?>
                    <i class="fas fa-exclamation-circle"></i> Partial
                <?php else: ?>
                    <i class="fas fa-times-circle"></i> Non-Compliant
                <?php endif; ?>
            </span>
        </div>
    </header>

    <?php if(!empty($barangayData['warnings'])): ?>
        <?php $__currentLoopData = $barangayData['warnings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bm-alert bm-alert-<?php echo e($warning['type']); ?>" role="alert">
            <div class="bm-alert-icon">
                <i class="fas fa-<?php echo e($warning['type'] === 'critical' ? 'exclamation-triangle' : 'exclamation-circle'); ?>"></i>
            </div>
            <div class="bm-alert-content">
                <strong><?php echo e($warning['title']); ?></strong>
                <p><?php echo e($warning['message']); ?></p>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

    <?php if(!empty($barangayData['abyip_schedule'])): ?>
        <?php $schedule = $barangayData['abyip_schedule']; ?>
        <section class="bm-card bm-schedule-banner">
            <div class="bm-card-head">
                <h3><i class="fas fa-calendar-alt"></i> <?php echo e($schedule['title'] ?? 'ABYIP Submission'); ?></h3>
                <span class="bm-schedule-status"><?php echo e($schedule['status_label'] ?? '—'); ?></span>
            </div>
            <div class="bm-schedule-grid">
                <div class="bm-schedule-item">
                    <p class="bm-schedule-label">Start</p>
                    <p class="bm-schedule-value"><?php echo e($schedule['date_start'] ?? '—'); ?></p>
                </div>
                <div class="bm-schedule-item">
                    <p class="bm-schedule-label">Deadline</p>
                    <p class="bm-schedule-value"><?php echo e($schedule['deadline'] ?? '—'); ?></p>
                </div>
                <div class="bm-schedule-item">
                    <p class="bm-schedule-label">Original Deadline</p>
                    <p class="bm-schedule-value"><?php echo e($schedule['original_deadline'] ?? '—'); ?></p>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <div class="bm-tab-bar" role="tablist">
        <button type="button" class="bm-tab-btn active" data-tab="abyip" id="tab-abyip" role="tab" aria-selected="true">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Barangay ABYIP</span>
        </button>
        <button type="button" class="bm-tab-btn" data-tab="accomplishment" id="tab-accomplishment" role="tab" aria-selected="false">
            <i class="fas fa-trophy"></i>
            <span>Accomplishment</span>
        </button>
    </div>

    <div id="section-abyip" class="bm-tab-panel" role="tabpanel">
        <section class="bm-card bm-table-card">
            <div class="bm-table-toolbar">
                <div class="bm-table-toolbar-title">
                    <h3><i class="fas fa-file-invoice-dollar"></i> Submitted ABYIP Reports</h3>
                    <p>Review ABYIP submissions from this barangay</p>
                </div>
                <div class="bm-table-toolbar-filters">
                    <div class="bm-search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="search" id="abyipSearchInput" placeholder="Search reports..." aria-label="Search ABYIP reports">
                    </div>
                    <select id="abyipFilterYear" aria-label="Filter by year">
                        <option value="all">All Years</option>
                        <?php $__currentLoopData = collect($barangayData['abyip']['reports'] ?? [])->pluck('fiscal_year')->unique()->sortDesc(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($year); ?>"><?php echo e($year); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <select id="abyipFilterStatus" aria-label="Filter by status">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>
            <div class="bm-table-wrap">
                <table class="bm-table bm-data-table" id="abyipTable">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Fiscal Year</th>
                            <th>Date Submitted</th>
                            <th>Submitted By</th>
                            <th>Status</th>
                            <th>View</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $barangayData['abyip']['reports'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="bm-abyip-row" data-year="<?php echo e($report['fiscal_year'] ?? ''); ?>" data-status="<?php echo e($report['status'] ?? 'pending'); ?>" data-id="<?php echo e($report['id']); ?>">
                            <td class="bm-cell-strong" data-label="Title"><?php echo e($report['name'] ?? 'N/A'); ?></td>
                            <td data-label="Fiscal Year"><?php echo e($report['fiscal_year'] ?? '—'); ?></td>
                            <td data-label="Date Submitted"><?php echo e(!empty($report['date_submitted']) ? date('M d, Y h:i A', strtotime($report['date_submitted'])) : '—'); ?></td>
                            <td data-label="Submitted By"><?php echo e($report['submitted_by'] ?? '—'); ?></td>
                            <td data-label="Status">
                                <span class="bm-status-pill bm-status-<?php echo e($report['status'] ?? 'pending'); ?>">
                                    <?php echo e(ucfirst($report['status'] ?? 'pending')); ?>

                                </span>
                            </td>
                            <td data-label="View">
                                <button type="button" class="bm-view-btn" data-abyip-view="<?php echo e($report['id']); ?>">View</button>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr class="bm-empty-row-tr">
                            <td colspan="6" class="bm-empty-row">
                                <i class="fas fa-inbox"></i>
                                <span>No ABYIP reports submitted yet</span>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div id="section-accomplishment" class="bm-tab-panel" role="tabpanel" hidden>
        <section class="bm-card bm-table-card">
            <div class="bm-table-toolbar">
                <div class="bm-table-toolbar-title">
                    <h3><i class="fas fa-trophy"></i> Approved Accomplishments</h3>
                    <p>Approved accomplishment reports from this barangay</p>
                </div>
                <div class="bm-table-toolbar-filters">
                    <div class="bm-search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="search" id="accomplishmentSearchInput" placeholder="Search programs..." aria-label="Search accomplishments">
                    </div>
                    <select id="accomplishmentFilterTerm" aria-label="Filter by term">
                        <option value="all">All Terms</option>
                        <?php $__currentLoopData = $barangayData['accomplishment_terms'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $term): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($term); ?>"><?php echo e($term); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <select id="accomplishmentFilterYear" aria-label="Filter by year">
                        <option value="all">All Years</option>
                        <?php $__currentLoopData = $barangayData['accomplishment_years'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($year); ?>"><?php echo e($year); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
            <div class="bm-table-wrap">
                <table class="bm-table bm-data-table" id="accomplishmentTable">
                    <thead>
                        <tr>
                            <th>Program</th>
                            <th>Activity</th>
                            <th>Code</th>
                            <th>Term</th>
                            <th>Year</th>
                            <th>Date Approved</th>
                            <th>Report</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $barangayData['accomplishments'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr data-term="<?php echo e($item['term'] ?? ''); ?>" data-year="<?php echo e($item['year'] ?? ''); ?>">
                            <td class="bm-cell-strong" data-label="Program"><?php echo e($item['title'] ?? 'N/A'); ?></td>
                            <td data-label="Activity"><?php echo e($item['activity'] ?? '—'); ?></td>
                            <td data-label="Code"><?php echo e($item['program_code'] ?? '—'); ?></td>
                            <td data-label="Term"><?php echo e($item['term'] ?? '—'); ?></td>
                            <td data-label="Year"><?php echo e($item['year'] ?? '—'); ?></td>
                            <td data-label="Date Approved"><?php echo e(!empty($item['start_date']) ? date('M d, Y', strtotime($item['start_date'])) : '—'); ?></td>
                            <td data-label="Report">
                                <a href="<?php echo e($item['file_url'] ?? '#'); ?>" target="_blank" rel="noopener" class="bm-link-btn">
                                    <i class="fas fa-file-pdf"></i> View PDF
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr class="bm-empty-row-tr">
                            <td colspan="7" class="bm-empty-row">
                                <i class="fas fa-inbox"></i>
                                <span>No approved accomplishments yet</span>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<div id="viewModal" class="view-modal">
    <div class="view-modal-content">
        <div class="view-modal-header">
            <h3 class="view-modal-title">
                <i class="fas fa-file-invoice-dollar"></i>
                ABYIP Submission Details
            </h3>
            <div class="view-modal-controls">
                <button type="button" class="view-modal-control-btn" onclick="toggleFullscreen()" title="Toggle Fullscreen" id="fullscreenBtn">
                    <i class="fas fa-expand"></i>
                </button>
                <button type="button" class="view-modal-control-btn" onclick="closeViewModal()" title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="view-modal-body">
            <div class="submission-meta-grid">
                <div class="meta-item">
                    <span class="meta-label">Barangay</span>
                    <span class="meta-value" id="modalBarangay"><?php echo e($barangayData['name']); ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Fiscal Year</span>
                    <span class="meta-value" id="modalFiscalYear">-</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Date Submitted</span>
                    <span class="meta-value" id="modalDateSubmitted">-</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Submitted Time</span>
                    <span class="meta-value" id="modalSubmittedTime">-</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Submitted By</span>
                    <span class="meta-value" id="modalSubmittedBy">-</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Status</span>
                    <span class="meta-value">
                        <span class="status-badge status-pending" id="modalStatus">Pending</span>
                    </span>
                </div>
                <div class="meta-item meta-item-full">
                    <span class="meta-label">Title</span>
                    <span class="meta-value" id="modalTitle">-</span>
                </div>
            </div>

            <div id="modalRejectionReason" class="modal-rejection-notice" style="display:none;"></div>

            <div class="submission-preview-section">
                <h4 class="preview-section-title">Uploaded ABYIP Document</h4>
                <div id="abyipPreviewMount" class="abyip-preview-mount">
                    <p class="preview-loading">Select View to load document preview.</p>
                </div>
            </div>
        </div>

        <div class="view-modal-footer" id="viewModalFooter">
            <div class="rejection-form" id="rejectForm" style="display:none;">
                <h4 class="form-title">Reject Submission</h4>
                <div class="form-group">
                    <label>Rejection Reason <span class="required">*</span></label>
                    <textarea id="abyipRejectReason" class="form-control" rows="4" maxlength="1000" placeholder="Provide a reason for rejection..."></textarea>
                    <span class="error-message" id="rejectReasonError"></span>
                </div>
                <div class="form-actions">
                    <button type="button" class="form-btn cancel-btn" onclick="hideRejectForm()">Cancel</button>
                    <button type="button" class="form-btn submit-btn reject-submit-btn" onclick="submitRejection()">Submit Rejection</button>
                </div>
            </div>

            <div class="modal-actions" id="modalActions">
                <button type="button" class="action-btn reject-btn" onclick="showRejectForm()">Reject</button>
                <button type="button" class="action-btn approve-btn" onclick="submitApproval()">Approve</button>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="toast">
    <div class="toast-content">
        <span class="toast-message"></span>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        window.barangayMonitoringShowConfig = {
            showUrl: <?php echo json_encode(url('/api/barangay-abyip/__ID__'), 15, 512) ?>,
            approveUrl: <?php echo json_encode(url('/api/barangay-abyip/__ID__/approve'), 15, 512) ?>,
            rejectUrl: <?php echo json_encode(url('/api/barangay-abyip/__ID__/reject'), 15, 512) ?>,
            csrfToken: <?php echo json_encode(csrf_token(), 15, 512) ?>,
            abyipReports: <?php echo json_encode($barangayData['abyip']['reports'] ?? [], 15, 512) ?>,
        };
    </script>
    <script src="<?php echo e(url('/modules/barangay-monitoring/js/barangay-monitoring-show.js')); ?>?v=<?php echo e(time()); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout::app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\BarangayMonitoring\Providers/../Views/show.blade.php ENDPATH**/ ?>