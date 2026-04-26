import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Core
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/theme.js',

                // Profile Module
                'app/Modules/Profile/assets/css/profile.css',
                'app/Modules/Profile/assets/css/change-email.css',
                'app/Modules/Profile/assets/js/profile.js',
                'app/Modules/Profile/assets/js/change-email.js',

                // Layout Module
                'app/Modules/Layout/assets/css/sidebar.css',
                'app/Modules/Layout/assets/css/header.css',
                'app/Modules/Layout/assets/js/sidebar.js',
                'app/Modules/Layout/assets/js/header.js',
                'app/Modules/Layout/assets/js/logout.js',

                // Accounts Module
                'app/Modules/Accounts/assets/css/account.css',
                'app/Modules/Accounts/assets/js/account.js',

                // Audit Log Module
                'app/Modules/AuditLog/assets/css/auditlogs.css',
                'app/Modules/AuditLog/assets/js/auditlogs.js',

                // Dashboard Module
                'app/Modules/Dashboard/assets/css/dashboard.css',
                'app/Modules/Dashboard/assets/js/dashboard.js',

                // Authentication Module
                'app/Modules/Authentication/assets/css/login.css',
                'app/Modules/Authentication/assets/js/login.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],

    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
