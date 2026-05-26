<section class="sch-app-panel is-active" id="panel-personal" data-panel="personal">
    <div class="sch-app-card">
        <h2 class="sch-app-card-title">Personal Information</h2>
        <div class="sch-app-grid sch-app-grid-3">
            <div class="sch-app-field">
                <label for="lastName">Last Name <span class="sch-req">*</span></label>
                <input type="text" id="lastName" name="lastName" required placeholder="Last name">
            </div>
            <div class="sch-app-field">
                <label for="firstName">First Name <span class="sch-req">*</span></label>
                <input type="text" id="firstName" name="firstName" required placeholder="First name">
            </div>
            <div class="sch-app-field">
                <label for="middleName">Middle Name</label>
                <input type="text" id="middleName" name="middleName" placeholder="Middle name">
            </div>
            <div class="sch-app-field">
                <label for="suffix">Suffix</label>
                <input type="text" id="suffix" name="suffix" placeholder="Jr., Sr., III">
            </div>
            <div class="sch-app-field">
                <label for="birthdate">Date of Birth <span class="sch-req">*</span></label>
                <input type="date" id="birthdate" name="birthdate" required>
            </div>
            <div class="sch-app-field">
                <label for="age">Age <span class="sch-req">*</span></label>
                <input type="number" id="age" name="age" readonly required placeholder="Auto">
            </div>
            <div class="sch-app-field">
                <label for="gender">Sex <span class="sch-req">*</span></label>
                <select id="gender" name="gender" required>
                    <option value="">Select</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div class="sch-app-field">
                <label for="civilStatus">Civil Status <span class="sch-req">*</span></label>
                <select id="civilStatus" name="civilStatus" required>
                    <option value="">Select</option>
                    <option value="Single">Single</option>
                    <option value="Married">Married</option>
                    <option value="Widowed">Widowed</option>
                </select>
            </div>
            <div class="sch-app-field sch-app-field-span-3">
                <label for="address">Complete Address <span class="sch-req">*</span></label>
                <textarea id="address" name="address" rows="2" required placeholder="Complete address"></textarea>
            </div>
            <div class="sch-app-field">
                <label for="contactNumber">Contact Number <span class="sch-req">*</span></label>
                <input type="tel" id="contactNumber" name="contactNumber" required placeholder="09XXXXXXXXX">
            </div>
            <div class="sch-app-field">
                <label for="email">Email Address <span class="sch-req">*</span></label>
                <input type="email" id="email" name="email" required placeholder="email@example.com">
            </div>
            <div class="sch-app-field">
                <label for="zipCode">Zip Code <span class="sch-req">*</span></label>
                <input type="text" id="zipCode" name="zipCode" required placeholder="e.g. 4009">
            </div>
        </div>
        <div class="sch-app-panel-actions">
            <button type="button" class="sch-app-btn-save" data-save-step="personal">Save</button>
        </div>
    </div>
</section>
