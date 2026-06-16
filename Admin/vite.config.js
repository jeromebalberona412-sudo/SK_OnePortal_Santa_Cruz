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
                'app/Modules/Profile/assets/css/profile-verify.css',
                'app/Modules/Profile/assets/js/profile.js',
                'app/Modules/Profile/assets/js/change-email.js',
                'app/Modules/Profile/assets/js/change-email-verify.js',
                'app/Modules/Profile/assets/js/change-password-verify.js',

                // Layout Module
                'app/Modules/Layout/assets/css/sidebar.css',
                'app/Modules/Layout/assets/css/header.css',
                'app/Modules/Layout/assets/js/sidebar.js',
                'app/Modules/Layout/assets/js/header.js',
                'app/Modules/Layout/assets/js/logout.js',

                // Dashboard Module
                'app/Modules/Dashboard/assets/css/dashboard.css',
                'app/Modules/Dashboard/assets/js/dashboard.js',

                // Authentication Module
                'app/Modules/Authentication/assets/css/login.css',
                'app/Modules/Authentication/assets/css/forgot-password.css',
                'app/Modules/Authentication/assets/js/login.js',
                'app/Modules/Authentication/assets/js/forgot-password.js',
                'app/Modules/Authentication/assets/js/verify-email.js',

                // Manage Kabataan Module
                'app/Modules/Manage_Kabataan/assets/css/manage-kabataan.css',
                'app/Modules/Manage_Kabataan/assets/css/kk-questionnaire-view.css',
                'app/Modules/Manage_Kabataan/assets/js/manage-kabataan.js',
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
