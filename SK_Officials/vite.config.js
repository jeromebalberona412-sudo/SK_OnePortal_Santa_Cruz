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
                'app/Modules/Authentication/assets/css/takeover-wait.css',
                'app/Modules/Authentication/assets/js/takeover-wait.js',
                'app/Modules/Authentication/assets/css/password-reset-success.css',
                'app/Modules/Authentication/assets/js/password-reset-success.js',
                'app/Modules/Authentication/assets/css/reset-password.css',
                'app/Modules/Authentication/assets/js/reset-password.js',
                'app/Modules/Authentication/assets/css/verify-notice.css',
                'app/Modules/Authentication/assets/js/verify-notice.js',
                'app/Modules/Authentication/assets/css/verify-success.css',
                'app/Modules/Authentication/assets/js/verify-success.js',
                'app/Modules/Authentication/assets/css/verify-wait.css',
                'app/Modules/Authentication/assets/js/verify-wait.js',
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
                'app/Modules/Deleted_Abyip/assets/js/deleted-abyip.js',
                // Rejected KK Profiling module assets
                'app/Modules/Rejected_KKProfiling/assets/css/rejected-kkprofiling.css',
                'app/Modules/Rejected_KKProfiling/assets/js/rejected-kkprofiling.js',
                // Schedule KK Profiling module assets
                'app/Modules/ScheduleKKProfiling/assets/css/schedule-kkprofiling.css',
                'app/Modules/ScheduleKKProfiling/assets/js/schedule-kkprofiling.js',
                // Schedule Programs module assets
                'app/Modules/Program_Management/assets/css/schedule-programs.css',
                'app/Modules/Program_Management/assets/js/schedule-programs.js',
                // Scholarship (Equitable Access to Quality Education)
                'app/Modules/Program_Management/assets/css/scholarship/scholarship_application_form.css',
                'app/Modules/Program_Management/assets/css/scholarship/scholar_list.css',
                'app/Modules/Program_Management/assets/css/scholarship/scholar_evaluation.css',
                'app/Modules/Program_Management/assets/css/scholarship/scholar_report.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholarship-schedule.css',
        'app/Modules/Program_Management/assets/js/scholarship/scholarship-schedule.js',
        'app/Modules/Program_Management/assets/js/scholarship/scholarship-view-shared.js',
        'app/Modules/Program_Management/assets/js/scholarship/scholarship-applications.js',
        'app/Modules/Program_Management/assets/css/scholarship/scholar_application_from.css',
        'app/Modules/Program_Management/assets/js/scholarship/scholar_application_from.js',
                'app/Modules/Program_Management/assets/css/survey/survey.css',
                'app/Modules/Program_Management/assets/js/survey/survey.js',
        'app/Modules/Program_Management/assets/css/scholarship/approved-scholars.css',
        'app/Modules/Program_Management/assets/js/scholarship/approved-scholars.js',
                'app/Modules/Program_Management/assets/js/scholarship/scholar_evaluation.js',
                'app/Modules/Program_Management/assets/js/scholarship/scholar_schedule.js',
                'app/Modules/Program_Management/assets/js/scholarship/scholar_report.js',
                // Sports Development
                'app/Modules/Program_Management/assets/css/sports/sports_application_form.css',
                'app/Modules/Program_Management/assets/css/sports/sports_list.css',
                'app/Modules/Program_Management/assets/css/sports/sports_requests.css',
                'app/Modules/Program_Management/assets/css/sports/sports-tabs.css',
                'app/Modules/Program_Management/assets/js/sports/sports_application_form.js',
                'app/Modules/Program_Management/assets/js/sports/sports_requests.js',
                'app/Modules/Program_Management/assets/js/sports/sports_list.js',
                'app/Modules/Program_Management/assets/js/sports/sports_schedule.js',
                'app/Modules/Program_Management/assets/js/sports/sports-tabs.js',
                'app/Modules/Program_Management/assets/js/sports/sports_report.js',
                // Shared Schedule Programs Assets
                'app/Modules/Program_Management/assets/css/shared/sk-report-editor.css',
                'app/Modules/Program_Management/assets/js/shared/sk-report-editor.js',
                'app/Modules/GForm_Builder/assets/css/gform-builder.css',
                'app/Modules/GForm_Builder/assets/js/gform-builder.js',
                // Rejected Scholarship module assets
                'app/Modules/Rejected_Scholarship/assets/css/rejected-scholarship.css',
                'app/Modules/Rejected_Scholarship/assets/js/rejected-scholarship.js',
                // AI Assistant module assets
                'app/Modules/AI_Assistant/assets/css/ai-assistant-modal-form.css',
                'app/Modules/AI_Assistant/assets/js/ai-assistant-modal-form.js',
                'app/Modules/AI_Assistant/assets/css/ai-page.css',
                'app/Modules/AI_Assistant/assets/css/ai-recent-menu.css',
                'app/Modules/AI_Assistant/assets/css/ai-modal.css',
                'app/Modules/AI_Assistant/assets/js/ai-modal-close.js',
                'app/Modules/AI_Assistant/assets/js/ai-storage.js',
                'app/Modules/AI_Assistant/assets/js/ai-toast.js',
                'app/Modules/AI_Assistant/assets/js/ai-attachments.js',
                'app/Modules/AI_Assistant/assets/js/ai-recent-menu.js',
                'app/Modules/AI_Assistant/assets/js/ai-modal.js',
                'app/Modules/AI_Assistant/assets/js/ai-page.js',
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
