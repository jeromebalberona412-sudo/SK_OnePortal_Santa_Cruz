<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/Events/assets/css/events.css'
    ])
</head>
<body>

<!-- ================= HEADER ================= -->
@include('layout::header')

<!-- ================= SIDEBAR ================= -->
@include('layout::sidebar')

<!-- ================= MAIN CONTENT ================= -->
<main class="main-content">
    <div class="page-container events-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Events</h1>
                <p class="page-subtitle">
                    Schedule and monitor actual activities conducted under SK programs.
                </p>
            </div>
            <div class="page-header-right">
                <button type="button" class="btn primary-btn" id="addEventBtn">
                    + Schedule Event
                </button>
            </div>
        </section>

        <section class="page-filters-section">
            <div class="filters-row">
                <div class="filter-item">
                    <label for="eventSearch" class="filter-label">Search event</label>
                    <div class="filter-input-wrapper">
                        <input type="text" id="eventSearch" class="filter-input" placeholder="Search by name, program, or venue">
                    </div>
                </div>

                <div class="filter-item">
                    <label for="eventProgramFilter" class="filter-label">Related program</label>
                    <select id="eventProgramFilter" class="filter-select">
                        <option value="">All programs</option>
                        <option value="youth-leadership">Youth Leadership Training</option>
                        <option value="sports-dev">Sports Development Program</option>
                        <option value="green-barangay">Green Barangay Project</option>
                    </select>
                </div>

                <div class="filter-item">
                    <label for="eventDateFilter" class="filter-label">Date</label>
                    <input type="date" id="eventDateFilter" class="filter-input">
                </div>
            </div>
        </section>

        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title">Upcoming & Recent Events</h2>
                <p class="section-description">
                    Events represent the actual implementation of SK programs—such as league openings, trainings, and drives.
                </p>
            </div>

            <div class="events-layout">
                <div class="events-list-card">
                    <div class="events-list-header">
                        <span class="table-title">Event Schedule</span>
                    </div>
                    <div id="eventList" class="events-list">
                        <!-- Events rendered by events.js (UI-only, mock data) -->
                    </div>
                </div>

                <aside class="events-side-card">
                    <h3 class="side-title">Today at a glance</h3>
                    <div id="todayEvents" class="today-events">
                        <!-- Highlight events happening today (from events.js) -->
                    </div>
                    <p class="side-note">
                        Later, this panel can be connected to the Calendar module for a unified view.
                    </p>
                </aside>
            </div>
        </section>
    </div>
</main>

<!-- Events Modal (UI-only, handled by events.js) -->
<div class="modal-backdrop" id="eventModal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h2 class="modal-title">Schedule Event</h2>
            <button type="button" class="modal-close" data-modal-close>&times;</button>
        </div>
        <div class="modal-body">
            <div class="modal-field">
                <label for="eventNameInput">Event Type</label>
                <select id="eventNameInput">
                    <option value="">Select Event Type</option>
                    <optgroup label="Educational Events">
                        <option value="Seminar / Workshop">Seminar / Workshop</option>
                        <option value="Tutorial Sessions">Tutorial Sessions</option>
                        <option value="Career Guidance Seminar">Career Guidance Seminar</option>
                        <option value="Leadership Training">Leadership Training</option>
                    </optgroup>
                    <optgroup label="Sports Events">
                        <option value="Basketball League">Basketball League</option>
                        <option value="Volleyball Tournament">Volleyball Tournament</option>
                        <option value="Sports Fest">Sports Fest</option>
                        <option value="Fun Run">Fun Run</option>
                        <option value="Inter-Barangay Tournament">Inter-Barangay Tournament</option>
                    </optgroup>
                    <optgroup label="Environmental Events">
                        <option value="Clean-up Drive">Clean-up Drive</option>
                        <option value="Tree Planting Activity">Tree Planting Activity</option>
                        <option value="Recycling Program">Recycling Program</option>
                        <option value="Coastal Clean-up">Coastal Clean-up</option>
                        <option value="Waste Segregation Seminar">Waste Segregation Seminar</option>
                    </optgroup>
                    <optgroup label="Health Events">
                        <option value="Medical Mission">Medical Mission</option>
                        <option value="Dental Mission">Dental Mission</option>
                        <option value="Blood Donation Drive">Blood Donation Drive</option>
                        <option value="Mental Health Seminar">Mental Health Seminar</option>
                        <option value="Feeding Program">Feeding Program</option>
                    </optgroup>
                    <optgroup label="Livelihood Events">
                        <option value="Skills Training">Skills Training</option>
                        <option value="Entrepreneurship Seminar">Entrepreneurship Seminar</option>
                        <option value="Job Fair">Job Fair</option>
                        <option value="Livelihood Workshop">Livelihood Workshop</option>
                    </optgroup>
                    <optgroup label="Social Awareness">
                        <option value="Gender Sensitivity Training">Gender Sensitivity Training</option>
                        <option value="Anti-VAWC Seminar">Anti-VAWC Seminar</option>
                        <option value="Youth Awareness Campaign">Youth Awareness Campaign</option>
                        <option value="Anti-Bullying Campaign">Anti-Bullying Campaign</option>
                        <option value="Anti-Drug Campaign">Anti-Drug Campaign</option>
                        <option value="Crime Prevention Seminar">Crime Prevention Seminar</option>
                    </optgroup>
                    <optgroup label="Safety & Emergency">
                        <option value="Disaster Preparedness Training">Disaster Preparedness Training</option>
                        <option value="Fire Drill">Fire Drill</option>
                    </optgroup>
                    <optgroup label="Cultural Events">
                        <option value="Talent Show">Talent Show</option>
                        <option value="Cultural Festival">Cultural Festival</option>
                        <option value="Dance Competition">Dance Competition</option>
                        <option value="Singing Contest">Singing Contest</option>
                        <option value="Art Workshop">Art Workshop</option>
                    </optgroup>
                    <optgroup label="Technology Events">
                        <option value="Computer Literacy Training">Computer Literacy Training</option>
                        <option value="Digital Skills Workshop">Digital Skills Workshop</option>
                        <option value="Online Safety Seminar">Online Safety Seminar</option>
                        <option value="Coding Workshop">Coding Workshop</option>
                    </optgroup>
                    <optgroup label="Community Events">
                        <option value="Community Outreach">Community Outreach</option>
                        <option value="Barangay Assembly">Barangay Assembly</option>
                        <option value="Volunteer Activities">Volunteer Activities</option>
                        <option value="Fiesta Activities">Fiesta Activities</option>
                        <option value="Youth Assembly">Youth Assembly</option>
                    </optgroup>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="modal-field" id="otherEventField" style="display: none;">
                <label for="otherEventInput">Specify Event Name</label>
                <input type="text" id="otherEventInput" placeholder="Enter event name">
            </div>
            <div class="modal-field">
                <label for="eventVenueInput">Venue</label>
                <input type="text" id="eventVenueInput" placeholder="e.g. Barangay Covered Court">
            </div>
            <div class="modal-field">
                <label for="eventDateInput">Date</label>
                <input type="date" id="eventDateInput">
            </div>
            <div class="modal-field">
                <label for="eventTimeInput">Time</label>
                <input type="time" id="eventTimeInput">
            </div>
            <div class="modal-field">
                <label for="eventParticipantsInput">Expected Participants</label>
                <input type="number" id="eventParticipantsInput" min="0" step="1" placeholder="e.g. 120">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" data-modal-cancel>Cancel</button>
            <button type="button" class="btn primary-btn" id="eventSaveBtn">Save</button>
        </div>
    </div>
</div>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/Events/assets/js/events.js'
])

</body>
</html>

