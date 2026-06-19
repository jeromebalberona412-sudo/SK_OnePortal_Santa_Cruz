<?php
    $sharedData = [
        'data-account-id' => $account->id,
        'data-first-name' => $firstName ?? '',
        'data-last-name' => $lastName ?? '',
        'data-middle-name' => $middleName !== '' ? mb_strtoupper($middleName, 'UTF-8') : '',
        'data-suffix' => $profile?->suffix ?? '',
        'data-sex' => $profile?->sex ?? '',
        'data-date-of-birth' => $profile?->date_of_birth?->toDateString() ?? '',
        'data-age' => $profile?->age ?? '',
        'data-contact-number' => $profile?->contact_number ?? '',
        'data-email' => $account->email ?? '',
        'data-position' => $profile?->position ?? '',
        'data-federation-position' => $profile?->federation_position ?? '',
        'data-account-role' => $account->role ?? '',
        'data-barangay-id' => $account->barangay_id ?? '',
        'data-barangay-name' => $account->barangay?->name ?? '',
        'data-municipality' => $profile?->municipality ?? '',
        'data-province' => $profile?->province ?? '',
        'data-region' => $profile?->region ?? '',
        'data-status' => $account->status ?? '',
        'data-term-status' => $term?->status ?? 'ACTIVE',
        'data-term-start' => $term?->term_start?->toDateString() ?? '',
        'data-term-end' => $term?->term_end?->toDateString() ?? '',
    ];
?>

<div class="account-actions-menu">
    <button type="button"
            class="account-actions-trigger"
            aria-label="Account actions for <?php echo e($displayName); ?>"
            aria-haspopup="true"
            aria-expanded="false">
        <i class="fas fa-ellipsis-h" aria-hidden="true"></i>
    </button>

    <div class="account-actions-dropdown" role="menu">
        <button type="button"
                class="account-actions-item account-actions-item-view btn-view-account"
                role="menuitem"
                <?php $__currentLoopData = $sharedData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($value); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                data-email-verified-at="<?php echo e($account->email_verified_at?->format('m/d/Y h:i A') ?? ''); ?>">
            <i class="fas fa-eye" aria-hidden="true"></i>
            <span>View Details</span>
        </button>
        <button type="button"
                class="account-actions-item account-actions-item-edit btn-edit-account"
                role="menuitem"
                <?php $__currentLoopData = $sharedData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($value); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>>
            <i class="fas fa-pen" aria-hidden="true"></i>
            <span><?php echo e(($hideDelete ?? false) ? 'Assign Federation Position' : 'Edit Member'); ?></span>
        </button>
        <?php if(empty($hideDelete)): ?>
        <button type="button"
                class="account-actions-item account-actions-item-danger btn-delete-account"
                role="menuitem"
                data-account-id="<?php echo e($account->id); ?>"
                data-display-name="<?php echo e($displayName); ?>">
            <i class="fas fa-trash" aria-hidden="true"></i>
            <span>Delete Account</span>
        </button>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\Accounts\Providers/../Views/account_actions_menu.blade.php ENDPATH**/ ?>