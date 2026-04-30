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

                // Deleted SK Federation Module
                'app/Modules/DeletedSkFederation/assets/css/deleted-sk-federation.css',
                'app/Modules/DeletedSkFederation/assets/js/deleted-sk-federation.js',

                // Deleted SK Officials Module
                'app/Modules/DeletedSkOfficials/assets/css/deleted-sk-officials.css',
                'app/Modules/DeletedSkOfficials/assets/js/deleted-sk-officials.js',

                // Barangay Logos Module
                'app/Modules/BarangayLogos/assets/css/barangay-logos.css',
                'app/Modules/BarangayLogos/assets/js/barangay-logos.js',

                // Archived Records Module
                'app/Modules/ArchivedRecords/assets/css/SK_federation.css',
                'app/Modules/ArchivedRecords/assets/js/SK_federation.js',
                'app/Modules/ArchivedRecords/assets/css/SK_officials.css',
                'app/Modules/ArchivedRecords/assets/js/SK_officials.js',

                // Contact Us Module
                'app/Modules/ContactUs/assets/css/contact.css',
                'app/Modules/ContactUs/assets/js/contact.js',
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
