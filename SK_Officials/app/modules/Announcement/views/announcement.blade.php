<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/Announcement/assets/css/announcement.css'
    ])
</head>
<body>

<!-- ================= HEADER ================= -->
@include('layout::header')

<!-- ================= SIDEBAR ================= -->
@include('layout::sidebar')

<!-- ================= MAIN CONTENT ================= -->
<main class="main-content">
    <div class="announcement-page-container">
        <section class="announcement-header-section">
            <div>
                <h1 class="announcement-title">Announcements</h1>
                <p class="announcement-subtitle">Create and manage announcements for your barangay.</p>
            </div>
        </section>

        <section class="announcement-form-section">
            <form id="announcementForm" class="announcement-form" novalidate>
                <div class="form-group">
                    <label for="announcementTitle">Title <span class="required">*</span></label>
                    <input type="text" id="announcementTitle" name="title" placeholder="Enter announcement title" disabled>
                    <p class="error-message" data-for="title"></p>
                </div>

                <div class="form-group">
                    <label for="announcementContent">Content <span class="required">*</span></label>
                    <textarea id="announcementContent" name="content" rows="4" placeholder="Write the announcement details here" disabled></textarea>
                    <p class="error-message" data-for="content"></p>
                </div>

                <div class="form-actions">
                    <button type="submit" id="announcementPrimaryBtn" class="btn-primary">Add Announcement</button>
                    <button type="button" id="announcementCancelBtn" class="btn-secondary is-hidden">Cancel</button>
                </div>
            </form>
        </section>

        <section class="announcement-list-section">
            <h2 class="section-heading">All Announcements</h2>
            <div id="announcementList" class="announcement-list">
                <!-- Announcements rendered by announcement.js -->
            </div>
        </section>
    </div>
</main>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/Announcement/assets/js/announcement.js'
])

</body>
</html>

