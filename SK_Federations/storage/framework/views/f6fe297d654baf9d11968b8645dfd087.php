<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $__env->make('partials.favicon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Account Settings'); ?> - SK OnePortal</title>
    <link rel="stylesheet" href="<?php echo e(url('/modules/authentication/css/forgot-password.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(url('/modules/profile/css/change-email.css')); ?>">
    <?php $skFedAuthCssVersion = @filemtime(app_path('Modules/Profile/assets/css/sk-fed-account-auth.css')) ?: time(); ?>
    <link rel="stylesheet" href="<?php echo e(url('/modules/profile/css/sk-fed-account-auth.css')); ?>?v=<?php echo e($skFedAuthCssVersion); ?>">
    <link rel="stylesheet" href="<?php echo e(url('/shared/css/loading.css')); ?>">
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="sk-login-page">

    <div class="sk-bg-wrapper">
        <div class="sk-bg-image"></div>
        <div class="sk-gradient-overlay"></div>
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>

    <main class="sk-login-container">
        <div class="sk-branding-section">
            <div class="branding-content">
                <div class="logo-wrapper">
                    <img src="<?php echo e(asset('Images/SK_OnePortal.png')); ?>" alt="SK OnePortal Logo" class="sk-logo">
                </div>
                <h1 class="sk-main-title">SK OnePortal</h1>
                <p class="sk-tagline">SK Federations Portal – Santa Cruz, Laguna</p>
            </div>
        </div>

        <div class="sk-login-section">
            <div class="sk-login-card <?php echo $__env->yieldContent('card-class', ''); ?>">
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>
    </main>

    <?php echo $__env->yieldPushContent('scripts-before'); ?>
    <script src="<?php echo e(url('/shared/js/loading.js')); ?>"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\Profile\Providers/../Views/layouts/account-auth.blade.php ENDPATH**/ ?>