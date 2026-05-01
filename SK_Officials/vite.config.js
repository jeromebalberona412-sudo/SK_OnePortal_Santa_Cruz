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
                'app/Modules/Authentication/assets/css/login.css',
                'app/Modules/Authentication/assets/js/login.js',
                'app/Modules/Authentication/assets/css/forgot-password.css',
                'app/Modules/Authentication/assets/js/forgot-password.js',
                'app/Modules/Authentication/assets/js/loader.js',
                // Dashboard module assets
                'app/Modules/Dashboard/assets/css/dashboard.css',
                'app/Modules/Dashboard/assets/js/dashboard.js',
                // Profile module assets
                'app/Modules/Profile/assets/css/profile.css',
                'app/Modules/Profile/assets/js/profile.js',
                'app/Modules/Profile/assets/css/change-password.css',
                'app/Modules/Profile/assets/js/change-password.js',
                'app/Modules/Profile/assets/css/change-email.css',
                'app/Modules/Profile/assets/js/change-email.js',
                'app/Modules/Profile/assets/css/notification.css',
                'app/Modules/Profile/assets/js/notification.js',
                // Calendar module assets
                'app/Modules/Calendar/assets/css/calendar.css',
                'app/Modules/Calendar/assets/js/calendar.js',
                // Announcement module assets
                'app/Modules/Announcement/assets/css/announcement.css',
                'app/Modules/Announcement/assets/js/announcement.js',
                // Committees module assets
                'app/Modules/Committees/assets/css/committees.css',
                'app/Modules/Committees/assets/js/committees.js',
                // Programs module assets
                'app/Modules/Programs/assets/css/programs.css',
                'app/Modules/Programs/assets/js/programs.js',
                // Budget & Finance module assets
                'app/Modules/BudgetFinance/assets/css/budget-finance.css',
                'app/Modules/BudgetFinance/assets/js/budget-finance.js',
                // Kabataan module assets
                'app/Modules/Kabataan/assets/css/kabataan.css',
                'app/Modules/Kabataan/assets/js/kabataan.js',
                // Previous Kabataan module assets
                'app/Modules/PreviousKabataan/assets/css/previous-kabataan.css',
                'app/Modules/PreviousKabataan/assets/js/previous-kabataan.js',
                // KK Profiling Requests module assets
                'app/Modules/KKProfilingRequests/assets/css/kkprofiling-requests.css',
                'app/Modules/KKProfilingRequests/assets/js/kkprofiling-requests.js',
                // ABYIP module assets
                'app/Modules/ABYIP/assets/css/abyip.css',
                'app/Modules/ABYIP/assets/js/abyip.js',
                // Deleted Kabataan module assets
                'app/Modules/Deleted_Kabataan/assets/css/deleted-kabataan.css',
                'app/Modules/Deleted_Kabataan/assets/js/deleted-kabataan.js',
                // Rejected KK Profiling module assets
                'app/Modules/Rejected_KKProfiling/assets/css/rejected-kkprofiling.css',
                'app/Modules/Rejected_KKProfiling/assets/js/rejected-kkprofiling.js',
                // Schedule KK Profiling module assets
                'app/Modules/ScheduleKKProfiling/assets/css/schedule-kkprofiling.css',
                'app/Modules/ScheduleKKProfiling/assets/js/schedule-kkprofiling.js',
                // Schedule Programs module assets
                'app/Modules/schedule_programs/assets/css/schedule-programs.css',
                'app/Modules/schedule_programs/assets/js/schedule-programs.js',
                'app/Modules/schedule_programs/assets/css/sports_application_form.css',
                'app/Modules/schedule_programs/assets/js/sports_application_form.js',
                // Layout module assets
                'app/Modules/layout/css/header.css',
                'app/Modules/layout/css/sidebar.css',
                'app/Modules/layout/js/header.js',
                'app/Modules/layout/js/sidebar.js',
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
