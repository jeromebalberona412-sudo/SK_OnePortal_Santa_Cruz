<section class="sch-app-panel" id="panel-additional" data-panel="additional">
    <div class="sch-app-card">
        <h2 class="sch-app-card-title">Additional Information</h2>
        <div class="sch-app-grid sch-app-grid-3">
            <div class="sch-app-field sch-app-field-span-2">
                <label for="course">Course <span class="sch-req">*</span></label>
                <input type="text" id="course" name="course" required placeholder="Course / program name">
            </div>
            <div class="sch-app-field">
                <label for="courseAbbr">Course Abbreviation <span class="sch-req">*</span></label>
                <input type="text" id="courseAbbr" name="courseAbbr" required placeholder="e.g. BSIT">
            </div>
            <div class="sch-app-field">
                <label for="yearLevel">Year Level <span class="sch-req">*</span> <small>(based on attached Registration Form)</small></label>
                <select id="yearLevel" name="yearLevel" required>
                    <option value="">Select year level</option>
                    <option value="1ST YEAR">1ST YEAR</option>
                    <option value="2ND YEAR">2ND YEAR</option>
                    <option value="3RD YEAR">3RD YEAR</option>
                    <option value="4TH YEAR">4TH YEAR</option>
                </select>
            </div>
            <div class="sch-app-field">
                <label for="unitsEnrolled">Units Enrolled <span class="sch-req">*</span> <small>(based on attached Registration Form)</small></label>
                <input type="number" id="unitsEnrolled" name="unitsEnrolled" required min="1" placeholder="Units">
            </div>
            <div class="sch-app-field">
                <label for="expectedGradYear">Expected Year of Graduation <span class="sch-req">*</span></label>
                <input type="text" id="expectedGradYear" name="expectedGradYear" required maxlength="4" placeholder="YYYY">
            </div>
            <div class="sch-app-field">
                <label for="isGraduating">Graduating? <span class="sch-req">*</span></label>
                <select id="isGraduating" name="isGraduating" required>
                    <option value="">Select</option>
                    <option value="NO">NO</option>
                    <option value="YES">YES</option>
                </select>
            </div>
            <div class="sch-app-field">
                <label for="gradSemester">Semester of Graduation</label>
                <select id="gradSemester" name="gradSemester">
                    <option value="N/A">N/A</option>
                    <option value="1ST SEMESTER">1ST SEMESTER</option>
                    <option value="2ND SEMESTER">2ND SEMESTER</option>
                </select>
            </div>
            <div class="sch-app-field">
                <label for="gpa">GPA / General Average <span class="sch-req">*</span></label>
                <input type="text" id="gpa" name="gpa" required placeholder="e.g. 1.75 or 92%">
            </div>
            <div class="sch-app-field sch-app-field-span-2">
                <label for="schoolName">School Name <span class="sch-req">*</span></label>
                <input type="text" id="schoolName" name="schoolName" required placeholder="School name">
            </div>
            <div class="sch-app-field">
                <label for="schoolAbbr">School Abbreviation <span class="sch-req">*</span></label>
                <input type="text" id="schoolAbbr" name="schoolAbbr" required placeholder="e.g. LSPU">
            </div>
            <div class="sch-app-field sch-app-field-span-3">
                <label for="schoolAddress">School Address <span class="sch-req">*</span></label>
                <textarea id="schoolAddress" name="schoolAddress" rows="2" required placeholder="Complete school address"></textarea>
            </div>
            <div class="sch-app-field sch-app-field-span-3">
                <label for="otherScholarship">Are you a recipient of any other government-funded financial assistance / scholarship program? <span class="sch-req">*</span></label>
                <select id="otherScholarship" name="otherScholarship" required>
                    <option value="">Select</option>
                    <option value="NO">NO</option>
                    <option value="YES">YES</option>
                </select>
            </div>
            <div class="sch-app-field sch-app-field-span-3">
                <label for="essay">Reason for Applying (Essay) <span class="sch-req">*</span></label>
                <textarea id="essay" name="essay" rows="5" required minlength="50" placeholder="Share your story and reason for applying (minimum 50 characters)..."></textarea>
            </div>
        </div>
        <div class="sch-app-panel-actions sch-app-panel-actions-split">
            <button type="button" class="sch-app-btn-back" data-prev="background">← Previous</button>
            <button type="button" class="sch-app-btn-next" data-next="requirements">Next: Requirements →</button>
        </div>
    </div>
</section>
