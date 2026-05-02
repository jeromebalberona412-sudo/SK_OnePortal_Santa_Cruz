import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'app/Modules/Authentication/assets/css/youth-login.css',
                'app/Modules/Authentication/assets/js/youth-login.js',
                'app/Modules/Authentication/assets/css/youth-register.css',
                'app/Modules/Authentication/assets/js/youth-register.js',
                'app/Modules/Authentication/assets/css/youth-email-verification.css',
                'app/Modules/Authentication/assets/js/youth-email-verification.js',
                'app/Modules/Dashboard/assets/css/dashboard.css',
                'app/Modules/Dashboard/assets/js/dashboard.js',
                'app/Modules/Dashboard/assets/css/chatbot.css',
                'app/Modules/Dashboard/assets/js/chatbot.js',
                'app/Modules/Dashboard/assets/css/notif.css',
                'app/Modules/Dashboard/assets/js/notif.js',
                'app/Modules/Profile/assets/css/profile.css',
                'app/Modules/Profile/assets/js/profile.js',
                'app/Modules/Profile/assets/css/settings.css',
                'app/Modules/Homepage/assets/css/homepage.css',
                'app/Modules/Homepage/assets/js/homepage.js',
                'app/Modules/Homepage/assets/css/about.css',
                'app/Modules/Homepage/assets/css/pages.css',
                'app/Modules/Homepage/assets/css/faqs.css',
                'app/Modules/KKProfiling/assets/css/kkprofiling.css',
                'app/Modules/KKProfiling/assets/js/kkprofiling.js',
                'app/Modules/Shared/assets/css/loading.css',
                'app/Modules/Shared/assets/js/loading.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        rollupOptions: {
            output: {
                assetFileNames: (assetInfo) => {
                    let extType = assetInfo.name.split('.').at(1);
                    if (/png|jpe?g|svg|gif|tiff|bmp|ico|webp/i.test(extType)) {
                        extType = 'images';
                    }
                    return `assets/${extType}/[name]-[hash][extname]`;
                },
            },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
