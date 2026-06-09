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

                // Authentication
                'app/Modules/Authentication/assets/css/youth-login.css',
                'app/Modules/Authentication/assets/js/youth-login.js',
                'app/Modules/Authentication/assets/css/youth-register.css',
                'app/Modules/Authentication/assets/js/youth-register.js',
                'app/Modules/Authentication/assets/css/youth-email-verification.css',
                'app/Modules/Authentication/assets/js/youth-email-verification.js',

                // Layout (shared header & footer)
                'app/Modules/Layout/assets/css/kabataan-header.css',
                'app/Modules/Layout/assets/css/kabataan-logout.css',
                'app/Modules/Layout/assets/js/kabataan-header.js',
                'app/Modules/Layout/assets/js/kabataan-logout.js',
                'app/Modules/Homepage/assets/css/kabataan-footer.css',

                // Dashboard
                'app/Modules/Dashboard/assets/css/dashboard.css',
                'app/Modules/Dashboard/assets/js/dashboard.js',
                'app/Modules/Dashboard/assets/css/chatbot.css',
                'app/Modules/Dashboard/assets/js/chatbot.js',
                'app/Modules/Dashboard/assets/css/notif.css',
                'app/Modules/Dashboard/assets/js/notif.js',

                // Programs
                'app/Modules/Programs/assets/css/scholarship_landing.css',
                'app/Modules/Programs/assets/js/scholarship_landing.js',
                'app/Modules/Programs/assets/css/sports_landing.css',
                'app/Modules/Programs/assets/js/sports_landing.js',
                'app/Modules/Programs/assets/css/scholarship_application.css',
                'app/Modules/Programs/assets/css/scholarship_requirements.css',
                'app/Modules/Programs/assets/js/scholarship_application.js',
                'app/Modules/Programs/assets/js/scholarship_requirements.js',
                'app/Modules/Programs/assets/css/sports-registration.css',
                'app/Modules/Programs/assets/js/sports-registration.js',
                'app/Modules/Programs/assets/js/programs.js',
                'app/Modules/Programs/assets/js/kabataan-programs.js',
                'app/Modules/Programs/assets/js/program_survey_landing.js',
                'app/Modules/Programs/assets/js/program_survey_form.js',

                // Profile
                'app/Modules/Profile/assets/css/profile.css',
                'app/Modules/Profile/assets/js/profile.js',
                'app/Modules/Profile/assets/css/change-email.css',
                'app/Modules/Profile/assets/js/change-email.js',
                'app/Modules/Profile/assets/js/change-email-verify.js',
                'app/Modules/Profile/assets/css/change-password.css',
                'app/Modules/Profile/assets/js/change-password.js',
                'app/Modules/Profile/assets/js/change-password-verify.js',
                'app/Modules/Profile/assets/js/set-password.js',

                // Homepage
                'app/Modules/Homepage/assets/css/homepage-bootstrap.css',
                'app/Modules/Homepage/assets/css/homepage.css',
                'app/Modules/Homepage/assets/css/about.css',
                'app/Modules/Homepage/assets/css/pages.css',
                'app/Modules/Homepage/assets/css/faqs.css',
                'app/Modules/Homepage/assets/css/contact.css',
                'app/Modules/Homepage/assets/css/homepage-interactions.css',
                'app/Modules/Homepage/assets/css/homepage-responsive.css',
                'app/Modules/Homepage/assets/js/homepage.js',
                'app/Modules/Homepage/assets/js/faqs.js',

                // KK Profiling
                'app/Modules/KKProfiling/assets/css/kkprofiling.css',
                'app/Modules/KKProfiling/assets/js/kkprofiling.js',
                'app/Modules/KKProfiling/assets/css/kkprofiling-signup.css',
                'app/Modules/KKProfiling/assets/js/kkprofiling-signup.js',
                'app/Modules/KKProfiling/assets/css/kk-profiling-update.css',
                'app/Modules/KKProfiling/assets/js/kk-profiling-update.js',

                // Shared
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
