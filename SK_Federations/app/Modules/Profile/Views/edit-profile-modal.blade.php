<!-- Edit Profile Modal -->
<div id="editProfileModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Profile Information</h2>
            <button type="button" class="modal-close" onclick="closeEditModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editProfileForm">
            <div class="modal-body">
                <!-- Profile Picture Upload Section -->
                <div class="profile-picture-upload-section">
                    <div class="upload-avatar-container">
                        <img src="{{ $avatar }}" alt="Profile Picture" class="upload-avatar-preview" id="avatarPreview">
                        <div class="upload-avatar-overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    </div>
                    <div class="upload-avatar-info">
                        <h4>Profile Picture</h4>
                        <p>Click the camera icon to upload a new photo</p>
                        <input type="file" id="profilePictureInput" accept="image/*" style="display: none;" disabled>
                        <button type="button" class="btn-upload-avatar" onclick="document.getElementById('profilePictureInput').click()">
                            <i class="fas fa-upload"></i>
                            Choose Photo
                        </button>
                    </div>
                </div>

                <div class="form-divider"></div>

                <!-- Personal Information -->
                <h4 style="font-size:16px;font-weight:700;color:#0d1b4b;margin:24px 0 16px 0;display:flex;align-items:center;">
                    <i class="fas fa-user" style="margin-right:8px;color:#213F99;"></i>
                    Personal Information
                </h4>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_first_name">First Name <span style="color: #d0242b;">*</span></label>
                        <input type="text" id="edit_first_name" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name ?? '') }}" placeholder="First Name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_middle_name">Middle Name</label>
                        <input type="text" id="edit_middle_name" name="middle_name" class="form-control" value="{{ old('middle_name', $user->middle_name ?? '') }}" placeholder="Middle Name" maxlength="100">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_last_name">Last Name <span style="color: #d0242b;">*</span></label>
                        <input type="text" id="edit_last_name" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name ?? '') }}" placeholder="Last Name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_suffix">Suffix</label>
                        <select id="edit_suffix" name="suffix" class="form-control">
                            <option value="">Select Suffix</option>
                            <option value="Jr." {{ old('suffix', $user->suffix ?? '') === 'Jr.' ? 'selected' : '' }}>Jr.</option>
                            <option value="Sr." {{ old('suffix', $user->suffix ?? '') === 'Sr.' ? 'selected' : '' }}>Sr.</option>
                            <option value="II" {{ old('suffix', $user->suffix ?? '') === 'II' ? 'selected' : '' }}>II</option>
                            <option value="III" {{ old('suffix', $user->suffix ?? '') === 'III' ? 'selected' : '' }}>III</option>
                            <option value="IV" {{ old('suffix', $user->suffix ?? '') === 'IV' ? 'selected' : '' }}>IV</option>
                            <option value="V" {{ old('suffix', $user->suffix ?? '') === 'V' ? 'selected' : '' }}>V</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_sex">Sex <span style="color: #d0242b;">*</span></label>
                        <select id="edit_sex" name="sex" class="form-control" required>
                            <option value="">Select Sex</option>
                            <option value="Male" {{ old('sex', $officialProfile->sex ?? '') === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('sex', $officialProfile->sex ?? '') === 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_date_of_birth">Birthdate <span style="color: #d0242b;">*</span></label>
                        <input type="date" id="edit_date_of_birth" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', optional($officialProfile?->date_of_birth)->format('Y-m-d')) }}" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_age">Age</label>
                        <input type="number" id="edit_age" name="age" class="form-control" value="{{ old('age', $officialProfile->age ?? '') }}" min="0" max="150" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_contact_number">Contact Number <span style="color: #d0242b;">*</span></label>
                        <input type="text" id="edit_contact_number" name="contact_number" class="form-control" value="{{ old('contact_number', $officialProfile->contact_number ?? '') }}" maxlength="20" placeholder="Contact Number" required>
                    </div>
                </div>

                <!-- Position Information -->
                <h4 style="font-size:16px;font-weight:700;color:#0d1b4b;margin:24px 0 16px 0;display:flex;align-items:center;">
                    <i class="fas fa-briefcase" style="margin-right:8px;color:#213F99;"></i>
                    Position Information
                </h4>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_position">Position</label>
                        <select id="edit_position" name="position" class="form-control" disabled style="background-color: #f1f5f9; cursor: not-allowed;">
                            <option value="">Select Position</option>
                            @foreach (\App\Modules\Profile\Models\OfficialProfile::POSITIONS as $position)
                                <option value="{{ $position }}" {{ old('position', $officialProfile->position ?? '') === $position ? 'selected' : '' }}>{{ $position }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Status</label>
                        <input type="text" id="edit_status" name="status" class="form-control" value="{{ old('status', $user->status ?? '') }}" readonly style="background-color: #f1f5f9; cursor: not-allowed;">
                    </div>
                </div>

                <!-- Address -->
                <h4 style="font-size:16px;font-weight:700;color:#0d1b4b;margin:24px 0 16px 0;display:flex;align-items:center;">
                    <i class="fas fa-map-marker-alt" style="margin-right:8px;color:#213F99;"></i>
                    Address
                </h4>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_region">Region</label>
                        <input type="text" id="edit_region" name="region" class="form-control" value="{{ old('region', $officialProfile->region ?? 'IV-A CALABARZON') }}" readonly style="background-color: #f1f5f9; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label for="edit_province">Province</label>
                        <input type="text" id="edit_province" name="province" class="form-control" value="{{ old('province', $officialProfile->province ?? 'Laguna') }}" readonly style="background-color: #f1f5f9; cursor: not-allowed;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_municipality">Municipality</label>
                        <input type="text" id="edit_municipality" name="municipality" class="form-control" value="{{ old('municipality', $officialProfile->municipality ?? 'Santa Cruz') }}" readonly style="background-color: #f1f5f9; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label for="edit_barangay_id">Barangay</label>
                        <select id="edit_barangay_id" name="barangay_id" class="form-control" disabled style="background-color: #f1f5f9; cursor: not-allowed;">
                            <option value="">Select Barangay</option>
                            @foreach ($barangays as $barangay)
                                <option value="{{ $barangay->id }}" {{ (string) $selectedBarangayId === (string) $barangay->id ? 'selected' : '' }}>{{ $barangay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Term Information -->
                <h4 style="font-size:16px;font-weight:700;color:#0d1b4b;margin:24px 0 16px 0;display:flex;align-items:center;">
                    <i class="fas fa-calendar-alt" style="margin-right:8px;color:#213F99;"></i>
                    Term Information
                </h4>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_term_start">Term Start</label>
                        <input type="date" id="edit_term_start" name="term_start" class="form-control" value="{{ old('term_start', optional($officialProfile?->term_start)->format('Y-m-d')) }}" readonly style="background-color: #f1f5f9; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label for="edit_term_end">Term End</label>
                        <input type="date" id="edit_term_end" name="term_end" class="form-control" value="{{ old('term_end', optional($officialProfile?->term_end)->format('Y-m-d')) }}" readonly style="background-color: #f1f5f9; cursor: not-allowed;">
                    </div>
                </div>

                <!-- Account -->
                <h4 style="font-size:16px;font-weight:700;color:#0d1b4b;margin:24px 0 16px 0;display:flex;align-items:center;">
                    <i class="fas fa-envelope" style="margin-right:8px;color:#213F99;"></i>
                    Account
                </h4>

                <div class="form-group">
                    <label for="edit_email">Email Address</label>
                    <input type="email" id="edit_email" name="email" class="form-control" value="{{ old('email', $user->email) }}" readonly style="background-color: #f1f5f9; cursor: not-allowed;">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="success-modal">
    <div class="success-modal-content">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>Profile Updated Successfully!</h2>
        <p>Your profile information has been saved.</p>
    </div>
</div>

<!-- Password Change Success Modal -->
<div id="passwordSuccessModal" class="success-modal">
    <div class="success-modal-content">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>Password Changed Successfully!</h2>
        <p>Please log in again with your new password.</p>
    </div>
</div>

<!-- Forgot Password Confirmation Modal -->
<div id="forgotPasswordModal" class="modal">
    <div class="modal-content forgot-password-modal-content">
        <button type="button" class="modal-close-icon" onclick="closeForgotPasswordModal()">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-body" style="padding: 48px 40px;">
            <div style="text-align: center;">
                <div class="warning-icon-wrapper">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <h2 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 24px 0 12px 0;">
                    Reset Password
                </h2>
                <p style="font-size: 16px; color: #64748b; line-height: 1.6; margin: 0 0 32px 0;">
                    You will be logged out to reset your password.<br>Do you want to continue?
                </p>
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button type="button" class="btn-cancel-modern" onclick="closeForgotPasswordModal()">
                        Cancel
                    </button>
                    <button type="button" class="btn-continue-modern" onclick="proceedToForgotPassword()">
                        <i class="fas fa-arrow-right" style="margin-left: 8px;"></i>
                        Yes, Continue
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
