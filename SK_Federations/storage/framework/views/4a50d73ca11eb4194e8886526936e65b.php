

<?php $__env->startSection('title', 'SK Barangay Logos'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(url('/modules/barangay-logos/css/barangay-logos.css')); ?>?v=<?php echo e(@filemtime(app_path('Modules/BarangayLogos/assets/css/barangay-logos.css')) ?: time()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div id="blUploadOverlay" class="bl-upload-overlay" aria-hidden="true" hidden>
    <div class="bl-upload-overlay-inner">
        <div class="bl-upload-spinner">
            <div class="bl-upload-spinner-ring"></div>
            <div class="bl-upload-spinner-ring bl-upload-spinner-ring--2"></div>
            <div class="bl-upload-spinner-dot"></div>
        </div>
        <p class="bl-upload-overlay-title" id="blUploadOverlayTitle">Uploading Logo</p>
        <p class="bl-upload-overlay-sub">Please wait...</p>
    </div>
</div>

<div class="barangay-logos-page container-fluid" id="mainContent">
    <div class="barangay-logos-container">

        
        <div class="bl-page-header">
            <div class="bl-page-header-text">
                <h1 class="bl-page-title">SK Barangay Logos</h1>
                <p class="bl-page-subtitle">Manage logo images for each barangay in Santa Cruz</p>
            </div>

            <div class="bl-header-controls">
                    <div class="bl-search-wrap">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" class="bl-search-icon">
                            <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                            <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <input
                            type="text"
                            id="blSearchInput"
                            class="bl-search-input form-control"
                            placeholder="Search barangay..."
                            autocomplete="off"
                            aria-label="Search barangay"
                        />
                        <button type="button" class="bl-search-clear" id="blSearchClear" aria-label="Clear search" style="display:none;">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>

                    <div class="bl-counter-pill">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" class="bl-counter-icon">
                            <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.8"/>
                            <circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="1.8"/>
                            <polyline points="21 15 16 10 5 21" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                        <span id="uploadedCount"><?php echo e($logos->count()); ?></span>
                        <span class="bl-counter-sep">/</span>
                        <span>26</span>
                        <span class="bl-counter-label">Uploaded</span>
                    </div>

                    <button type="button" class="bl-toggle-btn" id="toggleLogosBtn">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" id="toggleIcon">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <span id="toggleBtnText">Hide Logos</span>
                        </button>
            </div>
        </div>

        
        <div class="bl-grid row" id="barangayGrid">

            <?php
                $barangayList = $barangays->isNotEmpty() ? $barangays : collect([
                    (object)['id' => null, 'name' => 'Alipit'],
                    (object)['id' => null, 'name' => 'Bagumbayan'],
                    (object)['id' => null, 'name' => 'Barangay I (Poblacion)'],
                    (object)['id' => null, 'name' => 'Barangay II (Poblacion)'],
                    (object)['id' => null, 'name' => 'Barangay III (Poblacion)'],
                    (object)['id' => null, 'name' => 'Barangay IV (Poblacion)'],
                    (object)['id' => null, 'name' => 'Barangay V (Poblacion)'],
                    (object)['id' => null, 'name' => 'Bubukal'],
                    (object)['id' => null, 'name' => 'Calios'],
                    (object)['id' => null, 'name' => 'Duhat'],
                    (object)['id' => null, 'name' => 'Gatid'],
                    (object)['id' => null, 'name' => 'Jasaan'],
                    (object)['id' => null, 'name' => 'Labuin'],
                    (object)['id' => null, 'name' => 'Malinao'],
                    (object)['id' => null, 'name' => 'Oogong'],
                    (object)['id' => null, 'name' => 'Pagsawitan'],
                    (object)['id' => null, 'name' => 'Palasan'],
                    (object)['id' => null, 'name' => 'Patimbao'],
                    (object)['id' => null, 'name' => 'San Jose'],
                    (object)['id' => null, 'name' => 'San Juan'],
                    (object)['id' => null, 'name' => 'San Pablo Norte'],
                    (object)['id' => null, 'name' => 'San Pablo Sur'],
                    (object)['id' => null, 'name' => 'Santisima Cruz'],
                    (object)['id' => null, 'name' => 'Santo Angel Central'],
                    (object)['id' => null, 'name' => 'Santo Angel Norte'],
                    (object)['id' => null, 'name' => 'Santo Angel Sur'],
                ]);
            ?>

            <?php $__currentLoopData = $barangayList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $barangay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $existingLogo = $barangay->id ? ($logos[$barangay->id] ?? null) : null;
            ?>
            <div
                class="bl-card<?php echo e($existingLogo ? ' has-logo' : ''); ?>"
                id="card-<?php echo e($index); ?>"
                data-barangay-id="<?php echo e($barangay->id); ?>"
                data-logo-id="<?php echo e($existingLogo?->id); ?>"
            >

                
                <div class="bl-preview-box" id="previewBox-<?php echo e($index); ?>">

                    
                    <div class="bl-placeholder" id="placeholder-<?php echo e($index); ?>" style="<?php echo e($existingLogo ? 'display:none;' : ''); ?>">
                        <svg viewBox="0 0 24 24" fill="none" class="bl-placeholder-icon" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="1.5"/>
                            <circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="1.5"/>
                            <polyline points="21 15 16 10 5 21" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                        <span class="bl-placeholder-text">No Logo</span>
                    </div>

                    
                    <img
                        id="img-<?php echo e($index); ?>"
                        src="<?php echo e($existingLogo?->url ?? ''); ?>"
                        alt="<?php echo e($barangay->name); ?> logo"
                        class="bl-logo-img"
                        style="<?php echo e($existingLogo && $logosVisible ?? true ? 'display:block;' : 'display:none;'); ?>"
                        onerror="this.style.display='none';document.getElementById('placeholder-<?php echo e($index); ?>').style.display='';"
                    />

                    
                    <div class="bl-hidden-overlay" id="overlay-<?php echo e($index); ?>" style="display:none;">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <span>Hidden</span>
                    </div>

                    
                    <button
                        type="button"
                        class="bl-remove-btn"
                        id="removeBtn-<?php echo e($index); ?>"
                        data-index="<?php echo e($index); ?>"
                        data-name="<?php echo e($barangay->name); ?>"
                        style="<?php echo e($existingLogo ? '' : 'display:none;'); ?>"
                        title="Remove logo"
                        aria-label="Remove logo for <?php echo e($barangay->name); ?>"
                    >
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <polyline points="3 6 5 6 21 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" stroke="white" stroke-width="2" stroke-linecap="round"/>
                            <path d="M10 11v6M14 11v6" stroke="white" stroke-width="2" stroke-linecap="round"/>
                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                
                <div class="bl-card-footer">
                    <p class="bl-barangay-name" title="<?php echo e($barangay->name); ?>"><?php echo e($barangay->name); ?></p>

                    <label
                        class="bl-upload-btn bl-upload-label"
                        id="uploadBtn-<?php echo e($index); ?>"
                        for="fileInput-<?php echo e($index); ?>"
                        data-index="<?php echo e($index); ?>"
                        data-name="<?php echo e($barangay->name); ?>"
                    >
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <polyline points="17 8 12 3 7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="12" y1="3" x2="12" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <span id="uploadBtnText-<?php echo e($index); ?>"><?php echo e($existingLogo ? 'Change Logo' : 'Upload Logo'); ?></span>
                    </label>

                    <input
                        type="file"
                        id="fileInput-<?php echo e($index); ?>"
                        class="bl-file-input"
                        data-index="<?php echo e($index); ?>"
                        data-name="<?php echo e($barangay->name); ?>"
                        accept="image/*"
                        style="display:none;"
                    />
                </div>

            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </div>

        
        <div id="blNoResults" class="bl-no-results" style="display:none;">
            No barangay found matching your search.
        </div>
    </div>
</div>


<div class="bl-modal-overlay" id="blChangeModal" role="dialog" aria-modal="true" style="display:none;">
    <div class="bl-modal bl-action-modal">

        <div class="bl-action-modal-body">
            <div class="bl-action-icon bl-action-icon-blue">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    <polyline points="17 8 12 3 7 8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="12" y1="3" x2="12" y2="15" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
            </div>
            <h3 class="bl-action-modal-title">Change Logo?</h3>
            <p class="bl-action-modal-msg">Are you sure you want to change this logo?</p>
            <p class="bl-action-modal-barangay" id="blChangeBarangayName"></p>
        </div>

        <div class="bl-modal-actions">
            <button type="button" class="bl-modal-btn-cancel" id="blChangeCancelBtn">Cancel</button>
            <button type="button" class="bl-modal-btn-action btn-blue" id="blChangeConfirmBtn">Yes, Continue</button>
        </div>

    </div>
</div>


<div class="bl-modal-overlay" id="blRemoveModal" role="dialog" aria-modal="true" style="display:none;">
    <div class="bl-modal bl-action-modal">

        <div class="bl-action-modal-body">
            <div class="bl-action-icon bl-action-icon-red">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
            </div>
            <h3 class="bl-action-modal-title">Remove Logo?</h3>
            <p class="bl-action-modal-msg">Are you sure you want to remove this logo?</p>
            <p class="bl-action-modal-barangay" id="blRemoveBarangayName"></p>
        </div>

        <div class="bl-modal-actions">
            <button type="button" class="bl-modal-btn-cancel" id="blRemoveCancelBtn">Cancel</button>
            <button type="button" class="bl-modal-btn-action btn-red" id="blRemoveConfirmBtn">Remove</button>
        </div>

    </div>
</div>


<div class="bl-modal-overlay" id="blConfirmModal" role="dialog" aria-modal="true" style="display:none;">
    <div class="bl-modal">

        <div class="bl-modal-header">
            <div class="bl-modal-header-icon">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="2"/>
                    <polyline points="21 15 16 10 5 21" stroke="currentColor" stroke-width="2"/>
                </svg>
            </div>
            <div>
                <h3 class="bl-modal-title">Upload Logo</h3>
                <p class="bl-modal-subtitle" id="blModalSubtitle">Barangay Name</p>
            </div>
            <button type="button" class="bl-modal-close" id="blUploadCancelBtn" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <div class="bl-modal-preview-wrap">
            <img id="blModalPreviewImg" src="" alt="Preview" class="bl-modal-preview-img" />
        </div>

        <div class="bl-modal-file-info">
            <span id="blModalFileName">filename.png</span>
            <span class="bl-modal-file-size" id="blModalFileSize">0 KB</span>
        </div>

        <div class="bl-modal-actions">
            <button type="button" class="bl-modal-btn-cancel" id="blUploadCancelBtn2">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
                Cancel
            </button>
            <button type="button" class="bl-modal-btn-confirm" id="blUploadConfirmBtn">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Upload Logo
            </button>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        window.barangayLogosRoutes = {
            upload: <?php echo json_encode(route('barangay-logos.upload'), 15, 512) ?>,
            deleteBase: <?php echo json_encode(url('/barangay-logos'), 15, 512) ?>,
        };
    </script>
    <script src="<?php echo e(url('/modules/barangay-logos/js/barangay-logos.js')); ?>?v=<?php echo e(@filemtime(app_path('Modules/BarangayLogos/assets/js/barangay-logos.js')) ?: time()); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout::app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\BarangayLogos\Providers/../Views/barangay-logos.blade.php ENDPATH**/ ?>