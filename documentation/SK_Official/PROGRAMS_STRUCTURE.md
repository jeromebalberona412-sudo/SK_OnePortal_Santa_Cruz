# SK Officials Schedule Programs - Complete Structure

## Overview
This document outlines the complete structure for all 8 program types in the SK Officials schedule_programs module.

## Program Types

### 1. Equitable Access to Quality Education (Scholarship)
**Status:** ✅ Complete
- **Files:**
  - `views/scholar_application_from.blade.php` - Program Schedule
  - `views/scholarship_requests.blade.php` - Program Requests
  - `views/scholar_list.blade.php` - Program List
  - `views/scholar_evaluation.blade.php` - Evaluation
  - `assets/js/scholar_application_from.js`
  - `assets/js/scholarship_requests.js`
  - `assets/js/scholar_list.js`
- **Color:** #2196F3 (Blue)
- **Icon:** 🎓

### 2. Environmental Protection
**Status:** 🔄 In Progress
- **Files:**
  - `views/environmental_application_form.blade.php` - Program Schedule
  - `views/environmental_requests.blade.php` - Program Requests
  - `views/environmental_list.blade.php` - Program List
  - `views/environmental_evaluation.blade.php` - Evaluation
  - `assets/js/environmental_program.js`
  - `assets/js/environmental_requests.js`
  - `assets/js/environmental_list.js`
- **Color:** #4CAF50 (Green)
- **Icon:** 🌱
- **Description:** Manage environmental programs, track participation, and promote sustainable practices

### 3. Disaster Risk Reduction and Resiliency
**Status:** 📝 Planned
- **Files:**
  - `views/disaster_application_form.blade.php` - Program Schedule
  - `views/disaster_requests.blade.php` - Program Requests
  - `views/disaster_list.blade.php` - Program List
  - `views/disaster_evaluation.blade.php` - Evaluation
  - `assets/js/disaster_program.js`
  - `assets/js/disaster_requests.js`
  - `assets/js/disaster_list.js`
- **Color:** #FF9800 (Orange)
- **Icon:** 🚩
- **Description:** Prepare youth for disaster response and build community resilience

### 4. Youth Employment and Livelihood
**Status:** 📝 Planned
- **Files:**
  - `views/livelihood_application_form.blade.php` - Program Schedule
  - `views/livelihood_requests.blade.php` - Program Requests
  - `views/livelihood_list.blade.php` - Program List
  - `views/livelihood_evaluation.blade.php` - Evaluation
  - `assets/js/livelihood_program.js`
  - `assets/js/livelihood_requests.js`
  - `assets/js/livelihood_list.js`
- **Color:** #9C27B0 (Purple)
- **Icon:** 💼
- **Description:** Provide employment opportunities and livelihood training for youth

### 5. Medicines Anti-Drug and Peace and Order
**Status:** 📝 Planned
- **Files:**
  - `views/antidrug_application_form.blade.php` - Program Schedule
  - `views/antidrug_requests.blade.php` - Program Requests
  - `views/antidrug_list.blade.php` - Program List
  - `views/antidrug_evaluation.blade.php` - Evaluation
  - `assets/js/antidrug_program.js`
  - `assets/js/antidrug_requests.js`
  - `assets/js/antidrug_list.js`
- **Color:** #E91E63 (Pink)
- **Icon:** 🚫
- **Description:** Promote drug-free lifestyle and maintain peace and order in the community

### 6. Gender Sensitivity
**Status:** 📝 Planned
- **Files:**
  - `views/gender_application_form.blade.php` - Program Schedule
  - `views/gender_requests.blade.php` - Program Requests
  - `views/gender_list.blade.php` - Program List
  - `views/gender_evaluation.blade.php` - Evaluation
  - `assets/js/gender_program.js`
  - `assets/js/gender_requests.js`
  - `assets/js/gender_list.js`
- **Color:** #9C27B0 (Purple)
- **Icon:** 👥
- **Description:** Promote gender equality and sensitivity awareness among youth

### 7. Feeding Program for KK Members
**Status:** 📝 Planned
- **Files:**
  - `views/feeding_application_form.blade.php` - Program Schedule
  - `views/feeding_requests.blade.php` - Program Requests
  - `views/feeding_list.blade.php` - Program List
  - `views/feeding_evaluation.blade.php` - Evaluation
  - `assets/js/feeding_program.js`
  - `assets/js/feeding_requests.js`
  - `assets/js/feeding_list.js`
- **Color:** #FF5722 (Deep Orange)
- **Icon:** 🍽️
- **Description:** Provide nutritional support and feeding programs for youth members

### 8. Sports Development
**Status:** ✅ Complete
- **Files:**
  - `views/sports_create_program.blade.php` - Program Schedule
  - `views/sports_requests.blade.php` - Program Requests
  - `views/sports_list.blade.php` - Program List
  - `views/sports_application_form.blade.php` - Application Form
  - `views/sports_application_history.blade.php` - History
  - `assets/js/sports_program.js`
  - `assets/js/sports_requests.js`
- **Color:** #00BCD4 (Cyan)
- **Icon:** ⚽
- **Description:** Promote physical fitness and sports excellence among youth

### 9. Others
**Status:** 📝 Planned
- **Files:**
  - `views/others_application_form.blade.php` - Program Schedule
  - `views/others_requests.blade.php` - Program Requests
  - `views/others_list.blade.php` - Program List
  - `views/others_evaluation.blade.php` - Evaluation
  - `assets/js/others_program.js`
  - `assets/js/others_requests.js`
  - `assets/js/others_list.js`
- **Color:** #607D8B (Blue Grey)
- **Icon:** ⚡
- **Description:** Miscellaneous programs and activities for youth development

