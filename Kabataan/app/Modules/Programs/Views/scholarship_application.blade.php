<!-- Global Loading Screen -->
@include('dashboard::loading')

<!-- Scholarship Application Modal -->
<div class="schol-modal-overlay" id="scholarshipApplicationModal">
    <div class="schol-modal-box">
        <div class="schol-modal-header">
            <h3>Scholarship Application Form</h3>
            <button type="button" class="schol-modal-close" id="scholarshipModalClose">&times;</button>
        </div>
        
        <div class="schol-modal-body">
            <!-- PDF Form -->
            <form id="scholarshipForm" class="schol-pdf-form">
                <!-- Header: Logo left, Title center, Date right -->
                <div class="schol-pdf-header">
                    <img src="/images/barangay_logo.png" alt="Barangay Logo" class="schol-pdf-logo-img">
                    <div style="flex:1;text-align:center;">
                        <h2 class="schol-pdf-title">SCHOLARSHIP APPLICATION FORM</h2>
                        <div class="schol-pdf-date-field">
                            <span class="schol-pdf-inline-label">Date:</span>
                            <input type="date" id="scholApplicationDate" class="schol-input-inline" required>
                        </div>
                    </div>
                    <!-- Picture upload -->
                    <div class="schol-pdf-picture-box" id="pictureBox" style="cursor:pointer;position:relative;">
                        <input type="file" id="pictureUpload" accept="image/*" style="display:none;">
                        <img id="picturePreview" style="display:none;width:100%;height:100%;object-fit:cover;border-radius:2px;">
                        <span id="pictureText">Picture<br>Here</span>
                    </div>
                </div>

                <!-- APPLICANT'S PERSONAL INFORMATION -->
                <div class="schol-pdf-section">
                    <p class="schol-pdf-inline-title">APPLICANT'S PERSONAL INFORMATION:</p>

                    <!-- Last Name / First Name / Middle Name inline -->
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">Last Name:</span>
                        <input type="text" name="lastName" class="schol-input-inline" required>
                        <span class="schol-pdf-inline-label">First Name:</span>
                        <input type="text" name="firstName" class="schol-input-inline" required>
                        <span class="schol-pdf-inline-label">Middle Name:</span>
                        <input type="text" name="middleName" class="schol-input-inline">
                    </div>

                    <!-- DOB / Gender / Age / Contact inline -->
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">Date of Birth:</span>
                        <input type="date" name="dateOfBirth" class="schol-input-inline" id="scholDOB" required>
                        <span class="schol-pdf-inline-label">Gender:</span>
                        <select name="gender" class="schol-input-inline" required>
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                        <span class="schol-pdf-inline-label">Age:</span>
                        <input type="number" name="age" class="schol-input-inline" id="scholAge" readonly>
                        <span class="schol-pdf-inline-label">Contact No:</span>
                        <input type="tel" name="contactNo" class="schol-input-inline" required>
                    </div>

                    <!-- Complete Address full width -->
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">Complete Address:</span>
                        <textarea name="completeAddress" class="schol-input-inline schol-textarea" required></textarea>
                    </div>

                    <!-- Email Address -->
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">Email Address:</span>
                        <input type="email" name="emailAddress" class="schol-input-inline" required>
                    </div>
                </div>

                <!-- ACADEMIC INFORMATION -->
                <div class="schol-pdf-section">
                    <p class="schol-pdf-inline-title">ACADEMIC INFORMATION:</p>

                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">Name of School:</span>
                        <input type="text" name="schoolName" class="schol-input-inline" required>
                    </div>
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">School Address:</span>
                        <input type="text" name="schoolAddress" class="schol-input-inline" required>
                    </div>
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">Year/Grade Level:</span>
                        <select name="yearLevel" class="schol-input-inline" required>
                            <option value="">Select</option>
                            <option value="Grade 7">Grade 7</option>
                            <option value="Grade 8">Grade 8</option>
                            <option value="Grade 9">Grade 9</option>
                            <option value="Grade 10">Grade 10</option>
                            <option value="Grade 11">Grade 11</option>
                            <option value="Grade 12">Grade 12</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                        <span class="schol-pdf-inline-label" style="margin-left:24px;">Program/Strand:</span>
                        <input type="text" name="programStrand" class="schol-input-inline">
                    </div>
                </div>

                <!-- SCHOLARSHIP INFO + SUBMITTED REQUIREMENTS side by side -->
                <div class="schol-pdf-section schol-pdf-bottom-section">
                    <div class="schol-pdf-bottom-left">
                        <p class="schol-pdf-inline-title">SCHOLARSHIP INFORMATION:</p>
                        <p class="schol-pdf-purpose-label">Purpose of Scholarship:</p>
                        <div class="schol-pdf-check-list">
                            <label class="schol-pdf-check-item">
                                <input type="checkbox" name="scholarshipPurpose" value="Tuition Fees" class="schol-checkbox">
                                <span class="schol-pdf-checkbox"></span> Tuition Fees
                            </label>
                            <label class="schol-pdf-check-item">
                                <input type="checkbox" name="scholarshipPurpose" value="Books/Equipments" class="schol-checkbox">
                                <span class="schol-pdf-checkbox"></span> Books/Equipments
                            </label>
                            <label class="schol-pdf-check-item">
                                <input type="checkbox" name="scholarshipPurpose" value="Living Expenses" class="schol-checkbox">
                                <span class="schol-pdf-checkbox"></span> Living Expenses
                            </label>
                            <label class="schol-pdf-check-item">
                                <input type="checkbox" name="scholarshipPurpose" value="Others" class="schol-checkbox" id="scholOthersCheck">
                                <span class="schol-pdf-checkbox"></span> Others (Specify):
                                <input type="text" name="scholarshipOthers" class="schol-input-inline" id="scholOthersInput" style="width:100px;margin-left:4px;" disabled>
                            </label>
                        </div>
                    </div>
                    <div class="schol-pdf-bottom-right">
                        <p class="schol-pdf-inline-title">SUBMITTED REQUIREMENTS:</p>

                        <!-- Document upload checkboxes -->
                        <div class="schol-pdf-check-list" style="margin-top:10px;">
                            <label class="schol-pdf-check-item">
                                <input type="checkbox" name="requirements" value="COR" class="schol-checkbox">
                                <span class="schol-pdf-checkbox"></span> COR – CERTIFIED TRUE COPY
                            </label>
                            <label class="schol-pdf-check-item">
                                <input type="checkbox" name="requirements" value="ID" class="schol-checkbox">
                                <span class="schol-pdf-checkbox"></span> PHOTO COPY OF ID (FRONT AND BACK)
                            </label>
                            <label class="schol-pdf-check-item">
                                <input type="checkbox" name="requirements" value="Enrollment" class="schol-checkbox">
                                <span class="schol-pdf-checkbox"></span> CERTIFICATE OF ENROLLMENT
                            </label>
                            <label class="schol-pdf-check-item">
                                <input type="checkbox" name="requirements" value="Indigency" class="schol-checkbox">
                                <span class="schol-pdf-checkbox"></span> BARANGAY CERTIFICATE OF INDIGENCY
                            </label>
                        </div>
                    </div>
                </div>

                <!-- E-Signature Section -->
                <div class="schol-pdf-section">
                    <p class="schol-pdf-inline-title">SIGNATURE:</p>
                    <div class="schol-sig-canvas-wrapper">
                        <canvas id="scholSignaturePad" class="schol-sig-canvas"></canvas>
                        <button type="button" class="schol-btn schol-btn-outline" id="scholClearSignature" style="margin-top:8px;">Clear Signature</button>
                    </div>
                    <input type="hidden" id="scholSignatureData" name="signature">
                    <div class="schol-pdf-sig-line"></div>
                    <p class="schol-pdf-sig-label">Signature over printed name</p>
                </div>
            </form>
        </div>

        <!-- Footer: Submit button -->
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="scholarshipModalCancel">Cancel</button>
            <button type="button" class="schol-btn schol-btn-save" id="scholarshipModalSubmit">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Submit Application
            </button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="schol-toast" id="scholarshipToast">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="scholarshipToastMsg"></span>
</div>
