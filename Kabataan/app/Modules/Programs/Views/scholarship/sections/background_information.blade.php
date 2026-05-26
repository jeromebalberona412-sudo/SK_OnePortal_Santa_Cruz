<section class="sch-app-panel" id="panel-background" data-panel="background">
    <div class="sch-app-card">
        <h2 class="sch-app-card-title">Background Information</h2>
        <div class="sch-app-grid sch-app-grid-3">
            <div class="sch-app-field">
                <label for="motherName">Mother's Full Name <span class="sch-req">*</span></label>
                <input type="text" id="motherName" name="motherName" required placeholder="Full name">
            </div>
            <div class="sch-app-field">
                <label for="motherOccupation">Mother's Occupation <span class="sch-req">*</span></label>
                <input type="text" id="motherOccupation" name="motherOccupation" required placeholder="Occupation">
            </div>
            <div class="sch-app-field">
                <label for="motherContact">Mother's Contact No. <span class="sch-req">*</span></label>
                <input type="tel" id="motherContact" name="motherContact" required placeholder="09XXXXXXXXX">
            </div>
            <div class="sch-app-field">
                <label for="fatherName">Father's Full Name <span class="sch-req">*</span></label>
                <input type="text" id="fatherName" name="fatherName" required placeholder="Full name">
            </div>
            <div class="sch-app-field">
                <label for="fatherOccupation">Father's Occupation <span class="sch-req">*</span></label>
                <input type="text" id="fatherOccupation" name="fatherOccupation" required placeholder="Occupation">
            </div>
            <div class="sch-app-field">
                <label for="fatherContact">Father's Contact No. <span class="sch-req">*</span></label>
                <input type="tel" id="fatherContact" name="fatherContact" required placeholder="09XXXXXXXXX">
            </div>
            <div class="sch-app-field">
                <label for="guardianName">Guardian's Full Name</label>
                <input type="text" id="guardianName" name="guardianName" placeholder="If applicable">
            </div>
            <div class="sch-app-field">
                <label for="guardianRelation">Relation to Guardian</label>
                <input type="text" id="guardianRelation" name="guardianRelation" placeholder="e.g. Brother, Aunt">
            </div>
            <div class="sch-app-field">
                <label for="guardianContact">Guardian's Contact No.</label>
                <input type="tel" id="guardianContact" name="guardianContact" placeholder="09XXXXXXXXX">
            </div>
            <div class="sch-app-field sch-app-field-span-3">
                <label for="familyIncome">Annual Family Gross Income <span class="sch-req">*</span></label>
                <input type="number" id="familyIncome" name="familyIncome" required min="0" placeholder="Amount in PHP">
            </div>
        </div>
        <div class="sch-app-panel-actions">
            <button type="button" class="sch-app-btn-save" data-save-step="background">Save</button>
        </div>
    </div>
</section>
