<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Application Management - SK Officials Portal</title>

    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/schedule_programs/assets/css/sports_application_form.css'
    ])
</head>
<body>
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="sports-application-container">
    <!-- Two Column Layout -->
    <div style="display: grid; grid-template-columns: 350px 1fr; gap: 24px; margin-bottom: 24px;">
        
        <!-- Left Side: Created Programs Table -->
        <div class="created-programs-section">
            <div class="section-card">
                <div class="section-header">
                    <h3 style="font-size: 16px; font-weight: 700; color: #1f2937; margin: 0;">Created Sports Programs</h3>
                    <p style="font-size: 12px; color: #6b7280; margin: 4px 0 0 0;">View and manage your programs</p>
                </div>
                <div class="programs-table-wrapper" style="margin-top: 16px;">
                    <table class="programs-mini-table">
                        <thead>
                            <tr>
                                <th>Program</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="createdProgramsTableBody">
                            <!-- Sample data -->
                            <tr>
                                <td>
                                    <div style="font-weight: 600; font-size: 13px; color: #111827;">Basketball Tournament 2026</div>
                                    <div style="font-size: 11px; color: #6b7280;">4 Divisions</div>
                                </td>
                                <td style="font-size: 12px; color: #4b5563;">May 15, 2026</td>
                                <td>
                                    <button type="button" class="btn-view-program" data-program-id="1" title="View Details">
                                        View
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div style="font-weight: 600; font-size: 13px; color: #111827;">Volleyball League</div>
                                    <div style="font-size: 11px; color: #6b7280;">2 Divisions</div>
                                </td>
                                <td style="font-size: 12px; color: #4b5563;">Jun 10, 2026</td>
                                <td>
                                    <button type="button" class="btn-view-program" data-program-id="2" title="View Details">
                                        View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Side: Create Button -->
        <div>
            <div class="form-header">
                <div>
                    <h2>Sports Application Management</h2>
                    <p class="subtitle">Create and manage sports program applications</p>
                </div>
                <button type="button" id="createProgramBtn" class="btn btn-create-program">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Create Sports Program
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Program Modal -->
<div class="modal-overlay" id="createProgramModal" style="display: none;">
    <div class="modal-box modal-large" id="createProgramModalBox">
        <div class="modal-header">
            <h3>Create Sports Program</h3>
            <button type="button" class="modal-close" id="closeProgramModal">&times;</button>
        </div>
        <div class="modal-body">
            <p class="modal-subtitle">Fill out the form below to create a new sports program</p>
            
            <form id="sportsApplicationForm" class="sports-form">
                
                <!-- Program Details Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                        Program Details
                    </h3>
                    
                    <div class="form-group">
                        <label for="programName">Program Name <span class="required">*</span></label>
                        <input type="text" id="programName" name="program_name" class="form-control" 
                            placeholder="e.g., Basketball Tournament 2026" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="startDate">Start Date <span class="required">*</span></label>
                            <input type="date" id="startDate" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="endDate">End Date <span class="required">*</span></label>
                            <input type="date" id="endDate" name="end_date" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="startTime">Start Time <span class="required">*</span></label>
                            <input type="time" id="startTime" name="start_time" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="endTime">End Time <span class="required">*</span></label>
                            <input type="time" id="endTime" name="end_time" class="form-control" required>
                        </div>
                    </div>
                </div>

                <!-- Sports Selection -->
                <div class="form-section">
                    <h3 class="section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                        Sports Selection
                    </h3>
                    <div class="form-group">
                        <label for="sportsType">Select Sport <span class="required">*</span></label>
                        <select id="sportsType" name="sports_type" class="form-control" required>
                            <option value="">— Select Sport —</option>
                            <option value="basketball">Basketball</option>
                            <option value="volleyball">Volleyball</option>
                            <option value="badminton">Badminton</option>
                            <option value="football">Football</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div id="otherSportField" class="form-group" style="display: none;">
                        <label for="otherSport">Specify Other Sport <span class="required">*</span></label>
                        <input type="text" id="otherSport" name="other_sport" class="form-control" placeholder="Enter sport name">
                    </div>
                </div>

                <!-- Announcement Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>
                        Announcement / Instructions
                    </h3>
                    <div class="form-group">
                        <label for="announcement">Program Announcement</label>
                        <textarea id="announcement" name="announcement" class="form-control" rows="4" 
                            placeholder="Enter instructions, guidelines, and important information for participants..."></textarea>
                        <small class="form-text">This will be displayed to participants when they view the application form.</small>
                    </div>
                </div>

                <!-- Dynamic Question Builder (Google Form Style) -->
                <div class="form-section question-builder-section">
                    <div class="section-title-with-action">
                        <h3 class="section-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            Application Form Questions
                        </h3>
                        <button type="button" id="addQuestionBtn" class="btn btn-add-question-inline">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add Question
                        </button>
                    </div>
                    <p class="instruction-text">Build your custom application form by adding questions below.</p>
                    
                    <div id="questionsContainer" class="questions-list">
                        <!-- Dynamic questions will be added here -->
                    </div>

                    <div class="empty-state" id="emptyQuestionsState">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
                        <p>No questions added yet</p>
                        <small>Click "Add Question" to start building your form</small>
                    </div>
                </div>

                <!-- Requirements Section (Google Form Style) -->
                <div class="form-section requirements-section">
                    <h3 class="section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        Requirements for Sports Application
                    </h3>
                    <p class="instruction-text">Specify the documents and requirements participants must submit.</p>
                    
                    <div id="requirementsContainer">
                        <!-- Dynamic requirements will be added here -->
                    </div>

                    <div class="question-actions">
                        <button type="button" id="addRequirementBtn" class="btn btn-add-question">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add Requirement
                        </button>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <div class="form-actions-right">
                        <button type="button" id="previewFormBtn" class="btn btn-preview-bottom">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Preview Form
                        </button>
                        <button type="button" class="btn btn-outline" id="cancelProgramBtn">Cancel</button>
                        <button type="submit" class="btn btn-save">Create Program</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

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

