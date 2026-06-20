
<?php
    $profileCssVersion = @filemtime(app_path('Modules/Profile/assets/css/profile.css')) ?: time();
?>
<link rel="stylesheet" href="<?php echo e(url('/modules/profile/css/profile.css')); ?>?v=<?php echo e($profileCssVersion); ?>">
<?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\Layout\Providers/../views/styles-profile.blade.php ENDPATH**/ ?>