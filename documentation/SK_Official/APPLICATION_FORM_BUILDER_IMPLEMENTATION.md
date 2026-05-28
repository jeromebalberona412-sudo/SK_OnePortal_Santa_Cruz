# Application Form Builder Implementation Plan

## Overview
Add the Application Form Builder feature (from scholarship schedule) to all committee schedule pages.

## Committees to Update
1. ✅ Environmental
2. ✅ Disaster
3. ✅ Livelihood
4. ✅ Medicines
5. ✅ Antidrug
6. ✅ Gender
7. ✅ Feeding
8. ✅ Others

## Required Components

### 1. CSS Files (Add to @vite)
```blade
'app/Modules/schedule_programs/assets/css/sports/sports_requests.css'
```
This contains all `.spfb-*` styles for the form builder.

### 2. JavaScript Files (Add to @vite)
```blade
'app/Modules/schedule_programs/assets/js/shared/spfb-form-builder.js'
```
This handles the form builder functionality.

### 3. Modal Structure Updates

#### Add to Modal Body (after Schedule Section):

```blade
<!-- Application Form Section -->
<div class="schol-schedule-card">
    <h4 class="schol-schedule-title" style="margin-bottom:16px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Application Form Builder
    </h4>
    
    <!-- Announcement Section -->
    <div class="spfb-announcement-section">
        <label class="spfb-announcement-label">Announcement <span style="color:#ef4444;">*</span></label>
        <p class="spfb-announcement-hint">This message will be shown to Kabataan members when they open the application form.</p>
        <textarea id="spfbAnnouncement" class="spfb-announcement-textarea" maxlength="500" placeholder="Enter announcement or instructions for applicants..."></textarea>
        <div class="spfb-announcement-counter"><span id="spfbAnnouncementCount">0</span>/500</div>
    </div>

    <!-- Custom Questions Builder -->
    <div class="spfb-section-card spfb-section-builder">
        <div class="spfb-section-label">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Custom Questions
            <span class="spfb-badge" id="spfbQuestionCount">0 questions</span>
        </div>
        <p class="spfb-builder-hint">Add custom questions that Kabataan members will answer when applying.</p>

        <div id="spfbQuestionList" class="spfb-question-list">
            <div class="spfb-empty-state" id="spfbEmptyState">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <p>No questions yet. Click <strong>Add Question</strong> to start building your custom form.</p>
            </div>
        </div>

        <button type="button" class="spfb-add-question-btn" id="spfbAddQuestionBtn">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Question
        </button>
    </div>
</div>
```

### 4. Additional Styles Needed

Add to `<head>` section:
```blade
<style>
    .toggle-switch {
        position: relative;
        width: 44px;
        height: 24px;
    }
    .toggle-input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 24px;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }
    .toggle-input:checked + .toggle-slider {
        background-color: #10b981;
    }
    .toggle-input:checked + .toggle-slider:before {
        transform: translateX(20px);
    }
    .saf-forms-table tbody tr.active-program-row {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
    }
    .saf-forms-table tbody tr.active-program-row td {
        color: white;
        border-bottom: 2px solid rgba(255,255,255,0.3);
    }
    .saf-forms-table tbody tr.active-program-row:hover {
        background: linear-gradient(135deg, #5568d3 0%, #653a8b 100%);
    }
</style>
```

### 5. CSS Classes Needed

The form builder uses these CSS classes (from sports_requests.css):
- `.spfb-announcement-section`
- `.spfb-announcement-label`
- `.spfb-announcement-hint`
- `.spfb-announcement-textarea`
- `.spfb-announcement-counter`
- `.spfb-section-card`
- `.spfb-section-builder`
- `.spfb-section-label`
- `.spfb-badge`
- `.spfb-builder-hint`
- `.spfb-question-list`
- `.spfb-empty-state`
- `.spfb-add-question-btn`
- `.spfb-question-card`
- `.spfb-card-preview-header`
- `.spfb-card-num`
- `.spfb-card-meta`
- `.spfb-card-label`
- `.spfb-card-type-tag`
- `.spfb-card-actions`
- `.spfb-req-badge`
- `.spfb-icon-btn`
- `.spfb-edit-btn`
- `.spfb-delete-btn`
- And many more...

## Implementation Steps

### Step 1: Update Vite Configuration
Add CSS file to all committee schedules:
```blade
@vite([
    'app/Modules/layout/css/header.css',
    'app/Modules/layout/css/sidebar.css',
    'app/Modules/schedule_programs/assets/css/scholarship/scholarship_application_form.css',
    'app/Modules/schedule_programs/assets/css/scholarship/scholar_application_from.css',
    'app/Modules/schedule_programs/assets/css/sports/sports_requests.css'  // ADD THIS
])
```

### Step 2: Add Inline Styles
Add the toggle switch and active row styles to `<head>` section.

### Step 3: Add Application Form Builder Section
Insert the complete Application Form Builder HTML after the Schedule Section in the modal.

### Step 4: Include JavaScript
Add the form builder JavaScript file:
```blade
@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/schedule_programs/assets/js/scholarship/scholar_application_from.js',
    'app/Modules/schedule_programs/assets/js/scholarship/scholar_schedule.js',
    'app/Modules/schedule_programs/assets/js/shared/spfb-form-builder.js'  // ADD THIS
])
```

### Step 5: Initialize Form Builder
Add initialization code at the end of the file:
```javascript
<script>
// Initialize form builder
document.addEventListener('DOMContentLoaded', () => {
    if (typeof window.SPFBFormBuilder !== 'undefined') {
        window.SPFBFormBuilder.init({
            containerId: 'spfbQuestionList',
            addButtonId: 'spfbAddQuestionBtn',
            emptyStateId: 'spfbEmptyState',
            questionCountId: 'spfbQuestionCount',
            announcementId: 'spfbAnnouncement',
            announcementCountId: 'spfbAnnouncementCount'
        });
    }
});
</script>
```

## Complexity Assessment

**High Complexity** - This is a major feature addition that requires:
1. Large HTML structure insertion
2. Multiple CSS file dependencies
3. JavaScript initialization
4. Data handling for custom questions
5. Integration with existing save/edit functionality

## Recommendation

Given the complexity and file size, I recommend:

1. **Create a shared partial** for the Application Form Builder
2. **Include it in all committee schedules** via `@include`
3. **Test thoroughly** with one committee first
4. **Roll out gradually** to other committees

## Alternative Approach

Create a new partial file:
`views/partials/application-form-builder.blade.php`

Then include it in each committee schedule:
```blade
@include('schedule_programs::partials.application-form-builder')
```

This approach:
- ✅ Reduces code duplication
- ✅ Makes maintenance easier
- ✅ Keeps individual files smaller
- ✅ Allows easy updates to all committees at once

## Date
May 28, 2026
