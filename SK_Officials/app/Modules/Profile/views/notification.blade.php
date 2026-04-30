<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications — SK Officials Portal</title>

    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Profile/assets/css/notification.css'
    ])
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content" id="mainContent">
<div class="notif-page-container">

    <!-- Page Header -->
    <div class="notif-page-header">
        <div class="notif-page-header-left">
            <h1 class="notif-page-title">Notifications</h1>
            <p class="notif-page-sub">Stay updated with the latest SK announcements and alerts.</p>
        </div>
        <button class="notif-page-mark-all" id="pageMarkAllBtn">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            Mark all as read
        </button>
    </div>

    <!-- Stats row -->
    <div class="notif-stats-row">
        <div class="notif-stat-card">
            <span class="notif-stat-value" id="totalCount">5</span>
            <span class="notif-stat-label">Total</span>
        </div>
        <div class="notif-stat-card notif-stat-unread">
            <span class="notif-stat-value" id="unreadCount">3</span>
            <span class="notif-stat-label">Unread</span>
        </div>
        <div class="notif-stat-card notif-stat-read">
            <span class="notif-stat-value" id="readCount">2</span>
            <span class="notif-stat-label">Read</span>
        </div>
    </div>

    <!-- Filter tabs -->
    <div class="notif-filter-bar">
        <button class="notif-filter-btn active" data-filter="all">All</button>
        <button class="notif-filter-btn" data-filter="unread">Unread</button>
        <button class="notif-filter-btn" data-filter="read">Read</button>
    </div>

    <!-- Notification list -->
    <div class="notif-page-list" id="notifPageList">

        <div class="notif-page-item notif-page-unread" data-id="1" data-category="announcement">
            <div class="notif-page-body">
                <div class="notif-page-category">Announcement</div>
                <div class="notif-page-title">SK Federation General Assembly</div>
                <div class="notif-page-text">All SK officials are required to attend the General Assembly on May 5, 2026 at 9:00 AM at the Municipal Hall, Santa Cruz, Laguna. Attendance is mandatory.</div>
                <div class="notif-page-time">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    9:12 AM · Apr 24, 2026
                </div>
            </div>
            <div class="notif-page-actions">
                <span class="notif-page-dot"></span>
                <button class="notif-page-read-btn" data-id="1" title="Mark as read">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </button>
            </div>
        </div>

        <div class="notif-page-item notif-page-unread" data-id="2" data-category="meeting">
            <div class="notif-page-body">
                <div class="notif-page-category">Meeting</div>
                <div class="notif-page-title">Committee Meeting — Education</div>
                <div class="notif-page-text">Education Committee meeting scheduled for May 10, 2026 at 2:00 PM. All committee members are required to attend. Agenda includes review of youth programs for Q2.</div>
                <div class="notif-page-time">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    8:05 AM · Apr 24, 2026
                </div>
            </div>
            <div class="notif-page-actions">
                <span class="notif-page-dot"></span>
                <button class="notif-page-read-btn" data-id="2" title="Mark as read">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </button>
            </div>
        </div>

        <div class="notif-page-item notif-page-unread" data-id="3" data-category="reminder">
            <div class="notif-page-body">
                <div class="notif-page-category">Reminder</div>
                <div class="notif-page-title">KK Profiling Deadline Reminder</div>
                <div class="notif-page-text">KK Profiling submission deadline is on May 15, 2026. Please submit all pending requests before the deadline to avoid disqualification.</div>
                <div class="notif-page-time">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    6:30 AM · Apr 24, 2026
                </div>
            </div>
            <div class="notif-page-actions">
                <span class="notif-page-dot"></span>
                <button class="notif-page-read-btn" data-id="3" title="Mark as read">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </button>
            </div>
        </div>

        <div class="notif-page-item" data-id="4" data-category="event">
            <div class="notif-page-body">
                <div class="notif-page-category">Event</div>
                <div class="notif-page-title">Youth Sports Festival Registration</div>
                <div class="notif-page-text">Registration for the Youth Sports Festival is now open. Register your team before April 30. Contact the SK Secretary for registration forms.</div>
                <div class="notif-page-time">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    3:45 PM · Apr 23, 2026
                </div>
            </div>
            <div class="notif-page-actions">
                <button class="notif-page-read-btn notif-read-done" data-id="4" title="Already read" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </button>
            </div>
        </div>

        <div class="notif-page-item" data-id="5" data-category="finance">
            <div class="notif-page-body">
                <div class="notif-page-category">Finance</div>
                <div class="notif-page-title">Budget Review — Q2 2026</div>
                <div class="notif-page-text">Q2 budget review has been approved. Updated allocation is now available in Budget &amp; Finance. Total approved budget: ₱1.42M for Q2 programs.</div>
                <div class="notif-page-time">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    10:20 AM · Apr 22, 2026
                </div>
            </div>
            <div class="notif-page-actions">
                <button class="notif-page-read-btn notif-read-done" data-id="5" title="Already read" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </button>
            </div>
        </div>

    </div><!-- /notif-page-list -->

    <!-- Empty state -->
    <div class="notif-page-empty" id="notifPageEmpty" style="display:none;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <h3>No notifications</h3>
        <p>You're all caught up! Check back later for updates.</p>
    </div>

</div><!-- /notif-page-container -->
</main>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Profile/assets/js/notification.js'
])

</body>
</html>
