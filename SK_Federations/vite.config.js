import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'app/Modules/Barangay_ABYIP/Assets/js/barangay_abyip.js',
                'app/Modules/Barangay_ABYIP/Assets/css/barangay_abyip.css',
                'app/Modules/Accounts/assets/css/account.css',
                'app/Modules/Accounts/assets/js/account.js',
                'app/Modules/AuditLog/assets/css/auditlogs.css',
                'app/Modules/AuditLog/assets/js/auditlogs.js',
                'app/Modules/Archive_Management/assets/css/deleted-sk-federation.css',
                'app/Modules/Archive_Management/assets/js/deleted-sk-federation.js',
                'app/Modules/Archive_Management/assets/css/deleted-sk-officials.css',
                'app/Modules/Archive_Management/assets/js/deleted-sk-officials.js',
                'app/Modules/Archive_Management/assets/css/SK_federation.css',
                'app/Modules/Archive_Management/assets/js/SK_federation.js',
                'app/Modules/Archive_Management/assets/css/SK_officials.css',
                'app/Modules/Archive_Management/assets/js/SK_officials.js',
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
