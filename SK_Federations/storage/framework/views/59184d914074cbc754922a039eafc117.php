<?php $__env->startSection('title', 'Manage Account'); ?>

<?php
    $isOfficials = ($accountType ?? 'sk_federation') === 'sk_officials';
    $pageTitle = $isOfficials ? 'Manage SK Officials Account' : 'Manage SK Federation Account';
    $pageSubtitle = $isOfficials
        ? 'Create or manage SK Officials member accounts'
        : 'Assign federation positions to SK Chairpersons from each barangay';
    $addLabel = $isOfficials ? 'Add SK Official' : 'Add Federation Member';
    $positionFilterLabel = $isOfficials ? 'Position' : 'Federation Position';
    $tableColspan = $isOfficials ? 7 : 6;
    $accountCssVersion = @filemtime(app_path('Modules/Accounts/assets/css/account.css')) ?: time();
    $accountJsVersion = @filemtime(app_path('Modules/Accounts/assets/js/account.js')) ?: time();
    $batchTemplateType = $isOfficials ? 'officials' : 'federation';
    $batchRole = $isOfficials ? 'sk_official' : 'sk_fed';
?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(url('/modules/accounts/css/account.css')); ?>?v=<?php echo e($accountCssVersion); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="main-content-modern accounts-page container-fluid" id="mainContent"
     x-data="accountsPage()"
     x-init="init()"
     data-account-type="<?php echo e($accountType ?? 'sk_federation'); ?>"
     data-batch-role="<?php echo e($batchRole); ?>"
     data-batch-template-type="<?php echo e($batchTemplateType); ?>"
     <?php if (! ($isOfficials)): ?>
     data-taken-federation-positions="<?php echo e(json_encode(array_values($takenFederationPositions ?? []))); ?>"
     <?php endif; ?>>

    <div class="manage-account-container">
        <div class="page-header-modern-with-button">
            <div class="page-header-top">
                <h1 class="page-title-modern" id="pageTitle"><?php echo e($pageTitle); ?></h1>
                <p class="page-subtitle-modern" id="pageSubtitle"><?php echo e($pageSubtitle); ?></p>
            </div>
            <div class="page-header-filters">
                <form method="get" action="#" class="accounts-filter-form" id="accountsFilterForm" novalidate>
                    <div class="accounts-filter-grid">
                        <div class="filter-dropdown-container">
                            <select id="barangayFilter" class="filter-dropdown form-select" name="barangay_id" aria-label="Filter by barangay">
                                <option value="">All Barangays</option>
                                <?php $__currentLoopData = $barangays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $barangay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($barangay->id); ?>" <?php echo e(request('barangay_id') == $barangay->id ? 'selected' : ''); ?>><?php echo e($barangay->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="filter-dropdown-container">
                            <select id="positionFilter" class="filter-dropdown form-select" name="position" aria-label="Filter by position">
                                <option value="">All Positions</option>
                                <?php $__currentLoopData = $positionOptions ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($value); ?>" <?php echo e(request('position') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="search-container">
                            <div class="search-input-wrap">
                                <input type="text" id="searchInput" name="search" class="search-input form-control" value="<?php echo e(request('search')); ?>" placeholder="Search accounts..." aria-label="Search accounts">
                                <button type="button" class="search-btn" id="searchBtn" aria-label="Search">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.35-4.35"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <?php if($isOfficials): ?>
                        <div class="header-action-buttons">
                            <button type="button" class="btn-primary-modern btn-green" id="addAccountBtn" onclick="openAddAccountModal()">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                                <span id="addButtonText"><?php echo e($addLabel); ?></span>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <?php if($isOfficials): ?>
        <div class="accounts-table-toolbar"
             x-show="selectedCount > 0"
             x-transition:enter="float-delete-enter"
             x-transition:leave="float-delete-leave"
             style="display:none;">
            <button type="button" class="btn-float-delete" @click="openBulkDelete()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                <span x-text="'Delete ' + selectedCount + ' row' + (selectedCount === 1 ? '' : 's')"></span>
            </button>
        </div>
        <?php endif; ?>

        <div class="table-card-modern">
            <div class="table-responsive">
                <table class="accounts-table" id="accountsTable">
                    <thead>
                        <tr>
                            <?php if($isOfficials): ?>
                            <th class="th-checkbox">
                                <input type="checkbox" class="account-checkbox account-checkbox-header"
                                       :checked="selectAll" @change="toggleSelectAll($event.target.checked)" aria-label="Select all visible rows">
                            </th>
                            <?php endif; ?>
                            <th class="th-name accounts-th-sortable" data-sort-key="name" data-sort-type="text" aria-sort="none">
                                <button type="button" class="accounts-sort-btn" aria-haspopup="menu" aria-expanded="false">
                                    Fullname <span class="accounts-sort-col-hint">(LN, FN, MI, Suffix)</span>
                                    <span class="accounts-sort-icon" aria-hidden="true"></span>
                                </button>
                            </th>
                            <th class="th-email">Email Address</th>
                            <?php if($isOfficials): ?>
                                <th class="th-barangay">Barangay</th>
                            <?php else: ?>
                                <th class="th-barangay">Barangay</th>
                            <?php endif; ?>
                            <th class="th-position"><?php echo e($isOfficials ? 'Position' : 'SK Position'); ?></th>
                            <?php if($isOfficials): ?>
                                <th class="th-term">Term End</th>
                            <?php else: ?>
                                <th class="th-federation-position">Federation Position</th>
                            <?php endif; ?>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="accountsTableBody">
                        <?php $__empty_1 = true; $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $profile = $account->officialProfile;
                                $term = $profile?->latestTerm;
                                $firstName = $profile?->first_name ? mb_strtoupper($profile->first_name, 'UTF-8') : null;
                                $lastName = $profile?->last_name ? mb_strtoupper($profile->last_name, 'UTF-8') : null;
                                $middleName = trim((string) ($profile?->middle_name ?? ''));
                                $middleInitial = $middleName !== '' ? mb_strtoupper(mb_substr($middleName, 0, 1, 'UTF-8'), 'UTF-8').'.' : null;
                                $suffixDisplay = $profile?->suffix;
                                if (in_array($suffixDisplay, ['NONE', 'None', null, ''], true)) {
                                    $suffixDisplay = null;
                                }
                                $nameCore = collect([$lastName, $firstName])->filter()->implode(', ');
                                $fullName = trim(collect([
                                    $nameCore,
                                    $middleInitial,
                                    $suffixDisplay,
                                ])->filter()->implode(' '));
                                $displayName = $fullName !== '' ? $fullName : ($account->name ?? 'N/A');
                                $sortName = strtolower(trim(collect([$lastName, $firstName, $middleInitial, $suffixDisplay])->filter()->implode(' ')));
                                $federationPosition = $profile?->displayFederationPosition();
                                $filterPosition = $isOfficials ? ($profile?->position ?? '') : ($profile?->federation_position ?? '');
                                $searchBlob = strtolower(trim(collect([
                                    $displayName,
                                    $account->email,
                                    $account->barangay?->name,
                                    $profile?->position,
                                    $federationPosition,
                                ])->filter()->implode(' ')));
                            ?>
                            <tr data-account-id="<?php echo e($account->id); ?>"
                                data-barangay-id="<?php echo e($account->barangay_id ?? ''); ?>"
                                data-filter-position="<?php echo e($filterPosition); ?>"
                                data-search-text="<?php echo e($searchBlob); ?>"
                                data-sort-name="<?php echo e($sortName); ?>"
                                data-sort-email="<?php echo e(strtolower($account->email ?? '')); ?>"
                                data-sort-barangay="<?php echo e(strtolower($account->barangay?->name ?? '')); ?>"
                                data-sort-position="<?php echo e(strtolower($profile?->position ?? '')); ?>"
                                data-sort-federation-position="<?php echo e(strtolower($federationPosition ?? '')); ?>"
                                data-sort-term="<?php echo e($term?->term_end?->format('Y-m-d') ?? ''); ?>">
                                <?php if($isOfficials): ?>
                                <td class="td-checkbox">
                                    <input type="checkbox" class="account-checkbox account-row-checkbox"
                                           value="<?php echo e($account->id); ?>"
                                           @change="toggleRow(<?php echo e($account->id); ?>, $event.target.checked)"
                                           :checked="selectedRows.includes(<?php echo e($account->id); ?>)"
                                           aria-label="Select <?php echo e($displayName); ?>">
                                </td>
                                <?php endif; ?>
                                <td class="td-name"><?php echo e($displayName); ?></td>
                                <td class="td-email"><?php echo e($account->email); ?></td>
                                <td class="td-barangay"><?php echo e($account->barangay?->name ?? '—'); ?></td>
                                <td class="td-position">
                                    <?php if($isOfficials): ?>
                                        <?php echo e($profile?->position ?? '—'); ?>

                                    <?php else: ?>
                                        <?php echo e($profile?->position ?? '—'); ?>

                                    <?php endif; ?>
                                </td>
                                <?php if($isOfficials): ?>
                                    <td class="td-term"><?php echo e($term?->term_end?->format('m/d/Y') ?? '—'); ?></td>
                                <?php else: ?>
                                    <td class="td-federation-position">
                                        <?php if($federationPosition): ?>
                                            <?php echo e($federationPosition); ?>

                                        <?php else: ?>
                                            <span class="text-muted">Not assigned</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td class="td-actions">
                                    <?php echo $__env->make('accounts::account_actions_menu', [
                                        'account' => $account,
                                        'profile' => $profile,
                                        'term' => $term,
                                        'displayName' => $displayName,
                                        'firstName' => $firstName,
                                        'lastName' => $lastName,
                                        'middleName' => $middleName,
                                        'hideDelete' => ! $isOfficials,
                                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="<?php echo e($tableColspan); ?>" class="text-center">No accounts found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="accounts-page-footer pagination-footer" aria-label="Table pagination">
            <div class="pagination-footer-nav">
                <button type="button" class="pagination-arrow" id="prevBtn" disabled aria-label="Previous page">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <span class="pagination-page-label">Page</span>
                <input type="number" class="pagination-page-input" id="pageInput" value="1" min="1" aria-label="Current page">
                <span class="pagination-page-of">of <span id="totalPages">1</span></span>
                <button type="button" class="pagination-arrow" id="nextBtn" disabled aria-label="Next page">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                </button>
            </div>
            <div class="pagination-footer-right">
                <select id="rowsPerPageSelect" class="pagination-rows-select" aria-label="Rows per page">
                    <option value="10">10 rows</option>
                    <option value="50">50 rows</option>
                    <option value="100">100 rows</option>
                </select>
                <span class="pagination-record-count" id="paginationInfo">0 records</span>
            </div>
        </div>
    </div>
</div>

<div id="accountsSortMenu" class="accounts-sort-menu" hidden role="menu" aria-label="Sort options"></div>

<?php if($isOfficials): ?>
<?php echo $__env->make('accounts::form_sk_officials', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php else: ?>
<?php echo $__env->make('accounts::assign_federation_position_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>
<?php echo $__env->make('accounts::view_account', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('accounts::delete_account_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div id="accountToast" role="status" aria-live="polite"><span id="accountToastMsg">Account successfully created!</span></div>
<div id="accountToastEdit" role="status" aria-live="polite"><span id="accountToastEditMsg">Account updated successfully!</span></div>
<div id="accountToastDelete" role="status" aria-live="polite"><span id="accountToastDeleteMsg">Account deleted successfully!</span></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="<?php echo e(url('/modules/accounts/js/account.js')); ?>?v=<?php echo e($accountJsVersion); ?>"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout::app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\Accounts\Providers/../Views/manage_account.blade.php ENDPATH**/ ?>