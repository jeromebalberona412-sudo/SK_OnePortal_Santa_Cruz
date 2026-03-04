import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                // Authentication module assets
                'app/modules/Authentication/assets/css/login.css',
                'app/modules/Authentication/assets/js/login.js',
                'app/modules/Authentication/assets/css/forgot-password.css',
                'app/modules/Authentication/assets/js/forgot-password.js',
                'app/modules/Authentication/assets/js/loader.js',
                // Dashboard module assets
                'app/modules/Dashboard/assets/css/dashboard.css',
                'app/modules/Dashboard/assets/js/dashboard.js',
                // Profile module assets
                'app/modules/Profile/assets/css/profile.css',
                'app/modules/Profile/assets/js/profile.js',
                // Calendar module assets
                'app/modules/Calendar/assets/css/calendar.css',
                'app/modules/Calendar/assets/js/calendar.js',
                // Announcement module assets
                'app/modules/Announcement/assets/css/announcement.css',
                'app/modules/Announcement/assets/js/announcement.js',
                // Layout module assets
                'app/modules/layout/css/header.css',
                'app/modules/layout/css/sidebar.css',
                'app/modules/layout/js/header.js',
                'app/modules/layout/js/sidebar.js'
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
