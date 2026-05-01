<?php

/**
 * Previous Kabataan Module Verification Script
 * 
 * Run this script to verify the module is properly installed
 * Usage: php verify-previous-kabataan.php
 */

echo "=== Previous Kabataan Module Verification ===\n\n";

$errors = [];
$warnings = [];
$success = [];

// Check 1: Route file exists
$routeFile = __DIR__ . '/app/Modules/Previous Kabataam/routes/web.php';
if (file_exists($routeFile)) {
    $success[] = "✓ Route file exists";
    $routeContent = file_get_contents($routeFile);
    if (strpos($routeContent, "'/previous-kabataan'") !== false) {
        $success[] = "✓ Route definition found";
    } else {
        $errors[] = "✗ Route definition not found in route file";
    }
} else {
    $errors[] = "✗ Route file missing: $routeFile";
}

// Check 2: View file exists
$viewFile = __DIR__ . '/app/Modules/Previous Kabataam/views/previous-kabataan.blade.php';
if (file_exists($viewFile)) {
    $success[] = "✓ View file exists";
    $viewContent = file_get_contents($viewFile);
    if (strpos($viewContent, "Previous Kabataan Records") !== false) {
        $success[] = "✓ View content verified";
    } else {
        $warnings[] = "⚠ View file exists but content may be incorrect";
    }
} else {
    $errors[] = "✗ View file missing: $viewFile";
}

// Check 3: CSS file exists
$cssFile = __DIR__ . '/app/Modules/Previous Kabataam/assets/css/previous-kabataan.css';
if (file_exists($cssFile)) {
    $success[] = "✓ CSS file exists";
} else {
    $errors[] = "✗ CSS file missing: $cssFile";
}

// Check 4: JS file exists
$jsFile = __DIR__ . '/app/Modules/Previous Kabataam/assets/js/previous-kabataan.js';
if (file_exists($jsFile)) {
    $success[] = "✓ JavaScript file exists";
    $jsContent = file_get_contents($jsFile);
    if (strpos($jsContent, "PREV_KAB_DATA") !== false) {
        $success[] = "✓ JavaScript sample data found";
    } else {
        $warnings[] = "⚠ JavaScript file exists but sample data may be missing";
    }
} else {
    $errors[] = "✗ JavaScript file missing: $jsFile";
}

// Check 5: Vite config updated
$viteConfig = __DIR__ . '/vite.config.js';
if (file_exists($viteConfig)) {
    $viteContent = file_get_contents($viteConfig);
    if (strpos($viteContent, "Previous Kabataam") !== false) {
        $success[] = "✓ Vite config includes Previous Kabataan assets";
    } else {
        $errors[] = "✗ Vite config not updated with Previous Kabataan assets";
    }
} else {
    $errors[] = "✗ Vite config file not found";
}

// Check 6: Sidebar updated
$sidebarFile = __DIR__ . '/app/Modules/layout/views/sidebar.blade.php';
if (file_exists($sidebarFile)) {
    $sidebarContent = file_get_contents($sidebarFile);
    if (strpos($sidebarContent, "previous-kabataan") !== false) {
        $success[] = "✓ Sidebar includes Previous Kabataan link";
    } else {
        $errors[] = "✗ Sidebar not updated with Previous Kabataan link";
    }
} else {
    $errors[] = "✗ Sidebar file not found";
}

// Check 7: AppServiceProvider
$providerFile = __DIR__ . '/app/Providers/AppServiceProvider.php';
if (file_exists($providerFile)) {
    $providerContent = file_get_contents($providerFile);
    if (strpos($providerContent, "loadModuleRoutes") !== false) {
        $success[] = "✓ AppServiceProvider loads module routes";
    } else {
        $errors[] = "✗ AppServiceProvider not configured to load module routes";
    }
} else {
    $errors[] = "✗ AppServiceProvider file not found";
}

// Check 8: Cache files
$routeCacheFile = __DIR__ . '/bootstrap/cache/routes-v7.php';
if (file_exists($routeCacheFile)) {
    $warnings[] = "⚠ Route cache file exists - you may need to clear it";
    $warnings[] = "  Run: php artisan route:clear";
}

$configCacheFile = __DIR__ . '/bootstrap/cache/config.php';
if (file_exists($configCacheFile)) {
    $warnings[] = "⚠ Config cache file exists - you may need to clear it";
    $warnings[] = "  Run: php artisan config:clear";
}

// Display results
echo "SUCCESS:\n";
foreach ($success as $msg) {
    echo "  $msg\n";
}

if (!empty($warnings)) {
    echo "\nWARNINGS:\n";
    foreach ($warnings as $msg) {
        echo "  $msg\n";
    }
}

if (!empty($errors)) {
    echo "\nERRORS:\n";
    foreach ($errors as $msg) {
        echo "  $msg\n";
    }
}

echo "\n";

if (empty($errors)) {
    echo "=== VERIFICATION PASSED ===\n";
    echo "\nNext steps:\n";
    echo "1. Clear Laravel cache:\n";
    echo "   php artisan route:clear\n";
    echo "   php artisan view:clear\n";
    echo "   php artisan config:clear\n";
    echo "   php artisan cache:clear\n";
    echo "\n2. Rebuild Vite assets:\n";
    echo "   npm run build\n";
    echo "\n3. Restart development server:\n";
    echo "   php artisan serve --port=8080\n";
    echo "\n4. Access the page:\n";
    echo "   http://localhost:8080/previous-kabataan\n";
} else {
    echo "=== VERIFICATION FAILED ===\n";
    echo "\nPlease fix the errors above before proceeding.\n";
}

echo "\n";
