<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications — SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/Profile/assets/css/notification.css'
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
            <div class="notif-page-icon notif-icon-blue">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 11l18-5v6l-18 5z"></path>
                    <path d="M6 21v-5.5"></path>
                </svg>
            </div>
            <div class="notif-page-body">
                <div class="notif-page-category">Announcement</div>
                <div class="notif-page-title">SK Federation General Assembly</div>
                <div class="notif-page-text">All SK officials are required to attend the General Assembly on May 5, 2026 at 9:00 AM at the Municipal Hall, Santa Cruz, Laguna. Attendance is mandatory.</div>
                <div class="notif-page-time">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    2 minutes ago
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
            <div class="notif-page-icon notif-icon-green">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                </svg>
            </div>
            <div class="notif-page-body">
                <div class="notif-page-category">Meeting</div>
                <div class="notif-page-title">Committee Meeting — Education</div>
                <div class="notif-page-text">Education Committee meeting scheduled for May 10, 2026 at 2:00 PM. All committee members are required to attend. Agenda includes review of youth programs for Q2.</div>
                <div class="notif-page-time">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    1 hour ago
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
            <div class="notif-page-icon notif-icon-yellow">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div class="notif-page-body">
                <div class="notif-page-category">Reminder</div>
                <div class="notif-page-title">KK Profiling Deadline Reminder</div>
                <div class="notif-page-text">KK Profiling submission deadline is on May 15, 2026. Please submit all pending requests before the deadline to avoid disqualification.</div>
                <div class="notif-page-time">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    3 hours ago
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
            <div class="notif-page-icon notif-icon-purple">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="notif-page-body">
                <div class="notif-page-category">Event</div>
                <div class="notif-page-title">Youth Sports Festival Registration</div>
                <div class="notif-page-text">Registration for the Youth Sports Festival is now open. Register your team before April 30. Contact the SK Secretary for registration forms.</div>
                <div class="notif-page-time">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    Yesterday
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
            <div class="notif-page-icon notif-icon-red">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 1v22"></path>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="notif-page-body">
                <div class="notif-page-category">Finance</div>
                <div class="notif-page-title">Budget Review — Q2 2026</div>
                <div class="notif-page-text">Q2 budget review has been approved. Updated allocation is now available in Budget &amp; Finance. Total approved budget: ₱1.42M for Q2 programs.</div>
                <div class="notif-page-time">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    Apr 22, 2026
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
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/Profile/assets/js/notification.js'
])

</body>
</html>
