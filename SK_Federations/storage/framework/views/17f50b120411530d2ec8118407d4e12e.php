<?php $__env->startSection('title', ($barangayData['name'] ?? 'Barangay') . ' - Barangay Monitoring'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(url('/modules/barangay-monitoring/css/barangay-monitoring.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="bm-container">
            <a class="bm-back-link" href="<?php echo e(route('barangay-monitoring')); ?>" style="margin-bottom:16px;display:inline-flex;align-items:center;gap:6px;"><i class="fas fa-arrow-left"></i> Back to All Barangays</a>

            
            <div style="margin-bottom:20px;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
                    <div>
                        <h2 style="font-size:28px;font-weight:800;color:#0d1b4b;margin-bottom:4px;"><?php echo e($barangayData['name']); ?></h2>
                        <p style="font-size:14px;color:#64748b;"><i class="fas fa-map-marker-alt" style="margin-right:6px;color:#213F99;"></i><?php echo e($barangayData['name']); ?>, <?php echo e($barangayData['municipality']); ?></p>
                    </div>
                    
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:12px;font-weight:600;color:#64748b;">Status:</span>
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:700;
                        <?php if($barangayData['compliance_status'] === 'compliant'): ?>
                            background:#dcfce7;color:#15803d;
                        <?php elseif($barangayData['compliance_status'] === 'partial'): ?>
                            background:#fef3c7;color:#b45309;
                        <?php else: ?>
                            background:#fee2e2;color:#dc2626;
                        <?php endif; ?>
                        ">
                            <?php if($barangayData['compliance_status'] === 'compliant'): ?>
                                <i class="fas fa-check-circle"></i> Compliant
                            <?php elseif($barangayData['compliance_status'] === 'partial'): ?>
                                <i class="fas fa-exclamation-circle"></i> Partial
                            <?php else: ?>
                                <i class="fas fa-times-circle"></i> Non-Compliant
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>

            
            <?php if(!empty($barangayData['warnings'])): ?>
                <?php $__currentLoopData = $barangayData['warnings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div style="margin-bottom:20px;padding:16px;border-radius:12px;border-left:4px solid;
                <?php if($warning['type'] === 'critical'): ?>
                    background:#fee2e2;border-color:#dc2626;
                <?php else: ?>
                    background:#fef3c7;border-color:#f59e0b;
                <?php endif; ?>
                ">
                    <div style="display:flex;align-items:flex-start;gap:12px;">
                        <div style="font-size:20px;
                        <?php if($warning['type'] === 'critical'): ?>
                            color:#dc2626;
                        <?php else: ?>
                            color:#f59e0b;
                        <?php endif; ?>
                        ">
                            <?php if($warning['type'] === 'critical'): ?>
                                <i class="fas fa-exclamation-triangle"></i>
                            <?php else: ?>
                                <i class="fas fa-exclamation-circle"></i>
                            <?php endif; ?>
                        </div>
                        <div style="flex:1;">
                            <h4 style="margin:0 0 4px 0;font-size:14px;font-weight:700;
                            <?php if($warning['type'] === 'critical'): ?>
                                color:#991b1b;
                            <?php else: ?>
                                color:#92400e;
                            <?php endif; ?>
                            "><?php echo e($warning['title']); ?></h4>
                            <p style="margin:0 0 12px 0;font-size:13px;
                            <?php if($warning['type'] === 'critical'): ?>
                                color:#7f1d1d;
                            <?php else: ?>
                                color:#78350f;
                            <?php endif; ?>
                            "><?php echo e($warning['message']); ?></p>
                            <button onclick="openWarningModal('<?php echo e($warning['type']); ?>', '<?php echo e($warning['title']); ?>')" style="padding:6px 12px;border:none;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;
                            <?php if($warning['type'] === 'critical'): ?>
                                background:#dc2626;color:#fff;
                            <?php else: ?>
                                background:#f59e0b;color:#fff;
                            <?php endif; ?>
                            ">
                                <i class="fas fa-bell"></i> Send Warning
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>

            
            <div id="summaryDropdownMobile" style="display:none;margin-bottom:20px;">
                <button onclick="toggleSummaryDropdown()" style="width:100%;padding:12px;border:1px solid #e2e8f0;background:#fff;border-radius:8px;font-size:14px;font-weight:600;color:#213F99;cursor:pointer;display:flex;align-items:center;justify-content:space-between;">
                    <span><i class="fas fa-chart-bar" style="margin-right:6px;"></i>Summary Cards</span>
                    <i class="fas fa-chevron-down" id="summaryDropdownIcon"></i>
                </button>
                <div id="summaryDropdownContent" style="display:none;margin-top:8px;"></div>
            </div>

            
            <div id="summaryCardsDesktop" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px;margin-bottom:24px;">
                
                <div style="background:linear-gradient(135deg,#213F99 0%,#1a2f7a 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(33,63,153,.15);">
                    <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-users" style="margin-right:6px;"></i>Total Kabataan Register</p>
                    <p style="font-size:28px;font-weight:800;line-height:1;"><?php echo e(number_format($barangayData['program_stats']['total_youth_population'] ?? 0)); ?></p>
                </div>

                
                <div style="background:linear-gradient(135deg,#8b5cf6 0%,#6d28d9 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(139,92,246,.15);">
                    <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-tasks" style="margin-right:6px;"></i>Total Program Created</p>
                    <p style="font-size:28px;font-weight:800;line-height:1;"><?php echo e($barangayData['program_stats']['total_programs_created'] ?? 0); ?></p>
                </div>

                
                <div style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(59,130,246,.15);">
                    <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-spinner" style="margin-right:6px;"></i>Ongoing Programs</p>
                    <p style="font-size:28px;font-weight:800;line-height:1;"><?php echo e($barangayData['program_stats']['total_ongoing'] ?? 0); ?></p>
                </div>

                
                <div style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(16,185,129,.15);">
                    <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-check-circle" style="margin-right:6px;"></i>Compliant Rate</p>
                    <p style="font-size:28px;font-weight:800;line-height:1;"><?php echo e($barangayData['compliance_score'] ?? 0); ?>%</p>
                </div>

                
                <div style="background:linear-gradient(135deg,#f97316 0%,#ea580c 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(249,115,22,.15);">
                    <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>Non-Compliant Rate</p>
                    <p style="font-size:28px;font-weight:800;line-height:1;"><?php echo e(100 - ($barangayData['compliance_score'] ?? 0)); ?>%</p>
                </div>

                
                <div style="background:linear-gradient(135deg,#06b6d4 0%,#0891b2 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(6,182,212,.15);">
                    <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-user-check" style="margin-right:6px;"></i>Participation Rate</p>
                    <p style="font-size:28px;font-weight:800;line-height:1;"><?php echo e($barangayData['performance_summary']['attendance_rate'] ?? 0); ?>%</p>
                </div>

                
                <div style="background:linear-gradient(135deg,#ec4899 0%,#db2777 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(236,72,153,.15);">
                    <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-chart-pie" style="margin-right:6px;"></i>Annual Budget Utilization</p>
                    <p style="font-size:28px;font-weight:800;line-height:1;"><?php echo e($barangayData['abyip']['budget_utilization'] ?? 0); ?>%</p>
                </div>

                
                <div style="background:linear-gradient(135deg,#eab308 0%,#ca8a04 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(234,179,8,.15);">
                    <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-clipboard-list" style="margin-right:6px;"></i>ABYIP Project Count</p>
                    <p style="font-size:28px;font-weight:800;line-height:1;"><?php echo e($barangayData['abyip']['project_count'] ?? 0); ?></p>
                </div>

                
                <div style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(245,158,11,.15);">
                    <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-wallet" style="margin-right:6px;"></i>Remaining Balance</p>
                    <p style="font-size:28px;font-weight:800;line-height:1;">₱<?php echo e(number_format($barangayData['abyip']['remaining_balance'] ?? 0, 2)); ?></p>
                </div>
            </div>

            
            <div id="summaryCardsMobile" style="display:none;"></div>

            
            <div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid #e2e8f0;">
                <button onclick="switchTab('profile')" id="tab-profile" style="padding:10px 20px;border:none;background:none;font-size:14px;font-weight:600;color:#213F99;border-bottom:2px solid #213F99;margin-bottom:-2px;cursor:pointer;">
                    <i class="fas fa-id-badge"></i> Profile
                </button>
                <button onclick="switchTab('abyip')" id="tab-abyip" style="padding:10px 20px;border:none;background:none;font-size:14px;font-weight:600;color:#64748b;border-bottom:2px solid transparent;margin-bottom:-2px;cursor:pointer;">
                    <i class="fas fa-file-invoice-dollar"></i> Barangay ABYIP
                </button>
                <button onclick="switchTab('accomplishment')" id="tab-accomplishment" style="padding:10px 20px;border:none;background:none;font-size:14px;font-weight:600;color:#64748b;border-bottom:2px solid transparent;margin-bottom:-2px;cursor:pointer;">
                    <i class="fas fa-trophy"></i> Accomplishment
                </button>
            </div>

            
            <?php
            $brgyColors = [
                'brgy-1-poblacion'=>'#213F99','brgy-2-poblacion'=>'#2196F3','brgy-3-poblacion'=>'#9C27B0',
                'brgy-4-poblacion'=>'#FF9800','brgy-5-poblacion'=>'#009688','labuin'=>'#f44336',
                'pagsawitan'=>'#673AB7','san-jose'=>'#0450a8','santisima-cruz'=>'#FF5722',
            ];
            $brgyColor = $brgyColors[$barangayData['slug'] ?? ''] ?? '#213F99';
            $initials  = strtoupper(substr($barangayData['name'], 0, 2));
            ?>
            <div id="section-profile">
                <div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">
                    
                    <div style="display:flex;flex-direction:column;gap:16px;">
                        <div class="bm-card">
                            <div class="bm-card-head"><h3><i class="fas fa-users" style="color:#213F99;margin-right:6px;"></i>SK Officers</h3></div>
                            <div style="padding:0 20px 16px;">
                                <?php $__currentLoopData = $barangayData['officials']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f1f5f9;">
                                    <div style="width:36px;height:36px;border-radius:50%;background:<?php echo e($brgyColor); ?>;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0;">
                                        <?php echo e(strtoupper(substr($o['name'], 0, 2))); ?>

                                    </div>
                                    <div>
                                        <p style="font-size:13px;font-weight:700;color:#1e293b;"><?php echo e($o['name']); ?></p>
                                        <p style="font-size:11px;color:#94a3b8;"><?php echo e($o['role']); ?></p>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <div class="bm-card">
                            <div class="bm-card-head"><h3><i class="fas fa-phone" style="color:#213F99;margin-right:6px;"></i>Contact Information</h3></div>
                            <div style="padding:0 20px 16px;">
                                <?php $__currentLoopData = [['fas fa-phone','Phone','[SK Contact Number]'],['fas fa-envelope','Email','[SK Email Address]'],['fas fa-map-marker-alt','Address',$barangayData['name'].', Santa Cruz, Laguna'],['fas fa-clock','Office Hours','Mon–Fri, 8:00 AM – 5:00 PM']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #f1f5f9;font-size:13px;">
                                    <div style="width:30px;height:30px;border-radius:8px;background:#eff3ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="<?php echo e($row[0]); ?>" style="font-size:12px;color:#213F99;"></i>
                                    </div>
                                    <div>
                                        <p style="font-size:11px;color:#94a3b8;"><?php echo e($row[1]); ?></p>
                                        <p style="font-size:13px;font-weight:600;color:#1e293b;"><?php echo e($row[2]); ?></p>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bm-card">
                        <div class="bm-card-head"><h3><i class="fas fa-newspaper" style="color:#213F99;margin-right:6px;"></i>Posts from <?php echo e($barangayData['name']); ?></h3></div>
                        <div style="padding:0 20px 16px;display:flex;flex-direction:column;gap:14px;">
                            <?php $__currentLoopData = $barangayData['programs']['current_programs']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prog): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div style="border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
                                <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                                    <div style="width:40px;height:40px;border-radius:50%;background:<?php echo e($brgyColor); ?>;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;flex-shrink:0;"><?php echo e($initials); ?></div>
                                    <div>
                                        <p style="font-size:13px;font-weight:700;color:#0d1b4b;">SK <?php echo e($barangayData['name']); ?></p>
                                        <p style="font-size:11px;color:#94a3b8;"><span style="background:#dbeafe;color:#1d4ed8;padding:2px 7px;border-radius:8px;font-size:10px;font-weight:700;text-transform:uppercase;margin-right:4px;">Program</span><?php echo e($prog['timeline']); ?></p>
                                    </div>
                                </div>
                                <h4 style="font-size:15px;font-weight:700;color:#0d1b4b;margin-bottom:4px;"><?php echo e($prog['title']); ?></h4>
                                <p style="font-size:13px;color:#475569;"><?php echo e($prog['sector']); ?> program · <?php echo e($prog['participants']); ?> participants</p>
                                <div style="display:flex;gap:8px;margin-top:12px;padding-top:10px;border-top:1px solid #f1f5f9;">
                                    <button style="flex:1;padding:7px;border:none;background:none;border-radius:8px;font-size:12px;color:#64748b;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'"><i class="fas fa-thumbs-up"></i> Like</button>
                                    <button style="flex:1;padding:7px;border:none;background:none;border-radius:8px;font-size:12px;color:#64748b;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'"><i class="fas fa-comment"></i> Comment</button>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <div id="section-abyip" style="display:none;">
                
                <div style="margin-bottom:24px;">
                    <h2 style="font-size:24px;font-weight:800;color:#0d1b4b;margin-bottom:4px;"><i class="fas fa-file-invoice-dollar" style="color:#213F99;margin-right:8px;"></i>Barangay ABYIP</h2>
                    <p style="font-size:14px;color:#64748b;">Annual Budget and Work Plan - Summary and Reports</p>
                </div>

                
                <section class="bm-card" style="margin-bottom:18px;">
                    <div class="bm-card-head" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                        <h3><i class="fas fa-file-invoice-dollar" style="color:#213F99;margin-right:6px;"></i>Submitted ABYIP Reports</h3>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <div style="position:relative;">
                                <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:12px;"></i>
                                <input type="text" id="abyipSearchInput" onkeyup="searchAbyipReports()" placeholder="Search reports..." style="padding:6px 10px 6px 32px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;color:#475569;width:200px;">
                            </div>
                            <select id="abyipFilterYear" onchange="filterAbyipByYear()" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;color:#475569;">
                                <option value="all">All Years</option>
                                <option value="2026">2026</option>
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                            </select>
                            <select id="abyipFilterRecent" onchange="filterAbyipRecent()" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;color:#475569;">
                                <option value="all">All Reports</option>
                                <option value="recent">Recently Submitted</option>
                            </select>
                        </div>
                    </div>
                    <div class="bm-table-wrap">
                        <table class="bm-table" id="abyipTable">
                            <thead>
                                <tr>
                                    <th><span class="bm-th-text">Title</span></th>
                                    <th><span class="bm-th-text">Date Created</span></th>
                                    <th><span class="bm-th-text">Time Created</span></th>
                                    <th><span class="bm-th-text">Status</span></th>
                                    <th><span class="bm-th-text">Report</span></th>
                                    <th><span class="bm-th-text">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $barangayData['abyip']['reports'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr data-date="<?php echo e(strtotime($report['date_submitted'] ?? '')); ?>" data-report-id="<?php echo e($report['id'] ?? ''); ?>" data-report-name="<?php echo e($report['name'] ?? ''); ?>" data-barangay="<?php echo e($barangayData['name'] ?? ''); ?>" data-type="abyip">
                                    <td style="font-weight:600;color:#0d1b4b;"><?php echo e($report['name'] ?? 'N/A'); ?></td>
                                    <td style="font-size:13px;color:#64748b;"><?php echo e(date('M d, Y', strtotime($report['date_submitted'] ?? 'now'))); ?></td>
                                    <td style="font-size:13px;color:#64748b;"><?php echo e(date('h:i A', strtotime($report['date_submitted'] ?? 'now'))); ?></td>
                                    <td>
                                        <select onchange="updateReportStatus(this, '<?php echo e($report['id'] ?? ''); ?>', 'abyip', '<?php echo e($barangayData['name'] ?? ''); ?>')" style="padding:4px 8px;border:1px solid #e2e8f0;border-radius:6px;font-size:11px;color:#475569;background:#fff;cursor:pointer;">
                                            <option value="pending" <?php echo e(($report['status'] ?? 'pending') === 'pending' ? 'selected' : ''); ?>>Pending</option>
                                            <option value="approved" <?php echo e(($report['status'] ?? '') === 'approved' ? 'selected' : ''); ?>>Approved</option>
                                            <option value="rejected" <?php echo e(($report['status'] ?? '') === 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                                        </select>
                                    </td>
                                    <td>
                                        <?php if(!empty($report['file'])): ?>
                                        <a href="<?php echo e($report['file']); ?>" target="_blank" style="display:inline-flex;align-items:center;gap:4px;font-size:11px;color:#213F99;text-decoration:none;background:#eff3ff;padding:4px 10px;border-radius:6px;">
                                            <i class="fas fa-file-pdf"></i> View PDF
                                        </a>
                                        <?php else: ?>
                                        <span style="font-size:11px;color:#94a3b8;">No file</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:4px;">
                                            <button onclick="editReport('<?php echo e($report['id'] ?? ''); ?>', 'abyip')" title="Edit Status" style="padding:4px 8px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:11px;color:#213F99;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="deleteReport('<?php echo e($report['id'] ?? ''); ?>', 'abyip')" title="Delete" style="padding:4px 8px;border:1px solid #fee2e2;background:#fff;border-radius:6px;font-size:11px;color:#dc2626;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff'">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button onclick="archiveReport('<?php echo e($report['id'] ?? ''); ?>', 'abyip')" title="Archive" style="padding:4px 8px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:11px;color:#64748b;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" style="text-align:center;padding:20px;color:#94a3b8;">No ABYIP reports submitted yet</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-top:1px solid #e2e8f0;flex-wrap:wrap;gap:8px;">
                        <span style="font-size:12px;color:#64748b;white-space:nowrap;">Showing <span id="abyipStart">1</span>-<span id="abyipEnd">5</span> of <span id="abyipTotal"><?php echo e(count($barangayData['abyip']['reports'] ?? [])); ?></span></span>
                        <div style="display:flex;gap:4px;flex-wrap:wrap;">
                            <button onclick="prevAbyipPage()" style="padding:6px 10px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:11px;color:#213F99;cursor:pointer;transition:background .2s;white-space:nowrap;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                                <i class="fas fa-chevron-left"></i> Prev
                            </button>
                            <span id="abyipPageInfo" style="padding:6px 10px;font-size:11px;color:#64748b;white-space:nowrap;">Page 1</span>
                            <button onclick="nextAbyipPage()" style="padding:6px 10px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:11px;color:#213F99;cursor:pointer;transition:background .2s;white-space:nowrap;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            
            <div id="section-accomplishment" style="display:none;">
                
                <div style="margin-bottom:24px;">
                    <h2 style="font-size:24px;font-weight:800;color:#0d1b4b;margin-bottom:4px;"><i class="fas fa-trophy" style="color:#213F99;margin-right:8px;"></i>Program Accomplishment</h2>
                    <p style="font-size:14px;color:#64748b;">Summary of program performance and achievements</p>
                </div>

                
                <section class="bm-card" style="margin-bottom:18px;">
                    <div class="bm-card-head" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                        <h3><i class="fas fa-chart-bar" style="color:#213F99;margin-right:6px;"></i>Programs Report</h3>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <div style="position:relative;">
                                <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:12px;"></i>
                                <input type="text" id="programSearchInput" onkeyup="searchPrograms()" placeholder="Search programs..." style="padding:6px 10px 6px 32px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;color:#475569;width:200px;">
                            </div>
                            <select id="programFilterYear" onchange="filterProgramByYear()" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;color:#475569;">
                                <option value="all">All Years</option>
                                <option value="2026">2026</option>
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                            </select>
                            <select id="programFilterRecent" onchange="filterProgramRecent()" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;color:#475569;">
                                <option value="all">All Programs</option>
                                <option value="recent">Recently Added</option>
                            </select>
                        </div>
                    </div>
                    <div class="bm-table-wrap">
                        <table class="bm-table" id="accomplishmentTable">
                            <thead>
                                <tr>
                                    <th><span class="bm-th-text">Program Title</span></th>
                                    <th><span class="bm-th-text">Date Created</span></th>
                                    <th><span class="bm-th-text">Description</span></th>
                                    <th><span class="bm-th-text">Committee</span></th>
                                    <th><span class="bm-th-text">Duration</span></th>
                                    <th><span class="bm-th-text">Status</span></th>
                                    <th><span class="bm-th-text">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $barangayData['program_list'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prog): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr data-date="<?php echo e(strtotime($prog['timeline'] ?? '')); ?>" data-sector="<?php echo e($prog['sector'] ?? 'N/A'); ?>" data-description="<?php echo e($prog['description'] ?? 'N/A'); ?>">
                                    <td style="font-weight:600;color:#0d1b4b;"><?php echo e($prog['title'] ?? 'N/A'); ?></td>
                                    <td style="font-size:13px;color:#64748b;"><?php echo e(date('M d, Y', strtotime($prog['start_date'] ?? 'now'))); ?></td>
                                    <td style="font-size:13px;color:#64748b;max-width:250px;"><?php echo e(Str::limit($prog['description'] ?? 'No description', 50)); ?></td>
                                    <td style="font-size:13px;color:#64748b;"><?php echo e($prog['sector'] ?? 'N/A'); ?></td>
                                    <td style="font-size:13px;color:#64748b;"><?php echo e($prog['timeline'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700;
                                        <?php if(($prog['status'] ?? '') === 'Completed'): ?>
                                            background:#dcfce7;color:#15803d;
                                        <?php elseif(($prog['status'] ?? '') === 'Ongoing'): ?>
                                            background:#dbeafe;color:#1d4ed8;
                                        <?php else: ?>
                                            background:#fef3c7;color:#b45309;
                                        <?php endif; ?>
                                        ">
                                            <?php echo e($prog['status'] ?? 'Planned'); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:4px;">
                                            <button onclick="viewProgramMore('<?php echo e($prog['title'] ?? ''); ?>')" title="View More" style="padding:4px 8px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:11px;color:#213F99;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="deleteProgram('<?php echo e($prog['title'] ?? ''); ?>')" title="Delete" style="padding:4px 8px;border:1px solid #fee2e2;background:#fff;border-radius:6px;font-size:11px;color:#dc2626;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff'">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button onclick="archiveProgram('<?php echo e($prog['title'] ?? ''); ?>')" title="Archive" style="padding:4px 8px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:11px;color:#64748b;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" style="text-align:center;padding:20px;color:#94a3b8;">No programs found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-top:1px solid #e2e8f0;flex-wrap:wrap;gap:8px;">
                        <span style="font-size:12px;color:#64748b;white-space:nowrap;">Showing <span id="progStart">1</span>-<span id="progEnd">5</span> of <span id="progTotal"><?php echo e(count($barangayData['program_list'] ?? [])); ?></span></span>
                        <div style="display:flex;gap:4px;flex-wrap:wrap;">
                            <button onclick="prevProgPage()" style="padding:6px 10px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:11px;color:#213F99;cursor:pointer;transition:background .2s;white-space:nowrap;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                                <i class="fas fa-chevron-left"></i> Prev
                            </button>
                            <span id="progPageInfo" style="padding:6px 10px;font-size:11px;color:#64748b;white-space:nowrap;">Page 1</span>
                            <button onclick="nextProgPage()" style="padding:6px 10px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:11px;color:#213F99;cursor:pointer;transition:background .2s;white-space:nowrap;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>

    <div id="programDetailModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;padding:20px;">
        <div style="background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.3);max-width:600px;width:100%;max-height:90vh;overflow-y:auto;animation:slideIn .3s ease-out;">
            
            <div style="display:flex;align-items:center;justify-content:space-between;padding:24px;border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                <h2 id="modalProgramName" style="font-size:20px;font-weight:800;color:#0d1b4b;margin:0;"></h2>
                <button onclick="closeProgramDetailModal()" style="border:none;background:none;font-size:24px;color:#64748b;cursor:pointer;padding:0;width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:6px;transition:background .2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            
            <div style="padding:24px;display:flex;flex-direction:column;gap:20px;">
                
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:8px;background:#f3e8ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-tag" style="color:#7c3aed;font-size:18px;"></i>
                    </div>
                    <div>
                        <p style="font-size:12px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;text-transform:uppercase;">Program Sector</p>
                        <p id="modalProgramSector" style="font-size:14px;color:#0d1b4b;margin:0;font-weight:600;"></p>
                    </div>
                </div>

                
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:8px;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-file-alt" style="color:#d97706;font-size:18px;"></i>
                    </div>
                    <div>
                        <p style="font-size:12px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;text-transform:uppercase;">Description</p>
                        <p id="modalProgramDescription" style="font-size:14px;color:#0d1b4b;margin:0;line-height:1.5;"></p>
                    </div>
                </div>

                
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:8px;background:#eff3ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-calendar" style="color:#213F99;font-size:18px;"></i>
                    </div>
                    <div>
                        <p style="font-size:12px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;text-transform:uppercase;">Program Date</p>
                        <p id="modalProgramDate" style="font-size:14px;color:#0d1b4b;margin:0;font-weight:600;"></p>
                    </div>
                </div>

                
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:8px;background:#dbeafe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-users" style="color:#1d4ed8;font-size:18px;"></i>
                    </div>
                    <div>
                        <p style="font-size:12px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;text-transform:uppercase;">Participants Joined</p>
                        <p id="modalProgramRegistered" style="font-size:14px;color:#0d1b4b;margin:0;font-weight:600;"></p>
                    </div>
                </div>

                
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:8px;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-chart-pie" style="color:#15803d;font-size:18px;"></i>
                    </div>
                    <div>
                        <p style="font-size:12px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;text-transform:uppercase;">Attendance Rate</p>
                        <p id="modalProgramAttendance" style="font-size:14px;color:#0d1b4b;margin:0;font-weight:600;"></p>
                    </div>
                </div>

                
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:8px;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-star" style="color:#d97706;font-size:18px;"></i>
                    </div>
                    <div>
                        <p style="font-size:12px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;text-transform:uppercase;">Evaluation Rate</p>
                        <p id="modalProgramEvaluation" style="font-size:14px;color:#0d1b4b;margin:0;font-weight:600;"></p>
                    </div>
                </div>
            </div>

            
            <div style="display:flex;gap:8px;padding:16px 24px;border-top:1px solid #e2e8f0;background:#f8fafc;justify-content:flex-end;">
                <button onclick="printProgramDetail()" style="padding:8px 16px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:13px;font-weight:600;color:#213F99;cursor:pointer;transition:background .2s;display:flex;align-items:center;gap:6px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                    <i class="fas fa-print"></i> Print
                </button>
                <button onclick="closeProgramDetailModal()" style="padding:8px 16px;border:none;background:#213F99;border-radius:6px;font-size:13px;font-weight:600;color:#fff;cursor:pointer;transition:background .2s;display:flex;align-items:center;gap:6px;" onmouseover="this.style.background='#1a2f7a'" onmouseout="this.style.background='#213F99'">
                    <i class="fas fa-check"></i> Close
                </button>
            </div>
        </div>
    </div>

    
    <style>
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    
    <div id="warningModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;padding:20px;">
        <div style="background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.3);max-width:500px;width:100%;animation:slideIn .3s ease-out;">
            
            <div style="display:flex;align-items:center;justify-content:space-between;padding:24px;border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                <h2 id="warningModalTitle" style="font-size:18px;font-weight:800;color:#0d1b4b;margin:0;"></h2>
                <button onclick="closeWarningModal()" style="border:none;background:none;font-size:24px;color:#64748b;cursor:pointer;padding:0;width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:6px;transition:background .2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            
            <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                <input type="hidden" id="warningTypeInput">
                
                
                <div>
                    <label style="display:block;font-size:13px;font-weight:700;color:#0d1b4b;margin-bottom:8px;">
                        <i class="fas fa-exclamation-circle" style="margin-right:6px;color:#dc2626;"></i>Select Reason for Warning
                    </label>
                    <select id="warningReasonSelect" onchange="handleReasonChange()" style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="">-- Choose a reason --</option>
                        <option value="Missing ABYIP Report">Missing ABYIP Report</option>
                        <option value="Missing Accomplishment Report">Missing Accomplishment Report</option>
                        <option value="Delayed Submission">Delayed Submission</option>
                        <option value="Incomplete Documentation">Incomplete Documentation</option>
                        <option value="Low Participation Rate">Low Participation Rate</option>
                        <option value="Budget Mismanagement">Budget Mismanagement</option>
                        <option value="other">Other (Please specify)</option>
                    </select>
                </div>

                
                <div id="otherReasonInput" style="display:none;">
                    <input type="text" placeholder="Please specify the reason..." style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;box-sizing:border-box;">
                </div>

                
                <div>
                    <label style="display:block;font-size:13px;font-weight:700;color:#0d1b4b;margin-bottom:8px;">
                        <i class="fas fa-comment" style="margin-right:6px;color:#213F99;"></i>Additional Message (Optional)
                    </label>
                    <textarea id="warningMessage" placeholder="Add any additional details or instructions..." style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;font-family:inherit;resize:vertical;min-height:80px;box-sizing:border-box;"></textarea>
                </div>
            </div>

            
            <div style="display:flex;gap:8px;padding:16px 24px;border-top:1px solid #e2e8f0;background:#f8fafc;justify-content:flex-end;">
                <button onclick="closeWarningModal()" style="padding:8px 16px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:13px;font-weight:600;color:#213F99;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                    Cancel
                </button>
                <button onclick="sendWarning()" style="padding:8px 16px;border:none;background:#dc2626;border-radius:6px;font-size:13px;font-weight:600;color:#fff;cursor:pointer;transition:background .2s;display:flex;align-items:center;gap:6px;" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">
                    <i class="fas fa-paper-plane"></i> Send Warning
                </button>
            </div>
        </div>
    </div>

    

    <script src="<?php echo e(url('/shared/js/loading.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout::app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\BarangayMonitoring\Providers/../Views/show.blade.php ENDPATH**/ ?>