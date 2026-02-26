import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'app/Modules/Dashboard/assets/css/dashboard.css',
                'app/Modules/Dashboard/assets/js/dashboard.js',
                'app/Modules/Dashboard/assets/css/layout/sidebar.css',
                'app/Modules/Dashboard/assets/css/layout/header.css',
                'app/Modules/Dashboard/assets/js/layout/sidebar.js',
                'app/Modules/Dashboard/assets/js/layout/header.js',
                'app/Modules/Add_Account/assets/css/add_sk_fed.css',
                'app/Modules/Add_Account/assets/js/add_sk_fed.js'
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
