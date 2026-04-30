<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/Calendar/assets/css/calendar.css'
    ])
</head>
<body>

<!-- ================= HEADER ================= -->
@include('layout::header')

<!-- ================= SIDEBAR ================= -->
@include('layout::sidebar')

<!-- ================= MAIN CONTENT ================= -->
<main class="main-content">
    <div class="calendar-page-container">
        <section class="calendar-header-section">
            <div class="calendar-header-left">
                <h1 class="calendar-title">Calendar</h1>
                <p class="calendar-subtitle">View and annotate your monthly schedule.</p>
            </div>
            <div class="calendar-header-right">
                <span id="calendarMonthLabel" class="calendar-current-date"></span>
                <button type="button" id="calendarPrevBtn" class="calendar-nav-btn" aria-label="Previous">&laquo; Prev</button>
                <button type="button" id="calendarNextBtn" class="calendar-nav-btn" aria-label="Next">Next &raquo;</button>
                <button type="button" id="calendarJumpBtn" class="calendar-jump-btn" aria-label="Jump to date">Jump to date</button>
            </div>
        </section>

        <section class="calendar-main-section">
            <div class="calendar-legend">
                <span class="legend-item"><span class="legend-dot has-notes"></span>Day with notes</span>
                <span class="legend-item"><span class="legend-dot today"></span>Today</span>
            </div>

            <div class="calendar-grid" id="calendarGrid">
                <!-- Days will be rendered by calendar.js -->
            </div>
        </section>
    </div>
</main>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/Calendar/assets/js/calendar.js'
])

</body>
</html>