## Common Components

### Shared Partials
- `partials/program-tabs.blade.php` - Universal tab navigation for all programs
- `partials/scholarship-tabs.blade.php` - Scholarship-specific tabs (legacy)
- `partials/scholarship-page-top.blade.php` - Page header component
- `partials/scholar-report-modal.blade.php` - Report generation modal
- `partials/word-report-shell.blade.php` - Word editor shell

### Shared CSS
- `assets/css/scholarship_application_form.css` - Main styles (used by all programs)
- `assets/css/sports_requests.css` - Request management styles
- `assets/css/scholar_application_from.css` - Form builder styles
- `assets/css/scholar_report.css` - Report styles
- `assets/css/scholar_list.css` - List view styles

### Shared JavaScript Utilities
- `assets/js/spfb-form-builder.js` - Form builder utility (shared)
- `assets/js/scholar_schedule.js` - Schedule management utility

## File Naming Convention

### Views
- `{program}_application_form.blade.php` - Program schedule/creation page
- `{program}_requests.blade.php` - Application requests management
- `{program}_list.blade.php` - Approved participants list
- `{program}_evaluation.blade.php` - Evaluation and performance tracking

### JavaScript
- `{program}_program.js` - Main program logic (schedule, create, filter)
- `{program}_requests.js` - Request management logic
- `{program}_list.js` - List management and export logic

### Storage Keys
- `{program}_application_forms` - Program schedules
- `{program}_requests` - Application requests
- `{program}_list` - Approved participants

## Common Features Across All Programs

### 1. Program Schedule Tab
- Create new programs
- Set program details (name, committee, venue, description, terms)
- Schedule start/end dates and times
- Custom form builder for application questions
- Active program management
- Program history with filters
- Status management (Open, Closed, Upcoming, Auto)

### 2. Program Requests Tab
- View all applications
- Filter by status (Pending, Approved, Rejected)
- Search by name or details
- Date/time range filters
- View application details in PDF format
- Approve/Reject applications with reasons
- Delete applications
- Statistics cards (Total, Pending, Approved, Rejected)

### 3. Program List Tab
- View all approved participants
- Filter by year/program
- Search functionality
- Export to CSV
- Status management (Pending Payout, Paid, Cancelled)
- Edit participant status
- Pagination
- Statistics cards

### 4. Evaluation Tab
- Performance tracking
- Evaluation forms
- Progress monitoring
- Reports generation

## Implementation Priority

1. ✅ **Equitable Access to Quality Education** - Complete
2. ✅ **Sports Development** - Complete
3. 🔄 **Environmental Protection** - In Progress
4. 📝 **Disaster Risk Reduction** - Next
5. 📝 **Youth Employment and Livelihood** - Next
6. 📝 **Anti-Drug and Peace and Order** - Next
7. 📝 **Gender Sensitivity** - Next
8. 📝 **Feeding Program** - Next
9. 📝 **Others** - Last

## Routes Structure

```php
// Environmental Protection
Route::get('/environmental/schedule', 'EnvironmentalController@schedule')->name('environmental.schedule');
Route::get('/environmental/requests', 'EnvironmentalController@requests')->name('environmental.requests');
Route::get('/environmental/list', 'EnvironmentalController@list')->name('environmental.list');
Route::get('/environmental/evaluation', 'EnvironmentalController@evaluation')->name('environmental.evaluation');

// Disaster Risk Reduction
Route::get('/disaster/schedule', 'DisasterController@schedule')->name('disaster.schedule');
Route::get('/disaster/requests', 'DisasterController@requests')->name('disaster.requests');
Route::get('/disaster/list', 'DisasterController@list')->name('disaster.list');
Route::get('/disaster/evaluation', 'DisasterController@evaluation')->name('disaster.evaluation');

// ... similar pattern for all programs
```

## Database Schema (Future)

```sql
-- Programs table (all program types)
CREATE TABLE programs (
    id BIGINT PRIMARY KEY,
    program_type VARCHAR(50), -- 'scholarship', 'environmental', 'disaster', etc.
    program_name VARCHAR(200),
    committee VARCHAR(100),
    venue VARCHAR(500),
    description TEXT,
    terms TEXT,
    participation_qty INT,
    start_date DATE,
    end_date DATE,
    start_time TIME,
    end_time TIME,
    status VARCHAR(20), -- 'open', 'closed', 'upcoming'
    custom_questions JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Applications table (all program applications)
CREATE TABLE program_applications (
    id BIGINT PRIMARY KEY,
    program_id BIGINT,
    user_id BIGINT,
    application_data JSON,
    status VARCHAR(20), -- 'pending', 'approved', 'rejected'
    rejection_reason TEXT,
    submitted_at TIMESTAMP,
    reviewed_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Participants table (approved applicants)
CREATE TABLE program_participants (
    id BIGINT PRIMARY KEY,
    program_id BIGINT,
    application_id BIGINT,
    user_id BIGINT,
    status VARCHAR(20), -- 'active', 'completed', 'cancelled'
    payment_status VARCHAR(20), -- 'pending', 'paid' (if applicable)
    cancellation_reason TEXT,
    approved_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Next Steps

1. Create universal `program-tabs.blade.php` partial
2. Generate all remaining view files
3. Create corresponding JavaScript files
4. Update routes
5. Create controllers
6. Implement database integration
7. Add API endpoints
8. Testing and validation

## Notes

- All programs follow the same UI/UX pattern as the scholarship program
- Each program has its own color scheme and branding
- Form builders are customizable per program
- Reports can be generated for each program type
- All programs support the same workflow: Schedule → Request → List → Evaluation
