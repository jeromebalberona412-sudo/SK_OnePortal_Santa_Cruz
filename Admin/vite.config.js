import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'app/Modules/Profile/assets/css/profile.css',
                'app/Modules/Profile/assets/js/profile.js',
                'app/Modules/Layout/assets/css/layout/sidebar.css',
                'app/Modules/Layout/assets/css/layout/header.css',
                'app/Modules/Layout/assets/js/layout/sidebar.js',
                'app/Modules/Layout/assets/js/layout/header.js',
                'app/Modules/Accounts/assets/css/manage_account.css',
                'app/Modules/Accounts/assets/css/edit_sk_fed.css',
                'app/Modules/Accounts/assets/css/edit_sk_officials.css',
                'app/Modules/Accounts/assets/js/manage_account.js',
                'app/Modules/AuditLog/assets/css/auditlogs.css',
                'app/Modules/AuditLog/assets/js/auditlogs.js'
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
