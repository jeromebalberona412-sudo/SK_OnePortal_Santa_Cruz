import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // Authentication module assets
                'app/modules/Authentication/assets/css/login.css',
                'app/modules/Authentication/assets/js/login.js',
                'app/modules/Authentication/assets/css/forgot-password.css',
                'app/modules/Authentication/assets/js/forgot-password.js',
                'app/modules/Authentication/assets/js/loader.js',
                // Dashboard module assets
                'app/modules/Dashboard/assets/css/dashboard.css',
                'app/modules/Dashboard/assets/js/dashboard.js',
                // Profile module assets
                'app/modules/Profile/assets/css/profile.css',
                'app/modules/Profile/assets/js/profile.js',
                'app/modules/Profile/assets/css/change-password.css',
                'app/modules/Profile/assets/js/change-password.js',
                'app/modules/Profile/assets/css/change-email.css',
                'app/modules/Profile/assets/js/change-email.js',
                'app/modules/Profile/assets/css/notification.css',
                'app/modules/Profile/assets/js/notification.js',
                // Calendar module assets
                'app/modules/Calendar/assets/css/calendar.css',
                'app/modules/Calendar/assets/js/calendar.js',
                // Announcement module assets
                'app/modules/Announcement/assets/css/announcement.css',
                'app/modules/Announcement/assets/js/announcement.js',
                // Committees module assets
                'app/modules/Committees/assets/css/committees.css',
                'app/modules/Committees/assets/js/committees.js',
                // Programs module assets
                'app/modules/Programs/assets/css/programs.css',
                'app/modules/Programs/assets/js/programs.js',
                // Budget & Finance module assets
                'app/modules/BudgetFinance/assets/css/budget-finance.css',
                'app/modules/BudgetFinance/assets/js/budget-finance.js',
                // Kabataan module assets
                'app/modules/Kabataan/assets/css/kabataan.css',
                'app/modules/Kabataan/assets/js/kabataan.js',
                // KK Profiling Requests module assets
                'app/modules/KKProfilingRequests/assets/css/kkprofiling-requests.css',
                'app/modules/KKProfilingRequests/assets/js/kkprofiling-requests.js',
                // ABYIP module assets
                'app/modules/ABYIP/assets/css/abyip.css',
                'app/modules/ABYIP/assets/js/abyip.js',
                // Deleted Kabataan module assets
                'app/modules/Deleted_Kabataan/assets/css/deleted-kabataan.css',
                'app/modules/Deleted_Kabataan/assets/js/deleted-kabataan.js',
                // Rejected KK Profiling module assets
                'app/modules/Rejected_KKProfiling/assets/css/rejected-kkprofiling.css',
                'app/modules/Rejected_KKProfiling/assets/js/rejected-kkprofiling.js',
                // Schedule KK Profiling module assets
                'app/modules/ScheduleKKProfiling/assets/css/schedule-kkprofiling.css',
                'app/modules/ScheduleKKProfiling/assets/js/schedule-kkprofiling.js',
                // Layout module assets
                'app/modules/layout/css/header.css',
                'app/modules/layout/css/sidebar.css',
                'app/modules/layout/js/header.js',
                'app/modules/layout/js/sidebar.js'
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
