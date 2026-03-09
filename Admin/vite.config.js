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
                'app/Modules/Layout/assets/js/layout/logout.js',
                'app/Modules/Accounts/assets/css/manage_account.css',
                'app/Modules/Accounts/assets/css/add_sk_fed.css',
                'app/Modules/Accounts/assets/css/add_sk_officials.css',
                'app/Modules/Accounts/assets/css/edit_sk_fed.css',
                'app/Modules/Accounts/assets/css/edit_sk_officials.css',
                'app/Modules/Accounts/assets/js/manage_account.js',
                'app/Modules/Accounts/assets/js/add_sk_fed.js',
                'app/Modules/Accounts/assets/js/add_sk_officials.js',
                'app/Modules/Accounts/assets/js/edit_sk_fed.js',
                'app/Modules/Accounts/assets/js/edit_sk_officials.js',
                'app/Modules/Accounts/assets/js/view_account.js',
                'app/Modules/AuditLog/assets/css/auditlogs.css',
                'app/Modules/AuditLog/assets/js/auditlogs.js',
                'app/Modules/Authentication/assets/css/gov-auth.css',
                'app/Modules/Authentication/assets/js/gov-auth.js',
                'app/Modules/Authentication/assets/Oneportal_logo-removebg-preview.png',
                'app/Modules/Authentication/assets/Flag_of_Santa_Cruz__Laguna-removebg-preview.png'
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
