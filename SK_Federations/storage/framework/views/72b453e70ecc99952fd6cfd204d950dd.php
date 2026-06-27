<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $__env->make('partials.favicon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php if (! empty(trim($__env->yieldContent('meta-cache')))): ?>
        <?php echo $__env->yieldContent('meta-cache'); ?>
    <?php else: ?>
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
    <?php endif; ?>
    <title><?php echo $__env->yieldContent('title', 'SK OnePortal'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php
        $dashboardCssVersion = @filemtime(app_path('Modules/Dashboard/assets/css/dashboard.css')) ?: time();
        $layoutCssVersion = @filemtime(app_path('Modules/Layout/assets/css/layout.css')) ?: time();
        $layoutJsVersion = @filemtime(app_path('Modules/Layout/assets/js/layout.js')) ?: time();
        $loadingCssVersion = @filemtime(public_path('shared/css/loading.css')) ?: time();
        $loadingJsVersion = @filemtime(public_path('shared/js/loading.js')) ?: time();
    ?>
    <link rel="stylesheet" href="<?php echo e(url('/modules/dashboard/css/dashboard.css')); ?>?v=<?php echo e($dashboardCssVersion); ?>">
    <link rel="stylesheet" href="<?php echo e(url('/modules/layout/css/layout.css')); ?>?v=<?php echo e($layoutCssVersion); ?>">
    <link rel="stylesheet" href="<?php echo e(url('/shared/css/loading.css')); ?>?v=<?php echo e($loadingCssVersion); ?>">
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body <?php echo $__env->yieldPushContent('body-attributes'); ?>>
    <?php echo $__env->make('layout::anti-back', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('layout::header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('layout::sidebar-overlay', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('layout::sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="main-content <?php echo $__env->yieldPushContent('main-class'); ?>" <?php echo $__env->yieldPushContent('main-attributes'); ?>>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <?php echo $__env->make('layout::logout-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script src="<?php echo e(url('/shared/js/loading.js')); ?>?v=<?php echo e($loadingJsVersion); ?>"></script>
    <script src="<?php echo e(url('/modules/layout/js/layout.js')); ?>?v=<?php echo e($layoutJsVersion); ?>"></script>
    <script>
        window.logoutRoute = "<?php echo e(route('logout')); ?>";
        window.loginRoute  = "<?php echo e(route('login')); ?>";
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\Administrator\Documents\SK_OnePortal_Santa_Cruz\SK_Federations\app\Modules\Layout\Providers/../views/app.blade.php ENDPATH**/ ?>