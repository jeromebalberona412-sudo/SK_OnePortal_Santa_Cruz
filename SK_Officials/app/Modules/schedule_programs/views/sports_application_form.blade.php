<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Application Form - SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/schedule_programs/assets/css/sports_application_form.css'
    ])
</head>
<body>
//sample
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="sports-application-container">
    <div class="form-header">
        <h2>Sports Program Application Form</h2>
        <p class="subtitle">Configure your sports program details and requirements</p>
    </div>

    <form id="sportsApplicationForm" class="sports-form">
        
        <!-- Sports Selection -->
        <div class="form-section">
            <h3 class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                Sports Selection
            </h3>
            <div class="form-group">
                <label for="sportsType">Select Sport <span class="required">*</span></label>
                <select id="sportsType" name="sports_type" class="form-control" required>
                    <option value="">— Select Sport —</option>
                    <option value="basketball">Basketball</option>
                    <option value="volleyball">Volleyball</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div id="otherSportField" class="form-group" style="display: none;">
                <label for="otherSport">Specify Other Sport <span class="required">*</span></label>
                <input type="text" id="otherSport" name="other_sport" class="form-control" placeholder="Enter sport name">
            </div>
        </div>

        <!-- Age Classification (Basketball Only) -->
        <div id="ageClassificationSection" class="form-section" style="display: none;">
            <h3 class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                Age Classification (KK Only: 15–30)
            </h3>
            <div class="age-divisions">
                <div class="form-check">
                    <input type="checkbox" id="cadetDivision" name="divisions[]" value="cadet" class="form-check-input">
                    <label for="cadetDivision" class="form-check-label">
                        <strong>Cadet Division</strong> – 15–17 years old
                    </label>
                </div>
                <div class="form-check">
                    <input type="checkbox" id="youthDivision" name="divisions[]" value="youth" class="form-check-input">
                    <label for="youthDivision" class="form-check-label">
                        <strong>Youth Division</strong> – 18–21 years old
                    </label>
                </div>
                <div class="form-check">
                    <input type="checkbox" id="youngAdultDivision" name="divisions[]" value="young_adult" class="form-check-input">
                    <label for="youngAdultDivision" class="form-check-label">
                        <strong>Young Adult Division</strong> – 22–25 years old
                    </label>
                </div>
                <div class="form-check">
                    <input type="checkbox" id="seniorDivision" name="divisions[]" value="senior" class="form-check-input">
                    <label for="seniorDivision" class="form-check-label">
                        <strong>KK Senior Division</strong> – 26–30 years old
                    </label>
                </div>
            </div>
        </div>

        <!-- Announcement Section -->
        <div class="form-section">
            <h3 class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>
                Announcement Section
            </h3>
            <div class="form-group">
                <label for="announcement">Program Announcement / Instructions</label>
                <textarea id="announcement" name="announcement" class="form-control" rows="5" 
                    placeholder="Enter instructions about required submissions, general guidelines for participants, and other important information..."></textarea>
                <small class="form-text">This will be displayed to participants when they view the application form.</small>
            </div>
        </div>

        <!-- Dynamic Requirements / Application Form -->
        <div class="form-section">
            <h3 class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Requirements / Application Form
            </h3>
            <p class="instruction-text">Please input the required questions for this sports application form.</p>
            
            <div id="questionsContainer">
                <!-- Dynamic questions will be added here -->
            </div>

            <div class="question-actions">
                <button type="button" id="addQuestionBtn" class="btn btn-add-question">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Question
                </button>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <div class="form-actions-right">
                <button type="button" id="previewBtnBottom" class="btn btn-preview-bottom">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Preview Form
                </button>
                <button type="button" class="btn btn-outline" onclick="window.history.back()">Cancel</button>
                <button type="submit" class="btn btn-save">Save Program</button>
            </div>
        </div>

    </form>
</div>

<!-- Preview Button (Fixed Position) - Hidden, using bottom button instead -->
<button type="button" id="previewBtn" class="btn-preview-fixed" style="display: none;">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
    Preview Form
</button>

<!-- Preview Modal -->
<div class="preview-modal-overlay" id="previewModal" style="display: none;">
    <div class="preview-modal" id="previewModalBox">
        <div class="preview-modal-header">
            <h3>Form Preview</h3>
            <div class="preview-modal-controls">
                <button type="button" class="preview-control-btn" id="previewToggle" title="Maximize">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/></svg>
                </button>
                <button type="button" class="preview-control-btn preview-close-btn" id="previewClose" title="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        </div>
        <div class="preview-modal-body" id="previewContent">
            <!-- Preview content will be inserted here -->
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="delete-modal-overlay" id="deleteModal" style="display: none;">
    <div class="delete-modal">
        <div class="delete-modal-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <h3 class="delete-modal-title">Remove Question?</h3>
        <p class="delete-modal-message">Are you sure you want to remove this question? This action cannot be undone.</p>
        <div class="delete-modal-actions">
            <button type="button" class="btn btn-cancel-delete" id="cancelDelete">Cancel</button>
            <button type="button" class="btn btn-confirm-delete" id="confirmDelete">Remove</button>
        </div>
    </div>
</div>
</main>

<!-- Question Template (Hidden) -->
<template id="questionTemplate">
    <div class="question-item">
        <div class="question-header">
            <span class="question-number"></span>
            <div class="question-actions">
                <button type="button" class="btn-duplicate-question" title="Duplicate">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                </button>
                <button type="button" class="btn-remove-question" title="Delete">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                </button>
            </div>
        </div>
        <div class="question-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Question Label</label>
                    <input type="text" class="form-control question-label" name="questions[][label]" 
                        placeholder="e.g., Upload School ID, Birth Certificate, etc.">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Question Type</label>
                    <select class="form-control question-type" name="questions[][type]">
                        <option value="text">Text Input</option>
                        <option value="textarea">Long Text</option>
                        <option value="file">File Upload</option>
                        <option value="email">Email</option>
                        <option value="number">Number</option>
                        <option value="phone">Phone Number</option>
                        <option value="select">Dropdown</option>
                        <option value="radio">Multiple Choice</option>
                        <option value="checkbox">Checkboxes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Required</label>
                    <select class="form-control question-required" name="questions[][required]">
                        <option value="required">Required</option>
                        <option value="optional">Optional</option>
                    </select>
                </div>
            </div>
            <div class="form-group options-group" style="display: none;">
                <label>Options</label>
                <div class="options-list">
                    <!-- Options will be added here dynamically -->
                </div>
                <button type="button" class="btn btn-add-option">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Option
                </button>
            </div>
        </div>
    </div>
</template>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/schedule_programs/assets/js/sports_application_form.js'
])

</body>
</html>
