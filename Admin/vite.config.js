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
                'app/Modules/Authentication/assets/css/forgot-password.css',
                'app/Modules/Authentication/assets/js/login.js',
                'app/Modules/Authentication/assets/js/forgot-password.js',

                // Barangay Logos Module
                'app/Modules/BarangayLogos/assets/css/barangay-logos.css',
                'app/Modules/BarangayLogos/assets/js/barangay-logos.js',

                // Contact Us Module
                'app/Modules/ContactUs/assets/css/contact.css',
                'app/Modules/ContactUs/assets/js/contact.js',

                // Manage Kabataan Module
                'app/Modules/Manage_Kabataan/assets/css/manage_kabataan.css',
                'app/Modules/Manage_Kabataan/assets/js/manage_kabataan.js',

                // Manage Location Module
                'app/Modules/Manage_Location/assets/css/manage_location.css',
                'app/Modules/Manage_Location/assets/js/manage_location.js',

                // ── Archive Management Module (unified) ──────────────────────
                // Deleted SK Federation & Officials (Consolidated)
                'app/Modules/Archive_Management/assets/css/deleted-sk-federation.css',
                'app/Modules/Archive_Management/assets/js/deleted-sk-federation.js',
                'app/Modules/Archive_Management/assets/css/deleted-sk-officials.css',
                'app/Modules/Archive_Management/assets/js/deleted-sk-officials.js',

                // Archived Data — SK Federation & Officials Records
                'app/Modules/Archive_Management/assets/css/SK_federation.css',
                'app/Modules/Archive_Management/assets/js/SK_federation.js',
                'app/Modules/Archive_Management/assets/css/SK_officials.css',
                'app/Modules/Archive_Management/assets/js/SK_officials.js',

                // SK Officials Archive — Deleted Kabataan
                'app/Modules/Archive_Management/assets/css/deleted-kabataan.css',
                'app/Modules/Archive_Management/assets/js/deleted-kabataan.js',

                // Deleted Barangay (Archive)
                'app/Modules/Archive_Management/assets/css/deleted-barangay.css',
                'app/Modules/Archive_Management/assets/js/deleted-barangay.js',

                // SK Officials Archive — Rejected KK Profiling
                'app/Modules/Archive_Management/assets/css/rejected-kk-profiling.css',
                'app/Modules/Archive_Management/assets/js/rejected-kk-profiling.js',

                // SK Officials Archive — Rejected Scholarships
                'app/Modules/Archive_Management/assets/css/rejected-scholarships.css',
                'app/Modules/Archive_Management/assets/js/rejected-scholarships.js',
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
