import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/theme.js',
                'app/Modules/Profile/assets/css/profile.css',
                'app/Modules/Profile/assets/js/profile.js',
                'app/Modules/Layout/assets/css/sidebar.css',
                'app/Modules/Layout/assets/css/header.css',
                'app/Modules/Layout/assets/js/sidebar.js',
                'app/Modules/Layout/assets/js/header.js',
                'app/Modules/Layout/assets/js/logout.js',
                'app/Modules/Accounts/assets/css/manage_account.css',
                'app/Modules/Accounts/assets/css/edit_account_base.css',
                'app/Modules/Accounts/assets/css/add_sk_fed.css',
                'app/Modules/Accounts/assets/css/add_sk_officials.css',
                'app/Modules/Accounts/assets/css/edit_sk_fed.css',
                'app/Modules/Accounts/assets/css/edit_sk_officials.css',
                'app/Modules/Accounts/assets/css/view_account.css',
                'app/Modules/Accounts/assets/js/manage_account.js',
                'app/Modules/Accounts/assets/js/add_sk_fed.js',
                'app/Modules/Accounts/assets/js/add_sk_officials.js',
                'app/Modules/Accounts/assets/js/edit_sk_fed.js',
                'app/Modules/Accounts/assets/js/edit_sk_officials.js',
                'app/Modules/Accounts/assets/js/view_account.js',
                'app/Modules/AuditLog/assets/css/auditlogs.css',
                'app/Modules/AuditLog/assets/js/auditlogs.js',
                'app/Modules/Dashboard/assets/css/dashboard.css',
                'app/Modules/Dashboard/assets/js/dashboard.js',
                'app/Modules/Authentication/assets/css/login.css',
                'app/Modules/Authentication/assets/js/login.js'
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