<!-- Payment Status Modal -->
<div class="modal-overlay" id="paymentModal" style="display: none;">
    <div class="modal-box modal-small">
        <div class="modal-header">
            <h3>Payment Status</h3>
            <button type="button" class="modal-close" id="closePaymentModal">&times;</button>
        </div>
        <div class="modal-body">
            <p class="modal-message">Has the applicant paid the registration fee?</p>
            <div class="payment-buttons">
                <button type="button" class="btn btn-paid" id="btnPaid">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Paid
                </button>
                <button type="button" class="btn btn-not-paid" id="btnNotPaid">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Not Paid
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Reason Modal -->
<div class="modal-overlay" id="rejectModal" style="display: none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Rejection Reason</h3>
            <button type="button" class="modal-close" id="closeRejectModal">&times;</button>
        </div>
        <div class="modal-body">
            <p class="modal-message">Please select reason(s) for rejection:</p>
            <div class="reject-reasons">
                <label class="reject-reason-item">
                    <input type="checkbox" name="reject_reason" value="Incomplete Requirements">
                    <span>Incomplete Requirements</span>
                </label>
                <label class="reject-reason-item">
                    <input type="checkbox" name="reject_reason" value="Invalid Documents">
                    <span>Invalid Documents</span>
                </label>
                <label class="reject-reason-item">
                    <input type="checkbox" name="reject_reason" value="Does Not Meet Age Criteria">
                    <span>Does Not Meet Age Criteria</span>
                </label>
                <label class="reject-reason-item">
                    <input type="checkbox" name="reject_reason" value="Duplicate Application">
                    <span>Duplicate Application</span>
                </label>
                <label class="reject-reason-item">
                    <input type="checkbox" name="reject_reason" value="Late Submission">
                    <span>Late Submission</span>
                </label>
                <label class="reject-reason-item">
                    <input type="checkbox" name="reject_reason" value="Other" id="rejectOtherCheckbox">
                    <span>Other</span>
                </label>
            </div>
            <div id="rejectOtherTextarea" style="display:none;margin-top:12px;">
                <label for="rejectOtherText">Please specify:</label>
                <textarea id="rejectOtherText" class="form-control" rows="3" placeholder="Enter rejection reason..."></textarea>
            </div>
            <div class="modal-actions" style="margin-top:20px;">
                <button type="button" class="btn btn-outline" id="cancelReject">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmReject">Confirm Rejection</button>
            </div>
        </div>
    </div>
</div>

<!-- View Program Details Modal -->
<div class="view-program-modal-overlay" id="viewProgramModal" style="display: none;">
    <div class="view-program-modal">
        <div class="view-program-modal-header">
            <h3>Program Details</h3>
            <button type="button" class="modal-close" id="closeViewProgramModal">&times;</button>
        </div>
        <div class="view-program-modal-body" id="viewProgramContent">
            <!-- Program details will be inserted here -->
        </div>
    </div>
</div>

<!-- Question Template (Hidden) -->
<template id="questionTemplate">
    <div class="question-item">
        <div class="question-header">
            <span class="question-number"></span>
            <div class="question-actions">
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
                        placeholder="e.g., Full Name, Age, Contact Number">
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
        </div>
    </div>
</template>

<!-- Requirement Template (Hidden) -->
<template id="requirementTemplate">
    <div class="requirement-item">
        <div class="requirement-header">
            <span class="requirement-number"></span>
            <div class="requirement-actions">
                <button type="button" class="btn-remove-requirement" title="Delete">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                </button>
            </div>
        </div>
        <div class="requirement-body">
            <div class="form-group">
                <label>Requirement Name</label>
                <input type="text" class="form-control requirement-name" name="requirements[][name]" 
                    placeholder="e.g., Birth Certificate, Medical Certificate, ID Photo">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>File Type</label>
                    <select class="form-control requirement-type" name="requirements[][type]">
                        <option value="pdf">PDF Document</option>
                        <option value="image">Image (JPG, PNG)</option>
                        <option value="any">Any File Type</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Required</label>
                    <select class="form-control requirement-required" name="requirements[][required]">
                        <option value="required">Required</option>
                        <option value="optional">Optional</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Description / Instructions</label>
                <textarea class="form-control requirement-description" name="requirements[][description]" rows="2" 
                    placeholder="Add any specific instructions for this requirement..."></textarea>
            </div>
        </div>
    </div>
</template>

</main>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/schedule_programs/assets/js/sports_application_form.js'
])

</body>
</html>
        
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
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/schedule_programs/assets/js/sports_application_form.js'
])

</body>
</html>
