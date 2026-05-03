import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'app/Modules/Reports/assets/js/reports.js',
                'app/Modules/Barangay_ABYIP/Assets/js/barangay_abyip.js',
                'app/Modules/Barangay_ABYIP/Assets/css/barangay_abyip.css'
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
