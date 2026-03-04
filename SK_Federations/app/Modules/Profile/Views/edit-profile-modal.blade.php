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
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_last_name">Last Name <span style="color: #d0242b;">*</span></label>
                        <input type="text" id="edit_last_name" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name ?? '') }}" placeholder="e.g., Dela Cruz" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_first_name">First Name <span style="color: #d0242b;">*</span></label>
                        <input type="text" id="edit_first_name" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name ?? '') }}" placeholder="e.g., Juan" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_middle_initial">Middle Initial</label>
                        <input type="text" id="edit_middle_initial" name="middle_initial" class="form-control" value="{{ old('middle_initial', $user->middle_initial ?? '') }}" placeholder="e.g., P">
                    </div>
                    <div class="form-group">
                        <label for="edit_suffix">Suffix (Optional)</label>
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

                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" class="form-control" value="{{ old('email', $user->email) }}" readonly style="background-color: #f1f5f9; cursor: not-allowed;">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_date_of_birth">Date of Birth</label>
                        <input type="date" id="edit_date_of_birth" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', optional($officialProfile?->date_of_birth)->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group">
                        <label for="edit_age">Age</label>
                        <input type="number" id="edit_age" name="age" class="form-control" value="{{ old('age', $officialProfile->age ?? '') }}" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_contact_number">Contact Number <span style="color: #d0242b;">*</span></label>
                        <input type="text" id="edit_contact_number" name="contact_number" class="form-control" value="{{ old('contact_number', $officialProfile->contact_number ?? '') }}" maxlength="11" pattern="[0-9]{11}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_position">Position</label>
                        <select id="edit_position" name="position" class="form-control" disabled style="background-color: #f1f5f9; cursor: not-allowed;">
                            <option value="">Select Position</option>
                            @foreach (\App\Modules\Profile\Models\OfficialProfile::POSITIONS as $position)
                                <option value="{{ $position }}" {{ old('position', $officialProfile->position ?? '') === $position ? 'selected' : '' }}>{{ $position }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_municipality">Municipality</label>
                        <input type="text" id="edit_municipality" name="municipality" class="form-control" value="{{ old('municipality', $officialProfile->municipality ?? 'Santa Cruz') }}" readonly style="background-color: #f1f5f9; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label for="edit_region">Region</label>
                        <input type="text" id="edit_region" name="region" class="form-control" value="{{ old('region', $officialProfile->region ?? 'IV-A CALABARZON') }}" readonly style="background-color: #f1f5f9; cursor: not-allowed;">
                    </div>
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
